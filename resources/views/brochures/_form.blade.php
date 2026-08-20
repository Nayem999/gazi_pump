@csrf
@if (isset($brochure))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $brochure->title ?? '') }}" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">PDF File @if (empty($brochure))<span class="text-danger">*</span>@endif</label>
        <input type="file" name="file" accept="application/pdf" class="form-control @error('file') is-invalid @enderror" @if (empty($brochure)) required @endif>
        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (! empty($brochure))
            <div class="form-text"><a href="{{ $brochure->fileUrl() }}" download>View current PDF</a> — leave blank to keep it.</div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">Cover Image</label>
        <input type="file" name="cover_image" id="imageInput" accept="image/*" class="form-control @error('cover_image') is-invalid @enderror">
        @error('cover_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (! empty($brochure) && $brochure->cover_image)
            <img id="imagePreview" src="{{ $brochure->coverImageUrl() }}" class="mt-2 rounded" style="height:80px;width:80px;object-fit:cover">
        @else
            <img id="imagePreview" class="mt-2 rounded d-none" style="height:80px;width:80px;object-fit:cover">
        @endif
    </div>

    <div class="col-12 d-flex align-items-center">
        <div class="form-check form-switch">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1" @checked(old('is_published', $brochure->is_published ?? true))>
            <label class="form-check-label" for="is_published">Published</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($brochure) ? 'Update Brochure' : 'Create Brochure' }}</button>
    <a href="{{ route('brochures.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
