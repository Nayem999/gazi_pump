@csrf
@if (isset($visitPlan))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
        <select name="user_id" id="planExecutive" class="form-select @error('user_id') is-invalid @enderror" required>
            <option value="">— Select Executive —</option>
            @foreach ($executives as $executive)
                <option value="{{ $executive->id }}" @selected((string) old('user_id', $visitPlan->user_id ?? '') === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if (isset($visitPlan))
        <div class="col-md-6">
            <label class="form-label">Dealer <span class="text-danger">*</span></label>
            <select name="dealer_id" class="form-select @error('dealer_id') is-invalid @enderror" required>
                <option value="">— Select Dealer —</option>
                @foreach ($dealers as $dealer)
                    <option value="{{ $dealer->id }}" @selected((string) old('dealer_id', $visitPlan->dealer_id) === (string) $dealer->id)>{{ $dealer->name }} ({{ $dealer->dealer_code }})</option>
                @endforeach
            </select>
            @error('dealer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        @php
            $oldTerritoryId = old('territory_id', $visitPlan->territory_id ?? null);
            $oldTerritory = (string) $oldTerritoryId === (string) ($visitPlan->territory_id ?? '')
                ? $visitPlan->territory
                : ($oldTerritoryId ? \App\Models\Territory::find($oldTerritoryId) : null);
        @endphp
        <div class="col-md-6">
            <label class="form-label">Territory</label>
            <select name="territory_id" id="visitPlanTerritory" class="form-select @error('territory_id') is-invalid @enderror" data-ajax-url="{{ route('territories.options') }}">
                <option value="">— Auto (from dealer) —</option>
                @if ($oldTerritory)
                    <option value="{{ $oldTerritory->id }}" selected>{{ $oldTerritory->name }}</option>
                @endif
            </select>
            <div class="form-text">Leave blank to use the dealer's own territory automatically.</div>
            @error('territory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @else
        {{--
            Guided flow: Executive -> Territory (only the ones actually
            assigned to that executive) -> picking a Territory auto-adds
            every dealer in it below. The manual search box stays available
            too, for adding dealers outside the picked territory (or when
            the executive has none assigned yet).
        --}}
        <div class="col-md-6">
            <label class="form-label">Territory</label>
            <select name="territory_id" id="planTerritory" class="form-select @error('territory_id') is-invalid @enderror" disabled>
                <option value="">— Select Executive First —</option>
            </select>
            <div class="form-text">Shows this executive's assigned territories; picking one adds all of its dealers below.</div>
            @error('territory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <label class="form-label">Dealer(s) <span class="text-danger">*</span></label>
            <div class="position-relative mb-2">
                <input type="text" id="dealerSearchInput" class="form-control" placeholder="Type a dealer name or code to search and add..." autocomplete="off">
                <div id="dealerSearchResults" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index:1000;max-height:260px;overflow-y:auto"></div>
            </div>
            <div id="dealerChips" class="d-flex flex-wrap gap-2"></div>
            <div class="form-text">Picking a territory above adds every dealer in it — you can still add more, or remove any, individually. A separate visit plan is created for each dealer.</div>
            @error('dealer_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            @error('dealer_ids.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    @endif

    <div class="col-md-4">
        <label class="form-label">Planned Date <span class="text-danger">*</span></label>
        <input type="date" name="planned_date" class="form-control @error('planned_date') is-invalid @enderror"
               value="{{ old('planned_date', isset($visitPlan) ? $visitPlan->planned_date->toDateString() : '') }}" required>
        @error('planned_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected((string) old('status', $visitPlan->status->value ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $visitPlan->notes ?? '') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($visitPlan) ? 'Update Visit Plan' : 'Plan Visit' }}</button>
    <a href="{{ route('visit-plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@unless (isset($visitPlan))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('dealerSearchInput');
                const resultsBox = document.getElementById('dealerSearchResults');
                const chipsContainer = document.getElementById('dealerChips');
                const optionsUrl = '{{ route('dealers.options') }}';
                let searchTimer = null;

                function addChip(id, name) {
                    if (chipsContainer.querySelector(`[data-dealer-chip="${id}"]`)) {
                        return;
                    }

                    const chip = document.createElement('span');
                    chip.className = 'badge text-bg-secondary d-inline-flex align-items-center gap-2 py-2 px-3';
                    chip.dataset.dealerChip = id;
                    chip.innerHTML = `${name} <input type="hidden" name="dealer_ids[]" value="${id}"><button type="button" class="btn-close btn-close-white" style="font-size:.6rem" aria-label="Remove" data-remove-dealer-chip></button>`;
                    chipsContainer.appendChild(chip);
                }

                function hideResults() {
                    resultsBox.classList.add('d-none');
                    resultsBox.innerHTML = '';
                }

                function renderResults(items) {
                    if (!items.length) {
                        resultsBox.innerHTML = '<div class="list-group-item text-muted small">No matching dealers</div>';
                        resultsBox.classList.remove('d-none');
                        return;
                    }

                    resultsBox.innerHTML = items.map((item) => {
                        const label = `${item.name} (${item.dealer_code})`;
                        return `
                            <button type="button" class="list-group-item list-group-item-action" data-result-id="${item.id}" data-result-name="${label}">
                                ${label}
                            </button>
                        `;
                    }).join('');
                    resultsBox.classList.remove('d-none');
                }

                function search(query) {
                    clearTimeout(searchTimer);

                    searchTimer = setTimeout(function () {
                        fetch(`${optionsUrl}?search=${encodeURIComponent(query)}`, { headers: { Accept: 'application/json' } })
                            .then((r) => r.json())
                            .then(renderResults);
                    }, 200);
                }

                searchInput.addEventListener('input', function () {
                    search(searchInput.value.trim());
                });

                searchInput.addEventListener('focus', function () {
                    search(searchInput.value.trim());
                });

                resultsBox.addEventListener('click', function (e) {
                    const button = e.target.closest('[data-result-id]');
                    if (!button) {
                        return;
                    }

                    addChip(button.dataset.resultId, button.dataset.resultName);
                    searchInput.value = '';
                    hideResults();
                    searchInput.focus();
                });

                document.addEventListener('click', function (e) {
                    if (!e.target.closest('#dealerSearchInput') && !e.target.closest('#dealerSearchResults')) {
                        hideResults();
                    }
                });

                chipsContainer.addEventListener('click', function (e) {
                    if (e.target.closest('[data-remove-dealer-chip]')) {
                        e.target.closest('[data-dealer-chip]').remove();
                    }
                });

                // Guided flow: Executive -> Territory (scoped to that
                // executive's own assignments) -> picking a Territory
                // auto-adds every dealer in it as a chip above. Additive,
                // not a reset — it won't remove dealers already added
                // manually or from a previously picked territory.
                const executiveSelect = document.getElementById('planExecutive');
                const territorySelect = document.getElementById('planTerritory');

                window.initCascadingSelect(
                    executiveSelect,
                    territorySelect,
                    '{{ route('territories.options') }}',
                    'user_id',
                    { placeholder: '— Select Territory —' }
                );

                window.$(territorySelect).on('change', function () {
                    const territoryId = territorySelect.value;
                    if (!territoryId) {
                        return;
                    }

                    fetch(`${optionsUrl}?territory_id=${territoryId}`, { headers: { Accept: 'application/json' } })
                        .then((r) => r.json())
                        .then((items) => {
                            items.forEach((item) => addChip(item.id, `${item.name} (${item.dealer_code})`));
                        });
                });
            });
        </script>
    @endpush
@endunless
