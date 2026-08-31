@php
    if (old('achievement_items')) {
        $existingItemsForJs = collect(old('achievement_items'))->values()->all();
    } elseif (isset($entry)) {
        $existingItemsForJs = $entry->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'order_achieved' => (float) $item->order_achieved,
            'collection_achieved' => (float) $item->collection_achieved,
            'quantity_achieved' => $item->quantity_achieved,
        ])->all();
    } else {
        $existingItemsForJs = [];
    }

    $defaultMode = old('mode', isset($entry) && $entry->isProductWise() ? 'product_wise' : 'single');

    // A plain Sales Executive can only ever report their own achievement —
    // locked to their own name instead of offered as a choice. The select
    // stays disabled (so it can't be tampered with via the UI) and a
    // matching submit-time re-enable below keeps its value in the POST
    // body, same pattern already used on the Order and Target forms.
    $lockExecutiveField = auth()->user()->isSalesExecutiveOnly();
@endphp

@csrf
@if (isset($entry))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
        <select name="user_id" id="executiveSelect" class="form-select @error('user_id') is-invalid @enderror" required @disabled($lockExecutiveField)>
            <option value="">— Select Executive —</option>
            @foreach ($executives as $executive)
                <option value="{{ $executive->id }}" data-sales-team="{{ $executive->sales_team_id }}" @selected((string) old('user_id', $entry->user_id ?? ($lockExecutiveField ? auth()->id() : '')) === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror"
               value="{{ old('entry_date', isset($entry) ? $entry->entry_date->toDateString() : now()->toDateString()) }}" required>
        @error('entry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label d-block">Achievement Type</label>
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="mode" id="modeSingle" value="single" autocomplete="off" @checked($defaultMode === 'single')>
            <label class="btn btn-outline-primary" for="modeSingle">Single Achievement</label>

            <input type="radio" class="btn-check" name="mode" id="modeProductWise" value="product_wise" autocomplete="off" @checked($defaultMode === 'product_wise')>
            <label class="btn btn-outline-primary" for="modeProductWise">Product-wise Achievement</label>
        </div>
        <div class="form-text">Report one overall figure for the day, or break it down per product — matches however that month's Target was set.</div>
    </div>

    <div class="col-12" id="singleAchievementFields">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Order Value Achieved <span class="text-danger">*</span></label>
                <input type="number" min="0" step="0.01" name="order_value_achieved" class="form-control @error('order_value_achieved') is-invalid @enderror"
                       value="{{ old('order_value_achieved', $entry->order_value_achieved ?? '') }}">
                @error('order_value_achieved') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Collection Achieved <span class="text-danger">*</span></label>
                <input type="number" min="0" step="0.01" name="collection_achieved" class="form-control @error('collection_achieved') is-invalid @enderror"
                       value="{{ old('collection_achieved', $entry->collection_achieved ?? '') }}">
                @error('collection_achieved') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Quantity Achieved (units) <span class="text-danger">*</span></label>
                <input type="number" min="0" step="1" name="quantity_achieved" class="form-control @error('quantity_achieved') is-invalid @enderror"
                       value="{{ old('quantity_achieved', $entry->quantity_achieved ?? '') }}">
                @error('quantity_achieved') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="col-12" id="productWiseFields">
        <label class="form-label mb-2">Product Achievements</label>
        @error('achievement_items') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

        <div class="table-responsive">
            <table class="table align-middle" id="achievementItemsTable">
                <thead>
                    <tr>
                        <th style="min-width:220px">Product</th>
                        <th style="width:150px">Order Achieved</th>
                        <th style="width:150px">Collection Achieved</th>
                        <th style="width:140px">Quantity Achieved</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="achievementItemsBody"></tbody>
                <tfoot>
                    <tr>
                        <td class="text-end fw-semibold">Totals</td>
                        <td class="fw-semibold" id="orderAchievedTotal">0.00</td>
                        <td class="fw-semibold" id="collectionAchievedTotal">0.00</td>
                        <td class="fw-semibold" id="quantityAchievedTotal">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <button type="button" id="addAchievementItemBtn" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-plus me-1"></i>Add Product
        </button>
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $entry->notes ?? '') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($entry) ? 'Update Achievement' : 'Record Achievement' }}</button>
    <a href="{{ route('achievements.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

