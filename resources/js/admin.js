/**
 * Shared admin-shell behaviour: sidebar toggle, confirm-before-destroy forms,
 * and a default DataTables initializer. Reused by every module's views.
 */
document.addEventListener('DOMContentLoaded', () => {
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
