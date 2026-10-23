<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WarehouseItemBalance
{
    public function current(int|string $warehouseId, string $itemCode): float
    {
        $asOf = Carbon::today()->toDateString();

        return (float) DB::table('transaction_headers as h')
            ->join('transaction_details as d', 'd.trans_id', '=', 'h.trans_id')
            ->where('h.warehouseid', $warehouseId)
            ->where('h.trans_date', '<=', $asOf)
            ->where('h.status', '!=', 'X')
            ->whereIn('h.trans_type', ['IN', 'OUT', 'ADJ'])
            ->where('d.item_code', $itemCode)
            ->selectRaw("COALESCE(SUM(CASE WHEN h.trans_type = 'IN' THEN d.qty_uom1 WHEN h.trans_type = 'OUT' THEN -d.qty_uom1 WHEN h.trans_type = 'ADJ' THEN d.qty_uom1 ELSE 0 END), 0) AS balance")
            ->value('balance');
    }
}
