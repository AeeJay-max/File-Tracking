<?php

use App\Models\Department;
use App\Models\FileRecord;
use App\Models\User;
use Tests\TestCase;

function makeTransferUser(Department $department, string $role = 'user'): User
{
    return User::factory()->create([
        'role' => $role,
        'department_id' => $department->id,
        'is_active' => true,
        'can_create_file' => $role === 'user',
    ]);
}

it('allows only the current owner to transfer through direct and department ownership changes', function () {
    /** @var TestCase $this */
    $sourceDepartment = Department::factory()->create(['name' => 'Source Department']);
    $financeDepartment = Department::factory()->create(['name' => 'Finance Department']);
    $accountsDepartment = Department::factory()->create(['name' => 'Accounts Department']);

    $userA = makeTransferUser($sourceDepartment);
    $userB = makeTransferUser($sourceDepartment);
    $financeAdmin = makeTransferUser($financeDepartment, 'admin');
    $financeUser = makeTransferUser($financeDepartment);
    $accountsAdmin = makeTransferUser($accountsDepartment, 'admin');

    $file = FileRecord::create([
        'department_id' => $sourceDepartment->id,
        'current_department_id' => $sourceDepartment->id,
        'created_by' => $userA->id,
        'current_user_id' => $userA->id,
        'file_name' => 'Ownership Test',
        'file_number' => 'OWN-001',
        'status' => 'active',
    ]);

    $this->actingAs($userA)->post(route('files.transfer.store'), [
        'file_record_uuid' => $file->uuid,
        'destination_type' => 'same',
        'to_user_id' => $userB->id,
    ])->assertRedirect(route('files.index'));

    expect($file->fresh()->current_user_id)->toBe($userB->id);

    $this->actingAs($userA)
        ->get(route('files.transfer.create', $file->uuid))
        ->assertForbidden();

    $this->actingAs($userB)->post(route('files.transfer.store'), [
        'file_record_uuid' => $file->uuid,
        'destination_type' => 'other',
        'department_id' => $financeDepartment->id,
    ])->assertRedirect(route('files.index'));

    $file->refresh();
    // Cross-department transfer: current_user_id becomes null (pending_assignment)
    // and current_department_id updates to the destination.
    // department_id ALWAYS retains the ORIGIN department — it never changes.
    expect($file->current_user_id)->toBeNull()
        ->and($file->current_department_id)->toBe($financeDepartment->id)
        ->and($file->department_id)->toBe($sourceDepartment->id)
        ->and($file->status)->toBe('pending_assignment');

    // Simulate the finance admin being assigned the file by their admin
    $file->update(['current_user_id' => $financeAdmin->id, 'status' => 'active']);

    $this->actingAs($financeAdmin)->post(route('files.transfer.store'), [
        'file_record_uuid' => $file->uuid,
        'destination_type' => 'same',
        'to_user_id' => $financeUser->id,
    ])->assertRedirect(route('files.index'));

    expect($file->fresh()->current_user_id)->toBe($financeUser->id);

    $this->actingAs($financeAdmin)
        ->get(route('files.transfer.create', $file->uuid))
        ->assertForbidden();

    $this->actingAs($financeUser)->post(route('files.transfer.store'), [
        'file_record_uuid' => $file->uuid,
        'destination_type' => 'other',
        'department_id' => $accountsDepartment->id,
    ])->assertRedirect(route('files.index'));

    $file->refresh();
    // Same pattern: cross-dept sets current_user_id=null, current_department_id=destination
    expect($file->current_user_id)->toBeNull()
        ->and($file->current_department_id)->toBe($accountsDepartment->id)
        ->and($file->department_id)->toBe($sourceDepartment->id)
        ->and($file->status)->toBe('pending_assignment');

    // Simulate accounts admin being assigned
    $file->update(['current_user_id' => $accountsAdmin->id, 'status' => 'active']);

    $this->actingAs($accountsAdmin)
        ->get(route('files.transfer.create', $file->uuid))
        ->assertOk();
});

it('does not let a super admin transfer a file they do not own', function () {
    /** @var TestCase $this */
    $department = Department::factory()->create();
    $owner = makeTransferUser($department);
    $superAdmin = makeTransferUser($department, 'super_admin');

    $file = FileRecord::create([
        'department_id' => $department->id,
        'current_department_id' => $department->id,
        'created_by' => $owner->id,
        'current_user_id' => $owner->id,
        'file_name' => 'Super Admin Ownership Test',
        'file_number' => 'OWN-002',
        'status' => 'active',
    ]);

    $this->actingAs($superAdmin)
        ->get(route('files.transfer.create', $file->uuid))
        ->assertForbidden();
});
