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

it('automatically removes unread notification badge when acting on or viewing a file', function () {
    /** @var TestCase $this */
    $department = Department::create([
        'name' => 'Records Department',
        'code' => 'REC',
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
        'current_user_id' => $recipient->id,
        'current_department_id' => $department->id,
        'file_name' => 'Confidential Record',
        'file_number' => 'REC-101',
        'status' => 'active',
    ]);

    $transfer = FileTransfer::create([
        'file_id' => $file->id,
        'sender_id' => $sender->id,
        'receiver_id' => $recipient->id,
        'remarks' => 'Review file',
        'transferred_at' => now(),
    ]);

    $recipient->notify(new FileTransferredNotification($transfer));

    expect($recipient->unreadNotifications()->count())->toBe(1);

    // Recipient views or acts on the file without opening the notification dropdown
    $this->actingAs($recipient)->get(route('files.show', $file->uuid))->assertOk();

    // The unread notification for that file should now be marked as read automatically
    expect($recipient->fresh()->unreadNotifications()->count())->toBe(0);
});

it('sends an email when a file is transferred', function () {
    /** @var TestCase $this */
    \Illuminate\Support\Facades\Notification::fake();

    $department = Department::create([
        'name' => 'Human Resources',
        'code' => 'HR',
        'is_active' => true,
    ]);

    $sender = User::factory()->create([
        'department_id' => $department->id,
    ]);

    $recipient = User::factory()->create([
        'department_id' => $department->id,
        'email' => 'officer@example.com',
    ]);

    $file = FileRecord::create([
        'department_id' => $department->id,
        'created_by' => $sender->id,
        'current_user_id' => $sender->id,
        'file_name' => 'Leave Application',
        'file_number' => 'HR-2026-001',
        'status' => 'active',
    ]);

    $transfer = FileTransfer::create([
        'file_id' => $file->id,
        'sender_id' => $sender->id,
        'receiver_id' => $recipient->id,
        'carried_by' => 'Courier Ainos',
        'remarks' => 'For your attention',
        'transferred_at' => now(),
    ]);

    $recipient->notify(new FileTransferredNotification($transfer));

    \Illuminate\Support\Facades\Notification::assertSentTo(
        $recipient,
        FileTransferredNotification::class,
        function (FileTransferredNotification $notification, array $channels) use ($recipient) {
            expect($channels)->toContain('mail');
            expect($channels)->toContain('database');

            $mailData = $notification->toMail($recipient);
            expect($mailData->subject)->toContain('File Transferred: HR-2026-001');
            return true;
        }
    );
});

it('sends an email when an overdue file is pinged', function () {
    /** @var TestCase $this */
    \Illuminate\Support\Facades\Notification::fake();

    $department = Department::create([
        'name' => 'Legal Affairs',
        'code' => 'LEG',
        'is_active' => true,
    ]);

    $recordsAdmin = User::factory()->create([
        'department_id' => $department->id,
        'role' => 'admin',
    ]);

    $holder = User::factory()->create([
        'department_id' => $department->id,
        'email' => 'holder@example.com',
    ]);

    $file = FileRecord::create([
        'department_id' => $department->id,
        'created_by' => $recordsAdmin->id,
        'current_user_id' => $holder->id,
        'current_department_id' => $department->id,
        'file_name' => 'Urgent Contract Review',
        'file_number' => 'LEG-2026-999',
        'status' => 'active',
    ]);

    $holder->notify(new \App\Notifications\FileOverdueNotification($file, $recordsAdmin));

    \Illuminate\Support\Facades\Notification::assertSentTo(
        $holder,
        \App\Notifications\FileOverdueNotification::class,
        function (\App\Notifications\FileOverdueNotification $notification, array $channels) use ($holder) {
            expect($channels)->toContain('mail');
            expect($channels)->toContain('database');

            $mailData = $notification->toMail($holder);
            expect($mailData->subject)->toContain('Overdue File Notice: LEG-2026-999');
            return true;
        }
    );
});

it('gracefully handles SMTP failures without throwing an error and still stores database notification', function () {
    /** @var TestCase $this */
    $department = Department::create([
        'name' => 'Transport',
        'code' => 'TRN',
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'department_id' => $department->id,
        'email' => 'officer@example.com',
    ]);

    $file = FileRecord::create([
        'department_id' => $department->id,
        'created_by' => $user->id,
        'current_user_id' => $user->id,
        'current_department_id' => $department->id,
        'file_name' => 'Vehicle Fleet Report',
        'file_number' => 'TRN-2026-001',
        'status' => 'active',
    ]);

    // Force an invalid SMTP host to trigger network failure
    config(['mail.mailers.smtp.host' => 'invalid-nonexistent-smtp-host.local']);
    config(['mail.default' => 'smtp']);

    // Send notification without mocking Notification facade so SafeMailChannel actually executes
    $user->notify(new \App\Notifications\FileOverdueNotification($file, $user));

    // Assert database notification was still stored successfully
    expect($user->notifications()->count())->toBe(1);
    expect($user->notifications()->first()->type)->toBe(\App\Notifications\FileOverdueNotification::class);
});


