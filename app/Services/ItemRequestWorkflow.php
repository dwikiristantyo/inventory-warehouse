<?php

namespace App\Services;

use App\Mail\ItemRequestStatusMail;
use App\Models\ItemRequest;
use App\Models\TransactionHeader;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class ItemRequestWorkflow
{
    public function notifyApprovers(ItemRequest $itemRequest): void
    {
        $emails = User::query()
            ->where('status', 'active')
            ->whereNotNull('email')
            ->where(function ($query): void {
                $query->where('usergroupid', 1)
                    ->orWhereHas('groupDetails', fn ($details) => $details
                        ->where('menu_id', '5')
                        ->where('can_add', true));
            })
            ->pluck('email')
            ->filter()
            ->unique()
            ->all();

        if ($emails === []) {
            Log::warning('No approvers with email are configured for item requests.', [
                'request_no' => $itemRequest->request_no,
            ]);

            return;
        }

        $this->send($emails, $itemRequest, 'submitted');
    }

    public function accept(ItemRequest $itemRequest, User $reviewer): ItemRequest
    {
        abort_unless($reviewer->canApproveItemRequests(), 403);

        $itemRequest = DB::transaction(function () use ($itemRequest, $reviewer): ItemRequest {
            $lockedRequest = ItemRequest::query()
                ->with('details.item')
                ->lockForUpdate()
                ->findOrFail($itemRequest->getKey());

            if ($lockedRequest->status !== ItemRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'decision' => 'Permintaan ini sudah diproses.',
                ]);
            }

            $today = now();
            $periodIsLocked = DB::table('lock_periods')
                ->where('warehouseid', $lockedRequest->warehouseid)
                ->where('year', $today->year)
                ->where('month', $today->month)
                ->where('is_locked', true)
                ->exists();

            if ($periodIsLocked) {
                throw ValidationException::withMessages([
                    'decision' => 'Periode transaksi gudang ini sedang dikunci.',
                ]);
            }

            $this->synchronizeTransactionSequences();

            $transaction = TransactionHeader::create([
                'trans_no' => 'OUT-REQ-' . $lockedRequest->request_no,
                'trans_type' => 'OUT',
                'trans_date' => $today->toDateString(),
                'warehouseid' => $lockedRequest->warehouseid,
                'remark' => $lockedRequest->requester_name . ' - ' . $lockedRequest->department,
                'status' => 'A',
                'created_by' => $reviewer->getKey(),
            ]);

            foreach ($lockedRequest->details as $detail) {
                $transaction->details()->create([
                    'item_code' => $detail->item_code,
                    'qty_uom1' => $detail->quantity,
                    'qty_uom2' => 0,
                ]);
            }

            $lockedRequest->update([
                'status' => ItemRequest::STATUS_ACCEPTED,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => $today,
                'transaction_id' => $transaction->getKey(),
            ]);

            return $lockedRequest->fresh(['warehouse', 'details.item', 'transaction']);
        });

        $this->send($itemRequest->requester_email, $itemRequest, 'accepted');

        return $itemRequest;
    }

    public function reject(ItemRequest $itemRequest, User $reviewer, string $reason): ItemRequest
    {
        abort_unless($reviewer->canApproveItemRequests(), 403);

        $itemRequest = DB::transaction(function () use ($itemRequest, $reviewer, $reason): ItemRequest {
            $lockedRequest = ItemRequest::query()
                ->lockForUpdate()
                ->findOrFail($itemRequest->getKey());

            if ($lockedRequest->status !== ItemRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'decision' => 'Permintaan ini sudah diproses.',
                ]);
            }

            $lockedRequest->update([
                'status' => ItemRequest::STATUS_REJECTED,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $lockedRequest->fresh(['warehouse', 'details.item']);
        });

        $this->send($itemRequest->requester_email, $itemRequest, 'rejected');

        return $itemRequest;
    }

    private function synchronizeTransactionSequences(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('LOCK TABLE transaction_headers, transaction_details IN SHARE ROW EXCLUSIVE MODE');
        DB::statement(<<<'SQL'
            SELECT setval(
                pg_get_serial_sequence('public.transaction_headers', 'trans_id'),
                GREATEST(
                    COALESCE((SELECT MAX(trans_id) FROM public.transaction_headers), 1),
                    (SELECT last_value FROM public.transaction_headers_trans_id_seq)
                ),
                true
            )
            SQL);
        DB::statement(<<<'SQL'
            SELECT setval(
                pg_get_serial_sequence('public.transaction_details', 'id'),
                GREATEST(
                    COALESCE((SELECT MAX(id) FROM public.transaction_details), 1),
                    (SELECT last_value FROM public.transaction_details_id_seq)
                ),
                true
            )
            SQL);
    }

    private function send(array|string $recipients, ItemRequest $itemRequest, string $event): void
    {
        try {
            Mail::to($recipients)->send(new ItemRequestStatusMail($itemRequest, $event));
        } catch (Throwable $exception) {
            Log::error('Failed to send item request email.', [
                'request_no' => $itemRequest->request_no,
                'event' => $event,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
