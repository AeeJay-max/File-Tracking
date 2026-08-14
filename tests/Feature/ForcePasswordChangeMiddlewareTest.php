<?php

use App\Models\Department;
use App\Models\User;

dataset('roles-and-dashboards', [
    ['super_admin', 'super_admin.dashboard'],
    ['admin', 'admin.dashboard'],
    ['user', 'user.dashboard'],
]);

test('verified default-password users are redirected to profile', function (string $role) {
    $department = Department::factory()->create();

    $user = User::factory()->create([
        'role' => $role,
        'department_id' => $department->id,
        'email_verified_at' => now(),
        'must_change_password' => true,
    ]);

    $this
        ->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('profile.edit'));
})->with('roles-and-dashboards');

test('unverified default-password users are redirected to email verification first', function (string $role) {
    $department = Department::factory()->create();

    $user = User::factory()->create([
        'role' => $role,
        'department_id' => $department->id,
        'email_verified_at' => null,
        'must_change_password' => true,
    ]);

    $this
        ->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
})->with('roles-and-dashboards');

test('changing password clears force flag and allows dashboard access', function (string $role, string $expectedDashboardRoute) {
    $department = Department::factory()->create();

    $user = User::factory()->create([
        'role' => $role,
        'department_id' => $department->id,
        'email_verified_at' => now(),
        'must_change_password' => true,
        'password' => bcrypt('password'),
    ]);

    $this
        ->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('profile.edit'));

    $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->put(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->must_change_password)->toBeFalse();

    $this
        ->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route($expectedDashboardRoute));
})->with('roles-and-dashboards');
