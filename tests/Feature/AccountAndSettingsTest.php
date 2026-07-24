<?php

use App\Enums\PartStatus;
use App\Enums\UserRole;
use App\Models\Part;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated users can read and update their own profile', function () {
    $user = User::factory()->create([
        'firstName' => 'Asha',
        'lastName' => 'Mushi',
        'email' => 'asha@example.com',
        'phone' => '0712345678',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/account')
        ->assertOk()
        ->assertJsonPath('data.user.email', 'asha@example.com')
        ->assertJsonPath('data.canDeleteAccount', true);

    $this->patchJson('/api/v1/account/profile', [
        'firstName' => 'Neema',
        'lastName' => 'Mushi',
        'email' => 'neema@example.com',
        'phone' => '0787654321',
    ])
        ->assertOk()
        ->assertJsonPath('data.user.fullName', 'Neema Mushi')
        ->assertJsonPath('data.user.email', 'neema@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'firstName' => 'Neema',
        'email' => 'neema@example.com',
    ]);
});

test('password changes require the current password and revoke tokens', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword1'),
    ]);

    Sanctum::actingAs($user);

    $this->putJson('/api/v1/account/password', [
        'currentPassword' => 'wrong-password',
        'password' => 'NewPassword2',
        'password_confirmation' => 'NewPassword2',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('currentPassword');

    $this->putJson('/api/v1/account/password', [
        'currentPassword' => 'OldPassword1',
        'password' => 'NewPassword2',
        'password_confirmation' => 'NewPassword2',
    ])->assertOk();

    expect(Hash::check('NewPassword2', $user->fresh()->password))->toBeTrue();
});

test('the last administrator cannot delete their own account', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'password' => Hash::make('AdminPassword1'),
    ]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/account')
        ->assertOk()
        ->assertJsonPath('data.canDeleteAccount', false);

    $this->deleteJson('/api/v1/account', [
        'currentPassword' => 'AdminPassword1',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('account');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('an administrator can delete their account when another administrator exists', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'password' => Hash::make('AdminPassword1'),
    ]);
    User::factory()->create(['role' => UserRole::ADMIN->value]);

    Sanctum::actingAs($admin);

    $this->deleteJson('/api/v1/account', [
        'currentPassword' => 'AdminPassword1',
    ])->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $admin->id]);
});

test('settings persist personal preferences and admin workspace controls', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('data.preferences.theme', 'system')
        ->assertJsonPath('data.workspace.lowStockThreshold', 10)
        ->assertJsonPath('data.canManageWorkspace', true);

    $this->patchJson('/api/v1/settings/preferences', [
        'theme' => 'dark',
        'locale' => 'sw',
        'compactTables' => true,
    ])
        ->assertOk()
        ->assertJsonPath('data.preferences.theme', 'dark')
        ->assertJsonPath('data.preferences.locale', 'sw')
        ->assertJsonPath('data.preferences.compactTables', true);

    $this->patchJson('/api/v1/settings/workspace', [
        'lowStockThreshold' => 15,
        'allowNegativeStock' => true,
    ])
        ->assertOk()
        ->assertJsonPath('data.workspace.lowStockThreshold', 15)
        ->assertJsonPath('data.workspace.allowNegativeStock', true);

    $this->assertDatabaseHas('user_preferences', [
        'userId' => $admin->id,
        'theme' => 'dark',
        'locale' => 'sw',
    ]);
    $this->assertDatabaseHas('workspace_settings', [
        'key' => 'default',
        'lowStockThreshold' => 15,
    ]);
});

test('inventory settings recalculate status and control negative stock', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
    $part = Part::query()->create([
        'partName' => 'Brake Pad',
        'partNumber' => 'BP-SETTINGS',
        'quantity' => 12,
        'price' => 5000,
        'status' => PartStatus::IN_STOCK,
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson('/api/v1/settings/workspace', [
        'lowStockThreshold' => 15,
    ])->assertOk();

    expect($part->fresh()->status)->toBe(PartStatus::LOW_STOCK);

    $payload = [
        'purpose' => 'DAMAGED',
        'dispatchedAt' => now()->toIso8601String(),
        'items' => [
            ['partId' => $part->id, 'quantity' => 20],
        ],
    ];

    $this->postJson('/api/v1/outgoing-stocks', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items');

    $this->patchJson('/api/v1/settings/workspace', [
        'allowNegativeStock' => true,
    ])->assertOk();

    $this->postJson('/api/v1/outgoing-stocks', $payload)->assertCreated();

    expect($part->fresh()->quantity)->toBe(-8)
        ->and($part->fresh()->status)->toBe(PartStatus::OUT_OF_STOCK);
});

test('non-admin users cannot update workspace controls', function () {
    $user = User::factory()->create(['role' => UserRole::USER->value]);

    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/settings/workspace', [
        'lowStockThreshold' => 15,
    ])->assertForbidden();
});

test('notification preferences control generated alerts', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
    Part::query()->create([
        'partName' => 'Oil Filter',
        'partNumber' => 'OF-NOTIFY',
        'quantity' => 2,
        'price' => 4000,
        'status' => PartStatus::LOW_STOCK,
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson('/api/v1/settings/preferences', [
        'lowStockAlerts' => false,
        'salesDigest' => false,
        'debtReminders' => false,
    ])->assertOk();

    $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('meta.total', 0)
        ->assertJsonPath('meta.unreadCount', 0);
});
