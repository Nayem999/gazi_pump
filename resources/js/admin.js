/**
 * Wires a parent <select> to a dependent child <select>, fetching the
 * child's options from a JSON endpoint whenever the parent changes (e.g.
 * Division -> District options on the Thana form/filter bar). Kept generic
 * so any future parent/child pair can reuse it the same way.
 *
 * @param {HTMLSelectElement|null} parentEl
 * @param {HTMLSelectElement|null} childEl
 * @param {string} url the options endpoint, e.g. "/districts-options"
 * @param {string} paramName the query param the endpoint filters on, e.g. "division_id"
 * @param {{placeholder?: string, initialChildValue?: string|number}} [options]
 */
function initCascadingSelect(parentEl, childEl, url, paramName, options = {}) {
    if (!parentEl || !childEl) return;
    const placeholder = options.placeholder || '— Select —';
    const load = (parentValue, selectedChildValue) => {
        childEl.innerHTML = `<option value="">${placeholder}</option>`;
        if (!parentValue) { childEl.disabled = true; window.refreshSelect2?.(childEl); return; }
        childEl.disabled = false;
        fetch(`${url}?${paramName}=${encodeURIComponent(parentValue)}`, { headers: { Accept: 'application/json' } })
            .then((r) => r.json())
            .then((items) => {
                items.forEach((item) => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name;
                    if (selectedChildValue && String(item.id) === String(selectedChildValue)) opt.selected = true;
                    childEl.appendChild(opt);
                });
                // Select2 (see select2-init.js) only re-renders in response to
                // the underlying <select>'s native "change" event — it doesn't
                // observe these injected <option>s on its own.
                window.refreshSelect2?.(childEl);
            });
    };
    // Once select2-init.js attaches Select2 to parentEl, user interaction no
    // longer dispatches a plain native "change" that addEventListener would
    // catch — Select2 changes the value through jQuery's own event system
    // instead. Binding through jQuery (when present) catches both that and
    // genuine native changes, so this keeps working whether or not the
    // element ends up Select2-ified.
    if (window.$) {
        window.$(parentEl).on('change', () => load(parentEl.value, null));
    } else {
        parentEl.addEventListener('change', () => load(parentEl.value, null));
    }
    if (options.initialChildValue && parentEl.value) {
        load(parentEl.value, options.initialChildValue);
    }
}

// Exposed on window: this module is bundled as an ES module, so the plain
// function declaration above is not visible to the inline (non-module)
// <script> blocks in Blade views that call it.
window.initCascadingSelect = initCascadingSelect;

/**
 * Shared admin-shell behaviour: sidebar toggle, confirm-before-destroy forms,
 * and a default DataTables initializer. Reused by every module's views.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Thana form/filter: Division -> District cascade. No-op on any page
    // without these specific elements (guarded inside initCascadingSelect).
    initCascadingSelect(
        document.getElementById('division_id'),
        document.getElementById('district_id'),
        '/districts-options',
        'division_id',
        { initialChildValue: document.getElementById('district_id')?.dataset.initialValue }
    );

    const sidebar = document.querySelector('.app-sidebar');
    const toggleBtn = document.querySelector('[data-sidebar-toggle]');

    toggleBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('show');
    });

    document.addEventListener('click', (event) => {
        if (window.innerWidth >= 992) {
            return;
        }
        if (sidebar?.classList.contains('show') && !sidebar.contains(event.target) && !toggleBtn?.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                return;
            }
            event.preventDefault();
            // Row-level action forms (edit/delete/restore) render nested
            // inside the list page's bulk-action form. The submit event
            // bubbles, so without this an ancestor form's own data-confirm
            // handler also fires for the same click, replaces the just-shown
            // dialog with its own, and ends up submitting the WRONG form
            // (the outer bulk form instead of the row's own) once confirmed.
            event.stopPropagation();

            window.Swal.fire({
                title: form.dataset.confirmTitle || 'Are you sure?',
                text: form.dataset.confirmText || 'This action cannot be undone.',
                icon: form.dataset.confirmIcon || 'warning',
                showCancelButton: true,
                confirmButtonText: form.dataset.confirmButton || 'Yes, proceed',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });
    });

    // Copy-to-clipboard for any [data-copy] element (e.g. dealer phone
    // numbers) — a small Swal toast confirms the copy instead of a full
    // confirm-style dialog, since this isn't a decision the user needs to
    // approve, just feedback that it happened.
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-copy]');

        if (!trigger) {
            return;
        }

        event.preventDefault();

        navigator.clipboard.writeText(trigger.dataset.copy).then(() => {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Phone number copied.',
                timer: 1500,
                showConfirmButton: false,
            });
        });
    });

    // DataTables' default error mode pops a blocking browser alert() for any
    // internal warning (e.g. its own Responsive extension misreads empty-table
    // placeholder rows as a column mismatch). That's never appropriate for an
    // end user, so route warnings to the console instead, per DataTables' own
    // production guidance: https://datatables.net/reference/option/
    if (window.$?.fn?.dataTable) {
        window.$.fn.dataTable.ext.errMode = 'none';
    }

    document.querySelectorAll('table.data-table').forEach((table) => {
        window.$(table).DataTable({
            responsive: true,
            pageLength: 25,
            order: [],
            language: {
                emptyTable: 'No data available.',
                zeroRecords: 'No matching records found.',
            },
        }).on('error.dt', (event, settings, techNote, message) => {
            console.warn('DataTables warning:', message);
        });
    });

    // Glass topbar gains a deeper shadow once content scrolls underneath it.
    // The page itself scrolls (not an inner container), so a window listener
    // is all that's needed here.
    const topbar = document.querySelector('.app-topbar');
    window.addEventListener('scroll', () => {
        topbar?.classList.toggle('scrolled', window.scrollY > 4);
    });

    // List/Grid/Card display toggle: preference is remembered per page (not
    // globally), so a user can keep Products in Grid while Users stays List.
    document.querySelectorAll('[data-view-toggle]').forEach((group) => {
        const panelContainer = group.closest('.card');
        const panels = panelContainer?.querySelectorAll('[data-view-panel]');
        const storageKey = `listView:${window.location.pathname}`;

        const applyMode = (mode) => {
            panels?.forEach((panel) => {
                panel.classList.toggle('d-none', panel.dataset.viewPanel !== mode);
            });
            group.querySelectorAll('[data-view-mode]').forEach((btn) => {
                btn.classList.toggle('active', btn.dataset.viewMode === mode);
            });
        };

        applyMode(localStorage.getItem(storageKey) || 'list');

        group.querySelectorAll('[data-view-mode]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const mode = btn.dataset.viewMode;
                localStorage.setItem(storageKey, mode);
                applyMode(mode);
            });
        });
    });

    // Reveal-on-scroll: stat cards and content cards fade/slide in the first
    // time they enter the viewport, then stop being observed. Skipped
    // entirely for motion-sensitive users (the CSS keyframe itself is also
    // disabled via prefers-reduced-motion, this just avoids the wasted work).
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealTargets = document.querySelectorAll('.stat-card, .card');

    if (!prefersReducedMotion && revealTargets.length && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: .1 });

        revealTargets.forEach((el) => revealObserver.observe(el));
    }
});
