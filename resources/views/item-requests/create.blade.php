<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Permintaan Barang</title>
    <style>
        :root { color-scheme: light; --ink: #18343a; --muted: #61747a; --line: #d7e0df; --paper: #fff; --canvas: #eef4f2; --brand: #087f78; --brand-dark: #06635e; --accent: #e0a333; --error: #a83232; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--canvas); color: var(--ink); font-family: "Segoe UI", sans-serif; }
        .shell { max-width: 980px; margin: 0 auto; padding: 36px 20px 56px; }
        .masthead { display: flex; justify-content: space-between; align-items: end; gap: 16px; border-bottom: 3px solid var(--brand); padding-bottom: 18px; }
        .eyebrow { margin: 0 0 8px; color: var(--brand-dark); font-size: 12px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { margin: 0; font-size: 30px; line-height: 1.15; }
        .lede { margin: 8px 0 0; color: var(--muted); }
        .stamp { flex: 0 0 auto; border-left: 3px solid var(--accent); padding: 8px 0 8px 12px; color: var(--muted); font-size: 13px; }
        form { margin-top: 24px; }
        .section { margin-top: 18px; padding: 22px; background: var(--paper); border: 1px solid var(--line); border-radius: 6px; }
        .section-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; }
        h2 { margin: 0; font-size: 17px; }
        .fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        label { display: grid; gap: 7px; color: #314d52; font-size: 13px; font-weight: 650; }
        input, select { width: 100%; min-height: 42px; border: 1px solid #bacac8; border-radius: 4px; background: white; color: var(--ink); padding: 9px 11px; font: inherit; }
        input[readonly] { background: #f3f6f5; color: #51666b; }
        input:focus, select:focus { outline: 3px solid #9bd6ce; border-color: var(--brand); }
        .detail-row { display: grid; grid-template-columns: minmax(210px, 2.5fr) minmax(110px, 1fr) minmax(110px, 1fr) minmax(130px, 1.2fr) 38px; gap: 10px; align-items: end; padding: 14px 0; border-top: 1px solid var(--line); }
        .detail-row:first-child { border-top: 0; }
        .button { display: inline-flex; justify-content: center; align-items: center; min-height: 40px; padding: 9px 14px; border: 0; border-radius: 4px; background: var(--brand); color: white; font: inherit; font-weight: 700; cursor: pointer; }
        .button:hover { background: var(--brand-dark); }
        .button-secondary { border: 1px solid var(--brand); background: white; color: var(--brand-dark); }
        .button-secondary:hover { background: #e9f5f3; }
        .button-remove { width: 38px; height: 40px; padding: 0; background: #f8e9e7; color: var(--error); font-size: 20px; }
        .button-remove:hover { background: #f1d5d1; }
        .form-footer { display: flex; justify-content: flex-end; margin-top: 18px; }
        .notice, .errors { margin-top: 18px; padding: 14px 16px; border-radius: 4px; }
        .notice { border-left: 4px solid var(--brand); background: #e1f3ed; }
        .errors { border-left: 4px solid var(--error); background: #fae8e6; color: #742626; }
        .errors ul { margin: 6px 0 0; padding-left: 20px; }
        .field-error { color: var(--error); font-size: 12px; font-weight: 500; }
        @media (max-width: 700px) {
            .shell { padding: 22px 14px 36px; }
            .masthead { align-items: start; flex-direction: column; }
            h1 { font-size: 25px; }
            .section { padding: 16px; }
            .fields { grid-template-columns: 1fr; }
            .detail-row { grid-template-columns: minmax(0, 1fr) minmax(95px, .7fr) 38px; gap: 10px; }
            .detail-row .field-item { grid-column: 1 / -1; }
            .detail-row .field-uom { grid-column: 1; }
            .detail-row .field-balance { grid-column: 2; }
            .detail-row .button-remove { grid-column: 3; grid-row: 2; }
            .section-heading { align-items: start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <main class="shell">
        <header class="masthead">
            <div>
                <p class="eyebrow">Inventory · Warehouse</p>
                <h1>Permintaan Barang</h1>
                <p class="lede">Isi detail pemohon dan barang yang dibutuhkan.</p>
            </div>
            <div class="stamp">Formulir permintaan publik</div>
        </header>

        @if (session('request_no'))
            <div class="notice" role="status">Permintaan terkirim. Nomor permintaan: <strong>{{ session('request_no') }}</strong></div>
        @endif

        @if ($errors->any())
            <div class="errors" role="alert">
                Periksa kembali data yang dimasukkan.
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="post" action="{{ route('public.item-requests.store') }}" autocomplete="on">
            @csrf
            <section class="section">
                <div class="section-heading"><h2>Informasi Pemohon</h2></div>
                <div class="fields">
                    <label>Email
                        <input type="email" name="requester_email" value="{{ old('requester_email') }}" maxlength="255" autocomplete="email" required>
                        @error('requester_email')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label>Nama
                        <input type="text" name="requester_name" value="{{ old('requester_name') }}" maxlength="150" autocomplete="name" required>
                        @error('requester_name')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label>Department
                        <input type="text" name="department" value="{{ old('department') }}" maxlength="150" required>
                        @error('department')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label>Gudang
                        <select name="warehouseid" id="warehouseid" required>
                            <option value="">Pilih gudang</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->warehouseid }}" @selected(old('warehouseid') == $warehouse->warehouseid)>{{ $warehouse->warehouse_name }}</option>
                            @endforeach
                        </select>
                        @error('warehouseid')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <h2>Detail Barang</h2>
                    <button class="button button-secondary" type="button" id="add-detail">+ Tambah Barang</button>
                </div>
                <div id="details-list">
                    @foreach (old('details', [['item_code' => '', 'quantity' => '']]) as $index => $detail)
                        <div class="detail-row">
                            <label class="field-item">Kode + Nama Item
                                <select name="details[{{ $index }}][item_code]" class="item-code" required>
                                    <option value="">Pilih item</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->item_code }}" data-uom="{{ $item->uom1 }}" @selected(($detail['item_code'] ?? '') === $item->item_code)>{{ $item->item_code }} - {{ $item->description }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Quantity
                                <input type="number" name="details[{{ $index }}][quantity]" value="{{ $detail['quantity'] ?? '' }}" min="0.01" step="0.01" required>
                            </label>
                            <label class="field-uom">UOM
                                <input type="text" class="uom" value="" readonly>
                            </label>
                            <label class="field-balance">Current Balance
                                <input type="text" class="balance" value="" readonly>
                            </label>
                            <button class="button button-remove remove-detail" type="button" aria-label="Hapus barang">&times;</button>
                        </div>
                    @endforeach
                </div>
                @error('details')<span class="field-error">{{ $message }}</span>@enderror
                @error('details.*.item_code')<span class="field-error">{{ $message }}</span>@enderror
                @error('details.*.quantity')<span class="field-error">{{ $message }}</span>@enderror
            </section>

            <div class="form-footer"><button class="button" type="submit">Kirim Permintaan</button></div>
        </form>
    </main>

    <template id="detail-template">
        <div class="detail-row">
            <label class="field-item">Kode + Nama Item
                <select name="details[__INDEX__][item_code]" class="item-code" required>
                    <option value="">Pilih item</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->item_code }}" data-uom="{{ $item->uom1 }}">{{ $item->item_code }} - {{ $item->description }}</option>
                    @endforeach
                </select>
            </label>
            <label>Quantity
                <input type="number" name="details[__INDEX__][quantity]" min="0.01" step="0.01" required>
            </label>
            <label class="field-uom">UOM
                <input type="text" class="uom" readonly>
            </label>
            <label class="field-balance">Current Balance
                <input type="text" class="balance" readonly>
            </label>
            <button class="button button-remove remove-detail" type="button" aria-label="Hapus barang">&times;</button>
        </div>
    </template>

    <script>
        const detailList = document.getElementById('details-list');
        const detailTemplate = document.getElementById('detail-template');
        const warehouseSelect = document.getElementById('warehouseid');
        const balanceUrl = @json($balanceUrl);
        let nextIndex = {{ count(old('details', [['item_code' => '', 'quantity' => '']])) }};

        async function refreshBalance(row) {
            const itemSelect = row.querySelector('.item-code');
            const selectedItem = itemSelect.options[itemSelect.selectedIndex];
            const uomInput = row.querySelector('.uom');
            const balanceInput = row.querySelector('.balance');
            uomInput.value = selectedItem?.dataset.uom || '';
            balanceInput.value = '';

            if (!itemSelect.value || !warehouseSelect.value) return;

            const params = new URLSearchParams({ warehouseid: warehouseSelect.value, item_code: itemSelect.value });
            try {
                const response = await fetch(`${balanceUrl}?${params}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) return;
                const data = await response.json();
                uomInput.value = data.uom || '';
                balanceInput.value = Number(data.current_balance).toFixed(2);
            } catch {
                balanceInput.value = 'Tidak tersedia';
            }
        }

        function bindRow(row) {
            row.querySelector('.item-code').addEventListener('change', () => refreshBalance(row));
            row.querySelector('.remove-detail').addEventListener('click', () => {
                if (detailList.children.length > 1) row.remove();
            });
            refreshBalance(row);
        }

        document.querySelectorAll('.detail-row').forEach(bindRow);
        warehouseSelect.addEventListener('change', () => detailList.querySelectorAll('.detail-row').forEach(refreshBalance));
        document.getElementById('add-detail').addEventListener('click', () => {
            const markup = detailTemplate.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
            detailList.insertAdjacentHTML('beforeend', markup);
            bindRow(detailList.lastElementChild);
        });
    </script>
</body>
</html>
