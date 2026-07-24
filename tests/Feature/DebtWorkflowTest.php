<?php

use App\Enums\PartStatus;
use App\Models\Part;
use App\Models\User;
use App\Services\IncomingStockService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a debt sale creates a receivable for the unpaid balance', function () {
    $user = User::factory()->create();
    $part = Part::query()->create([
        'partName' => 'Brake Pad',
        'partNumber' => 'BP-001',
        'quantity' => 10,
        'price' => 5000,
        'status' => PartStatus::IN_STOCK,
    ]);

    $sale = app(SaleService::class)->store([
        'customerName' => 'Asha M.',
        'customerPhone' => '0712345678',
        'isDebt' => true,
        'debtDueDate' => '2026-08-01',
        'paymentStatus' => 'PARTIAL',
        'paymentMethod' => 'MOBILE_MONEY',
        'amountPaid' => 2000,
        'soldAt' => now()->toIso8601String(),
        'items' => [[
            'partId' => $part->id,
            'quantity' => 2,
            'unitPrice' => 5000,
        ]],
    ], $user);

    expect($sale->totalAmount)->toBe('10000.00')
        ->and($sale->amountPaid)->toBe('2000.00')
        ->and($sale->paymentStatus)->toBe('PARTIAL')
        ->and($sale->receivable)->not->toBeNull()
        ->and($sale->receivable->debtDate->toDateString())->toBe(now()->toDateString())
        ->and($sale->receivable->balanceAmount)->toBe('8000.00')
        ->and($sale->receivable->status)->toBe('PARTIAL');

    $receivableId = $sale->receivable->id;
    app(SaleService::class)->destroy($sale);
    $this->assertDatabaseMissing('receivables', ['id' => $receivableId]);
});

test('a debt stock intake creates a payable for the supplier balance', function () {
    $user = User::factory()->create();
    $part = Part::query()->create([
        'partName' => 'Oil Filter',
        'partNumber' => 'OF-001',
        'quantity' => 3,
        'price' => 8000,
        'status' => PartStatus::LOW_STOCK,
    ]);

    $intake = app(IncomingStockService::class)->store([
        'invoiceNumber' => 'INV-200',
        'supplierName' => 'Mlimani Parts',
        'supplierPhone' => '0755555555',
        'isDebt' => true,
        'debtDueDate' => '2026-08-15',
        'amountPaid' => 5000,
        'receivedAt' => now()->toIso8601String(),
        'items' => [[
            'partId' => $part->id,
            'quantity' => 5,
            'unitCost' => 3000,
        ]],
    ], $user);

    expect($intake->totalAmount)->toBe('15000.00')
        ->and($intake->payable)->not->toBeNull()
        ->and($intake->payable->debtDate->toDateString())->toBe(now()->toDateString())
        ->and($intake->payable->balanceAmount)->toBe('10000.00')
        ->and($intake->payable->status)->toBe('PARTIAL');

    $payableId = $intake->payable->id;
    app(IncomingStockService::class)->destroy($intake);
    $this->assertDatabaseMissing('payables', ['id' => $payableId]);
});

test('a due date before the debt date returns a localized validation error', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->withHeader('X-Locale', 'sw')->postJson('/api/v1/receivables', [
        'customerName' => 'Rehema J.',
        'totalAmount' => 50000,
        'debtDate' => '2026-07-10',
        'dueDate' => '2026-07-09',
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors('dueDate');

    expect(mb_strtolower($response->json('errors.dueDate.0')))->toContain('tarehe');
});
