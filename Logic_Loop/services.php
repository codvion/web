<?php
require 'config/database.php';
session_start();
$header_nav['service'] = 'active';
$pagetitle = 'Services – Logic Loop';
$page_description = 'Explore Logic Loop’s full range of electrical engineering services: power systems, PCB design, industrial automation, embedded systems, renewable energy, and safety compliance.';
$page_keywords = 'electrical engineering services, power system design, PCB layout, PLC programming, embedded systems, solar energy, arc flash study';
$page_canonical = 'https://logicloop.wuaze.com/services';
include 'components/header.php';
?>

<link rel="stylesheet" href="style/services.css">

<div class="page-hero">
    <div class="hero-grid"></div>
    <div style="position:relative;z-index:2">
        <h1 class="page-title">Our <span>Services</span></h1>
        <p class="page-desc">Comprehensive electrical engineering solutions from high voltage power systems to micro scale embedded electronics.</p>
    </div>
</div>

<section class="services-full">
    <div style="text-align:center;max-width:600px;margin:0 auto 10px">
        <div class="section-tag reveal">What We Offer</div>
        <h2 class="section-title reveal">Full <span>Spectrum</span> Engineering</h2>
    </div>
    <div class="services-full-grid">
        <div class="sfc reveal">
            <div class="sfc-head">
                <div class="sfc-icon"><i class="fas fa-bolt"></i></div>
                <div><div class="sfc-title">Power Systems Engineering</div><div class="sfc-sub">Design & Analysis</div></div>
            </div>
            <div class="sfc-desc">Complete power system studies, load flow, short circuit, coordination, and arc flash analysis per IEEE standards.</div>
            <ul class="sfc-list"><li>LV/MV Switchgear Design</li><li>Protection Relay Coordination</li><li>Transformer & Generator Sizing</li></ul>
        </div>
        <div class="sfc reveal">
            <div class="sfc-head">
                <div class="sfc-icon"><i class="fas fa-microchip"></i></div>
                <div><div class="sfc-title">PCB & Embedded Design</div><div class="sfc-sub">Hardware & Firmware</div></div>
            </div>
            <div class="sfc-desc">End-to-end electronics development: schematic, layout, prototyping, and embedded C/C++ firmware.</div>
            <ul class="sfc-list"><li>Multi-layer PCB (up to 8 layers)</li><li>STM32, ESP32, AVR, PIC</li><li>IoT & Wireless Integration</li></ul>
        </div>
        <div class="sfc reveal">
            <div class="sfc-head">
                <div class="sfc-icon"><i class="fas fa-industry"></i></div>
                <div><div class="sfc-title">Industrial Automation</div><div class="sfc-sub">PLC & SCADA</div></div>
            </div>
            <div class="sfc-desc">Custom automation solutions for manufacturing, process control, and material handling systems.</div>
            <ul class="sfc-list"><li>PLC Programming (Siemens, Allen Bradley)</li><li>HMI/SCADA Development</li><li>VFD & Servo Integration</li></ul>
        </div>
        <div class="sfc reveal">
            <div class="sfc-head">
                <div class="sfc-icon"><i class="fas fa-solar-panel"></i></div>
                <div><div class="sfc-title">Renewable Energy</div><div class="sfc-sub">Solar & Storage</div></div>
            </div>
            <div class="sfc-desc">Feasibility, design, and installation supervision for on grid, off grid, and hybrid solar power plants.</div>
            <ul class="sfc-list"><li>PVsyst Simulation</li><li>BESS Sizing</li><li>Net Metering Documentation</li></ul>
        </div>
        <div class="sfc reveal">
            <div class="sfc-head">
                <div class="sfc-icon"><i class="fas fa-shield-alt"></i></div>
                <div><div class="sfc-title">Safety & Compliance</div><div class="sfc-sub">Arc Flash / Earthing</div></div>
            </div>
            <div class="sfc-desc">Ensure personnel safety and regulatory compliance with detailed risk assessments and studies.</div>
            <ul class="sfc-list"><li>Arc Flash Hazard Analysis</li><li>Earthing & Lightning Protection</li><li>PEC / NEC / IEC Compliance</li></ul>
        </div>
        <div class="sfc reveal">
            <div class="sfc-head">
                <div class="sfc-icon"><i class="fas fa-charging-station"></i></div>
                <div><div class="sfc-title">EV Charging Infrastructure</div><div class="sfc-sub">AC & DC Fast Chargers</div></div>
            </div>
            <div class="sfc-desc">Complete turnkey solutions for residential, commercial, and public EV charging stations.</div>
            <ul class="sfc-list"><li>Load Management</li><li>OCPP Compliance</li><li>Grid Integration</li></ul>
        </div>
    </div>
</section>

<section class="section" style="background:var(--panel)">
    <div style="text-align:center;max-width:700px;margin:0 auto">
        <div class="section-tag reveal">Our Process</div>
        <h2 class="section-title reveal">From <span>Concept</span> to Commissioning</h2>
    </div>
    <div style="max-width:1100px;margin:60px auto 0;display:grid;grid-template-columns:repeat(4,1fr);gap:20px">
        <div class="value-card reveal"><div class="value-icon"><i class="fas fa-clipboard-list"></i></div><h4>1. Discovery</h4><p>Understand requirements, feasibility, and initial design.</p></div>
        <div class="value-card reveal"><div class="value-icon"><i class="fas fa-cogs"></i></div><h4>2. Engineering</h4><p>Detailed design, simulation, and prototyping.</p></div>
        <div class="value-card reveal"><div class="value-icon"><i class="fas fa-microchip"></i></div><h4>3. Implementation</h4><p>Manufacturing, coding, integration, and testing.</p></div>
        <div class="value-card reveal"><div class="value-icon"><i class="fas fa-check-circle"></i></div><h4>4. Commissioning</h4><p>On-site installation, training, and handover.</p></div>
    </div>
