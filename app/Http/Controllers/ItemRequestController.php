<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\Warehouse;
use App\Services\ItemRequestWorkflow;
use App\Services\WarehouseItemBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemRequestController extends Controller
{
    public function create(): View
    {
        return view('item-requests.create', [
            'items' => Item::query()
                ->where('status', 'active')
                ->orderBy('item_code')
                ->get(['item_code', 'description', 'uom1']),
            'warehouses' => Warehouse::query()
                ->where('status', 'active')
                ->orderBy('warehouse_name')
                ->get(['warehouseid', 'warehouse_name']),
            'balanceUrl' => route('public.item-requests.balance'),
        ]);
    }

    public function balance(Request $request, WarehouseItemBalance $balance): JsonResponse
    {
        $validated = $request->validate([
            'warehouseid' => ['required', Rule::exists('warehouses', 'warehouseid')->where('status', 'active')],
            'item_code' => ['required', Rule::exists('items', 'item_code')->where('status', 'active')],
        ]);

        return response()->json([
            'current_balance' => $balance->current($validated['warehouseid'], $validated['item_code']),
            'uom' => Item::query()->whereKey($validated['item_code'])->value('uom1'),
        ]);
    }

    public function store(Request $request, WarehouseItemBalance $balance, ItemRequestWorkflow $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'requester_email' => ['required', 'email:rfc', 'max:255'],
            'requester_name' => ['required', 'string', 'max:150'],
            'department' => ['required', 'string', 'max:150'],
            'warehouseid' => ['required', Rule::exists('warehouses', 'warehouseid')->where('status', 'active')],
            'details' => ['required', 'array', 'min:1', 'max:30'],
            'details.*.item_code' => ['required', 'distinct', Rule::exists('items', 'item_code')->where('status', 'active')],
            'details.*.quantity' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
        ]);

        $itemRequest = DB::transaction(function () use ($validated, $balance): ItemRequest {
            $itemRequest = ItemRequest::create([
                'request_no' => 'REQ-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
                'requester_email' => mb_strtolower($validated['requester_email']),
                'requester_name' => $validated['requester_name'],
                'department' => $validated['department'],
                'warehouseid' => $validated['warehouseid'],
                'status' => ItemRequest::STATUS_PENDING,
            ]);

            foreach ($validated['details'] as $detail) {
                $item = Item::query()->whereKey($detail['item_code'])->firstOrFail();
                $itemRequest->details()->create([
                    'item_code' => $item->item_code,
                    'quantity' => $detail['quantity'],
                    'uom' => $item->uom1,
                    'current_balance' => $balance->current($validated['warehouseid'], $item->item_code),
                ]);
            }

            return $itemRequest->fresh(['warehouse', 'details.item']);
        });

        $workflow->notifyApprovers($itemRequest);

        return redirect()
            ->route('public.item-requests.create')
            ->with('request_no', $itemRequest->request_no);
    }
}
