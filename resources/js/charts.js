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
 * Customer portal dashboard: last-6-months purchase totals (bar) and
 * purchases-by-product breakdown (horizontal bar) — same ApexCharts +
 * theme-listener pattern as renderAttendanceChart() above.
 */
function renderCustomerPurchaseTrendChart() {
    const el = document.getElementById('customerPurchaseTrendChart');
    if (!el || !window.ApexCharts) {
        return;
    }

    const data = JSON.parse(el.dataset.chartPurchases || '[]');
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
        series: [{ name: 'Purchases', data: data.map((row) => row.total) }],
        xaxis: { categories: data.map((row) => row.label) },
        colors: ['#0d5aa7'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
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
    renderCustomerPurchaseTrendChart();
    renderCustomerProductBreakdownChart();
    initCountUp();
});
