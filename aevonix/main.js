(function () {
  'use strict';

  const navbar = document.getElementById('navbar');
  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 40);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  const toggle = document.getElementById('navToggle');
  const mobileMenu = document.getElementById('navMobile');
  if (toggle && mobileMenu) {
    toggle.addEventListener('click', () => {
      const open = toggle.classList.toggle('open');
      mobileMenu.classList.toggle('open', open);
      toggle.setAttribute('aria-expanded', String(open));
    });
    mobileMenu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        toggle.classList.remove('open');
        mobileMenu.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  const currentPage = window.location.pathname.split('/').pop() || 'index';
  document.querySelectorAll('.nav-links a, .nav-mobile a').forEach(a => {
    const href = a.getAttribute('href');
    if (href === currentPage || (currentPage === '' && href === 'index')) {
      a.classList.add('active');
    }
  });

  const reveals = document.querySelectorAll('.reveal');
  if (reveals.length > 0) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          observer.unobserve(e.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    reveals.forEach(el => observer.observe(el));
  }

  const faqItems = document.querySelectorAll('.faq-item');
  function closeFaq(item) {
    const btn = item.querySelector('.faq-btn');
    const body = item.querySelector('.faq-body');
    if (!btn || !body) return;
    btn.setAttribute('aria-expanded', 'false');
    body.style.maxHeight = '0px';
    item.classList.remove('open');
  }
  function openFaq(item) {
    const btn = item.querySelector('.faq-btn');
    const body = item.querySelector('.faq-body');
    if (!btn || !body) return;
    btn.setAttribute('aria-expanded', 'true');
    body.style.maxHeight = body.scrollHeight + 'px';
    item.classList.add('open');
  }
  faqItems.forEach(item => {
    const btn = item.querySelector('.faq-btn');
    if (!btn) return;
    btn.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      faqItems.forEach(otherItem => {
        if (otherItem !== item) closeFaq(otherItem);
      });
      if (isOpen) closeFaq(item);
      else openFaq(item);
    });
  });
  const faqSearch = document.getElementById('faqSearch');
  if (faqSearch) {
    faqSearch.addEventListener('input', () => {
      const query = faqSearch.value.toLowerCase().trim();
      faqItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        const match = !query || text.includes(query);
        item.style.display = match ? '' : 'none';
        if (!match) closeFaq(item);
      });
    });
  }
  document.querySelectorAll('.faq-nav-item').forEach(link => {
    link.addEventListener('click', () => {
      document.querySelectorAll('.faq-nav-item').forEach(item => item.classList.remove('active'));
      link.classList.add('active');
    });
  });
  window.addEventListener('resize', () => {
    document.querySelectorAll('.faq-item.open').forEach(item => {
      const body = item.querySelector('.faq-body');
      if (body) body.style.maxHeight = body.scrollHeight + 'px';
    });
  });

  function animateCounter(el) {
    const target = parseFloat(el.dataset.target);
    const suffix = el.dataset.suffix || '';
    const duration = 1600;
    const start = performance.now();
    const isDecimal = target % 1 !== 0;
    function tick(now) {
      const t = Math.min((now - start) / duration, 1);
      const ease = 1 - Math.pow(1 - t, 3);
      const value = target * ease;
      el.textContent = (isDecimal ? value.toFixed(1) : Math.floor(value)) + suffix;
      if (t < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
  const counters = document.querySelectorAll('[data-target]');
  if (counters.length > 0) {
    const counterObserver = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          animateCounter(e.target);
          counterObserver.unobserve(e.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(el => counterObserver.observe(el));
  }

  document.querySelectorAll('.project-form, .contact-form').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      const success = document.getElementById('formSuccess');
      const btn = form.querySelector('button[type="submit"]');
      if (!btn) return;
      const original = btn.innerHTML;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      btn.disabled = true;
      btn.style.opacity = '0.7';
      setTimeout(() => {
        if (success && form.id === 'contactForm') {
          form.style.display = 'none';
          success.classList.add('show');
        } else {
          btn.innerHTML = '✓ Message Sent!';
          setTimeout(() => {
            btn.innerHTML = original;
            btn.disabled = false;
            btn.style.opacity = '';
            form.reset();
          }, 2500);
        }
      }, 700);
    });
  });

  const pricingToggle = document.getElementById('pricingToggle');
  if (pricingToggle) {
    const prices = { p1: [799, 639], p2: [1799, 1439] };
    let annual = false;
    const updatePricing = () => {
      annual = !annual;
      pricingToggle.classList.toggle('on', annual);
      pricingToggle.setAttribute('aria-checked', String(annual));
      Object.keys(prices).forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = prices[id][annual ? 1 : 0].toLocaleString();
      });
    };
    pricingToggle.addEventListener('click', updatePricing);
    pricingToggle.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        updatePricing();
      }
    });
  }

  document.querySelectorAll('.svc-tab, .filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      const group = tab.classList.contains('svc-tab') ? '.svc-tab' : '.filter-tab';
      document.querySelectorAll(group).forEach(t => {
        t.classList.remove('active');
        if (t.hasAttribute('aria-selected')) t.setAttribute('aria-selected', 'false');
      });
      tab.classList.add('active');
      if (tab.hasAttribute('aria-selected')) tab.setAttribute('aria-selected', 'true');
    });
  });
})();
