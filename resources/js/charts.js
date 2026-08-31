/**
 * Dashboard flagship widgets: the attendance-trend ApexCharts area chart
 * (re-rendered on theme change so its colors stay legible in dark mode) and
 * a small count-up animation for the stat-card values.
 */
function renderAttendanceChart() {
    const el = document.getElementById('attendanceTrendChart');
    if (!el || !window.ApexCharts) {
        return;
    }

    const data = JSON.parse(el.dataset.chartAttendance || '[]');
    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';

    const buildOptions = () => ({
        chart: {
            type: 'area',
            height: 280,
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 500 },
            fontFamily: 'inherit',
        },
        theme: { mode: isDark() ? 'dark' : 'light' },
        series: [
            { name: 'Present', data: data.map((row) => row.present) },
            { name: 'Late', data: data.map((row) => row.late) },
            { name: 'Absent', data: data.map((row) => row.absent) },
        ],
        xaxis: { categories: data.map((row) => row.date) },
        colors: ['#16a34a', '#f59e0b', '#dc2626'],
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: { opacityFrom: .35, opacityTo: .05 },
        },
        dataLabels: { enabled: false },
        legend: { position: 'top' },
    });

    // Skeleton placeholder while ApexCharts constructs the SVG — genuinely
    // useful here (unlike the DataTables, which render server-side HTML with
    // nothing async to mask) since chart rendering happens after page load.
    el.classList.add('skeleton');
    el.style.minHeight = '280px';

    let chart = new window.ApexCharts(el, buildOptions());
    chart.render().then(() => {
        el.classList.remove('skeleton');
        el.style.minHeight = '';
    });

    document.addEventListener('theme:changed', () => {
        chart.destroy();
        chart = new window.ApexCharts(el, buildOptions());
        chart.render();
    });
}

/**
 * Admin dashboard: last-6-months order value vs collection amount, scoped
 * server-side to the viewing admin's own territories when they have any
 * assigned. Two clustered bars per month (Orders, Collections), each
 * stacked into its Pending/Approved/Rejected slice — ApexCharts' per-series
 * `group` combines stacking (same group) with clustering (different
 * groups) in one chart. Same ApexCharts + theme-listener pattern as
 * renderAttendanceChart() above.
 */
function renderOrderVsCollectionChart() {
    const el = document.getElementById('orderVsCollectionChart');
    if (!el || !window.ApexCharts) {
        return;
    }

    const data = JSON.parse(el.dataset.chartOrderVsCollection || '[]');
    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';

    // Six distinct colors, one per series, so each Pending/Approved/Rejected
    // slice is tellable apart by color alone — not just by legend name or
    // which cluster (Orders vs Collections) it sits in.
    const seriesColors = ['#fbbf24', '#22c55e', '#ef4444', '#f59e0b', '#0d9488', '#b91c1c'];

    const buildOptions = () => ({
        chart: {
            type: 'bar',
            height: 280,
            stacked: true,
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 500 },
            fontFamily: 'inherit',
        },
        theme: { mode: isDark() ? 'dark' : 'light' },
        series: [
            { name: 'Order Pending', group: 'orders', data: data.map((row) => row.order_pending) },
            { name: 'Order Approved', group: 'orders', data: data.map((row) => row.order_approved) },
            { name: 'Order Rejected', group: 'orders', data: data.map((row) => row.order_rejected) },
            { name: 'Collection Pending', group: 'collections', data: data.map((row) => row.collection_pending) },
            { name: 'Collection Approved', group: 'collections', data: data.map((row) => row.collection_approved) },
            { name: 'Collection Rejected', group: 'collections', data: data.map((row) => row.collection_rejected) },
        ],
        xaxis: { categories: data.map((row) => row.label) },
        colors: seriesColors,
        plotOptions: { bar: { borderRadius: 4, columnWidth: '70%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top' },
        tooltip: { y: { formatter: (value) => value.toLocaleString() } },
    });

    el.classList.add('skeleton');
    el.style.minHeight = '280px';

    let chart = new window.ApexCharts(el, buildOptions());
    chart.render().then(() => {
        el.classList.remove('skeleton');
        el.style.minHeight = '';
    });

    document.addEventListener('theme:changed', () => {
        chart.destroy();
        chart = new window.ApexCharts(el, buildOptions());
        chart.render();
    });
}

/**
 * Admin dashboard: today's approved collections broken down by payment
 * mode (Cash/Bank Transfer/Cheque/MFS) as a donut chart with the total
 * shown in its center — the per-mode amounts/percentages themselves are
 * server-rendered in the table beside it, so this only needs to draw the
 * rings. Same ApexCharts + theme-listener pattern as renderAttendanceChart()
 * above.
 */
