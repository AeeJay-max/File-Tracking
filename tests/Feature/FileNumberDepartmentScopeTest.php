<?php

/**
 * FileNumberDepartmentScopeTest
 *
 * Verifies the department-scoped file number uniqueness requirements:
 *
 *  1. Same file number in the same department is REJECTED.
 *  2. Same file number in different departments is ALLOWED.
 *  3. Transferring a file to a department that already has the same file number
 *     does NOT cause a conflict (origin department_id is preserved).
 *  4. Public search correctly distinguishes files with the same number from
 *     different departments (returns disambiguation choices).
 *  5. Database composite unique (department_id, file_number) is enforced at
 *     the DB level — duplicate insert throws an integrity exception.
 */

use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\User;
use Illuminate\Database\QueryException;

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Create a verified user belonging to a department.
 */
function makeUser(Department $dept, array $attrs = []): User
{
    return User::factory()->create(array_merge([
        'role'             => 'user',
        'department_id'    => $dept->id,
        'is_active'        => true,
        'can_create_file'  => true,
        'email_verified_at'=> now(),
    ], $attrs));
}

/**
 * Directly insert a FileRecord row (bypasses HTTP) and seed its creation movement.
 * Uses (department_id, file_number) as the identity key.
 */
function seedFile(Department $dept, User $user, string $fileNumber, string $fileName = 'Test File'): FileRecord
{
    $file = FileRecord::create([
        'created_by'            => $user->id,
        'current_user_id'       => $user->id,
        'department_id'         => $dept->id,
        'current_department_id' => $dept->id,
        'file_name'             => $fileName,
        'file_number'           => strtoupper(trim($fileNumber)),
        'status'                => 'active',
    ]);

    FileMovement::create([
        'file_id'         => $file->id,
        'from_user'       => $user->id,
        'to_user'         => $user->id,
        'from_department' => $dept->id,
        'to_department'   => $dept->id,
        'action'          => 'created',
        'remarks'         => 'Seeded for test',
    ]);

    return $file;
}

// ── Test 1: Duplicate file number in the SAME department is rejected ──────────

it('rejects duplicate file numbers within the same department via HTTP', function () {
    $dept = Department::factory()->create();
    $user = makeUser($dept);

    // First creation — should succeed
    $this->actingAs($user)
        ->post(route('files.store'), [
            'file_number'   => '1001',
            'file_name'     => 'Original File',
            'department_id' => $dept->id,
        ])
        ->assertRedirect(route('files.index'));

    expect(FileRecord::where('department_id', $dept->id)->where('file_number', '1001')->count())->toBe(1);

    // Second creation with same number + same dept — must fail validation
    $this->actingAs($user)
        ->post(route('files.store'), [
            'file_number'   => '1001',
            'file_name'     => 'Duplicate File',
            'department_id' => $dept->id,
        ])
        ->assertSessionHasErrors('file_number');

    // Still only one record
    expect(FileRecord::where('department_id', $dept->id)->where('file_number', '1001')->count())->toBe(1);
});

it('rejects duplicate file numbers at the database level (composite unique constraint)', function () {
    $dept = Department::factory()->create();
    $user = makeUser($dept);

    // Seed the first file directly (bypasses HTTP validation)
    seedFile($dept, $user, '1001', 'First File');

    // Attempt a raw DB insert with the same (department_id, file_number) — must throw
    expect(fn () => FileRecord::create([
        'created_by'            => $user->id,
        'current_user_id'       => $user->id,
        'department_id'         => $dept->id,
        'current_department_id' => $dept->id,
        'file_name'             => 'Duplicate Raw Insert',
        'file_number'           => '1001',
        'status'                => 'active',
    ]))->toThrow(QueryException::class);
});

// ── Test 2: Same file number in DIFFERENT departments is allowed ───────────────

