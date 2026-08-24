@extends('layouts.admin')

@section('title', 'My Profile')

@section('breadcrumb')
    <li class="breadcrumb-item active">My Profile</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">My Profile</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Photo</label>
                                <input type="file" name="photo" id="photoInput" accept="image/*" class="form-control @error('photo') is-invalid @enderror">
                                @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if ($user->photo)
                                    <img id="photoPreview" src="{{ $user->photoUrl() }}" class="mt-2 rounded" style="height:64px;width:64px;object-fit:cover">
                                @else
                                    <img id="photoPreview" class="mt-2 rounded d-none" style="height:64px;width:64px;object-fit:cover">
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $user->phone) }}">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Employee ID</label>
                                <input type="text" class="form-control" value="{{ $user->employee_id }}" disabled>
                                <div class="form-text">Contact an administrator to change this.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                <div class="form-text">Leave blank to keep your current password.</div>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('photoInput')?.addEventListener('change', function (e) {
            const preview = document.getElementById('photoPreview');
            const file = e.target.files[0];
            if (! file) {
                return;
            }
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    </script>
@endpush
