@php
    if (old('items')) {
        $existingItemsForJs = collect(old('items'))->values()->all();
    } elseif (isset($order)) {
        $existingItemsForJs = $order->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'discount_amount' => (float) $item->discount_amount,
        ])->all();
    } else {
        $existingItemsForJs = [];
    }
@endphp

@csrf
@if (isset($order))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
            <option value="">— Select Executive —</option>
            @foreach ($executives as $executive)
                <option value="{{ $executive->id }}" @selected((string) old('user_id', $order->user_id ?? '') === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Dealer <span class="text-danger">*</span></label>
        <select name="dealer_id" class="form-select @error('dealer_id') is-invalid @enderror" required>
            <option value="">— Select Dealer —</option>
            @foreach ($dealers as $dealer)
                <option value="{{ $dealer->id }}" @selected((string) old('dealer_id', $order->dealer_id ?? '') === (string) $dealer->id)>{{ $dealer->name }} ({{ $dealer->dealer_code }})</option>
            @endforeach
        </select>
        @error('dealer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Order Date <span class="text-danger">*</span></label>
        <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror"
               value="{{ old('order_date', isset($order) ? $order->order_date->toDateString() : now()->toDateString()) }}" required>
        @error('order_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label mb-2">Products <span class="text-danger">*</span></label>
        @error('items') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

        <div class="table-responsive">
            <table class="table align-middle" id="itemsTable">
                <thead>
                    <tr>
                        <th style="min-width:220px">Product</th>
                        <th style="width:100px">Quantity</th>
                        <th style="width:130px">Unit Price</th>
                        <th style="width:130px">Discount</th>
                        <th style="width:130px">Line Total</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>

        <button type="button" id="addItemBtn" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-plus me-1"></i>Add Product
        </button>

        <div class="alert alert-info d-flex justify-content-between mt-3 mb-0">
            <span>Grand Total</span>
            <strong id="grandTotalPreview">0.00</strong>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks', $order->remarks ?? '') }}</textarea>
        @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($order) ? 'Update Order' : 'Record Order' }}</button>
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

{{-- Product options shared by every line-item row --}}
<template id="productOptionsTemplate">
    <option value="">— Select Product —</option>
    @foreach ($products as $product)
        <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} ({{ $product->sku }})</option>
    @endforeach
</template>

@push('scripts')
    <script>
        // Wrapped in DOMContentLoaded: this inline script runs synchronously
        // as the parser reaches it, but window.$ (jQuery, needed by the
        // product select's Select2 change binding below) is only defined
        // once the deferred module bundle (app.js) finishes executing —
        // which happens at/before DOMContentLoaded, not before.
        document.addEventListener('DOMContentLoaded', function () {
            const itemsBody = document.getElementById('itemsBody');
            const addItemBtn = document.getElementById('addItemBtn');
            const grandTotalPreview = document.getElementById('grandTotalPreview');
            const productOptionsHtml = document.getElementById('productOptionsTemplate').innerHTML;
            const maxDiscountPercent = {{ (float) config('sfa.orders.max_discount_percent') }};
            let rowIndex = 0;

            function buildRow(item) {
                item = item || {};
                const index = rowIndex++;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <select name="items[${index}][product_id]" class="form-select form-select-sm product-select" required>${productOptionsHtml}</select>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][quantity]" class="form-control form-control-sm quantity-input" min="1" step="1" value="${item.quantity || 1}" required>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][unit_price]" class="form-control form-control-sm unit-price-input" min="0" step="0.01" value="${item.unit_price || ''}" required>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][discount_amount]" class="form-control form-control-sm discount-input" min="0" step="0.01" value="${item.discount_amount || 0}">
                    </td>
                    <td class="line-total-preview fw-semibold">0.00</td>
                    <td>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="ti ti-trash"></i></button>
                    </td>
                `;
                itemsBody.appendChild(tr);

                const productSelect = tr.querySelector('.product-select');
                const quantityInput = tr.querySelector('.quantity-input');
                const unitPriceInput = tr.querySelector('.unit-price-input');
                const discountInput = tr.querySelector('.discount-input');

                if (item.product_id) {
                    productSelect.value = item.product_id;
                }

                // Bound through jQuery, not addEventListener: window.initSelect2()
                // below attaches Select2 to this row's product select, which
                // (like every other Select2 field in this app) changes its
                // value via jQuery's own event system rather than dispatching
                // a plain native "change" addEventListener would catch.
                window.$(productSelect).on('change', function () {
                    const option = productSelect.options[productSelect.selectedIndex];
                    const price = option?.dataset.price;
                    if (price && !unitPriceInput.value) {
                        unitPriceInput.value = parseFloat(price).toFixed(2);
                    }
                    recalculate();
                });

                [quantityInput, unitPriceInput, discountInput].forEach((el) => el.addEventListener('input', recalculate));
                tr.querySelector('.remove-item-btn').addEventListener('click', function () {
                    tr.remove();
                    recalculate();
                });

                // Newly inserted after the page's initial Select2 scan, so it
                // needs its own pass — see select2-init.js.
                window.initSelect2?.();

                return tr;
            }

            function recalculate() {
                let grandTotal = 0;

                itemsBody.querySelectorAll('tr').forEach((tr) => {
                    const quantity = parseFloat(tr.querySelector('.quantity-input').value) || 0;
                    const unitPrice = parseFloat(tr.querySelector('.unit-price-input').value) || 0;
                    const discount = parseFloat(tr.querySelector('.discount-input').value) || 0;
                    const subtotal = quantity * unitPrice;
                    const lineTotal = Math.max(0, subtotal - discount);
                    tr.querySelector('.line-total-preview').textContent = lineTotal.toFixed(2);

                    const discountInput = tr.querySelector('.discount-input');
                    const maxDiscount = subtotal * (maxDiscountPercent / 100);
                    discountInput.classList.toggle('is-invalid', discount > maxDiscount);

                    grandTotal += lineTotal;
                });

                grandTotalPreview.textContent = grandTotal.toFixed(2);
            }

            addItemBtn.addEventListener('click', function () {
                buildRow();
                recalculate();
            });

            const existingItems = @json($existingItemsForJs);

            if (existingItems.length > 0) {
                existingItems.forEach((item) => buildRow(item));
            } else {
                buildRow();
            }

            recalculate();
        });
    </script>
@endpush
