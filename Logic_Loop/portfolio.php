<?php
require 'config/database.php';
session_start();
$header_nav['portfolio'] = 'active';
$pagetitle = 'Portfolio – Logic Loop';
$page_description = 'View our successful electrical engineering projects: power systems upgrades, PCB designs, industrial automation, solar farms, and more.';
$page_keywords = 'electrical projects, power system case study, PCB portfolio, automation projects, solar installation';
$page_canonical = 'https://logicloop.wuaze.com/portfolio';
include 'components/header.php';
?>
<link rel="stylesheet" href="style/portfolio.css">

<div class="page-hero">
    <div class="hero-grid"></div>
    <div style="position:relative;z-index:2">
        <h1 class="page-title">Our <span>Portfolio</span></h1>
        <p class="page-desc">A showcase of engineering projects from industrial power systems to cutting edge IoT devices.</p>
    </div>
</div>

<section class="portfolio-section">
    <div class="filter-bar">
        <button class="filter-btn active" onclick="filterPort(this, 'all')"><i class="fas fa-th-large"></i> All</button>
        <button class="filter-btn" onclick="filterPort(this, 'power')"><i class="fas fa-bolt"></i> Power Systems</button>
        <button class="filter-btn" onclick="filterPort(this, 'pcb')"><i class="fas fa-microchip"></i> PCB/Embedded</button>
        <button class="filter-btn" onclick="filterPort(this, 'automation')"><i class="fas fa-robot"></i> Automation</button>
        <button class="filter-btn" onclick="filterPort(this, 'renewable')"><i class="fas fa-solar-panel"></i> Renewable</button>
    </div>
    <div class="portfolio-grid" id="portGrid">
        <div class="portfolio-card" data-cat="power">
            <div class="port-thumb"><i class="fas fa-charging-station"></i></div>
            <div class="port-info">
                <h3>33/11kV Substation Upgrade</h3>
                <p>Complete revamp of industrial substation with new switchgear, protection relays, and SCADA integration.</p>
                <div class="port-tags"><span class="ptag">Power</span><span class="ptag">SCADA</span></div>
            </div>
        </div>
        <div class="portfolio-card" data-cat="pcb">
            <div class="port-thumb"><i class="fas fa-microchip"></i></div>
            <div class="port-info">
                <h3>IoT Energy Monitor PCB</h3>
                <p>Custom 4 layer ESP32 board with current/voltage sensing, WiFi/BLE, and cloud dashboard.</p>
                <div class="port-tags"><span class="ptag">PCB</span><span class="ptag">IoT</span></div>
            </div>
        </div>
        <div class="portfolio-card" data-cat="automation">
            <div class="port-thumb"><i class="fas fa-industry"></i></div>
            <div class="port-info">
                <h3>Packaging Line PLC Upgrade</h3>
                <p>Replaced obsolete PLC with Siemens S7-1200, added HMI, reduced downtime by 35%.</p>
                <div class="port-tags"><span class="ptag">PLC</span><span class="ptag">HMI</span></div>
            </div>
        </div>
        <div class="portfolio-card" data-cat="renewable">
            <div class="port-thumb"><i class="fas fa-solar-panel"></i></div>
            <div class="port-info">
                <h3>1MW Solar Farm Gujranwala</h3>
                <p>Engineering, DC/AC design, grid interconnection, and net metering approval.</p>
                <div class="port-tags"><span class="ptag">Solar</span><span class="ptag">Grid tie</span></div>
            </div>
        </div>
        <div class="portfolio-card" data-cat="power">
            <div class="port-thumb"><i class="fas fa-tachometer-alt"></i></div>
            <div class="port-info">
                <h3>Arc Flash Study Cement Plant</h3>
                <p>IEEE 1584 compliant analysis, labeling, and PPE recommendations.</p>
                <div class="port-tags"><span class="ptag">Arc Flash</span><span class="ptag">Safety</span></div>
            </div>
        </div>
        <div class="portfolio-card" data-cat="pcb">
            <div class="port-thumb"><i class="fas fa-heartbeat"></i></div>
            <div class="port-info">
                <h3>Medical Wearable PCB</h3>
                <p>Ultra low power design with ECG front end and BLE transmission.</p>
                <div class="port-tags"><span class="ptag">Medical</span><span class="ptag">BLE</span></div>
            </div>
        </div>
    </div>
</section>

<section class="stats-bar">
    <div class="stats-grid">
        <div class="stat-item"><span class="stat-num" data-target="350">0</span><div class="stat-label"><i class="fas fa-check-circle"></i> Projects Completed</div></div>
        <div class="stat-item"><span class="stat-num" data-target="100">0</span><div class="stat-label"><i class="fas fa-smile"></i> Client Satisfaction %</div></div>
        <div class="stat-item"><span class="stat-num" data-target="15">0</span><div class="stat-label"><i class="fas fa-chart-line"></i> Industries Served</div></div>
        <div class="stat-item"><span class="stat-num" data-target="97">0</span><div class="stat-label"><i class="fas fa-clock"></i> On-Time Delivery %</div></div>
    </div>
</section>

<section class="section" style="background:var(--panel)">
    <div style="text-align:center;max-width:600px;margin:0 auto">
        <div class="section-tag reveal"><i class="fas fa-star"></i> Client Love</div>
        <h2 class="section-title reveal">What <span>Clients</span> Say About Our Work</h2>
    </div>
    <div class="testi-grid">
        <div class="testi-card reveal">
            <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <p class="testi-text">"The team redesigned our entire factory power distribution. Efficiency up 28%, energy costs down. Exceptional work."</p>
            <div class="testi-author"><div class="testi-avatar">AK</div><div><div class="testi-name">Ahmed Karimi</div><div class="testi-role">Karimi Industries</div></div></div>
        </div>
        <div class="testi-card reveal">
            <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <p class="testi-text">"Logic Loop delivered our IoT gateway PCB on first prototype. Flawless design and excellent support."</p>
            <div class="testi-author"><div class="testi-avatar">SR</div><div><div class="testi-name">Sara Rehman</div><div class="testi-role">NovaTech</div></div></div>
        </div>
        <div class="testi-card reveal">
            <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <p class="testi-text">"Their PLC automation saved us 40% in labor costs. Highly recommended for industrial projects."</p>
            <div class="testi-author"><div class="testi-avatar">MH</div><div><div class="testi-name">Muhammad Hassan</div><div class="testi-role">SteelPak</div></div></div>
        </div>
    </div>
</section>

<div class="cta-banner">
    <h2>Ready to <span style="color:var(--electric)">Start Your Project?</span></h2>
    <p>Let’s turn your idea into a reality. Contact us today for a free consultation.</p>
    <a href="contact" class="btn-p"><i class="fas fa-paper-plane"></i> Get Quote</a>
</div>

<script src="scripts/script.js"></script>
<script src="scripts/index.js"></script>
<?php include 'components/footerpre.php'; ?>
<?php include 'components/footer.php'; ?>