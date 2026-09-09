@csrf
@if (isset($connection))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Connection Name <span class="text-danger">*</span></label>
        <input type="text" name="connection_name" class="form-control @error('connection_name') is-invalid @enderror"
               value="{{ old('connection_name', $connection->connection_name ?? '') }}" required>
        @error('connection_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Tally Company Name <span class="text-danger">*</span></label>
        <input type="text" name="tally_company_name" class="form-control @error('tally_company_name') is-invalid @enderror"
               value="{{ old('tally_company_name', $connection->tally_company_name ?? '') }}" required>
        @error('tally_company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Tally Company GUID</label>
        <input type="text" name="tally_company_guid" class="form-control @error('tally_company_guid') is-invalid @enderror"
               value="{{ old('tally_company_guid', $connection->tally_company_guid ?? '') }}"
               placeholder="Filled in automatically once the Sync Agent first connects, or enter it if known">
        @error('tally_company_guid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Host <span class="text-danger">*</span></label>
        <input type="text" name="host" class="form-control @error('host') is-invalid @enderror"
               value="{{ old('host', $connection->host ?? 'localhost') }}" required>
        <div class="form-text">The Sync Agent's own machine reaches Tally here — never a public address (see Tally Integration Architecture).</div>
        @error('host') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label">Port <span class="text-danger">*</span></label>
        <input type="number" name="port" class="form-control @error('port') is-invalid @enderror"
               value="{{ old('port', $connection->port ?? 9000) }}" min="1" max="65535" required>
        @error('port') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Protocol <span class="text-danger">*</span></label>
        <select name="protocol" class="form-select @error('protocol') is-invalid @enderror" required>
            @foreach (['http', 'https'] as $protocol)
                <option value="{{ $protocol }}" @selected(old('protocol', $connection->protocol ?? 'http') === $protocol)>{{ strtoupper($protocol) }}</option>
            @endforeach
        </select>
        @error('protocol') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">API Format <span class="text-danger">*</span></label>
        <select name="api_format" class="form-select @error('api_format') is-invalid @enderror" required>
            @foreach (\App\Enums\TallyApiFormat::cases() as $format)
                <option value="{{ $format->value }}" @selected(old('api_format', $connection->api_format?->value ?? 'xml') === $format->value)>{{ $format->label() }}</option>
            @endforeach
        </select>
        <div class="form-text">Match whatever this customer's TallyPrime version actually supports — not every version speaks JSON.</div>
        @error('api_format') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $connection->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($connection) ? 'Update Connection' : 'Create Connection' }}</button>
    <a href="{{ route('tally-connections.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
