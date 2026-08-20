@extends('layouts.admin')

@section('title', 'Send Announcement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Announcements</a></li>
    <li class="breadcrumb-item active">Send</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('announcements.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" rows="4" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                        @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Audience <span class="text-danger">*</span></label>
                        <select name="audience" id="audienceSelect" class="form-select @error('audience') is-invalid @enderror" required>
                            @foreach (\App\Enums\AnnouncementAudience::cases() as $audience)
                                <option value="{{ $audience->value }}" @selected(old('audience') === $audience->value)>{{ $audience->label() }}</option>
                            @endforeach
                        </select>
                        @error('audience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 audience-field" data-audience="role">
                        <label class="form-label">Role</label>
                        <select name="audience_role" class="form-select @error('audience_role') is-invalid @enderror">
                            <option value="">— Select —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('audience_role') === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                        @error('audience_role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 audience-field" data-audience="territory">
                        <label class="form-label">Territory</label>
                        <select name="audience_territory_id" class="form-select @error('audience_territory_id') is-invalid @enderror">
                            <option value="">— Select —</option>
                            @foreach ($territories as $territory)
                                <option value="{{ $territory->id }}" @selected((string) old('audience_territory_id') === (string) $territory->id)>{{ $territory->name }}</option>
                            @endforeach
                        </select>
                        @error('audience_territory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 audience-field" data-audience="user">
                        <label class="form-label">User</label>
                        <select name="audience_user_id" class="form-select @error('audience_user_id') is-invalid @enderror">
                            <option value="">— Select —</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('audience_user_id') === (string) $user->id)>{{ $user->name }} ({{ $user->employee_id }})</option>
                            @endforeach
                        </select>
                        @error('audience_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i>Send Announcement</button>
                    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const select = document.getElementById('audienceSelect');
            const fields = document.querySelectorAll('.audience-field');

            function sync() {
                fields.forEach((field) => {
                    field.classList.toggle('d-none', field.dataset.audience !== select.value);
                });
            }

            select.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
