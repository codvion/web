<?php
require 'config/database.php';
session_start();
$pagetitle = 'LogicLoop – Electrical Engineering & Automation Solutions';
$page_description = 'LogicLoop delivers expert electrical engineering services: power systems, PCB design, industrial automation, embedded systems, and renewable energy. Free consultation.';
$page_keywords = 'electrical engineering, power systems, PCB design, industrial automation, embedded systems, renewable energy, PLC programming, circuit design, Lahore';
$page_canonical = 'https://logicloop.wuaze.com';
$header_nav['home'] = 'active';
include 'components/header.php';
?>
<link rel="stylesheet" href="style/index.css">

<section class="hero">
    <div class="hero-grid"></div>
    <div style="position:relative;z-index:2">
        <div class="hero-badge"><i class="fas fa-bolt"></i> POWERING INNOVATION SINCE 2018</div>
        <h1 class="hero-title"><span class="l1">Engineering</span><span class="l2">The Future</span></h1>
        <p class="hero-desc">Logic Loop delivers cutting edge electrical engineering solutions from circuit design to smart automation. We transform your vision into precise, powerful systems that work.</p>
        <div class="hero-btns">
            <a class="btn-p" href="services">Explore Services</a>
            <a class="btn-o" href="portfolio">View Portfolio</a>
        </div>
    </div>
    <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.07;pointer-events:none" viewBox="0 0 1400 800" preserveAspectRatio="xMidYMid slice">
        <path d="M100 400L250 400L250 200L400 200L400 350L600 350L600 150L800 150L800 400L1000 400L1000 250L1200 250L1200 400L1350 400" stroke="#00f5ff" stroke-width="1.5" fill="none"><animate attributeName="stroke-dasharray" values="0,3000;3000,0" dur="5s" repeatCount="indefinite"/></path>
        <path d="M100 600L300 600L300 450L550 450L550 600L750 600L750 500L900 500L900 600L1100 600L1100 480L1300 480" stroke="#f5e642" stroke-width="1" fill="none" opacity=".6"><animate attributeName="stroke-dasharray" values="0,3000;3000,0" dur="7s" repeatCount="indefinite"/></path>
    </svg>
</section>

<!-- Stats Bar -->
<div class="stats-bar">
    <div class="stats-grid">
        <div class="stat-item reveal"><span class="stat-num" data-target="350">0</span><div class="stat-label">Projects Delivered</div></div>
        <div class="stat-item reveal"><span class="stat-num" data-target="120">0</span><div class="stat-label">Happy Clients</div></div>
        <div class="stat-item reveal"><span class="stat-num" data-target="7">0</span><div class="stat-label">Years Experience</div></div>
        <div class="stat-item reveal"><span class="stat-num" data-target="15">0</span><div class="stat-label">Expert Engineers</div></div>
    </div>
</div>

