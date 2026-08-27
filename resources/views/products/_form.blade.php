@csrf
@if (isset($product))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Category <span class="text-danger">*</span></label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">— Select Category —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->parent_id ? '— ' : '' }}{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Sales Team</label>
        <select name="sales_team_id" class="form-select @error('sales_team_id') is-invalid @enderror">
            <option value="">— All Teams —</option>
            @foreach ($salesTeams as $salesTeam)
                <option value="{{ $salesTeam->id }}" @selected((string) old('sales_team_id', $product->sales_team_id ?? '') === (string) $salesTeam->id)>{{ $salesTeam->name }}</option>
            @endforeach
        </select>
        <div class="form-text">Restricts this product to executives under this team. Leave blank to make it visible to every team.</div>
        @error('sales_team_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $product->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">SKU <span class="text-danger">*</span></label>
        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
               value="{{ old('sku', $product->sku ?? '') }}" required>
        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Price <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" name="price" class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price', $product->price ?? '') }}" required>
        </div>
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Image</label>
        <input type="file" name="image" id="imageInput" accept="image/*" class="form-control @error('image') is-invalid @enderror">
        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (! empty($product) && $product->image)
            <img id="imagePreview" src="{{ $product->imageUrl() }}" class="mt-2 rounded" style="height:80px;width:80px;object-fit:cover">
        @else
            <img id="imagePreview" class="mt-2 rounded d-none" style="height:80px;width:80px;object-fit:cover">
        @endif
    </div>

    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $product->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($product) ? 'Update Product' : 'Create Product' }}</button>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
