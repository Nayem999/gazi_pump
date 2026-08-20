@csrf
@if (isset($news))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $news->title ?? '') }}" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Excerpt</label>
        <input type="text" name="excerpt" class="form-control @error('excerpt') is-invalid @enderror"
               value="{{ old('excerpt', $news->excerpt ?? '') }}" maxlength="500">
        @error('excerpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Body <span class="text-danger">*</span></label>
        <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="8" required>{{ old('body', $news->body ?? '') }}</textarea>
        @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Cover Image</label>
        <input type="file" name="cover_image" id="imageInput" accept="image/*" class="form-control @error('cover_image') is-invalid @enderror">
        @error('cover_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (! empty($news) && $news->cover_image)
            <img id="imagePreview" src="{{ $news->coverImageUrl() }}" class="mt-2 rounded" style="height:80px;width:80px;object-fit:cover">
        @else
            <img id="imagePreview" class="mt-2 rounded d-none" style="height:80px;width:80px;object-fit:cover">
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label">Published At</label>
        <input type="datetime-local" name="published_at" class="form-control @error('published_at') is-invalid @enderror"
               value="{{ old('published_at', isset($news) && $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : '') }}">
        @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1" @checked(old('is_published', $news->is_published ?? true))>
            <label class="form-check-label" for="is_published">Published</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($news) ? 'Update Article' : 'Create Article' }}</button>
    <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
