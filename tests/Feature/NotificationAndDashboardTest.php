<?php

use App\Enums\PartStatus;
use App\Enums\UserRole;
use App\Models\IncomingStock;
use App\Models\IncomingStockItem;
use App\Models\OutgoingStock;
use App\Models\OutgoingStockItem;
use App\Models\Part;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createReportPart(array $overrides = []): Part
{
    return Part::query()->create([
        'partName' => 'Brake Pad',
        'partNumber' => 'BP-001',
        'quantity' => 10,
        'price' => 5000,
        'status' => PartStatus::LOW_STOCK,
        ...$overrides,
    ]);
}

test('notifications include active business alerts and persist user actions', function () {
    $this->travelTo(now()->setDate(2026, 7, 19)->setTime(10, 0));

    $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
    $otherUser = User::factory()->create(['role' => UserRole::ADMIN->value]);
    $lowStockPart = createReportPart();
    createReportPart([
        'partName' => 'Oil Filter',
        'partNumber' => 'OF-001',
        'quantity' => 0,
        'status' => PartStatus::OUT_OF_STOCK,
    ]);

    Receivable::query()->create([
        'customerName' => 'Asha',
        'referenceNumber' => 'SALE-100',
        'totalAmount' => 50000,
        'amountPaid' => 10000,
        'balanceAmount' => 40000,
        'status' => 'PARTIAL',
        'debtDate' => '2026-07-10',
        'dueDate' => '2026-07-22',
        'createdBy' => $admin->id,
    ]);
    Payable::query()->create([
        'creditorName' => 'Mlimani Parts',
        'referenceNumber' => 'INV-200',
        'totalAmount' => 30000,
        'amountPaid' => 0,
        'balanceAmount' => 30000,
        'status' => 'PENDING',
        'debtDate' => '2026-07-10',
        'dueDate' => '2026-07-19',
        'createdBy' => $admin->id,
    ]);
    Payable::query()->create([
        'creditorName' => 'Not due soon',
        'totalAmount' => 10000,
        'amountPaid' => 0,
        'balanceAmount' => 10000,
        'status' => 'PENDING',
        'debtDate' => '2026-07-10',
        'dueDate' => '2026-07-23',
        'createdBy' => $admin->id,
    ]);

    $saleDispatch = OutgoingStock::query()->create([
        'dispatchNumber' => 'DSP-SALE',
        'purpose' => 'SALE',
        'dispatchedBy' => $admin->id,
        'dispatchedAt' => '2026-07-18 12:00:00',
    ]);
    OutgoingStockItem::query()->create([
        'outgoingStockId' => $saleDispatch->id,
        'partId' => $lowStockPart->id,
        'quantity' => 2,
    ]);
    $sale = Sale::query()->create([
        'saleNumber' => 'SALE-101',
        'paymentStatus' => 'PAID',
        'paymentMethod' => 'CASH',
        'totalAmount' => 10000,
        'amountPaid' => 10000,
        'soldBy' => $admin->id,
        'soldAt' => '2026-07-18 12:00:00',
        'outgoingStockId' => $saleDispatch->id,
    ]);
    SaleItem::query()->create([
        'saleId' => $sale->id,
        'partId' => $lowStockPart->id,
        'quantity' => 2,
        'unitPrice' => 5000,
        'subtotal' => 10000,
    ]);

    $otherDispatch = OutgoingStock::query()->create([
        'dispatchNumber' => 'DSP-DAMAGE',
        'purpose' => 'DAMAGED',
        'dispatchedBy' => $admin->id,
        'dispatchedAt' => '2026-07-18 13:00:00',
    ]);
    OutgoingStockItem::query()->create([
        'outgoingStockId' => $otherDispatch->id,
        'partId' => $lowStockPart->id,
        'quantity' => 1,
    ]);

    $incoming = IncomingStock::query()->create([
        'invoiceNumber' => 'INV-201',
        'receivedBy' => $admin->id,
        'receivedAt' => '2026-07-18 09:00:00',
        'totalAmount' => 15000,
    ]);
    IncomingStockItem::query()->create([
        'incomingStockId' => $incoming->id,
        'partId' => $lowStockPart->id,
        'quantity' => 3,
        'unitCost' => 5000,
        'subtotal' => 15000,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/notifications');
    $response
        ->assertOk()
        ->assertJsonPath('meta.unreadCount', 5)
        ->assertJsonFragment(['type' => 'LOW_STOCK'])
        ->assertJsonFragment(['type' => 'OUT_OF_STOCK'])
        ->assertJsonFragment(['type' => 'DEBT_DUE_RECEIVABLE'])
        ->assertJsonFragment(['type' => 'DEBT_DUE_PAYABLE'])
        ->assertJsonFragment(['type' => 'DAILY_TREND']);

    $daily = collect($response->json('data'))->firstWhere('type', 'DAILY_TREND');
    expect($daily['details']['salesRevenue'])->toBe('10000')
        ->and($daily['details']['saleDispatchQuantity'])->toBe(2)
        ->and($daily['details']['otherDispatchQuantity'])->toBe(1)
        ->and($daily['details']['outgoingQuantity'])->toBe(3);

    $notificationId = $response->json('data.0.id');
    $this->postJson("/api/v1/notifications/{$notificationId}/read")->assertOk();
    $this->deleteJson("/api/v1/notifications/{$notificationId}")->assertOk();
    $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('meta.total', 4);

    Sanctum::actingAs($otherUser);
    $this->postJson("/api/v1/notifications/{$notificationId}/read")
        ->assertNotFound();

    Sanctum::actingAs($admin);
    $this->postJson('/api/v1/notifications/read-all')->assertOk();
    $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('meta.unreadCount', 0);
    $this->deleteJson('/api/v1/notifications')->assertOk();
    $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);
});

test('dashboard summary returns real inventory sales movement and product data', function () {
    $this->travelTo(now()->setDate(2026, 7, 19)->setTime(10, 0));

    $user = User::factory()->create();
    $part = createReportPart([
        'quantity' => 8,
        'price' => 2500,
    ]);
    createReportPart([
        'partName' => 'Empty Filter',
        'partNumber' => 'EF-001',
        'quantity' => 0,
        'price' => 4000,
        'status' => PartStatus::OUT_OF_STOCK,
    ]);

    $outgoing = OutgoingStock::query()->create([
        'dispatchNumber' => 'DSP-300',
        'purpose' => 'SALE',
        'dispatchedBy' => $user->id,
        'dispatchedAt' => now(),
    ]);
    OutgoingStockItem::query()->create([
        'outgoingStockId' => $outgoing->id,
        'partId' => $part->id,
        'quantity' => 3,
    ]);
    $sale = Sale::query()->create([
        'saleNumber' => 'SALE-300',
        'paymentStatus' => 'PARTIAL',
        'paymentMethod' => 'MOBILE_MONEY',
        'totalAmount' => 12000,
        'amountPaid' => 6000,
        'soldBy' => $user->id,
        'soldAt' => now(),
        'outgoingStockId' => $outgoing->id,
    ]);
    SaleItem::query()->create([
        'saleId' => $sale->id,
        'partId' => $part->id,
        'quantity' => 3,
        'unitPrice' => 4000,
        'subtotal' => 12000,
    ]);

    $incoming = IncomingStock::query()->create([
        'invoiceNumber' => 'INV-300',
        'receivedBy' => $user->id,
        'receivedAt' => now(),
        'totalAmount' => 5000,
    ]);
    IncomingStockItem::query()->create([
        'incomingStockId' => $incoming->id,
        'partId' => $part->id,
        'quantity' => 2,
        'unitCost' => 2500,
        'subtotal' => 5000,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/dashboard/summary')
        ->assertOk()
        ->assertJsonPath('data.inventory.productCount', 2)
        ->assertJsonPath('data.inventory.quantity', 8)
        ->assertJsonPath('data.inventory.value', '20000')
        ->assertJsonPath('data.inventory.lowStockCount', 1)
        ->assertJsonPath('data.inventory.outOfStockCount', 1)
        ->assertJsonPath('data.salesOverview.saleCount', 1)
        ->assertJsonPath('data.salesOverview.totalSales', '12000')
        ->assertJsonPath('data.salesOverview.collectionRate', 50)
        ->assertJsonPath('data.topProducts.0.partName', 'Brake Pad')
        ->assertJsonPath('data.topProducts.0.quantity', 3);

    $currentMonth = collect($response->json('data.monthlyTrends'))->last();
    expect($currentMonth['stockIn'])->toBe(2)
        ->and($currentMonth['stockOut'])->toBe(3)
        ->and($currentMonth['salesRevenue'])->toBe('12000');
});
