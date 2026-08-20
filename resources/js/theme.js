/**
 * Theme toggle. The initial `data-bs-theme` attribute is already set by a
 * synchronous inline <script> in <head> (before this bundle loads) to avoid
 * a flash of the wrong theme — see layouts/admin.blade.php. This module only
 * wires up the toggle button, persists the choice, and lets other modules
 * (charts.js) react via the `theme:changed` event.
 */
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.querySelector('[data-theme-toggle]');

    const syncIcon = (theme) => {
        const icon = toggleBtn?.querySelector('i');
        icon?.classList.toggle('ti-sun', theme === 'dark');
        icon?.classList.toggle('ti-moon', theme !== 'dark');
    };

    syncIcon(document.documentElement.getAttribute('data-bs-theme'));

    toggleBtn?.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
        syncIcon(next);
        document.dispatchEvent(new CustomEvent('theme:changed', { detail: { theme: next } }));
    });
});
