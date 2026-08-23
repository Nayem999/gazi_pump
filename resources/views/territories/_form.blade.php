@csrf
@if (isset($territory))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Code <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $territory->code ?? '') }}" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $territory->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Division <span class="text-danger">*</span></label>
        <select id="geoDivision" name="division_id" class="form-select @error('division_id') is-invalid @enderror" required>
            <option value="">— Select Division —</option>
            @foreach ($divisions as $division)
                <option value="{{ $division->id }}" @selected((string) old('division_id', $territory->division_id ?? '') === (string) $division->id)>{{ $division->name }}</option>
            @endforeach
        </select>
        @error('division_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">District <span class="text-danger">*</span></label>
        <select id="geoDistrict" name="district_id" class="form-select @error('district_id') is-invalid @enderror" required @disabled(empty($territory->division_id ?? null))>
            <option value="">— Select District —</option>
            @isset($territory->district)
                <option value="{{ $territory->district->id }}" selected>{{ $territory->district->name }}</option>
            @endisset
        </select>
        @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Thana / Upazila <span class="text-danger">*</span></label>
        <select id="geoThana" name="thana_id" class="form-select @error('thana_id') is-invalid @enderror" required @disabled(empty($territory->district_id ?? null))>
            <option value="">— Select Thana —</option>
            @isset($territory->thana)
                <option value="{{ $territory->thana->id }}" selected>{{ $territory->thana->name }}</option>
            @endisset
        </select>
        @error('thana_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Territory Manager</label>
        <select name="manager_id" class="form-select @error('manager_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach ($managers as $manager)
                <option value="{{ $manager->id }}" @selected((string) old('manager_id', $territory->manager_id ?? '') === (string) $manager->id)>
                    {{ $manager->name }} ({{ $manager->employee_id }})
                </option>
            @endforeach
        </select>
        @error('manager_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Center Latitude</label>
        <input type="number" step="0.0000001" name="center_lat" class="form-control @error('center_lat') is-invalid @enderror"
               value="{{ old('center_lat', $territory->center_lat ?? '') }}" placeholder="e.g. 23.8103">
        @error('center_lat') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Center Longitude</label>
        <input type="number" step="0.0000001" name="center_lng" class="form-control @error('center_lng') is-invalid @enderror"
               value="{{ old('center_lng', $territory->center_lng ?? '') }}" placeholder="e.g. 90.4125">
        @error('center_lng') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Territory Boundary</label>
        <div id="territoryBoundaryMap" style="height:400px;border-radius:.5rem"></div>
        <div class="form-text">Use the draw tools (top-left of the map) to outline the territory. Drawing a shape replaces any previous one and automatically fills in the center coordinates above.</div>
        <textarea name="boundary" id="boundaryInput" class="d-none @error('boundary') is-invalid @enderror">{{ old('boundary', ! empty($territory?->boundary) ? json_encode($territory->boundary) : '') }}</textarea>
        @error('boundary') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $territory->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($territory) ? 'Update Territory' : 'Create Territory' }}</button>
    <a href="{{ route('territories.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const geoDivision = document.getElementById('geoDivision');
            const geoDistrict = document.getElementById('geoDistrict');
            const geoThana = document.getElementById('geoThana');

            initCascadingSelect(geoDivision, geoDistrict, '{{ route('districts.options') }}', 'division_id', { placeholder: '— Select District —' });
            initCascadingSelect(geoDistrict, geoThana, '{{ route('thanas.options') }}', 'district_id', { placeholder: '— Select Thana —' });

            // Bound through jQuery, not addEventListener — see the matching
            // comment in dealers/_form.blade.php.
            window.$(geoDivision).on('change', function () {
                geoThana.innerHTML = '<option value="">— Select Thana —</option>';
                geoThana.disabled = true;
                window.refreshSelect2?.(geoThana);
            });

            const latInput = document.querySelector('input[name="center_lat"]');
            const lngInput = document.querySelector('input[name="center_lng"]');
            const boundaryInput = document.getElementById('boundaryInput');

            const defaultLat = parseFloat(latInput.value) || 23.8103;
            const defaultLng = parseFloat(lngInput.value) || 90.4125;

            const map = window.L.map('territoryBoundaryMap').setView([defaultLat, defaultLng], latInput.value ? 12 : 7);
            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            const drawnItems = new window.L.FeatureGroup().addTo(map);

            const existingBoundary = boundaryInput.value.trim();
            if (existingBoundary) {
                try {
                    window.L.geoJSON(JSON.parse(existingBoundary)).eachLayer((layer) => drawnItems.addLayer(layer));
                    if (drawnItems.getLayers().length > 0) {
                        map.fitBounds(drawnItems.getBounds(), { padding: [20, 20] });
                    }
                } catch (e) {
                    // Malformed existing boundary JSON — leave the map blank, user redraws.
                }
            }

            map.addControl(new window.L.Control.Draw({
                draw: {
                    polygon: true,
                    rectangle: true,
                    marker: false,
                    circle: false,
                    circlemarker: false,
                    polyline: false,
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true,
                },
            }));

            function syncFromLayers() {
                const layers = drawnItems.getLayers();
                if (layers.length === 0) {
                    boundaryInput.value = '';
                    return;
                }
                boundaryInput.value = JSON.stringify(layers[0].toGeoJSON().geometry);
                const center = layers[0].getBounds().getCenter();
                latInput.value = center.lat.toFixed(7);
                lngInput.value = center.lng.toFixed(7);
            }

            // Only one boundary shape per territory — a new draw replaces the previous one.
            map.on(window.L.Draw.Event.CREATED, function (e) {
                drawnItems.clearLayers();
                drawnItems.addLayer(e.layer);
                syncFromLayers();
            });
            map.on(window.L.Draw.Event.EDITED, syncFromLayers);
            map.on(window.L.Draw.Event.DELETED, syncFromLayers);
        });
    </script>
@endpush
