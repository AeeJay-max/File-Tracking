<?php

use App\Models\Department;
use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Models\User;
use App\Notifications\FileTransferredNotification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    Storage::fake('public');
});

it('shows the notifications page for an authenticated user', function () {
    /** @var TestCase $this */
    $department = Department::create([
        'name' => 'Operations',
        'code' => 'OPS',
        'is_active' => true,
    ]);

    /** @var User $sender */
    $sender = User::factory()->create([
        'role' => 'admin',
        'department_id' => $department->id,
    ]);

    /** @var User $recipient */
    $recipient = User::factory()->create([
        'role' => 'user',
        'department_id' => $department->id,
    ]);

    $file = FileRecord::create([
        'department_id' => $department->id,
        'created_by' => $sender->id,
        'current_user_id' => $sender->id,
        'file_name' => 'Policy Document',
        'file_number' => 'DOC-001',
        'status' => 'active',
    ]);

    $transfer = FileTransfer::create([
        'file_id' => $file->id,
        'sender_id' => $sender->id,
        'receiver_id' => $recipient->id,
        'remarks' => 'Unit handoff',
        'transferred_at' => now(),
    ]);

    $recipient->notify(new FileTransferredNotification($transfer));

    $response = $this->actingAs($recipient)->get(route('notifications.index'));

    $response->assertOk();
    $response->assertSee('File Transferred');
    $response->assertSee('transferred DOC-001');
});

it('marks visible notifications as read when the dropdown opens', function () {
    /** @var TestCase $this */
    $department = Department::create([
        'name' => 'Operations',
        'code' => 'OPS',
        'is_active' => true,
    ]);

    /** @var User $sender */
    $sender = User::factory()->create([
        'role' => 'admin',
        'department_id' => $department->id,
    ]);

    /** @var User $recipient */
    $recipient = User::factory()->create([
        'role' => 'user',
        'department_id' => $department->id,
    ]);

    $file = FileRecord::create([
        'department_id' => $department->id,
        'created_by' => $sender->id,
        'current_user_id' => $sender->id,
        'file_name' => 'Policy Document',
        'file_number' => 'DOC-002',
        'status' => 'active',
    ]);

    $transfer = FileTransfer::create([
        'file_id' => $file->id,
        'sender_id' => $sender->id,
        'receiver_id' => $recipient->id,
        'remarks' => 'Unit handoff',
        'transferred_at' => now(),
    ]);

    $recipient->notify(new FileTransferredNotification($transfer));

    expect($recipient->unreadNotifications)->toHaveCount(1);

    $notificationId = $recipient->fresh()->unreadNotifications()->first()->id;

    $response = $this->actingAs($recipient)->postJson(route('notifications.readVisible'), [
        'ids' => [$notificationId],
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('unread_count', 0);
    $this->assertSame(0, $recipient->fresh()->unreadNotifications->count());
});
