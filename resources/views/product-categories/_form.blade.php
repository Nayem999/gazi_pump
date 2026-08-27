@csrf
@if (isset($category))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Code <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $category->code ?? '') }}" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $category->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Parent Category</label>
        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror"
                @if (isset($category) && $category->children()->exists()) disabled @endif>
            <option value="">— None (Top-level Category) —</option>
            @foreach ($parentCategories as $parentCategory)
                <option value="{{ $parentCategory->id }}" @selected((string) old('parent_id', $category->parent_id ?? '') === (string) $parentCategory->id)>{{ $parentCategory->name }}</option>
            @endforeach
        </select>
        @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (isset($category) && $category->children()->exists())
            <div class="form-text">This category already has sub-categories, so it can't become a sub-category itself.</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $category->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($category) ? 'Update Category' : 'Create Category' }}</button>
    <a href="{{ route('product-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
