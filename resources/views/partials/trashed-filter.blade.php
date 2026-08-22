<div class="{{ $colClass ?? 'col-md-2' }}">
    <label class="form-label">Trashed</label>
    <select name="trashed" class="form-select">
        <option value="">Without Trashed</option>
        <option value="with" @selected(($filters['trashed'] ?? '') === 'with')>With Trashed</option>
        <option value="only" @selected(($filters['trashed'] ?? '') === 'only')>Only Trashed</option>
    </select>
</div>
