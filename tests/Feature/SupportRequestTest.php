<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated users can submit support requests with account context', function () {
    config([
        'services.google_sheets.support_webhook_url' => 'https://script.google.com/test',
        'services.google_sheets.support_webhook_secret' => 'test-secret',
    ]);

    Http::fake([
        'https://script.google.com/*' => Http::response(['ok' => true]),
    ]);

    $user = User::factory()->create([
        'firstName' => 'Asha',
        'lastName' => 'Mushi',
        'email' => 'asha@example.com',
        'phone' => '0712345678',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/support-requests', [
        'type' => 'feedback',
        'category' => 'dashboard',
        'subject' => 'Dashboard feedback',
        'message' => 'The dashboard is clear, but I would like a quicker stock filter.',
        'priority' => 'normal',
        'contactPreference' => 'email',
        'rating' => 4,
        'sourcePath' => '/dashboard/help-center',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.type', 'feedback')
        ->assertJsonPath('data.rating', 4)
        ->assertJsonPath('message', 'Your request has been sent. We will follow up shortly.');

    $this->assertDatabaseHas('support_requests', [
        'userId' => $user->id,
        'subject' => 'Dashboard feedback',
        'sheetSyncStatus' => 'synced',
    ]);

    Http::assertSent(fn ($request) => $request->url() === 'https://script.google.com/test'
        && $request['secret'] === 'test-secret'
        && $request['user']['email'] === 'asha@example.com'
        && $request['referenceNumber'] === $response->json('data.referenceNumber'));
});

test('support history is scoped to the authenticated user', function () {
    config(['services.google_sheets.support_webhook_url' => null]);

    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    Sanctum::actingAs($firstUser);
    $this->postJson('/api/v1/support-requests', supportPayload('My support request'))
        ->assertCreated();

    Sanctum::actingAs($secondUser);
    $this->postJson('/api/v1/support-requests', supportPayload('Another support request'))
        ->assertCreated();

    $this->getJson('/api/v1/support-requests')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.subject', 'Another support request');
});

test('a support request remains saved if Google Sheets is unavailable', function () {
    config([
        'services.google_sheets.support_webhook_url' => 'https://script.google.com/test',
        'services.google_sheets.support_webhook_secret' => 'test-secret',
    ]);

    Http::fake([
        'https://script.google.com/*' => Http::response([], 500),
    ]);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/support-requests', supportPayload('Stock report issue'))
        ->assertCreated();

    $this->assertDatabaseHas('support_requests', [
        'userId' => $user->id,
        'subject' => 'Stock report issue',
        'sheetSyncStatus' => 'failed',
    ]);
});

test('support request validation requires useful details', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/support-requests', [
        'type' => 'feedback',
        'category' => '',
        'subject' => 'No',
        'message' => 'Too short',
        'priority' => 'immediate',
        'contactPreference' => 'sms',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'category',
            'subject',
            'message',
            'priority',
            'contactPreference',
            'rating',
        ]);
});

function supportPayload(string $subject): array
{
    return [
        'type' => 'help',
        'category' => 'inventory',
        'subject' => $subject,
        'message' => 'I need assistance understanding an inventory workflow in the dashboard.',
        'priority' => 'normal',
        'contactPreference' => 'email',
        'sourcePath' => '/dashboard/help-center',
    ];
}
