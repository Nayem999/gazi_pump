<?php

/**
 * Front-controller shim so Apache's SCRIPT_NAME stays rooted at this
 * directory instead of jumping into public/ — Symfony's base-path
 * detection needs SCRIPT_NAME's directory to match REQUEST_URI's, and an
 * internal rewrite straight into public/index.php breaks that match,
 * making every route 404. __DIR__ inside the required file still resolves
 * to public/, so its own relative paths (vendor/autoload.php, etc.) are
 * unaffected.
 */
require __DIR__.'/public/index.php';
