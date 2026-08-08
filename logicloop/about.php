<?php
require 'config/database.php';
session_start();
$header_nav['about'] = 'active';
$pagetitle = 'About - Logic Loop';
$page_description = 'Learn about Logic Loop – a team of licensed electrical engineers delivering power systems, automation, and embedded solutions since 2018.';
$page_keywords = 'electrical engineering company, Logic Loop, power systems engineers, PCB design Pakistan, industrial automation';
$page_canonical = 'https://logicloop.wuaze.com/about';
include 'components/header.php';
?>
<link rel="stylesheet" href="style/about.css">

<div class="page-hero">
    <div class="hero-grid"></div>
    <div style="position:relative;z-index:2">
        <h1 class="page-title">About <span>Logic Loop</span></h1>
        <p class="page-desc">We are a team of passionate electrical engineers dedicated to transforming complex technical challenges into elegant, reliable solutions.</p>
    </div>
</div>

<div class="about-story">
    <div>
        <div class="section-tag">Our Story</div>
        <h2 class="section-title" style="font-size:1.8rem;margin-bottom:24px">Engineering <span>Excellence</span><br>Since 2018</h2>
        <p>Logic Loop was founded by <strong>CodVion</strong>, a visionary electrical engineer with 15 years of experience in power systems and industrial automation. What began as a solo consultancy has grown into a 15 person powerhouse serving clients across Pakistan and the Middle East.</p>
        <p>Our name reflects our philosophy: every great engineering solution is a <strong>feedback loop</strong> a continuous cycle of analysis, design, testing, and refinement until the output is perfect.</p>
    </div>
    <div>
        <div class="section-tag">Our Values</div>
        <h2 class="section-title" style="font-size:1.8rem;margin-bottom:24px">What <span>Drives</span> Us</h2>
        <div class="values-list">
            <div class="value-card reveal"><div class="value-icon"><i class="fas fa-bullseye"></i></div><h4>Precision</h4></div>
            <div class="value-card reveal"><div class="value-icon"><i class="fas fa-lightbulb"></i></div><h4>Innovation</h4></div>
            <div class="value-card reveal"><div class="value-icon"><i class="fas fa-handshake"></i></div><h4>Integrity</h4></div>
            <div class="value-card reveal"><div class="value-icon"><i class="fas fa-bolt"></i></div><h4>Speed</h4></div>
        </div>
    </div>
</div>

<section class="section" style="background:var(--panel); padding:80px 5%">
    <div class="why-grid" style="max-width:1000px; margin:0 auto">
        <div>
            <div class="section-tag reveal">Our Mission</div>
            <h2 class="section-title reveal">Empower <span>Innovation</span> Through Engineering</h2>
            <p style="color:var(--muted); line-height:1.9">To deliver world class electrical engineering solutions that are precise, sustainable, and future ready. We bridge the gap between complex theory and real world application.</p>
        </div>
        <div>
            <div class="section-tag reveal">Our Vision</div>
            <h2 class="section-title reveal">A <span>Smarter</span>, Electrified World</h2>
            <p style="color:var(--muted); line-height:1.9">To become the most trusted electrical engineering partner in South Asia and beyond, known for technical excellence, integrity, and transformative impact.</p>
        </div>
    </div>
</section>

<section class="section" style="background:var(--darker)">
    <div style="text-align:center; max-width:700px; margin:0 auto">
        <div class="section-tag reveal">Our Journey</div>
        <h2 class="section-title reveal">Milestones That <span>Define Us</span></h2>
    </div>
    <div style="max-width:1000px; margin:60px auto 0; display:grid; grid-template-columns:repeat(auto-fit, minmax(220px,1fr)); gap:30px">
        <div class="value-card reveal"><div class="value-icon"><i class="fas fa-rocket"></i></div><h4>2018</h4><p style="font-size:0.8rem">Founded as a solo consultancy</p></div>
        <div class="value-card reveal"><div class="value-icon"><i class="fas fa-industry"></i></div><h4>2020</h4><p style="font-size:0.8rem">First industrial automation project</p></div>
        <div class="value-card reveal"><div class="value-icon"><i class="fas fa-globe-asia"></i></div><h4>2022</h4><p style="font-size:0.8rem">Expanded to UAE & Qatar</p></div>
        <div class="value-card reveal"><div class="value-icon"><i class="fas fa-microscope"></i></div><h4>2024</h4><p style="font-size:0.8rem">Opened advanced R&D lab</p></div>
    </div>
</section>

<section class="section" style="background:var(--panel); text-align:center">
    <div class="section-tag reveal">Accreditations</div>
    <h2 class="section-title reveal">Trusted By <span>Industry Leaders</span></h2>
    <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:40px; margin-top:50px">
        <div><img src="img/about1.svg" alt="PEC Certified" style="opacity:0.8"></div>
        <div><img src="img/about2.svg" alt="IEEE Member"></div>
        <div><img src="img/about3.svg" alt="IET Partner"></div>
        <div><img src="img/about4.svg" alt="ISO 9001"></div>
    </div>
</section>

<section class="team-section">
    <div style="text-align:center;max-width:600px;margin:0 auto 10px"><div class="section-tag reveal">The Team</div><h2 class="section-title reveal">Meet Our <span>Engineers</span></h2></div>
    <div class="team-grid">
        <div class="team-card reveal">
            <div class="team-avatar"><i class="fas fa-user-tie"></i></div>
            <h4>CodVion</h4>
            <div class="role">Founder / CEO</div>
        </div>
    </div>
</section>

<div class="cta-banner">
    <h2>Join Our <span style="color:var(--electric)">Team</span></h2>
    <p>We're always looking for passionate engineers. Check our careers page.</p>
    <a href="contact" class="btn-p">Contact Us</a>
</div>

<script src="scripts/script.js"></script>
<script src="scripts/index.js"></script>
<?php include 'components/footerpre.php'; ?>
<?php include 'components/footer.php'; ?>