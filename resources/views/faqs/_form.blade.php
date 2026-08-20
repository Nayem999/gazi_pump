@csrf
@if (isset($faq))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Question <span class="text-danger">*</span></label>
        <input type="text" name="question" class="form-control @error('question') is-invalid @enderror"
               value="{{ old('question', $faq->question ?? '') }}" required>
        @error('question') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Answer <span class="text-danger">*</span></label>
        <textarea name="answer" class="form-control @error('answer') is-invalid @enderror" rows="5" required>{{ old('answer', $faq->answer ?? '') }}</textarea>
        @error('answer') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Sort Order</label>
        <input type="number" min="0" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ old('sort_order', $faq->sort_order ?? 0) }}">
        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-9 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1" @checked(old('is_published', $faq->is_published ?? true))>
            <label class="form-check-label" for="is_published">Published</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($faq) ? 'Update FAQ' : 'Create FAQ' }}</button>
    <a href="{{ route('faqs.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