function renderPaymentModeChart() {
    const el = document.getElementById('paymentModeChart');
    if (!el || !window.ApexCharts) {
        return;
    }

    const labels = JSON.parse(el.dataset.labels || '[]');
    const series = JSON.parse(el.dataset.series || '[]');
    const colors = JSON.parse(el.dataset.colors || '[]');
    const totalLabel = el.dataset.totalLabel || '';
    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';

    const buildOptions = () => ({
        chart: {
            type: 'donut',
            height: 220,
            animations: { enabled: true, easing: 'easeinout', speed: 500 },
            fontFamily: 'inherit',
        },
        theme: { mode: isDark() ? 'dark' : 'light' },
        series,
        labels,
        colors,
        legend: { show: false },
        dataLabels: { enabled: false },
        stroke: { width: 2 },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total Collected',
                            formatter: () => totalLabel,
                        },
                    },
                },
            },
        },
        tooltip: { y: { formatter: (value) => value.toLocaleString() } },
    });

    let chart = new window.ApexCharts(el, buildOptions());
    chart.render();

    document.addEventListener('theme:changed', () => {
        chart.destroy();
        chart = new window.ApexCharts(el, buildOptions());
        chart.render();
    });
}

/**
 * Customer portal dashboard: last-6-months purchases vs payments (grouped
 * bar) and purchases-by-product breakdown (horizontal bar) — same
 * ApexCharts + theme-listener pattern as renderAttendanceChart() above.
 */
function renderCustomerPurchaseVsPaymentChart() {
    const el = document.getElementById('customerPurchaseVsPaymentChart');
    if (!el || !window.ApexCharts) {
        return;
    }

    const data = JSON.parse(el.dataset.chartPurchasesVsPayments || '[]');
    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';

    const buildOptions = () => ({
        chart: {
            type: 'bar',
            height: 280,
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 500 },
            fontFamily: 'inherit',
        },
        theme: { mode: isDark() ? 'dark' : 'light' },
        series: [
            { name: 'Purchases', data: data.map((row) => row.purchase) },
            { name: 'Payments', data: data.map((row) => row.payment) },
        ],
        xaxis: { categories: data.map((row) => row.label) },
        colors: ['#0d5aa7', '#16a34a'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top' },
    });

    el.classList.add('skeleton');
    el.style.minHeight = '280px';

    let chart = new window.ApexCharts(el, buildOptions());
    chart.render().then(() => {
        el.classList.remove('skeleton');
        el.style.minHeight = '';
    });

    document.addEventListener('theme:changed', () => {
        chart.destroy();
        chart = new window.ApexCharts(el, buildOptions());
        chart.render();
    });
}

function renderCustomerProductBreakdownChart() {
    const el = document.getElementById('customerProductBreakdownChart');
    if (!el || !window.ApexCharts) {
        return;
    }

    const data = JSON.parse(el.dataset.chartProducts || '[]');
    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';

    const buildOptions = () => ({
        chart: {
            type: 'bar',
            height: 280,
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 500 },
            fontFamily: 'inherit',
        },
        theme: { mode: isDark() ? 'dark' : 'light' },
        series: [{ name: 'Amount Spent', data: data.map((row) => row.total) }],
        xaxis: { categories: data.map((row) => row.name) },
        colors: ['#16a34a'],
        plotOptions: { bar: { borderRadius: 4, horizontal: true } },
        dataLabels: { enabled: false },
    });

    el.classList.add('skeleton');
    el.style.minHeight = '280px';

    let chart = new window.ApexCharts(el, buildOptions());
    chart.render().then(() => {
        el.classList.remove('skeleton');
        el.style.minHeight = '';
    });

    document.addEventListener('theme:changed', () => {
        chart.destroy();
        chart = new window.ApexCharts(el, buildOptions());
        chart.render();
    });
}

function initCountUp() {
    const targets = document.querySelectorAll('[data-countup]');
    if (!targets.length) {
        return;
    }

    const animate = (el) => {
        const target = parseFloat(el.dataset.countup);
        if (Number.isNaN(target)) {
            return;
        }

        const duration = 800;
        const start = performance.now();

        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - (1 - progress) ** 3;
            el.textContent = Math.round(target * eased).toLocaleString();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target.toLocaleString();
            }
        };

        requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animate(entry.target);
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: .3 });

    targets.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', () => {
    renderAttendanceChart();
    renderOrderVsCollectionChart();
    renderPaymentModeChart();
    renderCustomerPurchaseVsPaymentChart();
    renderCustomerProductBreakdownChart();
    initCountUp();
});
