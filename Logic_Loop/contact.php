<?php
require 'config/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $company = $_POST['company'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $service = $_POST['service'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO contacts (name, company, email, phone, service, message) VALUES (?, ?, ?, ?, ?, ?)");

    if ($stmt->execute([$name, $company, $email, $phone, $service, $message])) {
        $form['success'] = "Message sent successfully!";
    } else {
        $form['error'] = "Error: " . $conn->error;
    }
}

$header_nav['contact'] = 'active';
$pagetitle = 'Contact - Logic Loop';
$page_description = 'Get in touch with Logic Loop for electrical engineering services. Free consultation, quick response, and expert advice.';
$page_keywords = 'contact electrical engineer, Lahore engineering services, power systems consultation, PCB design quote';
$page_canonical = 'https://logicloop.wuaze.com/contact';
include 'components/header.php';
?>
<link rel="stylesheet" href="style/contact.css">

<div class="page-hero">
    <div class="hero-grid"></div>
    <div style="position:relative;z-index:2">
        <h1 class="page-title">Get In <span>Touch</span></h1>
        <p class="page-desc">Have a project in mind? Let's talk. We offer free initial consultations for all new clients.</p>
    </div>
</div>

<section class="contact-section">
    <div class="contact-grid">
        <div class="contact-info">
            <h3><i class="fas fa-address-card"></i> Contact Information</h3>
            <div class="contact-items">
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <h4>Visit Us</h4>
                        <p>Office #12, 3rd Floor, <br>Tech Hub Plaza, DHA Phase 5, <br>Lahore, Pakistan</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                    <div>
                        <h4>Call Us</h4>
                        <p>+92 301 6209237</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h4>Email Us</h4>
                        <p>codvion@gmail.com</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <h4>Business Hours</h4>
                        <p>Mon – Thu: 9am – 6pm <br>Fri: 9am – 1pm <br>Sat – Sun: Closed</p>
                    </div>
                </div>
            </div>
            <div style="margin-top:30px">
                <h4><i class="fas fa-share-alt"></i> Follow Us</h4>
                <div class="social-links">
                    <a href="#" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <div class="contact-form reveal">
            <h3><i class="fas fa-paper-plane"></i> Send Us a Message</h3>
            <form id="contactForm" action="contact" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" placeholder="Your name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Company (Optional)</label>
                        <input type="text" placeholder="Company name" name="company">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" placeholder="you@example.com" name="email" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" placeholder="+92 XXX XXXXXXX" name="phone" required>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Service Interest</label>
                    <select name="service">
                        <option>Power Systems Engineering</option>
                        <option>PCB / Embedded Design</option>
                        <option>Industrial Automation</option>
                        <option>Renewable Energy</option>
                        <option>Safety & Compliance</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-comment-dots"></i> Project Details</label>
                    <textarea placeholder="Tell us about your project, requirements, or questions..." name="message" ></textarea>
                </div>
                <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Send Message</button>
                <?php if(isset($form['success'])) { ?>
                <div id="formMsg" class="form-msg <?php if(isset($form['success'])) { echo htmlspecialchars('success'); }  ?>">✓ Thank you! We'll get back to you within 48 hours.</div>
                <?php } elseif(isset($form['error'])) { ?>
                <div id="formMsg" class="form-msg <?php if(isset($form['error'])) { echo htmlspecialchars('error'); }  ?>">Error Try Again Later.</div>
                <?php } ?>
            </form>
        </div>
    </div>
</section>

<section class="section" style="padding:0 5% 80px">
    <div style="text-align:center;margin-bottom:40px">
        <div class="section-tag reveal"><i class="fas fa-map-pin"></i> Visit Us</div>
        <h2 class="section-title reveal">Our <span>Office</span> Location</h2>
    </div>
    <div class="map-container">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d217519.63997839588!2d74.20409663827863!3d31.483219263197404!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39190483e56907ff%3A0x6b4c8a3b6c6b5b9f!2sLahore%2C%20Pakistan!5e0!3m2!1sen!2s!4v1699999999999!5m2!1sen!2s" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
</section>

<section class="section" style="background:var(--panel)">
    <div style="text-align:center;max-width:600px;margin:0 auto">
        <div class="section-tag reveal"><i class="fas fa-question-circle"></i> Help Center</div>
        <h2 class="section-title reveal">Frequently <span>Asked</span> Questions</h2>
    </div>
    <div class="faq-container" style="max-width:800px;margin:40px auto 0">
        <div class="faq-item reveal" onclick="toggleFAQ(this)">
            <div class="faq-q"><i class="fas fa-clock"></i> How quickly can you respond? <span class="faq-chevron">▾</span></div>
            <div class="faq-a"><p>We reply to all inquiries within 24 hours, often same day.</p></div>
        </div>
        <div class="faq-item reveal" onclick="toggleFAQ(this)">
            <div class="faq-q"><i class="fas fa-globe"></i> Do you work with international clients? <span class="faq-chevron">▾</span></div>
            <div class="faq-a"><p>Yes, we have served clients in UAE, Qatar, KSA, and USA remotely.</p></div>
        </div>
        <div class="faq-item reveal" onclick="toggleFAQ(this)">
            <div class="faq-q"><i class="fas fa-file-alt"></i> What information should I bring to the consultation? <span class="faq-chevron">▾</span></div>
            <div class="faq-a"><p>Any existing drawings, load requirements, or a rough description of your project goals.</p></div>
        </div>
        <div class="faq-item reveal" onclick="toggleFAQ(this)">
            <div class="faq-q"><i class="fas fa-lock"></i> Is my project information confidential? <span class="faq-chevron">▾</span></div>
            <div class="faq-a"><p>Absolutely. We sign NDAs and treat all client data with strict confidentiality.</p></div>
        </div>
    </div>
</section>

<section class="section" style="text-align:center">
    <div class="section-tag reveal"><i class="fas fa-handshake"></i> Trusted By</div>
    <h2 class="section-title reveal">Our <span>Partners</span> & Clients</h2>
    <div class="trust-badges">
        <div class="trust-badge"><i class="fas fa-industry"></i> Pakistan Steel</div>
        <div class="trust-badge"><i class="fas fa-solar-panel"></i> Reon Energy</div>
        <div class="trust-badge"><i class="fas fa-microchip"></i> Nestlé Pakistan</div>
        <div class="trust-badge"><i class="fas fa-charging-station"></i> Engro Corp</div>
        <div class="trust-badge"><i class="fas fa-robot"></i> Siemens Partner</div>
    </div>
</section>

<script src="scripts/script.js"></script>
<script src="scripts/index.js"></script>
<?php include 'components/footerpre.php'; ?>
<?php include 'components/footer.php'; ?>