{{-- Product options shared by every achievement-item row; data-sales-team
     drives the client-side filter below (empty = company-wide product,
     only shown when the selected executive also has no team). --}}
<template id="achievementProductOptionsTemplate">
    <option value="">— Select Product —</option>
    @foreach ($products as $product)
        <option value="{{ $product->id }}" data-sales-team="{{ $product->sales_team_id }}">{{ $product->name }} ({{ $product->sku }})</option>
    @endforeach
</template>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modeSingle = document.getElementById('modeSingle');
            const modeProductWise = document.getElementById('modeProductWise');
            const singleFields = document.getElementById('singleAchievementFields');
            const productWiseFields = document.getElementById('productWiseFields');
            const singleInputs = singleFields.querySelectorAll('input');
            const executiveSelect = document.getElementById('executiveSelect');

            // A disabled select's value is never included in form submission
            // — re-enable it right before the browser serializes the form so
            // a locked-in Sales Executive still gets sent (same pattern as
            // the Order and Target forms' own executive lock).
            executiveSelect?.closest('form')?.addEventListener('submit', function () {
                executiveSelect.disabled = false;
            });

            function syncMode() {
                const isProductWise = modeProductWise.checked;

                singleFields.classList.toggle('d-none', isProductWise);
                productWiseFields.classList.toggle('d-none', ! isProductWise);

                // Disabled (not just hidden) fields don't get submitted at
                // all, so the server only ever receives the active mode's
                // values — matching how AchievementEntryService/
                // StoreAchievementEntryRequest treat the other mode's
                // fields as absent, not zero.
                singleInputs.forEach((input) => { input.disabled = isProductWise; input.required = ! isProductWise; });

                // The product-wise table always has at least one row built
                // by JS (see buildRow() below), even while in single mode —
                // without disabling its inputs too, that blank row's empty
                // product_id still gets submitted, and achievement_items
                // merely being present in the request is enough to trigger
                // achievement_items.*.product_id's required_with rule,
                // failing validation with no visible error anywhere on this
                // page (nothing renders a message for that specific nested key).
                productWiseFields.querySelectorAll('select, input').forEach((el) => { el.disabled = ! isProductWise; });
            }

            modeSingle.addEventListener('change', syncMode);
            modeProductWise.addEventListener('change', syncMode);
            syncMode();

            // Product-wise mode needs an executive selected up front — that's
            // what the product list below gets filtered by team against —
            // so nudge the user straight there instead of letting them start
            // filling rows against an unfiltered/blank team.
            modeProductWise.addEventListener('change', function () {
                if (this.checked && executiveSelect && ! executiveSelect.value) {
                    window.$?.fn.select2 ? window.$(executiveSelect).select2('open') : executiveSelect.focus();
                }
            });

            const achievementItemsBody = document.getElementById('achievementItemsBody');
            const addAchievementItemBtn = document.getElementById('addAchievementItemBtn');
            const allProductOptionEls = Array.from(document.getElementById('achievementProductOptionsTemplate').content.querySelectorAll('option'));
            let rowIndex = 0;

            // The executive's sales_team_id (from the selected <option>'s
            // data attribute), or '' when none is selected / they have no
            // team — matches Product::scopeOwnedByTeam()'s own rule that a
            // team-less viewer sees everything unrestricted, but a viewer
            // with a team sees ONLY that team's products (not team-less
            // ones too).
            function selectedExecutiveTeamId() {
                return executiveSelect?.selectedOptions[0]?.dataset.salesTeam || '';
            }

            // Rebuilds a product <select>'s options to strictly the
            // executive's own team's products when they have a team, or
            // every product when they don't. If a previously chosen value
            // (desiredValue, or the select's current value) no longer
            // qualifies, it's preserved anyway rather than silently dropped
            // — e.g. an achievement saved before the executive changed
            // teams should still show what was actually reported.
            function applyProductTeamFilter(select, desiredValue) {
                const teamId = selectedExecutiveTeamId();
                const value = desiredValue !== undefined ? String(desiredValue) : select.value;

                select.innerHTML = '';
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '— Select Product —';
                select.appendChild(placeholder);

                allProductOptionEls.forEach((opt) => {
                    if (!opt.value) return;
                    const optTeam = opt.dataset.salesTeam || '';
                    if (teamId && optTeam !== teamId) return;
                    select.appendChild(opt.cloneNode(true));
                });

                if (value && ! Array.from(select.options).some((o) => o.value === value)) {
                    const original = allProductOptionEls.find((o) => o.value === value);
                    if (original) select.appendChild(original.cloneNode(true));
                }

                select.value = value || '';
            }

            function refilterAllProductRows() {
                achievementItemsBody.querySelectorAll('.product-select').forEach((select) => {
                    applyProductTeamFilter(select);
                    window.refreshSelect2?.(select);
                });
            }

            if (executiveSelect) {
                if (window.$) {
                    window.$(executiveSelect).on('change', refilterAllProductRows);
                } else {
                    executiveSelect.addEventListener('change', refilterAllProductRows);
                }
            }

            function buildRow(item) {
                item = item || {};
                const index = rowIndex++;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <select name="achievement_items[${index}][product_id]" class="form-select form-select-sm product-select"></select>
                    </td>
                    <td>
                        <input type="number" name="achievement_items[${index}][order_achieved]" class="form-control form-control-sm order-achieved-input" min="0" step="0.01" value="${item.order_achieved ?? ''}">
                    </td>
                    <td>
                        <input type="number" name="achievement_items[${index}][collection_achieved]" class="form-control form-control-sm collection-achieved-input" min="0" step="0.01" value="${item.collection_achieved ?? ''}">
                    </td>
                    <td>
                        <input type="number" name="achievement_items[${index}][quantity_achieved]" class="form-control form-control-sm quantity-achieved-input" min="0" step="1" value="${item.quantity_achieved ?? ''}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="ti ti-trash"></i></button>
                    </td>
                `;
                achievementItemsBody.appendChild(tr);

                const productSelect = tr.querySelector('.product-select');
                applyProductTeamFilter(productSelect, item.product_id ?? '');

                [
                    tr.querySelector('.order-achieved-input'),
                    tr.querySelector('.collection-achieved-input'),
                    tr.querySelector('.quantity-achieved-input'),
                ].forEach((el) => el.addEventListener('input', recalculateTotals));

                tr.querySelector('.remove-item-btn').addEventListener('click', function () {
                    tr.remove();
                    recalculateTotals();
                });

                // Newly inserted after the page's initial Select2 scan, so it
                // needs its own pass — see select2-init.js.
                window.initSelect2?.();

                return tr;
            }

            function recalculateTotals() {
                let orderTotal = 0;
                let collectionTotal = 0;
                let quantityTotal = 0;

                achievementItemsBody.querySelectorAll('tr').forEach((tr) => {
                    orderTotal += parseFloat(tr.querySelector('.order-achieved-input').value) || 0;
                    collectionTotal += parseFloat(tr.querySelector('.collection-achieved-input').value) || 0;
                    quantityTotal += parseInt(tr.querySelector('.quantity-achieved-input').value, 10) || 0;
                });

                document.getElementById('orderAchievedTotal').textContent = orderTotal.toFixed(2);
                document.getElementById('collectionAchievedTotal').textContent = collectionTotal.toFixed(2);
                document.getElementById('quantityAchievedTotal').textContent = quantityTotal;
            }

            addAchievementItemBtn.addEventListener('click', function () {
                buildRow();
                recalculateTotals();
            });

            const existingItems = @json($existingItemsForJs);

            if (existingItems.length > 0) {
                existingItems.forEach((item) => buildRow(item));
            } else {
                buildRow();
            }

            recalculateTotals();

            // Re-run now that the table actually has row(s) in it — the
            // first call above ran against an empty tbody, before these
            // rows existed to disable.
            syncMode();
        });
    </script>
@endpush
