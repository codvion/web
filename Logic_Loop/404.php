<?php
http_response_code(404);
require 'config/database.php';
session_start();
$pagetitle = '404 - Logic Loop';
$page_description = '404 Page Logic Loop';
$page_keywords = '404';
$page_canonical = 'https://logicloop.wuaze.com/404';
include 'components/header.php';
?>
<style>
    .page-404{display:flex;align-items:center;justify-content:center;text-align:center;padding:120px 5% 80px;position:relative;overflow:hidden}
.err-code{font-family:'Orbitron',monospace;font-size:clamp(6rem,18vw,14rem);font-weight:900;color:transparent;-webkit-text-stroke:2px rgba(0,245,255,.3);line-height:.9;margin-bottom:20px}
.err-title{font-family:'Orbitron',monospace;font-size:1.4rem;color:#fff;margin-bottom:12px}
.err-desc{font-size:.95rem;color:var(--muted);max-width:400px;margin:0 auto 36px;line-height:1.7}.btn-p{background:linear-gradient(135deg,var(--electric),#007799);color:var(--darker);padding:15px 34px;border:none;border-radius:6px;font-family:'Orbitron',monospace;font-size:.76rem;font-weight:700;letter-spacing:2px;cursor:pointer;transition:all .3s;text-transform:uppercase;text-decoration:none;display:inline-block;position:relative;overflow:hidden}
.btn-p:hover{transform:translateY(-3px);box-shadow:var(--glow),0 10px 30px rgba(0,0,0,.4)}
.btn-o{background:transparent;color:var(--electric);padding:15px 34px;border:1px solid rgba(0,245,255,.4);border-radius:6px;font-family:'Orbitron',monospace;font-size:.76rem;font-weight:600;letter-spacing:2px;cursor:pointer;transition:all .3s;text-transform:uppercase;text-decoration:none;display:inline-block}
.btn-o:hover{background:rgba(0,245,255,.08);border-color:var(--electric);transform:translateY(-3px);box-shadow:var(--glow)}
</style>
<div class="page" id="page-404">
  <div class="page-404">
    <div class="hero-grid"></div>
    <div style="position:relative;z-index:2">
      <div class="err-code">404</div>
      <div class="err-title">Circuit Breaker Tripped!</div>
      <p class="err-desc">This page has an open circuit. The signal can't reach its destination. Let's reroute you back to the main grid.</p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
        <a class="btn-p" href="index">← Back to Home</a>
        <a class="btn-o" href="contact">Contact Us</a>
      </div>
      <div style="margin-top:50px;opacity:.25">
        <svg width="320" height="80" viewBox="0 0 320 80" fill="none">
          <path d="M10 40L50 40L50 15L80 15L80 40" stroke="#00f5ff" stroke-width="2"/>
          <path d="M80 40L100 40" stroke="#00f5ff" stroke-width="2" stroke-dasharray="4,4"/>
          <circle cx="112" cy="40" r="12" stroke="#ff6b35" stroke-width="2" fill="none"/>
          <line x1="107" y1="35" x2="117" y2="45" stroke="#ff6b35" stroke-width="2"/>
          <line x1="117" y1="35" x2="107" y2="45" stroke="#ff6b35" stroke-width="2"/>
          <path d="M124 40L160 40L160 65L190 65L190 40L230 40L230 20L260 20L260 40L310 40" stroke="#00f5ff" stroke-width="2"/>
        </svg>
      </div>
    </div>
  </div>
</div>

<script src="scripts/script.js"></script>
<script src="scripts/index.js"></script>
<?php include 'components/footerpre.php'; ?>
<?php include 'components/footer.php'; ?>