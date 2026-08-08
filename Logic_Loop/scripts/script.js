// CURSOR
const cur = document.getElementById('cursor'), ring = document.getElementById('cursorRing');
let mx = 0, my = 0, rx = 0, ry = 0;
document.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; });
(function animC() { cur.style.left = mx + 'px'; cur.style.top = my + 'px'; rx += (mx - rx) * .12; ry += (my - ry) * .12; ring.style.left = rx + 'px'; ring.style.top = ry + 'px'; requestAnimationFrame(animC); })();
document.querySelectorAll('a,button,.service-card,.portfolio-card,.faq-item,.support-card,.team-card,.value-card,.pricing-card').forEach(el => {
  el.addEventListener('mouseenter', () => { cur.style.transform = 'translate(-50%,-50%) scale(2.2)'; ring.style.borderColor = 'rgba(0,245,255,.9)'; ring.style.transform = 'translate(-50%,-50%) scale(1.4)'; });
  el.addEventListener('mouseleave', () => { cur.style.transform = 'translate(-50%,-50%) scale(1)'; ring.style.borderColor = 'rgba(0,245,255,.5)'; ring.style.transform = 'translate(-50%,-50%) scale(1)'; });
});
function toggleMenu() {
  document.getElementById('navLinks').classList.toggle('open');
  document.getElementById("hamburger").classList.toggle("active");
}