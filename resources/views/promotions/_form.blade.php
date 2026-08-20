@csrf
@if (isset($promotion))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $promotion->title ?? '') }}" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $promotion->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Image</label>
        <input type="file" name="image" id="imageInput" accept="image/*" class="form-control @error('image') is-invalid @enderror">
        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (! empty($promotion) && $promotion->image)
            <img id="imagePreview" src="{{ $promotion->imageUrl() }}" class="mt-2 rounded" style="height:80px;width:80px;object-fit:cover">
        @else
            <img id="imagePreview" class="mt-2 rounded d-none" style="height:80px;width:80px;object-fit:cover">
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label">Starts</label>
        <input type="date" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror"
               value="{{ old('starts_at', $promotion->starts_at ?? '') }}">
        @error('starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Ends</label>
        <input type="date" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror"
               value="{{ old('ends_at', $promotion->ends_at ?? '') }}">
        @error('ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12 d-flex align-items-center">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $promotion->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($promotion) ? 'Update Promotion' : 'Create Promotion' }}</button>
    <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        document.getElementById('imageInput')?.addEventListener('change', function (e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    </script>
@endpush
