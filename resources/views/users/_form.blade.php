@csrf
@if (isset($user))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Employee ID</label>
        <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror"
               value="{{ old('employee_id', $user->employee_id ?? '') }}">
        @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $user->email ?? '') }}" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $user->phone ?? '') }}">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Designation</label>
        <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
               value="{{ old('designation', $user->designation ?? '') }}">
        @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
               value="{{ old('date_of_birth', isset($user) ? $user->date_of_birth?->format('Y-m-d') : '') }}">
        @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Manager</label>
        <select name="manager_id" class="form-select @error('manager_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach ($managers as $manager)
                <option value="{{ $manager->id }}" @selected((string) old('manager_id', $user->manager_id ?? '') === (string) $manager->id)>
                    {{ $manager->name }} ({{ $manager->employee_id }})
                </option>
            @endforeach
        </select>
        @error('manager_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Sales Team</label>
        <select name="sales_team_id" class="form-select @error('sales_team_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach ($salesTeams as $salesTeam)
                <option value="{{ $salesTeam->id }}" @selected((string) old('sales_team_id', $user->sales_team_id ?? '') === (string) $salesTeam->id)>
                    {{ $salesTeam->name }}
                </option>
            @endforeach
        </select>
        @error('sales_team_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Territories</label>
        <div class="position-relative mb-2">
            <input type="text" id="territorySearchInput" class="form-control" placeholder="Type a territory name to search and add..." autocomplete="off">
            <div id="territorySearchResults" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index:1000;max-height:260px;overflow-y:auto"></div>
        </div>
        <div id="territoryChips" class="d-flex flex-wrap gap-2">
            @foreach (($user->territories ?? []) as $territory)
                <span class="badge text-bg-secondary d-inline-flex align-items-center gap-2 py-2 px-3" data-territory-chip="{{ $territory->id }}">
                    {{ $territory->name }}
                    <input type="hidden" name="territory_ids[]" value="{{ $territory->id }}">
                    <button type="button" class="btn-close btn-close-white" style="font-size:.6rem" aria-label="Remove" data-remove-territory-chip></button>
                </span>
            @endforeach
        </div>
        @error('territory_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        @error('territory_ids.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" id="photoInput" accept="image/*" class="form-control @error('photo') is-invalid @enderror">
        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (! empty($user) && $user->photo)
            <img id="photoPreview" src="{{ $user->photoUrl() }}" class="mt-2 rounded" style="height:64px;width:64px;object-fit:cover">
        @else
            <img id="photoPreview" class="mt-2 rounded d-none" style="height:64px;width:64px;object-fit:cover">
        @endif
    </div>

    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                   @checked(old('status', $user->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ isset($user) ? 'New Password' : 'Password' }} @if (! isset($user)) <span class="text-danger">*</span> @endif</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }}>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (isset($user))
            <div class="form-text">Leave blank to keep the current password.</div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label">Roles</label>
        <div class="d-flex flex-wrap gap-3">
            @foreach ($roles as $role)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="role-{{ $role->id }}" name="roles[]" value="{{ $role->name }}"
                           @checked(collect(old('roles', isset($user) ? $user->roles->pluck('name') : []))->contains($role->name))>
                    <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="ti ti-check me-1"></i>{{ isset($user) ? 'Update User' : 'Create User' }}
    </button>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        document.getElementById('photoInput')?.addEventListener('change', function (e) {
            const preview = document.getElementById('photoPreview');
            const file = e.target.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('territorySearchInput');
            const resultsBox = document.getElementById('territorySearchResults');
            const chipsContainer = document.getElementById('territoryChips');
            const optionsUrl = '{{ route('territories.options') }}';
            let searchTimer = null;

            function addChip(id, name) {
                if (chipsContainer.querySelector(`[data-territory-chip="${id}"]`)) {
                    return;
                }

                const chip = document.createElement('span');
                chip.className = 'badge text-bg-secondary d-inline-flex align-items-center gap-2 py-2 px-3';
                chip.dataset.territoryChip = id;
                chip.innerHTML = `${name} <input type="hidden" name="territory_ids[]" value="${id}"><button type="button" class="btn-close btn-close-white" style="font-size:.6rem" aria-label="Remove" data-remove-territory-chip></button>`;
                chipsContainer.appendChild(chip);
            }

            function hideResults() {
                resultsBox.classList.add('d-none');
                resultsBox.innerHTML = '';
            }

            function renderResults(items) {
                if (!items.length) {
                    resultsBox.innerHTML = '<div class="list-group-item text-muted small">No matching territories</div>';
                    resultsBox.classList.remove('d-none');
                    return;
                }

                resultsBox.innerHTML = items.map((item) => `
                    <button type="button" class="list-group-item list-group-item-action" data-result-id="${item.id}" data-result-name="${item.name}">
                        ${item.name}
                    </button>
                `).join('');
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
                if (!e.target.closest('#territorySearchInput') && !e.target.closest('#territorySearchResults')) {
                    hideResults();
                }
            });

            chipsContainer.addEventListener('click', function (e) {
                if (e.target.closest('[data-remove-territory-chip]')) {
                    e.target.closest('[data-territory-chip]').remove();
                }
            });
        });
    </script>
@endpush
