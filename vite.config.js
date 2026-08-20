import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // Relative base so assets referenced from within built CSS (e.g. the
    // Tabler icon webfont's url() references) resolve relative to that CSS
    // file's own location instead of the site root — the app is served both
    // at the domain root (php artisan serve) and under a subdirectory
    // (XAMPP htdocs/gazi_pump), and only a relative path works in both.
    base: './',
    plugins: [
        laravel({
            input: ['resources/css/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
