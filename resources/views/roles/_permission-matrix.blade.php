@php
    $selected = collect(old('permissions', $selected ?? []));
@endphp

<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th style="width:2rem">
                    <input type="checkbox" class="form-check-input" id="permSelectAll">
                </th>
                <th>Module</th>
                <th>Permissions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permissions as $module => $modulePermissions)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input module-select-all" data-module="{{ $module }}">
                    </td>
                    <td class="text-capitalize fw-semibold">{{ $module }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($modulePermissions as $permission)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input perm-checkbox module-{{ $module }}"
                                           id="perm-{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}"
                                           @checked($selected->contains($permission->name))>
                                    <label class="form-check-label" for="perm-{{ $permission->id }}">
                                        {{ str_replace($module.'.', '', $permission->name) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('.module-select-all').forEach((master) => {
            const module = master.dataset.module;
            const children = document.querySelectorAll('.module-' + module);
            master.checked = [...children].every((c) => c.checked);

            master.addEventListener('change', () => {
                children.forEach((c) => { c.checked = master.checked; });
            });

            children.forEach((c) => c.addEventListener('change', () => {
                master.checked = [...children].every((chk) => chk.checked);
            }));
        });

        document.getElementById('permSelectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.perm-checkbox, .module-select-all').forEach((cb) => { cb.checked = this.checked; });
        });
    </script>
@endpush
