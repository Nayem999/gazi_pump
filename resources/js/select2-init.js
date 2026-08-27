/**
 * Turns every entity-picker <select> across the admin app into a searchable
 * Select2 dropdown — division/district/thana/territory (the geo hierarchy),
 * dealer, retailer, and user (covers manager, sales executive, and activity-log
 * "causer" pickers, since they're all just `<select name="user_id">` /
 * `<select name="manager_id">` under different labels). Matched by `name`
 * (and a couple of known `id`s for the handful of cascade-only selects that
 * have no `name` because they never get submitted) so any current or future
 * form using these same field names picks this up automatically — no
 * per-view opt-in needed.
 *
 * `select.product-select` is matched by class, not `name`, because the
 * Order form's line-item product pickers are indexed array fields
 * (`items[0][product_id]`, `items[1][product_id]`, ...) with no fixed name
 * — and they're built dynamically after this file's initial DOMContentLoaded
 * scan, so orders/_form.blade.php calls window.initSelect2() again itself
 * each time it inserts a new row (safe to call repeatedly: already-attached
 * selects are skipped below).
 *
 * A select carrying `data-ajax-url` (e.g. Territory on the Visit Plan form,
 * or the Territories multi-select on the Users form — both search across
 * 5,000+ rows) gets Select2's remote-search mode instead of the plain
 * client-side-filter mode — pre-rendering every <option> server-side isn't
 * an option at that size, so only the current value(s) (if any) are
 * rendered up front and the rest is fetched as the user types (or as soon
 * as the dropdown opens — see minimumInputLength below), reusing that
 * field's existing "*-options?search=" JSON endpoint.
 */
function initSelect2() {
    if (!window.$ || !window.$.fn.select2) {
        return;
    }

    const selector = [
        'select[name="division_id"]',
        'select[name="district_id"]',
        'select[name="thana_id"]',
        'select[name="territory_id"]',
        'select[name="territory_ids[]"]',
        'select[name="dealer_id"]',
        'select[name="retailer_id"]',
        'select[name="manager_id"]',
        'select[name="user_id"]',
        'select[name="causer_id"]',
        'select.product-select',
        '#geoDivision',
        '#geoDistrict',
        '#geoThana',
        '#division_id',
    ].join(', ');

    window.$(selector).each(function () {
        if (window.$(this).data('select2')) {
            return;
        }

        const config = {
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: ! this.required,
            placeholder: window.$(this).find('option').first().text() || 'Search…',
        };

        const ajaxUrl = this.dataset.ajaxUrl;
        if (ajaxUrl) {
            // 0, not 1: opening the dropdown should show an initial batch of
            // results right away (the options endpoint treats an empty
            // search as "give me the first page, alphabetically") rather
            // than sitting empty until the user types a first character.
            config.minimumInputLength = 0;
            config.ajax = {
                url: ajaxUrl,
                dataType: 'json',
                delay: 250,
                // `search: params.term || ''`, not just `params.term`: on
                // initial open params.term is undefined, and jQuery drops
                // undefined values from the query string entirely — the
                // options endpoint needs an actual (even empty) "search"
                // param present to know to return its default first-page
                // list instead of an empty result.
                data: (params) => ({ search: params.term || '' }),
                // select2 4.1.0's own AJAX adapter maps each result through
                // `AjaxAdapter.prototype._normalizeItem` as an unbound
                // function reference (a bug in its dist build) — under
                // Vite's ESM output that runs in strict mode, `this` inside
                // it is undefined instead of falling back to `window`,
                // and it throws reading `this.container`. That internal
                // branch only runs when `_resultId` is still unset, so
                // pre-setting it here skips straight past the bug.
                processResults: (data) => ({
                    results: data.map((item) => ({ id: item.id, text: item.name, _resultId: `select2-result-${item.id}` })),
                }),
            };
        }

        window.$(this).select2(config);
    });
}

// Exposed so views that inject new <select> elements after the initial
// DOMContentLoaded scan (e.g. the Order form's dynamic product rows) can
// re-run this to pick them up — already-attached selects are skipped above.
window.initSelect2 = initSelect2;

// Exposed so admin.js's cascading-select helper can ask Select2 to redraw a
// child <select> after swapping its <option>s via plain DOM — Select2 only
// re-renders in response to the underlying element's native "change" event,
// it doesn't observe DOM mutations on its own.
window.refreshSelect2 = function (el) {
    if (el && window.$?.fn?.select2) {
        window.$(el).trigger('change');
    }
};

document.addEventListener('DOMContentLoaded', initSelect2);
