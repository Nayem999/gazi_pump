import './bootstrap';

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import $ from 'jquery';
window.$ = window.jQuery = $;

import 'datatables.net-bs5';

// select2's dist build is UMD, and under Vite/Rollup's CommonJS interop it
// takes the "Node/CommonJS" branch of its own detection — which, unlike the
// "browser globals" branch, doesn't call its setup factory automatically; it
// exports a wrapper that has to be invoked explicitly with (root, jQuery) to
// actually attach $.fn.select2. A bare `import 'select2'` silently does
// nothing under a bundler for exactly this reason. (Its CSS is imported from
// app.scss instead, so it compiles into the same stylesheet as — and before
// — the dark-mode overrides in _layout.scss, rather than a separate,
// load-order-ambiguous chunk.)
import select2 from 'select2';
select2(window, $);

import Swal from 'sweetalert2';
window.Swal = Swal;

import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

// Default import (not `import * as L`) matters here: a namespace import is a
// frozen ES-module object, so leaflet-draw's `L.Draw = {}` (a new top-level
// property) fails silently while `L.Control.Draw = ...` (mutating the
// already-mutable `L.Control`) appears to work — a confusing partial
// failure. The default import resolves to Leaflet's real, mutable
// CommonJS exports object via Vite's interop, so both kinds of assignment
// work correctly.
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Vite doesn't rewrite Leaflet's own CSS-relative icon URLs, so the default
// marker icon renders broken unless we point it at the bundled asset URLs.
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

// Adds L.Control.Draw / L.Draw.* / L.Edit.* onto this same mutable Leaflet
// object — used by the Territory create/edit form's polygon-drawing map.
import 'leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';

// Adds L.markerClusterGroup() — groups nearby territory markers into a
// count bubble at low zoom (Territory Map), expanding/zooming in on click.
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

window.L = L;

import './theme';
import './admin';
import './charts';
import './select2-init';
