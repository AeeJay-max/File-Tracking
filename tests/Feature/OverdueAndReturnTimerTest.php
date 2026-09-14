<?php

use App\Models\Department;
use App\Models\FileRecord;
use App\Models\User;
use App\Notifications\FileOverdueNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

function makeOverdueTestUser(Department $dept, string $role = 'user', ?string $designationName = null): User
{
    $desig = $designationName
        ? \App\Models\Designation::firstOrCreate(['name' => $designationName, 'department_id' => $dept->id])
        : null;

    return User::factory()->create([
        'department_id' => $dept->id,
        'role' => $role,
        'designation_id' => $desig?->id,
        'is_active' => true,
    ]);
}

it('clears return_deadline timer when a file is returned to Records department', function () {
    /** @var TestCase $this */
    $recDept = Department::firstOrCreate(['code' => 'REC'], ['name' => 'Records Department', 'is_active' => true]);
    $financeDept = Department::factory()->create(['name' => 'Finance Department']);

    $recAdmin = makeOverdueTestUser($recDept, 'admin');
    $financeAdmin = makeOverdueTestUser($financeDept, 'admin');

    $file = FileRecord::create([
        'file_number' => 'TIMER-001',
        'file_name' => 'Timer Test File',
        'department_id' => $recDept->id,
        'current_department_id' => $financeDept->id,
        'created_by' => $recAdmin->id,
        'current_user_id' => $financeAdmin->id,
        'status' => 'active',
        'return_deadline' => now()->addHours(2),
        'has_permsec_reviewed' => true,
    ]);

    expect($file->return_deadline)->not->toBeNull();

    // Finance Admin returns file back to Records Admin
    $this->actingAs($financeAdmin)
        ->post(route('files.transfer.store'), [
            'file_record_uuid' => $file->uuid,
            'destination_type' => 'other',
            'department_id' => $recDept->id,
        ])
        ->assertRedirect();

    $file->refresh();

    // File returned to Records — timer countdown MUST STOP (return_deadline is null)
    expect($file->return_deadline)->toBeNull()
        ->and($file->current_department_id)->toBe($recDept->id);
});

it('allows Records department to assign a new return timeframe on new dispatch', function () {
    /** @var TestCase $this */
    $recDept = Department::firstOrCreate(['code' => 'REC'], ['name' => 'Records Department', 'is_active' => true]);
    $financeDept = Department::factory()->create(['name' => 'Finance Department']);

    $recAdmin = makeOverdueTestUser($recDept, 'admin');

    $file = FileRecord::create([
        'file_number' => 'DISPATCH-001',
        'file_name' => 'Dispatch Timer File',
        'department_id' => $recDept->id,
        'current_department_id' => $recDept->id,
        'created_by' => $recAdmin->id,
        'current_user_id' => $recAdmin->id,
        'status' => 'active',
        'return_deadline' => null,
        'has_permsec_reviewed' => true,
    ]);

    // Records Admin dispatches file to Finance Dept with 120 minutes return limit
    $this->actingAs($recAdmin)
        ->post(route('files.transfer.store'), [
            'file_record_uuid' => $file->uuid,
            'destination_type' => 'other',
            'department_id' => $financeDept->id,
            'return_minutes' => 120,
        ])
        ->assertRedirect();

    $file->refresh();

    expect($file->return_deadline)->not->toBeNull()
        ->and($file->current_department_id)->toBe($financeDept->id);
});

it('detects overdue files held > 8 hours in non-records departments and pings holder and HOD', function () {
    /** @var TestCase $this */
    Notification::fake();

    $recDept = Department::firstOrCreate(['code' => 'REC'], ['name' => 'Records Department', 'is_active' => true]);
    $financeDept = Department::factory()->create(['name' => 'Finance Department']);

    $recAdmin = makeOverdueTestUser($recDept, 'admin');
    $financeHod = makeOverdueTestUser($financeDept, 'admin');
    $financeOfficer = makeOverdueTestUser($financeDept, 'user');

    $file = FileRecord::create([
        'file_number' => 'OVERDUE-8H',
        'file_name' => 'Overdue Test File',
        'department_id' => $recDept->id,
        'current_department_id' => $financeDept->id,
        'created_by' => $recAdmin->id,
        'current_user_id' => $financeOfficer->id,
        'status' => 'active',
    ]);

    $movement = \App\Models\FileMovement::create([
        'file_id' => $file->id,
        'from_user' => $recAdmin->id,
        'to_user' => $financeOfficer->id,
        'from_department' => $recDept->id,
        'to_department' => $financeDept->id,
        'action' => 'transferred',
    ]);
    \Illuminate\Support\Facades\DB::table('file_movements')
        ->where('id', $movement->id)
        ->update(['created_at' => now()->subHours(9)]);

    $file->refresh();
    expect($file->isOverdue())->toBeTrue();

    // Records Admin pings the file
    $this->actingAs($recAdmin)
        ->post(route('files.pingOverdue', $file->uuid))
        ->assertRedirect();

    // Verify overdue notification sent to BOTH current holder (officer) and HOD
    Notification::assertSentTo($financeOfficer, FileOverdueNotification::class);
    Notification::assertSentTo($financeHod, FileOverdueNotification::class);
});

it('prevents marking operations completed if the file is not currently in the Records department', function () {
    /** @var TestCase $this */
    $recDept = Department::firstOrCreate(['code' => 'REC'], ['name' => 'Records Department', 'is_active' => true]);
    $financeDept = Department::factory()->create(['name' => 'Finance Department']);

    $recAdmin = makeOverdueTestUser($recDept, 'admin');
    $financeAdmin = makeOverdueTestUser($financeDept, 'admin');

    $file = FileRecord::create([
        'file_number' => 'CUSTODY-001',
        'file_name' => 'Custody Test File',
        'department_id' => $recDept->id,
        'current_department_id' => $financeDept->id,
        'created_by' => $recAdmin->id,
        'current_user_id' => $financeAdmin->id,
        'status' => 'active',
    ]);

    // Records Admin attempts to mark operations done while file is with Finance Dept -> MUST FAIL
    $this->actingAs($recAdmin)
        ->post(route('files.completeOperations', $file->uuid), ['remarks' => 'Done'])
        ->assertSessionHas('error');

    expect($file->fresh()->status)->toBe('active');

    // Return file to Records
    $file->update([
        'current_department_id' => $recDept->id,
        'current_user_id' => $recAdmin->id,
    ]);

    // Now Records Admin marks operations done -> MUST SUCCEED
    $this->actingAs($recAdmin)
        ->post(route('files.completeOperations', $file->uuid), ['remarks' => 'Done in Records'])
        ->assertSessionHas('success');

    expect($file->fresh()->status)->toBe('completed');
});