<!-- Services Overview -->
<section class="section" style="background:linear-gradient(180deg,var(--darker) 0%,var(--panel) 100%);padding-top:100px">
    <div style="text-align:center;max-width:700px;margin:0 auto 10px">
        <div class="section-tag reveal">What We Do</div>
        <h2 class="section-title reveal">Core <span>Engineering</span> Services</h2>
        <p style="color:var(--muted);font-size:.95rem;line-height:1.8" class="reveal">From power systems to embedded electronics we engineer solutions that work.</p>
    </div>
    <div class="services-grid">
        <div class="service-card reveal"><div class="service-icon"><i class="fas fa-bolt"></i></div><h3>Power Systems</h3><p>Complete design, analysis and optimization of high voltage and low voltage electrical power distribution systems for industrial and commercial applications.</p><a class="service-tag" href="services">→ Learn More</a></div>
        <div class="service-card reveal"><div class="service-icon"><i class="fas fa-plug"></i></div><h3>Circuit Design</h3><p>Custom PCB layout, schematic capture, analog & digital circuit design with rigorous testing and prototyping for mission critical hardware products.</p><a class="service-tag" href="services">→ Learn More</a></div>
        <div class="service-card reveal"><div class="service-icon"><i class="fas fa-robot"></i></div><h3>Automation & Control</h3><p>PLC programming, SCADA systems, industrial automation and intelligent control systems that maximize efficiency and minimize downtime.</p><a class="service-tag" href="services">→ Learn More</a></div>
        <div class="service-card reveal"><div class="service-icon"><i class="fas fa-satellite-dish"></i></div><h3>Embedded Systems</h3><p>Firmware development, microcontroller programming, IoT device design and real time systems for smart applications and connected devices.</p><a class="service-tag" href="services">→ Learn More</a></div>
        <div class="service-card reveal"><div class="service-icon"><i class="fas fa-battery-full"></i></div><h3>Energy Solutions</h3><p>Renewable energy integration, solar and wind power design, battery management systems and energy efficiency auditing for green infrastructure.</p><a class="service-tag" href="services">→ Learn More</a></div>
        <div class="service-card reveal"><div class="service-icon"><i class="fas fa-shield-alt"></i></div><h3>Safety & Compliance</h3><p>Electrical safety auditing, NEMA/IEC standards compliance, arc flash analysis, grounding studies and protection relay coordination.</p><a class="service-tag" href="services">→ Learn More</a></div>
    </div>
</section>

<!-- Why Us -->
<section class="section" style="background:var(--darker)">
    <div class="why-grid">
        <div>
            <svg width="100%" viewBox="0 0 400 400" fill="none" style="max-width:420px">
                <rect width="400" height="400" rx="20" fill="#0a1520"/>
                <defs><pattern id="cg" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(0,245,255,.08)" stroke-width=".5"/></pattern></defs>
                <rect width="400" height="400" fill="url(#cg)" rx="20"/>
                <path d="M60 200L120 200L120 120L200 120L200 80L280 80L280 120L340 120" stroke="#00f5ff" stroke-width="2" fill="none" opacity=".7"><animate attributeName="stroke-dasharray" values="0,600;600,0;600,0" dur="3.5s" repeatCount="indefinite"/></path>
                <path d="M60 200L120 200L120 280L200 280L200 320L280 320L280 280L340 280" stroke="#f5e642" stroke-width="2" fill="none" opacity=".5"><animate attributeName="stroke-dasharray" values="0,600;600,0;600,0" dur="4s" repeatCount="indefinite"/></path>
                <path d="M200 120L200 200L200 280" stroke="#ff6b35" stroke-width="1.5" fill="none" opacity=".4"><animate attributeName="stroke-dasharray" values="0,200;200,0;200,0" dur="2s" repeatCount="indefinite"/></path>
                <rect x="170" y="170" width="60" height="60" rx="6" fill="#0d2035" stroke="#00f5ff" stroke-width="1.5"/>
                <text x="200" y="206" text-anchor="middle" fill="#00f5ff" font-size="10" font-family="Orbitron,monospace" font-weight="700">MCU</text>
                <circle cx="120" cy="200" r="5" fill="#00f5ff"><animate attributeName="r" values="5;7;5" dur="2s" repeatCount="indefinite"/></circle>
                <circle cx="280" cy="200" r="4" fill="#f5e642"><animate attributeName="r" values="4;6;4" dur="2.5s" repeatCount="indefinite"/></circle>
                <circle cx="200" cy="120" r="4" fill="#00f5ff" opacity=".8"/>
                <circle cx="200" cy="280" r="4" fill="#f5e642" opacity=".8"/>
                <rect x="145" y="196" width="20" height="8" rx="2" fill="#0d2035" stroke="#00f5ff" stroke-width="1" opacity=".8"/>
                <rect x="235" y="196" width="20" height="8" rx="2" fill="#0d2035" stroke="#00f5ff" stroke-width="1" opacity=".8"/>
            </svg>
        </div>
        <div>
            <div class="section-tag reveal">Why Logic Loop</div>
            <h2 class="section-title reveal">Built on <span>Precision</span><br>& Expertise</h2>
            <ul class="why-list">
                <li class="why-item reveal"><span class="why-num">01</span><div><h4>Licensed Engineers</h4><p>Our team holds PE licenses and advanced degrees in Electrical, Electronics and Control Engineering.</p></div></li>
                <li class="why-item reveal"><span class="why-num">02</span><div><h4>End to End Solutions</h4><p>From concept to commissioning we handle every phase of your electrical engineering project.</p></div></li>
                <li class="why-item reveal"><span class="why-num">03</span><div><h4>Cutting Edge Tools</h4><p>We use MATLAB, AutoCAD Electrical, ETAP, Altium Designer, and advanced simulation platforms.</p></div></li>
                <li class="why-item reveal"><span class="why-num">04</span><div><h4>On Time, Every Time</h4><p>Rigorous project management ensures 97% on time delivery across all our engagements.</p></div></li>
            </ul>
        </div>
    </div>
