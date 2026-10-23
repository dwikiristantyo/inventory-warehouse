<?php

namespace Tests\Feature;

use App\Mail\ItemRequestStatusMail;
use App\Models\ItemRequest;
use App\Models\User;
use App\Services\ItemRequestWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ItemRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_request_can_be_accepted_into_goods_out_and_email_is_sent(): void
    {
        Mail::fake();
        [$approver, $warehouseId] = $this->createReferenceData();
        $this->createIncomingStock($approver, $warehouseId);

        $this->get('/request-items')
            ->assertOk()
            ->assertSee('Permintaan Barang');

        $this->post(route('public.item-requests.store'), [
            'requester_email' => 'requester@example.test',
            'requester_name' => 'Ayu Permata',
            'department' => 'Finance',
            'warehouseid' => $warehouseId,
            'details' => [
                ['item_code' => 'PEN0001', 'quantity' => 3],
            ],
        ])->assertRedirect(route('public.item-requests.create'));

        $itemRequest = ItemRequest::query()
            ->with('details')
            ->where('requester_email', 'requester@example.test')
            ->firstOrFail();

        $this->assertSame(ItemRequest::STATUS_PENDING, $itemRequest->status);
        $this->assertSame('12.00', $itemRequest->details->first()->current_balance);
        Mail::assertSent(ItemRequestStatusMail::class, fn (ItemRequestStatusMail $mail): bool => $mail->event === 'submitted');

        app(ItemRequestWorkflow::class)->accept($itemRequest, $approver);

        $this->assertDatabaseHas('item_requests', [
            'id' => $itemRequest->id,
            'status' => ItemRequest::STATUS_ACCEPTED,
            'reviewed_by' => $approver->id,
            'transaction_id' => $itemRequest->fresh()->transaction_id,
        ]);
        $this->assertDatabaseHas('transaction_headers', [
            'trans_id' => $itemRequest->fresh()->transaction_id,
            'trans_type' => 'OUT',
            'warehouseid' => $warehouseId,
            'remark' => 'Ayu Permata - Finance',
            'created_by' => $approver->id,
        ]);
        $this->assertDatabaseHas('transaction_details', [
            'trans_id' => $itemRequest->fresh()->transaction_id,
            'item_code' => 'PEN0001',
            'qty_uom1' => 3,
            'qty_uom2' => 0,
        ]);
        Mail::assertSent(ItemRequestStatusMail::class, fn (ItemRequestStatusMail $mail): bool => $mail->event === 'accepted');
    }

    public function test_rejected_request_is_not_converted_to_goods_out_and_email_is_sent(): void
    {
        Mail::fake();
        [$approver, $warehouseId] = $this->createReferenceData();
        $itemRequest = ItemRequest::create([
            'request_no' => 'REQ-REJECT-1',
            'requester_email' => 'requester@example.test',
            'requester_name' => 'Ayu Permata',
            'department' => 'Finance',
            'warehouseid' => $warehouseId,
            'status' => ItemRequest::STATUS_PENDING,
        ]);
        $itemRequest->details()->create([
            'item_code' => 'PEN0001',
            'quantity' => 3,
            'uom' => 'BOX',
            'current_balance' => 12,
        ]);

        app(ItemRequestWorkflow::class)->reject($itemRequest, $approver, 'Stok tidak tersedia.');

        $this->assertDatabaseHas('item_requests', [
            'id' => $itemRequest->id,
            'status' => ItemRequest::STATUS_REJECTED,
            'reviewed_by' => $approver->id,
            'rejection_reason' => 'Stok tidak tersedia.',
            'transaction_id' => null,
        ]);
        $this->assertDatabaseCount('transaction_headers', 0);
        Mail::assertSent(ItemRequestStatusMail::class, fn (ItemRequestStatusMail $mail): bool => $mail->event === 'rejected');
    }

    private function createReferenceData(): array
    {
        $now = now();
        $companyId = DB::table('companies')->insertGetId([
            'company_name' => 'Example Company',
            'business_line' => 'Office',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ], 'companyid');

        $warehouseId = DB::table('warehouses')->insertGetId([
            'companyid' => $companyId,
            'warehouse_name' => 'Main Warehouse',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ], 'warehouseid');

        $groupId = DB::table('user_groups')->insertGetId([
            'description' => 'Goods Out Approver',
            'created_at' => $now,
            'updated_at' => $now,
        ], 'usergroupid');

        DB::table('user_group_details')->insert([
            'usergroupid' => $groupId,
            'menu_id' => '5',
            'can_view' => true,
            'can_add' => true,
            'can_edit' => false,
            'can_delete' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $approver = User::create([
            'nik' => 'APPROVER-1',
            'username' => 'goods-out-approver',
            'email' => 'approver@example.test',
            'password' => Hash::make('password'),
            'usergroupid' => $groupId,
            'status' => 'active',
        ]);

        DB::table('categories')->insert([
            'category_code' => 'STATIONERY',
            'category_name' => 'Stationery',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('items')->insert([
            'item_code' => 'PEN0001',
            'category_code' => 'STATIONERY',
            'description' => 'Ballpoint Pen',
            'uprice' => 0,
            'uom1' => 'BOX',
            'uom2' => 'PCS',
            'conv_qty' => 12,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$approver, $warehouseId];
    }

    private function createIncomingStock(User $approver, int $warehouseId): void
    {
        $transId = DB::table('transaction_headers')->insertGetId([
            'trans_no' => 'IN-TEST-1',
            'trans_type' => 'IN',
            'trans_date' => now()->toDateString(),
            'warehouseid' => $warehouseId,
            'remark' => 'Opening test stock',
            'status' => 'A',
            'created_by' => $approver->id,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'trans_id');

        DB::table('transaction_details')->insert([
            'trans_id' => $transId,
            'item_code' => 'PEN0001',
            'qty_uom1' => 12,
            'qty_uom2' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
