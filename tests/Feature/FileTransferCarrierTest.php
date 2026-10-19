<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileTransferCarrierTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_to_department_records_carried_by()
    {
        $recordsDept = Department::create(['name' => 'Records Office', 'code' => 'REC', 'is_active' => true]);
        $targetDept = Department::create(['name' => 'Finance Department', 'code' => 'FIN', 'is_active' => true]);

        $recordsAdmin = User::factory()->create([
            'department_id' => $recordsDept->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $file = FileRecord::create([
            'file_name' => 'Budget Review 2026',
            'file_number' => 'MOSRAC/REC/2026/001',
            'origin_department_id' => $recordsDept->id,
            'department_id' => $recordsDept->id,
            'current_department_id' => $recordsDept->id,
            'current_user_id' => $recordsAdmin->id,
            'created_by' => $recordsAdmin->id,
            'status' => 'active',
            'has_permsec_reviewed' => true,
        ]);

        $response = $this->actingAs($recordsAdmin)->post(route('files.transfer.store'), [
            'file_record_uuid' => $file->uuid,
            'destination_type' => 'department',
            'department_id' => $targetDept->id,
            'carried_by' => 'John Messenger',
            'remarks' => 'Urgent review required',
        ]);

        $response->assertRedirect(route('files.index'));

        $this->assertDatabaseHas('file_transfers', [
            'file_id' => $file->id,
            'carried_by' => 'John Messenger',
        ]);

        $this->assertDatabaseHas('file_movements', [
            'file_id' => $file->id,
            'to_department' => $targetDept->id,
            'carried_by' => 'John Messenger',
        ]);

        $latestMovement = FileMovement::where('file_id', $file->id)->latest('id')->first();
        $this->assertStringContainsString('Sent to Finance Department and the one who carried it was John Messenger.', $latestMovement->remarks);
    }

    public function test_transfer_to_permsec_records_carried_by()
    {
        $recordsDept = Department::create(['name' => 'Records Office', 'code' => 'REC', 'is_active' => true]);
        $executiveDept = Department::create(['name' => 'Permanent Secretary Office', 'code' => 'PSO', 'is_active' => true]);

        $permSecDesig = Designation::create(['name' => 'Permanent Secretary', 'department_id' => $executiveDept->id]);

        $recordsAdmin = User::factory()->create([
            'department_id' => $recordsDept->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $permSec = User::factory()->create([
            'department_id' => $executiveDept->id,
            'designation_id' => $permSecDesig->id,
            'role' => 'user',
            'is_active' => true,
        ]);

        $file = FileRecord::create([
            'file_name' => 'Ministry Policy Draft',
            'file_number' => 'MOSRAC/REC/2026/002',
            'origin_department_id' => $recordsDept->id,
            'department_id' => $recordsDept->id,
            'current_department_id' => $recordsDept->id,
            'current_user_id' => $recordsAdmin->id,
            'created_by' => $recordsAdmin->id,
            'status' => 'active',
            'has_permsec_reviewed' => false,
        ]);

        $response = $this->actingAs($recordsAdmin)->post(route('files.transfer.store'), [
            'file_record_uuid' => $file->uuid,
            'destination_type' => 'user',
            'to_user_id' => $permSec->id,
            'carried_by' => 'Ainos Temu',
            'remarks' => 'Submitted for review',
        ]);

        $response->assertRedirect(route('files.index'));

        $this->assertDatabaseHas('file_transfers', [
            'file_id' => $file->id,
            'carried_by' => 'Ainos Temu',
        ]);

        $this->assertDatabaseHas('file_movements', [
            'file_id' => $file->id,
            'to_user' => $permSec->id,
            'carried_by' => 'Ainos Temu',
        ]);

        $latestMovement = FileMovement::where('file_id', $file->id)->latest('id')->first();
        $this->assertStringContainsString('Sent to the Permanent Secretary and the one who carried it was Ainos Temu.', $latestMovement->remarks);
    }
}
