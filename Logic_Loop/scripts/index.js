function animateCounters() {
    document.querySelectorAll('.stat-num[data-target]').forEach(el => {
        const target = parseInt(el.dataset.target);
        let c = 0;
        const step = target / 60;
        const timer = setInterval(() => {
            c = Math.min(c + step, target);
            el.textContent = Math.floor(c) + (target > 5 ? '+' : '');
            if (c >= target) clearInterval(timer);
        }, 25);
    });
}

function initReveal() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 80);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => {
        el.classList.remove('visible');
        observer.observe(el);
    });
    const statsSection = document.querySelector('.stats-grid');
    if (statsSection) {
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        statsObserver.observe(statsSection);
    }
}
if (document.getElementById('portGrid')) {
    window.filterPort = function (btn, cat) {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.portfolio-card').forEach(card => {
            if (cat === 'all' || card.dataset.cat === cat) {
                card.style.display = '';
                card.style.animation = 'fadeUp .4s ease';
            } else {
                card.style.display = 'none';
            }
        });
    };
}

window.submitForm = function () {
    const msg = document.getElementById('formMsg');
    if (msg) {
        msg.style.display = 'block';
        setTimeout(() => msg.style.display = 'none', 4000);
    }
};
window.submitTicket = function () {
    const msg = document.getElementById('ticketMsg');
    if (msg) {
        msg.style.display = 'block';
        setTimeout(() => msg.style.display = 'none', 5000);
    }
};

window.toggleFAQ = function (item) {
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(i => {
        i.classList.remove('open');
        const a = i.querySelector('.faq-a');
        if (a) a.classList.remove('open');
    });
    if (!isOpen) {
        item.classList.add('open');
        const a = item.querySelector('.faq-a');
        if (a) a.classList.add('open');
    }
};
window.addEventListener('load', initReveal);
