<?php

use App\Models\Department;
use App\Models\FileRecord;

/**
 * Public File Upload has been removed from this system.
 * Public users can now search files using the Public File Search feature.
 *
 * @see PublicFileSearchController
 * @see /public/file-search
 */
it('public file search page loads successfully', function () {
    $response = $this->get(route('public.file.search'));
    $response->assertStatus(200);
});

it('returns no result for nonexistent file number', function () {
    $response = $this->get(route('public.file.search.result', ['file_number' => 'INVALID-00000']));
    $response->assertSessionHas('search_error');
});

it('asks for department when multiple files share the same file number', function () {
    $deptA = Department::factory()->create(['name' => 'Department A']);
    $deptB = Department::factory()->create(['name' => 'Department B']);

    FileRecord::create([
        'department_id' => $deptA->id,
        'current_department_id' => $deptA->id,
        'file_number' => '1001',
        'file_name' => 'File A',
        'status' => 'active',
    ]);

    FileRecord::create([
        'department_id' => $deptB->id,
        'current_department_id' => $deptB->id,
        'file_number' => '1001',
        'file_name' => 'File B',
        'status' => 'active',
    ]);

    $response = $this->get(route('public.file.search.result', ['file_number' => '1001']));
    $response->assertSessionHas('search_error');
    $response->assertSessionHas('department_choices');
});

it('returns the correct file when department is provided with the file number', function () {
    $deptA = Department::factory()->create(['name' => 'Department A']);
    $deptB = Department::factory()->create(['name' => 'Department B']);

    FileRecord::create([
        'department_id' => $deptA->id,
        'current_department_id' => $deptA->id,
        'file_number' => '1001',
        'file_name' => 'File A',
        'status' => 'active',
    ]);

    FileRecord::create([
        'department_id' => $deptB->id,
        'current_department_id' => $deptB->id,
        'file_number' => '1001',
        'file_name' => 'File B',
        'status' => 'active',
    ]);

    $response = $this->get(route('public.file.search.result', [
        'file_number' => '1001',
        'department_uuid' => $deptB->uuid,
    ]));

    $response->assertOk();
    $response->assertSee('File B');
    $response->assertDontSee('File A');
});