it('allows the same file number in different departments via HTTP', function () {
    $deptA = Department::factory()->create(['name' => 'Department A']);
    $deptB = Department::factory()->create(['name' => 'Department B']);
    $user  = makeUser($deptA, ['can_create_file' => true]);

    // File in Dept A
    $this->actingAs($user)
        ->post(route('files.store'), [
            'file_number'   => '1001',
            'file_name'     => 'File in Dept A',
            'department_id' => $deptA->id,
        ])
        ->assertRedirect(route('files.index'));

    // Same number in Dept B — must also succeed
    $this->actingAs($user)
        ->post(route('files.store'), [
            'file_number'   => '1001',
            'file_name'     => 'File in Dept B',
            'department_id' => $deptB->id,
        ])
        ->assertRedirect(route('files.index'));

    // Two rows with the same file number — one per department
    expect(FileRecord::where('file_number', '1001')->count())->toBe(2)
        ->and(FileRecord::where('department_id', $deptA->id)->where('file_number', '1001')->exists())->toBeTrue()
        ->and(FileRecord::where('department_id', $deptB->id)->where('file_number', '1001')->exists())->toBeTrue();
});

it('allows the same file number in different departments at the database level', function () {
    $deptA = Department::factory()->create();
    $deptB = Department::factory()->create();
    $user  = makeUser($deptA);

    // Both inserts must succeed without throwing
    $fileA = seedFile($deptA, $user, '1001', 'File A');
    $fileB = seedFile($deptB, $user, '1001', 'File B');

    expect($fileA->department_id)->toBe($deptA->id)
        ->and($fileB->department_id)->toBe($deptB->id)
        ->and($fileA->file_number)->toBe($fileB->file_number)
        ->and(FileRecord::where('file_number', '1001')->count())->toBe(2);
});

// ── Test 3: Cross-department transfer does NOT cause a file-number conflict ────

it('allows transferring a file to a department that already holds the same file number', function () {
    $deptA = Department::factory()->create(['name' => 'Origin Dept']);
    $deptB = Department::factory()->create(['name' => 'Destination Dept']);

    $sender        = makeUser($deptA, ['is_active' => true]);
    $deptBAdminUser = makeUser($deptB, ['role' => 'admin', 'is_active' => true]);

    // File A: 1001 in Dept A (origin = Dept A)
    $fileA = seedFile($deptA, $sender, '1001', 'File A');

    // File B: 1001 already exists in Dept B (origin = Dept B)
    seedFile($deptB, $deptBAdminUser, '1001', 'File B native');

    // Transfer File A from Dept A → Dept B
    // This must NOT fail with a unique constraint violation because:
    //   fileA.department_id  stays = deptA.id  (origin is preserved)
    //   fileA.current_department_id changes to deptB.id
    $this->actingAs($sender)
        ->post(route('files.transfer.store'), [
            'file_record_uuid' => $fileA->uuid,
            'destination_type' => 'other',
            'department_id'    => $deptB->id,
        ])
        ->assertRedirect(route('files.index'));

    $fileA->refresh();

    // Origin preserved — still Dept A
    expect($fileA->department_id)->toBe($deptA->id)
        // Current holder updated — now Dept B
        ->and($fileA->current_department_id)->toBe($deptB->id)
        // Status set to pending_assignment (cross-dept transfer)
        ->and($fileA->status)->toBe('pending_assignment')
        // No records deleted — still two files with number 1001
        ->and(FileRecord::where('file_number', '1001')->count())->toBe(2);
});

it('preserves origin department_id after transfer', function () {
    $deptA = Department::factory()->create();
    $deptB = Department::factory()->create();
    $user  = makeUser($deptA, ['is_active' => true]);

    $file = seedFile($deptA, $user, 'FILE-XYZ');

    // Transfer to Dept B
    $this->actingAs($user)
        ->post(route('files.transfer.store'), [
            'file_record_uuid' => $file->uuid,
            'destination_type' => 'other',
            'department_id'    => $deptB->id,
        ])
        ->assertRedirect(route('files.index'));

    $file->refresh();

    // department_id = origin, never changes
    expect($file->department_id)->toBe($deptA->id)
        ->and($file->current_department_id)->toBe($deptB->id);
});

