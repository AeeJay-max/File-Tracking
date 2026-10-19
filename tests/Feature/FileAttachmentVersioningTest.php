<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\FileAttachment;
use App\Models\FileRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileAttachmentVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploading_updated_attachment_moves_side_by_side_without_overwriting()
    {
        Storage::fake('private');

        $recordsDept = Department::create(['name' => 'Records Office', 'code' => 'REC', 'is_active' => true]);
        $financeDept = Department::create(['name' => 'Finance Department', 'code' => 'FIN', 'is_active' => true]);

        $recordsAdmin = User::factory()->create([
            'department_id' => $recordsDept->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $financeOfficer = User::factory()->create([
            'department_id' => $financeDept->id,
            'role' => 'user',
            'is_active' => true,
        ]);

        // 1. Initial file created with original attachment
        $file = FileRecord::create([
            'file_name' => 'Annual Audit Plan',
            'file_number' => 'AUD/2026/01',
            'department_id' => $recordsDept->id,
            'current_department_id' => $financeDept->id,
            'current_user_id' => $financeOfficer->id,
            'created_by' => $recordsAdmin->id,
            'status' => 'active',
            'is_urgent' => true,
        ]);

        $originalFile = UploadedFile::fake()->create('original_plan.pdf', 500, 'application/pdf');
        $storedName1 = 'original.pdf';
        $path1 = $originalFile->storeAs('files/'.$file->uuid, $storedName1, 'private');

        $file->update([
            'attachment_path' => $path1,
            'attachment_name' => 'original_plan.pdf',
            'attachment_mime' => 'application/pdf',
        ]);

        $att1 = FileAttachment::create([
            'file_record_id' => $file->id,
            'user_id' => $recordsAdmin->id,
            'attachment_path' => $path1,
            'attachment_name' => 'original_plan.pdf',
            'attachment_mime' => 'application/pdf',
            'version' => 1,
            'version_label' => 'Original File',
            'is_latest' => true,
            'notes' => 'Original document',
        ]);

        // 2. Actioning officer attaches updated file
        $updatedFile = UploadedFile::fake()->create('updated_plan_v2.pdf', 800, 'application/pdf');

        $response = $this->actingAs($financeOfficer)->post(route('files.attachments.upload', $file->uuid), [
            'attachment' => $updatedFile,
            'notes' => 'Updated figures based on finance review',
        ]);

        $response->assertRedirect(route('files.show', $file->uuid));

        // 3. Verify side-by-side versions in database
        $this->assertDatabaseCount('file_attachments', 2);

        $att1->refresh();
        $this->assertFalse((bool) $att1->is_latest, 'Original file should no longer be marked as latest');
        $this->assertEquals(1, $att1->version);
        Storage::disk('private')->assertExists($att1->attachment_path);

        $att2 = FileAttachment::where('file_record_id', $file->id)->where('version', 2)->first();
        $this->assertNotNull($att2);
        $this->assertTrue((bool) $att2->is_latest, 'Updated file should be marked as latest');
        $this->assertEquals('Version 2 (Updated)', $att2->version_label);
        Storage::disk('private')->assertExists($att2->attachment_path);

        // Verify file_records pointer is updated to latest attachment
        $file->refresh();
        $this->assertEquals($att2->attachment_path, $file->attachment_path);

        // 4. Download original and updated attachments independently
        $dlOrig = $this->actingAs($financeOfficer)->get(route('files.attachments.download', [$file->uuid, $att1->uuid]));
        $dlOrig->assertOk();

        $dlUpdated = $this->actingAs($financeOfficer)->get(route('files.attachments.download', [$file->uuid, $att2->uuid]));
        $dlUpdated->assertOk();
    }

    public function test_urgent_flag_is_cleared_when_file_returns_to_records_department()
    {
        $recordsDept = Department::create(['name' => 'Records Office', 'code' => 'REC', 'is_active' => true]);
        $financeDept = Department::create(['name' => 'Finance Department', 'code' => 'FIN', 'is_active' => true]);

        $recordsAdmin = User::factory()->create([
            'department_id' => $recordsDept->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $financeAdmin = User::factory()->create([
            'department_id' => $financeDept->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $file = FileRecord::create([
            'file_name' => 'Urgent Procurement Case',
            'file_number' => 'MOSRAC/PROC/2026/05',
            'department_id' => $recordsDept->id,
            'current_department_id' => $financeDept->id,
            'current_user_id' => $financeAdmin->id,
            'created_by' => $recordsAdmin->id,
            'status' => 'active',
            'is_urgent' => true,
        ]);

        $this->assertTrue((bool) $file->is_urgent);

        // Return file back to Records department
        $response = $this->actingAs($financeAdmin)->post(route('files.adminReturnRecords', $file->uuid), [
            'carried_by' => 'Messenger Alex',
            'remarks' => 'Returning processed file to Records',
        ]);

        $response->assertRedirect(route('files.index'));

        $file->refresh();
        $this->assertFalse((bool) $file->is_urgent, 'Urgent flag must be cleared when returned to Records');
        $this->assertEquals($recordsDept->id, $file->current_department_id);
    }
}
