<h2>{{ match ($event) { 'submitted' => 'Permintaan Barang Baru', 'accepted' => 'Permintaan Barang Diterima', default => 'Permintaan Barang Ditolak' } }}</h2>

<p>Nomor permintaan: <strong>{{ $itemRequest->request_no }}</strong></p>
<p>Status: {{ strtoupper($itemRequest->status) }}</p>
<p>Nama: {{ $itemRequest->requester_name }}</p>
<p>Department: {{ $itemRequest->department }}</p>
<p>Gudang: {{ $itemRequest->warehouse?->warehouse_name }}</p>

@if ($event === 'rejected' && $itemRequest->rejection_reason)
    <p>Alasan penolakan: {{ $itemRequest->rejection_reason }}</p>
@endif

@if ($itemRequest->transaction_id)
    <p>Goods Out: {{ $itemRequest->transaction?->trans_no }}</p>
@endif

<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>UOM</th>
            <th>Saldo Saat Permintaan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($itemRequest->details as $detail)
            <tr>
                <td>{{ $detail->item_code }}</td>
                <td>{{ $detail->item?->description }}</td>
                <td>{{ $detail->quantity }}</td>
                <td>{{ $detail->uom }}</td>
                <td>{{ $detail->current_balance }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
