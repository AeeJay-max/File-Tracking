<?php

use App\Models\Department;
use App\Models\User;

test('impersonation start is allowed even when the impersonator must change password', function () {
    $department = Department::factory()->create();

    $superAdmin = User::factory()->create([
        'role' => 'super_admin',
        'department_id' => $department->id,
        'email_verified_at' => now(),
        'must_change_password' => true,
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'department_id' => $department->id,
        'email_verified_at' => now(),
        'must_change_password' => true,
    ]);

    $response = $this
        ->actingAs($superAdmin)
        ->post(route('impersonation.start', $admin));

    $response->assertRedirect(route('dashboard'));

    $this->assertSame($admin->id, auth()->id());
    $this->assertSame($superAdmin->id, session()->get('impersonator_id'));
});

test('active impersonation bypasses password enforcement and stop restores the original session', function () {
    $department = Department::factory()->create();

    $superAdmin = User::factory()->create([
        'role' => 'super_admin',
        'department_id' => $department->id,
        'email_verified_at' => now(),
        'must_change_password' => false,
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'department_id' => $department->id,
        'email_verified_at' => now(),
        'must_change_password' => true,
    ]);

    $this
        ->actingAs($superAdmin)
        ->post(route('impersonation.start', $admin))
        ->assertRedirect(route('dashboard'));

    $dashboardResponse = $this->get(route('dashboard'));

    $dashboardResponse->assertRedirect(route('admin.dashboard'));

    $stopResponse = $this->post(route('impersonation.stop'));

    $stopResponse->assertRedirect(route('dashboard'));

    $this->assertSame($superAdmin->id, auth()->id());
    $this->assertFalse(session()->has('impersonator_id'));
    $this->assertFalse(session()->has('impersonator_name'));
});

test('admin can stop impersonating a default-password user', function () {
    $department = Department::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'department_id' => $department->id,
        'email_verified_at' => now(),
        'must_change_password' => false,
    ]);

    $user = User::factory()->create([
        'role' => 'user',
        'department_id' => $department->id,
        'email_verified_at' => now(),
        'must_change_password' => true,
    ]);

    $this
        ->actingAs($admin)
        ->post(route('impersonation.start', $user))
        ->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))->assertRedirect(route('user.dashboard'));

    $this
        ->post(route('impersonation.stop'))
        ->assertRedirect(route('dashboard'));

    $this->assertSame($admin->id, auth()->id());
    $this->assertFalse(session()->has('impersonator_id'));
});

test('normal login still requires a password change', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'must_change_password' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertRedirect(route('profile.edit'));
});

test('normal login still requires email verification before password change enforcement', function () {
    $user = User::factory()->unverified()->create([
        'must_change_password' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertRedirect(route('verification.notice'));
});