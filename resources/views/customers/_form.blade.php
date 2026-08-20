@csrf
@if (isset($customer))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Customer Code <span class="text-danger">*</span></label>
        <input type="text" name="customer_code" class="form-control @error('customer_code') is-invalid @enderror"
               value="{{ old('customer_code', $customer->customer_code ?? '') }}" required>
        @error('customer_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $customer->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            <option value="">— Select Type —</option>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected((string) old('type', $customer->type->value ?? '') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Phone <span class="text-danger">*</span></label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $customer->phone ?? '') }}" required>
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $customer->email ?? '') }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Territory</label>
        <select name="territory_id" class="form-select @error('territory_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach ($territories as $territory)
                <option value="{{ $territory->id }}" @selected((string) old('territory_id', $customer->territory_id ?? '') === (string) $territory->id)>{{ $territory->name }}</option>
            @endforeach
        </select>
        @error('territory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $customer->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $customer->address ?? '') }}</textarea>
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
                       value="{{ old('gps_lat', $customer->gps_lat ?? '') }}" placeholder="Latitude">
                @error('gps_lat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <input type="number" step="0.0000001" name="gps_lng" id="gpsLng" class="form-control @error('gps_lng') is-invalid @enderror"
                       value="{{ old('gps_lng', $customer->gps_lng ?? '') }}" placeholder="Longitude">
                @error('gps_lng') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div id="customerLocationMap" style="height:320px;border-radius:.5rem" class="border"></div>
        <div class="form-text">Click the map (or drag the marker) to set the customer's location.</div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($customer) ? 'Update Customer' : 'Create Customer' }}</button>
    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const latInput = document.getElementById('gpsLat');
            const lngInput = document.getElementById('gpsLng');
            const defaultLat = parseFloat(latInput.value) || 23.8103;
            const defaultLng = parseFloat(lngInput.value) || 90.4125;

            const map = window.L.map('customerLocationMap').setView([defaultLat, defaultLng], latInput.value ? 15 : 7);
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
