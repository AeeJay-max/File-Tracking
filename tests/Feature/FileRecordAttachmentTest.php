<?php

use App\Models\Department;
use App\Models\FileRecord;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * File creation requires: file_number (manual), file_name, department_id, can_create_file=true.
 * Any user with can_create_file may create a file for any department.
 */
it('stores a file record with an attachment', function () {
    Storage::fake('private');

    $department = Department::factory()->create();

    $user = User::factory()->create([
        'role' => 'user',
        'department_id' => $department->id,
        'can_create_file' => true,
    ]);

    $response = $this->actingAs($user)->post(route('files.store'), [
        'file_number' => 'TEST/2026/001',
        'file_name' => 'Contract Document',
        'department_id' => $department->id,
        'remarks' => 'Initial upload',
        'attachment' => UploadedFile::fake()->create(
            'contract.docx', 100,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ),
    ]);

    $response->assertRedirect(route('files.index'));

    $this->assertDatabaseHas('file_records', [
        'file_name' => 'Contract Document',
        'file_number' => 'TEST/2026/001',
        'remarks' => 'Initial upload',
        'department_id' => $department->id,
    ]);

    $file = FileRecord::where('department_id', $department->id)
        ->where('file_number', 'TEST/2026/001')
        ->first();
    expect($file)->not->toBeNull();
    Storage::disk('private')->assertExists($file->attachment_path);
});

it('downloads a file record attachment', function () {
    Storage::fake('private');

    $department = Department::factory()->create();

    $user = User::factory()->create([
        'role' => 'user',
        'department_id' => $department->id,
        'can_create_file' => true,
    ]);

    $attachment = UploadedFile::fake()->create('report.pdf', 120, 'application/pdf');

    $this->actingAs($user)->post(route('files.store'), [
        'file_number' => 'RPT/2026/001',
        'file_name' => 'Report',
        'department_id' => $department->id,
        'remarks' => 'Upload for download',
        'attachment' => $attachment,
    ]);

    $file = FileRecord::where('department_id', $department->id)
        ->where('file_number', 'RPT/2026/001')
        ->first();
    expect($file)->not->toBeNull();

    $download = $this->actingAs($user)->get(route('files.download', $file->uuid));
    $download->assertStatus(200);
    $download->assertHeader('content-disposition');
});

it('blocks duplicate file numbers in the same department', function () {
    $department = Department::factory()->create();

    $user = User::factory()->create([
        'role' => 'user',
        'department_id' => $department->id,
        'can_create_file' => true,
    ]);

    // Create first file
    $this->actingAs($user)->post(route('files.store'), [
        'file_number' => 'DUPTEST/2026/001',
        'file_name' => 'Original',
        'department_id' => $department->id,
    ]);

    // Attempt duplicate file number — must fail validation
    $response = $this->actingAs($user)->post(route('files.store'), [
        'file_number' => 'DUPTEST/2026/001',
        'file_name' => 'Duplicate',
        'department_id' => $department->id,
    ]);

    $response->assertSessionHasErrors('file_number');
    expect(FileRecord::where('file_name', 'Duplicate')->count())->toBe(0);
});

it('allows the same file number in different departments', function () {
    $deptA = Department::factory()->create(['name' => 'Department A']);
    $deptB = Department::factory()->create(['name' => 'Department B']);

    $user = User::factory()->create([
        'role' => 'user',
        'department_id' => $deptA->id,
        'can_create_file' => true,
    ]);

    $this->actingAs($user)->post(route('files.store'), [
        'file_number' => '1001',
        'file_name' => 'File A',
        'department_id' => $deptA->id,
    ])->assertRedirect(route('files.index'));

    $this->actingAs($user)->post(route('files.store'), [
        'file_number' => '1001',
        'file_name' => 'File B',
        'department_id' => $deptB->id,
    ])->assertRedirect(route('files.index'));

    expect(FileRecord::where('file_number', '1001')->count())->toBe(2);
});

it('allows creating a file for any department', function () {
    $deptA = Department::factory()->create(['name' => 'Dept A']);
    $deptB = Department::factory()->create(['name' => 'Dept B']);

    // User is in deptA but creates a file for deptB
    $user = User::factory()->create([
        'role' => 'user',
        'department_id' => $deptA->id,
        'can_create_file' => true,
    ]);

    $response = $this->actingAs($user)->post(route('files.store'), [
        'file_number' => 'CROSS/DEPT/001',
        'file_name' => 'Cross-Dept File',
        'department_id' => $deptB->id,
    ]);

    $response->assertRedirect(route('files.index'));
    $this->assertDatabaseHas('file_records', [
        'file_name' => 'Cross-Dept File',
        'department_id' => $deptB->id,
    ]);
});