// ── Test 4: Public search disambiguates same file number across departments ────

it('returns disambiguation choices when the same file number exists in multiple departments', function () {
    $deptA = Department::factory()->create(['name' => 'Dept Alpha']);
    $deptB = Department::factory()->create(['name' => 'Dept Beta']);
    $user  = makeUser($deptA);

    seedFile($deptA, $user, 'FILE-9999', 'Alpha File');
    seedFile($deptB, $user, 'FILE-9999', 'Beta File');

    $response = $this->get(route('public.file.search.result', [
        'file_number' => 'FILE-9999',
    ]));

    // When multiple matches exist and no department filter is given,
    // the controller redirects back with disambiguation data
    $response->assertRedirect();
    $response->assertSessionHas('department_choices');
    $response->assertSessionHas('search_error');

    $choices = session('department_choices');
    expect($choices)->toHaveCount(2);

    $choiceNames = collect($choices)->pluck('name');
    expect($choiceNames)->toContain('Dept Alpha')
        ->and($choiceNames)->toContain('Dept Beta');
});

it('returns the correct file when department uuid is provided in public search', function () {
    $deptA = Department::factory()->create(['name' => 'Finance']);
    $deptB = Department::factory()->create(['name' => 'Administration']);
    $user  = makeUser($deptA);

    seedFile($deptA, $user, 'FILE-8888', 'Finance File 8888');
    seedFile($deptB, $user, 'FILE-8888', 'Admin File 8888');

    // Search with deptA's uuid — should return exactly one result (Finance)
    $response = $this->get(route('public.file.search.result', [
        'file_number'     => 'FILE-8888',
        'department_uuid' => $deptA->uuid,
    ]));

    // Single result → renders the search view with result data (no redirect)
    $response->assertOk();
    $response->assertViewHas('result');

    $result = $response->viewData('result');
    expect($result['file_number'])->toBe('FILE-8888')
        ->and($result['origin_department'])->toBe('Finance');
});

it('returns not-found error when file number exists in another department but not the searched one', function () {
    $deptA = Department::factory()->create(['name' => 'HR']);
    $deptB = Department::factory()->create(['name' => 'Legal']);
    $user  = makeUser($deptA);

    // FILE-7777 only exists in HR
    seedFile($deptA, $user, 'FILE-7777', 'HR File');

    // Search for FILE-7777 scoped to Legal — should return not-found
    $response = $this->get(route('public.file.search.result', [
        'file_number'     => 'FILE-7777',
        'department_uuid' => $deptB->uuid,
    ]));

    $response->assertRedirect();
    $response->assertSessionHas('search_error');
    expect(session('search_error'))->toContain('No file found');
});

// ── Test 5: FileRecord model scopes ───────────────────────────────────────────

it('scopeByOriginDepartmentAndNumber returns only the matching origin dept file', function () {
    $deptA = Department::factory()->create();
    $deptB = Department::factory()->create();
    $user  = makeUser($deptA);

    $fileA = seedFile($deptA, $user, 'SC-001', 'Scope Test A');
    $fileB = seedFile($deptB, $user, 'SC-001', 'Scope Test B');

    $result = FileRecord::byOriginDepartmentAndNumber($deptA->id, 'SC-001')->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($fileA->id);
});

it('existsInDepartment returns true for existing and false for non-existing', function () {
    $dept = Department::factory()->create();
    $user = makeUser($dept);

    seedFile($dept, $user, 'EX-001');

    expect(FileRecord::existsInDepartment($dept->id, 'EX-001'))->toBeTrue()
        ->and(FileRecord::existsInDepartment($dept->id, 'EX-999'))->toBeFalse();
});

it('existsInDepartment correctly excludes the record itself when checking', function () {
    $dept = Department::factory()->create();
    $user = makeUser($dept);

    $file = seedFile($dept, $user, 'EX-002');

    // Excluding the file itself — should return false (no OTHER record has this number)
    expect(FileRecord::existsInDepartment($dept->id, 'EX-002', $file->id))->toBeFalse();
});
