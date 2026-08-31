@php
    if (old('product_targets')) {
        $existingProductTargetsForJs = collect(old('product_targets'))->values()->all();
    } elseif (isset($target)) {
        $existingProductTargetsForJs = $target->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'order_target' => (float) $item->order_target,
            'collection_target' => (float) $item->collection_target,
            'quantity_target' => $item->quantity_target,
        ])->all();
    } else {
        $existingProductTargetsForJs = [];
    }

    $defaultMode = old('mode', isset($target) && $target->isProductWise() ? 'product_wise' : 'single');
@endphp

@csrf
@if (isset($target))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
            <option value="">— Select Executive —</option>
            @foreach ($executives as $executive)
                <option value="{{ $executive->id }}" data-sales-team="{{ $executive->sales_team_id }}" @selected((string) old('user_id', $target->user_id ?? '') === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Month <span class="text-danger">*</span></label>
        <select name="month" class="form-select @error('month') is-invalid @enderror" required>
            @foreach (range(1, 12) as $month)
                <option value="{{ $month }}" @selected((string) old('month', $target->month ?? now()->month) === (string) $month)>{{ \Illuminate\Support\Carbon::create(2000, $month, 1)->format('F') }}</option>
            @endforeach
        </select>
        @error('month') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Year <span class="text-danger">*</span></label>
        <input type="number" name="year" class="form-control @error('year') is-invalid @enderror"
               value="{{ old('year', $target->year ?? now()->year) }}" required>
        @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label d-block">Target Type</label>
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="mode" id="modeSingle" value="single" autocomplete="off" @checked($defaultMode === 'single')>
            <label class="btn btn-outline-primary" for="modeSingle">Single Target</label>

            <input type="radio" class="btn-check" name="mode" id="modeProductWise" value="product_wise" autocomplete="off" @checked($defaultMode === 'product_wise')>
            <label class="btn btn-outline-primary" for="modeProductWise">Product-wise Targets</label>
        </div>
        <div class="form-text">Set one overall target, or break it down per product — the totals shown everywhere else always match either way.</div>
    </div>

    <div class="col-12" id="singleTargetFields">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Order Value Target <span class="text-danger">*</span></label>
                <input type="number" min="1" step="0.01" name="order_value_target" class="form-control @error('order_value_target') is-invalid @enderror"
                       value="{{ old('order_value_target', $target->order_value_target ?? '') }}">
                @error('order_value_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Collection Target <span class="text-danger">*</span></label>
                <input type="number" min="1" step="0.01" name="collection_target" class="form-control @error('collection_target') is-invalid @enderror"
                       value="{{ old('collection_target', $target->collection_target ?? '') }}">
                @error('collection_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Quantity Target (units) <span class="text-danger">*</span></label>
                <input type="number" min="1" step="1" name="quantity_target" class="form-control @error('quantity_target') is-invalid @enderror"
                       value="{{ old('quantity_target', $target->quantity_target ?? '') }}">
                @error('quantity_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="col-12" id="productWiseFields">
        <label class="form-label mb-2">Product Targets</label>
        @error('product_targets') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

        <div class="table-responsive">
            <table class="table align-middle" id="productTargetsTable">
                <thead>
                    <tr>
                        <th style="min-width:220px">Product</th>
                        <th style="width:150px">Order Target</th>
                        <th style="width:150px">Collection Target</th>
                        <th style="width:140px">Quantity Target</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="productTargetsBody"></tbody>
                <tfoot>
                    <tr>
                        <td class="text-end fw-semibold">Totals</td>
                        <td class="fw-semibold" id="orderTargetTotal">0.00</td>
                        <td class="fw-semibold" id="collectionTargetTotal">0.00</td>
                        <td class="fw-semibold" id="quantityTargetTotal">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <button type="button" id="addProductTargetBtn" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-plus me-1"></i>Add Product
        </button>
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $target->notes ?? '') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($target) ? 'Update Target' : 'Assign Target' }}</button>
    <a href="{{ route('targets.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

{{-- Product options shared by every product-target row; data-sales-team
     drives the client-side filter below (empty = company-wide product,
     only shown when the selected executive also has no team). --}}
<template id="targetProductOptionsTemplate">
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
            const singleFields = document.getElementById('singleTargetFields');
            const productWiseFields = document.getElementById('productWiseFields');
            const singleInputs = singleFields.querySelectorAll('input');
            const executiveSelect = document.querySelector('select[name="user_id"]');

            function syncMode() {
                const isProductWise = modeProductWise.checked;

                singleFields.classList.toggle('d-none', isProductWise);
                productWiseFields.classList.toggle('d-none', ! isProductWise);

                // Disabled (not just hidden) fields don't get submitted at
                // all, so the server only ever receives the active mode's
                // values — matching how TargetService/StoreTargetRequest
                // treat the other mode's fields as absent, not zero.
                singleInputs.forEach((input) => { input.disabled = isProductWise; input.required = ! isProductWise; });
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

            const productTargetsBody = document.getElementById('productTargetsBody');
            const addProductTargetBtn = document.getElementById('addProductTargetBtn');
            const allProductOptionEls = Array.from(document.getElementById('targetProductOptionsTemplate').content.querySelectorAll('option'));
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
            // — e.g. a target saved before the executive changed teams
            // should still show what was actually assigned.
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
                productTargetsBody.querySelectorAll('.product-select').forEach((select) => {
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
                        <select name="product_targets[${index}][product_id]" class="form-select form-select-sm product-select"></select>
                    </td>
                    <td>
                        <input type="number" name="product_targets[${index}][order_target]" class="form-control form-control-sm order-target-input" min="0" step="0.01" value="${item.order_target ?? ''}">
                    </td>
                    <td>
                        <input type="number" name="product_targets[${index}][collection_target]" class="form-control form-control-sm collection-target-input" min="0" step="0.01" value="${item.collection_target ?? ''}">
                    </td>
                    <td>
                        <input type="number" name="product_targets[${index}][quantity_target]" class="form-control form-control-sm quantity-target-input" min="0" step="1" value="${item.quantity_target ?? ''}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="ti ti-trash"></i></button>
                    </td>
                `;
                productTargetsBody.appendChild(tr);

                const productSelect = tr.querySelector('.product-select');
                applyProductTeamFilter(productSelect, item.product_id ?? '');

                [
                    tr.querySelector('.order-target-input'),
                    tr.querySelector('.collection-target-input'),
                    tr.querySelector('.quantity-target-input'),
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

                productTargetsBody.querySelectorAll('tr').forEach((tr) => {
                    orderTotal += parseFloat(tr.querySelector('.order-target-input').value) || 0;
                    collectionTotal += parseFloat(tr.querySelector('.collection-target-input').value) || 0;
                    quantityTotal += parseInt(tr.querySelector('.quantity-target-input').value, 10) || 0;
                });

                document.getElementById('orderTargetTotal').textContent = orderTotal.toFixed(2);
                document.getElementById('collectionTargetTotal').textContent = collectionTotal.toFixed(2);
                document.getElementById('quantityTargetTotal').textContent = quantityTotal;
            }

            addProductTargetBtn.addEventListener('click', function () {
                buildRow();
                recalculateTotals();
            });

            const existingProductTargets = @json($existingProductTargetsForJs);

            if (existingProductTargets.length > 0) {
                existingProductTargets.forEach((item) => buildRow(item));
            } else {
                buildRow();
            }

            recalculateTotals();
        });
    </script>
@endpush
