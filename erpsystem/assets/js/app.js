const menuButton = document.querySelector('[data-menu-toggle]');
if (menuButton) {
    menuButton.addEventListener('click', () => {
        document.body.classList.toggle('menu-open');
    });
}

document.querySelectorAll('[data-table-search]').forEach((input) => {
    input.addEventListener('input', () => {
        const table = document.getElementById(input.dataset.tableSearch);
        if (!table) return;

        const needle = input.value.trim().toLowerCase();
        table.querySelectorAll('tbody tr').forEach((row) => {
            row.style.display = row.textContent.toLowerCase().includes(needle) ? '' : 'none';
        });
    });
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

const flash = document.querySelector('[data-flash]');
if (flash) {
    setTimeout(() => {
        flash.style.opacity = '0';
        flash.style.transform = 'translateY(-6px)';
        flash.style.transition = 'all .25s ease';
    }, 2200);
}

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
if (!reduceMotion) {
    document.querySelectorAll('.stat-card strong, .mini-stats strong').forEach((counter) => {
        const finalText = counter.textContent.trim();
        const target = Number(finalText.replace(/[^0-9.-]/g, ''));

        if (!Number.isFinite(target) || target === 0) return;

        const isCurrency = finalText.includes('Rs');
        const formatter = new Intl.NumberFormat();
        const startTime = performance.now();
        const duration = 850;

        const draw = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(target * eased);
            counter.textContent = isCurrency ? `Rs ${formatter.format(value)}` : formatter.format(value);

            if (progress < 1) {
                requestAnimationFrame(draw);
            } else {
                counter.textContent = finalText;
            }
        };

        requestAnimationFrame(draw);
    });
}

const chart = document.getElementById('financeChart');
if (chart) {
    const ctx = chart.getContext('2d');
    const revenue = Number(chart.dataset.revenue || 0);
    const expense = Number(chart.dataset.expense || 0);
    const values = [
        { label: 'Revenue', value: revenue, color: '#15803d' },
        { label: 'Costs', value: expense, color: '#b42318' },
    ];
    const max = Math.max(...values.map((item) => item.value), 1);
    const width = chart.width = chart.offsetWidth * window.devicePixelRatio;
    const height = chart.height = 240 * window.devicePixelRatio;
    ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
    const visualWidth = width / window.devicePixelRatio;
    const visualHeight = height / window.devicePixelRatio;

    const drawChart = (progress = 1) => {
        const eased = 1 - Math.pow(1 - progress, 3);

        ctx.clearRect(0, 0, visualWidth, visualHeight);
        ctx.font = '13px Segoe UI, Arial';
        ctx.fillStyle = '#667085';
        ctx.fillText('Rs', 18, 22);

        values.forEach((item, index) => {
            const barWidth = 86;
            const gap = 48;
            const x = 58 + index * (barWidth + gap);
            const barHeight = Math.max(8, (item.value / max) * 150 * eased);
            const y = 184 - barHeight;

            ctx.fillStyle = '#eef2f7';
            ctx.fillRect(x, 34, barWidth, 150);
            ctx.fillStyle = item.color;
            ctx.fillRect(x, y, barWidth, barHeight);
            ctx.fillStyle = '#17202a';
            ctx.font = '700 13px Segoe UI, Arial';
            ctx.fillText(item.label, x, 210);
            ctx.font = '12px Segoe UI, Arial';
            ctx.fillStyle = '#667085';
            ctx.fillText(new Intl.NumberFormat().format(item.value), x, 229);
        });
    };

    if (reduceMotion) {
        drawChart();
    } else {
        const startTime = performance.now();
        const duration = 700;
        const animate = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            drawChart(progress);
            if (progress < 1) requestAnimationFrame(animate);
        };
        requestAnimationFrame(animate);
    }
}