</section>

<section style="background:var(--panel);padding:100px 5%">
    <div style="text-align:center;max-width:600px;margin:0 auto 10px">
        <div class="section-tag reveal">Client Reviews</div>
        <h2 class="section-title reveal">What Our <span>Clients</span> Say</h2>
    </div>
    <div class="testi-grid">
        <div class="testi-card reveal"><div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><p class="testi-text">"Logic Loop redesigned our entire factory power distribution. The efficiency gains were remarkable reduced energy costs by 28% in the first quarter."</p><div class="testi-author"><div class="testi-avatar">AK</div><div><div class="testi-name">Ahmed Karimi</div><div class="testi-role">Plant Manager, Karimi Industries</div></div></div></div>
        <div class="testi-card reveal"><div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><p class="testi-text">"Exceptional circuit design work for our IoT product line. They nailed the PCB on first prototype. Professional, fast, and highly technical team."</p><div class="testi-author"><div class="testi-avatar">SR</div><div><div class="testi-name">Sara Rehman</div><div class="testi-role">CTO, NovaTech Devices</div></div></div></div>
        <div class="testi-card reveal"><div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div><p class="testi-text">"Their automation system transformed our production line. Zero unexpected downtime in 18 months since implementation. Highly recommend Logic Loop."</p><div class="testi-author"><div class="testi-avatar">MH</div><div><div class="testi-name">Muhammad Hassan</div><div class="testi-role">Operations Director, SteelPak Ltd</div></div></div></div>
    </div>
</section>

<section class="join-team reveal">
    <h2>Join the <span style="color:var(--electric)">Logic Loop</span> Team</h2>
    <p>Become part of our engineering community. Access exclusive resources, collaborate on projects, and grow your career.</p>
    <div class="join-buttons">
        <a href="login" class="btn-p btn-login"><i class="fas fa-lock"></i> Login</a>
        <a href="register" class="btn-p btn-register"><i class="fas fa-pen-alt"></i> Register</a>
    </div>
    <p style="margin-top: 30px; font-size: 0.8rem;">After login/register, you can apply for team positions, submit your portfolio, and access member only content.</p>
</section>

<div class="cta-banner">
    <div class="section-tag reveal" style="display:block;text-align:center;padding-left:0">Start a Project</div>
    <h2 class="reveal">Ready to Engineer <span style="color:var(--electric)">Something Great?</span></h2>
    <p class="reveal">Talk to our engineers today. Free initial consultation for every new project.</p>
    <a class="btn-p reveal" href="contact">Get Free Consultation</a>
</div>
<script src="scripts/script.js"></script>
<script src="scripts/index.js"></script>
<?php include 'components/footerpre.php'; ?>
<?php include 'components/footer.php'; ?>