</section>

<section class="section" style="background:var(--darker)">
    <div style="text-align:center;max-width:700px;margin:0 auto">
        <div class="section-tag reveal">Why Logic Loop</div>
        <h2 class="section-title reveal">What Makes Us <span>Different</span></h2>
    </div>
    <div style="max-width:1100px;margin:60px auto 0;display:grid;grid-template-columns:repeat(3,1fr);gap:30px">
        <div class="service-card reveal"><div class="service-icon"><i class="fas fa-trophy"></i></div><h3>10+ Years Combined Experience</h3><p>Senior engineers with deep domain expertise.</p></div>
        <div class="service-card reveal"><div class="service-icon"><i class="fas fa-flask"></i></div><h3>In-House Prototyping Lab</h3><p>Fast iteration and real world testing.</p></div>
        <div class="service-card reveal"><div class="service-icon"><i class="fas fa-file-alt"></i></div><h3>PEC Stamped Drawings</h3><p>Regulatory compliance ready.</p></div>
    </div>
</section>

<section class="pricing-section">
    <div style="text-align:center;max-width:700px;margin:0 auto">
        <div class="section-tag reveal">Pricing Plans</div>
        <h2 class="section-title reveal">Flexible <span>Engagement</span> Models</h2>
        <p style="color:var(--muted); margin-bottom:30px">Choose the plan that fits your project. All plans include free initial consultation.</p>
    </div>
    <div class="pricing-grid">
        <div class="pricing-card reveal">
            <div class="price-tier">STARTUP</div>
            <div class="price-amount">$1,500<span>/project</span></div>
            <div class="price-desc">Ideal for small projects and prototypes.</div>
            <ul class="price-features">
                <li>PCB design (2 layer, up to 100cm²)</li>
                <li>Firmware development (basic)</li>
                <li>2 design revisions</li>
                <li>Email support (48h response)</li>
                <li>Delivery: 10 14 days</li>
            </ul>
            <a href="contact" class="btn-p" style="display:inline-block; margin-top:20px">Get Quote</a>
        </div>
        <div class="pricing-card featured reveal">
            <div class="pricing-badge">MOST POPULAR</div>
            <div class="price-tier">PROFESSIONAL</div>
            <div class="price-amount">$4,900<span>/project</span></div>
            <div class="price-desc">Complete engineering solution for industrial applications.</div>
            <ul class="price-features">
                <li>Power system study (load flow, short circuit)</li>
                <li>PLC/HMI programming (up to 500 I/O)</li>
                <li>PCB design (4 layer, any size)</li>
                <li>On-site commissioning (up to 3 days)</li>
                <li>30 days post support</li>
                <li>Delivery: 4-6 weeks</li>
            </ul>
            <a href="contact" class="btn-p" style="display:inline-block; margin-top:20px; background:var(--electric); color:var(--darker)">Contact Sales</a>
        </div>
        <div class="pricing-card reveal">
            <div class="price-tier">ENTERPRISE</div>
            <div class="price-amount">Custom<span>/project</span></div>
            <div class="price-desc">Tailored for large scale, multi disciplinary projects.</div>
            <ul class="price-features">
                <li>Full turnkey automation</li>
                <li>SCADA & MES integration</li>
                <li>Renewable energy systems (MW scale)</li>
                <li>Annual maintenance contract (AMC)</li>
                <li>Dedicated project manager</li>
                <li>Unlimited revisions & 24/7 support</li>
            </ul>
            <a href="contact" class="btn-p" style="display:inline-block; margin-top:20px">Contact Us</a>
        </div>
    </div>
</section>

<section class="section" style="background:var(--panel)">
    <div style="text-align:center;max-width:600px;margin:0 auto">
        <div class="section-tag reveal">Common Questions</div>
        <h2 class="section-title reveal">Service <span>FAQs</span></h2>
    </div>
    <div class="faq-container" style="max-width:800px;margin:40px auto 0">
        <div class="faq-item reveal" onclick="toggleFAQ(this)"><div class="faq-q">Do you provide free estimates?<span class="faq-chevron">▾</span></div><div class="faq-a"><p>Yes, we offer free initial consultation and ballpark estimates for all projects.</p></div></div>
        <div class="faq-item reveal" onclick="toggleFAQ(this)"><div class="faq-q">What is your typical turnaround time?<span class="faq-chevron">▾</span></div><div class="faq-a"><p>Small projects (PCB design): 5-10 days. Large industrial projects: 4-12 weeks depending on scope.</p></div></div>
        <div class="faq-item reveal" onclick="toggleFAQ(this)"><div class="faq-q">Do you offer maintenance contracts?<span class="faq-chevron">▾</span></div><div class="faq-a"><p>Absolutely. We provide annual maintenance contracts (AMC) for all systems we install.</p></div></div>
    </div>
</section>

<script src="scripts/script.js"></script>
<script src="scripts/index.js"></script>
<?php include 'components/footerpre.php'; ?>
<?php include 'components/footer.php'; ?>