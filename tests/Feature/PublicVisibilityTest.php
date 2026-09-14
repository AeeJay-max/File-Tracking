<?php

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\FileRecord;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Setup departments
    $this->recordsDept = Department::create([
        'name' => 'Records Department',
        'code' => 'REC',
        'is_active' => true,
    ]);

    $this->financeDept = Department::create([
        'name' => 'Finance Department',
        'code' => 'FIN',
        'is_active' => true,
    ]);

    // Setup users
    $this->recordsHod = User::factory()->create([
        'department_id' => $this->recordsDept->id,
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->recordsUser = User::factory()->create([
        'department_id' => $this->recordsDept->id,
        'role' => 'user',
        'is_active' => true,
    ]);

    $this->financeHod = User::factory()->create([
        'department_id' => $this->financeDept->id,
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->folder = Folder::create([
        'folder_number' => 'FLD-100',
        'folder_name' => 'General Files Folder',
        'created_by' => $this->recordsHod->id,
    ]);

    // Create file record
    $this->file = FileRecord::create([
        'department_id' => $this->recordsDept->id,
        'current_department_id' => $this->recordsDept->id,
        'folder_id' => $this->folder->id,
        'file_name' => 'Road Construction Contract',
        'file_number' => 'REC/2026/001',
        'remarks' => 'Internal confidential financial audit notes',
        'created_by' => $this->recordsHod->id,
        'current_user_id' => $this->recordsHod->id,
        'status' => 'active',
        'is_public' => false,
    ]);
});

test('Records HOD can toggle public visibility to true and false', function () {
    $this->actingAs($this->recordsHod)
        ->post(route('files.togglePublic', $this->file->uuid))
        ->assertRedirect();

    expect($this->file->fresh()->is_public)->toBeTrue();

    // Verify Audit Log creation
    expect(AuditLog::where('auditable_id', $this->file->id)
        ->where('action', 'file.public_visibility_toggled')
        ->exists())->toBeTrue();

    // Toggle back to false
    $this->actingAs($this->recordsHod)
        ->post(route('files.togglePublic', $this->file->uuid))
        ->assertRedirect();

    expect($this->file->fresh()->is_public)->toBeFalse();
});

test('Non-Records HOD cannot toggle public visibility', function () {
    $this->actingAs($this->financeHod)
        ->post(route('files.togglePublic', $this->file->uuid))
        ->assertStatus(403);

    expect($this->file->fresh()->is_public)->toBeFalse();
});

test('Ordinary staff user cannot toggle public visibility', function () {
    $this->actingAs($this->recordsUser)
        ->post(route('files.togglePublic', $this->file->uuid))
        ->assertStatus(403);

    expect($this->file->fresh()->is_public)->toBeFalse();
});

test('Private file does not appear in public search', function () {
    expect($this->file->is_public)->toBeFalse();

    $response = $this->get(route('public.file.search.result', [
        'file_number' => 'REC/2026/001',
    ]));

    $response->assertSessionHas('search_error', 'No file found with this File Number for the selected department.');
});

test('Public file appears in search with ONLY safe minimal fields and zero internal data', function () {
    $this->file->update(['is_public' => true]);

    $response = $this->get(route('public.file.search.result', [
        'file_number' => 'REC/2026/001',
    ]));

    $response->assertStatus(200);
    $response->assertSee('REC/2026/001');
    $response->assertSee('Road Construction Contract');
    $response->assertSee('Records Department');

    // Ensure sensitive holder name, remarks, and download links are NOT present
    $response->assertDontSee($this->recordsHod->name);
    $response->assertDontSee('Internal confidential financial audit notes');
    $response->assertDontSee('Current Holder');
    $response->assertDontSee('files.download');
});

test('Toggling public visibility immediately invalidates public search cache', function () {
    $this->file->update(['is_public' => true]);

    // Perform public search to populate cache
    $this->get(route('public.file.search.result', [
        'file_number' => 'REC/2026/001',
    ]))->assertStatus(200);

    expect(Cache::has('public_file_REC/2026/001'))->toBeTrue();

    // Toggle public visibility to false as Records HOD
    $this->actingAs($this->recordsHod)
        ->post(route('files.togglePublic', $this->file->uuid));

    // Cache must be invalidated
    expect(Cache::has('public_file_REC/2026/001'))->toBeFalse();

    // Subsequent search must return not found
    $this->get(route('public.file.search.result', [
        'file_number' => 'REC/2026/001',
    ]))->assertSessionHas('search_error');
});

test('Public unauthenticated users cannot download attachments even for public files', function () {
    $this->file->update([
        'is_public' => true,
        'attachment_path' => 'files/'.$this->file->uuid.'/document.pdf',
        'attachment_name' => 'document.pdf',
    ]);

    // Unauthenticated request to download
    $response = $this->get(route('files.download', $this->file->uuid));

    // Should redirect to login (402/302)
    $response->assertRedirect(route('login'));
});
