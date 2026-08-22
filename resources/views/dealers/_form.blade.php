@csrf
@if (isset($dealer))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Dealer Code <span class="text-danger">*</span></label>
        <input type="text" name="dealer_code" class="form-control @error('dealer_code') is-invalid @enderror"
               value="{{ old('dealer_code', $dealer->dealer_code ?? '') }}" required>
        @error('dealer_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $dealer->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            <option value="">— Select Type —</option>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected((string) old('type', $dealer->type->value ?? '') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Phone <span class="text-danger">*</span></label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $dealer->phone ?? '') }}" required>
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $dealer->email ?? '') }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Division</label>
        <select id="geoDivision" class="form-select">
            <option value="">— Select Division —</option>
            @foreach ($divisions as $division)
                <option value="{{ $division->id }}" @selected((string) ($dealer->division_id ?? '') === (string) $division->id)>{{ $division->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">District</label>
        <select id="geoDistrict" class="form-select" @disabled(empty($dealer->division_id))>
            <option value="">— Select District —</option>
            @isset($dealer->district)
                <option value="{{ $dealer->district->id }}" selected>{{ $dealer->district->name }}</option>
            @endisset
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Thana / Upazila</label>
        <select id="geoThana" class="form-select" @disabled(empty($dealer->district_id))>
            <option value="">— Select Thana —</option>
            @isset($dealer->thana)
                <option value="{{ $dealer->thana->id }}" selected>{{ $dealer->thana->name }}</option>
            @endisset
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Territory</label>
        <select name="territory_id" id="geoTerritory" class="form-select @error('territory_id') is-invalid @enderror" @disabled(empty($dealer->thana_id))>
            <option value="">— None —</option>
            @isset($dealer->territory)
                <option value="{{ $dealer->territory->id }}" selected>{{ $dealer->territory->name }}</option>
            @endisset
        </select>
        @error('territory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $dealer->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $dealer->address ?? '') }}</textarea>
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label d-flex align-items-center justify-content-between">
            <span>GPS Location</span>
            <button type="button" id="useMyLocationBtn" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-map-pin me-1"></i>Use My Location
            </button>
        </label>
        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <input type="number" step="0.0000001" name="gps_lat" id="gpsLat" class="form-control @error('gps_lat') is-invalid @enderror"
                       value="{{ old('gps_lat', $dealer->gps_lat ?? '') }}" placeholder="Latitude">
                @error('gps_lat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <input type="number" step="0.0000001" name="gps_lng" id="gpsLng" class="form-control @error('gps_lng') is-invalid @enderror"
                       value="{{ old('gps_lng', $dealer->gps_lng ?? '') }}" placeholder="Longitude">
                @error('gps_lng') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div id="dealerLocationMap" style="height:320px;border-radius:.5rem" class="border"></div>
        <div class="form-text">Click the map (or drag the marker) to set the dealer's location.</div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($dealer) ? 'Update Dealer' : 'Create Dealer' }}</button>
    <a href="{{ route('dealers.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const geoDivision = document.getElementById('geoDivision');
            const geoDistrict = document.getElementById('geoDistrict');
            const geoThana = document.getElementById('geoThana');
            const geoTerritory = document.getElementById('geoTerritory');

            initCascadingSelect(geoDivision, geoDistrict, '{{ route('districts.options') }}', 'division_id', { placeholder: '— Select District —' });
            initCascadingSelect(geoDistrict, geoThana, '{{ route('thanas.options') }}', 'district_id', { placeholder: '— Select Thana —' });
            initCascadingSelect(geoThana, geoTerritory, '{{ route('territories.options') }}', 'thana_id', { placeholder: '— None —' });

            geoDivision.addEventListener('change', function () {
                geoThana.innerHTML = '<option value="">— Select Thana —</option>';
                geoThana.disabled = true;
                geoTerritory.innerHTML = '<option value="">— None —</option>';
                geoTerritory.disabled = true;
            });
            geoDistrict.addEventListener('change', function () {
                geoTerritory.innerHTML = '<option value="">— None —</option>';
                geoTerritory.disabled = true;
            });

            const latInput = document.getElementById('gpsLat');
            const lngInput = document.getElementById('gpsLng');
            const defaultLat = parseFloat(latInput.value) || 23.8103;
            const defaultLng = parseFloat(lngInput.value) || 90.4125;

            const map = window.L.map('dealerLocationMap').setView([defaultLat, defaultLng], latInput.value ? 15 : 7);
            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            let marker = latInput.value && lngInput.value
                ? window.L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map)
                : null;

            function setMarker(lat, lng) {
                latInput.value = lat.toFixed(7);
                lngInput.value = lng.toFixed(7);

                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = window.L.marker([lat, lng], { draggable: true }).addTo(map);
                    marker.on('dragend', () => {
                        const pos = marker.getLatLng();
                        setMarker(pos.lat, pos.lng);
                    });
                }
            }

            marker?.on('dragend', () => {
                const pos = marker.getLatLng();
                setMarker(pos.lat, pos.lng);
            });

            map.on('click', (e) => setMarker(e.latlng.lat, e.latlng.lng));

            document.getElementById('useMyLocationBtn').addEventListener('click', function () {
                if (! navigator.geolocation) {
                    window.Swal.fire('Not supported', 'Geolocation is not supported by this browser.', 'warning');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude, longitude } = position.coords;
                        setMarker(latitude, longitude);
                        map.setView([latitude, longitude], 16);
                    },
                    () => window.Swal.fire('Unable to fetch location', 'Please allow location access and try again.', 'error'),
                );
            });
        });
    </script>
@endpush
