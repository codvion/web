<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : '';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>LearnPro LMS | Professional Learning Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/learnpro-header.php'; ?>

    <main>
        <section class="hero">
            <div class="hero-inner">
                <div class="hero-copy" data-animate>
                    <span class="eyebrow">Premium LMS for modern learning teams</span>
                    <h1>LearnPro LMS</h1>
                    <p>Launch courses, manage instructors, enroll students, track video progress, and keep every learning action organized inside one clean, professional platform.</p>
                    <div class="hero-actions">
                        <?php if ($is_logged_in): ?>
                            <a class="btn primary" href="dashboard.php"><i data-lucide="layout-dashboard"></i> Open Dashboard</a>
                            <a class="btn" href="courses.php"><i data-lucide="book-open"></i> Browse Courses</a>
                        <?php else: ?>
                            <a class="btn primary" href="register.php"><i data-lucide="user-plus"></i> Create Account</a>
                            <a class="btn" href="login.php"><i data-lucide="log-in"></i> Login</a>
                        <?php endif; ?>
                    </div>
                    <div class="stats-row">
                        <div class="stat-box">
                            <strong>3</strong>
                            <span>Role dashboards</span>
                        </div>
                        <div class="stat-box">
                            <strong>5s</strong>
                            <span>Video unlock timer</span>
                        </div>
                        <div class="stat-box">
                            <strong>100%</strong>
                            <span>Prepared SQL flow</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="platform">
            <div class="container">
                <div class="section-head" data-animate>
                    <div>
                        <span class="eyebrow">Platform Overview</span>
                        <h2>A complete learning system, not just a template.</h2>
                        <p class="section-lead">LearnPro LMS includes public pages, authentication, course management, enrollment, notifications, video lesson tracking, and role-based access for real platform operations.</p>
                    </div>
                </div>
                <div class="grid three">
                    <article class="card" data-animate>
                        <span class="feature-icon"><i data-lucide="layers-3"></i></span>
                        <h3>Structured Course Library</h3>
                        <p>Courses include categories, levels, pricing, rich descriptions, cover media, publishing status, and ordered lessons.</p>
                    </article>
                    <article class="card" data-animate>
                        <span class="feature-icon"><i data-lucide="shield-check"></i></span>
                        <h3>Controlled Access</h3>
                        <p>Admin, instructor, and student roles each receive the right dashboard, actions, and protected page permissions.</p>
                    </article>
                    <article class="card" data-animate>
                        <span class="feature-icon"><i data-lucide="bell"></i></span>
                        <h3>Notification Center</h3>
                        <p>Important account, course, enrollment, and lesson progress updates are stored and displayed inside the platform.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section alt" id="roles">
            <div class="container split">
                <div data-animate>
                    <span class="eyebrow">Role Based LMS</span>
                    <h2>Every user sees the tools they need.</h2>
                    <p class="section-lead">The platform is designed for clear separation of responsibility, giving leadership full control while instructors and students stay focused.</p>
                    <div class="process-list">
                        <div class="process-item">
                            <span class="process-number">01</span>
                            <div>
                                <h3>Admin</h3>
                                <p>Manage all users, courses, lessons, enrollments, notifications, and platform-level reports.</p>
                            </div>
                        </div>
                        <div class="process-item">
                            <span class="process-number">02</span>
                            <div>
                                <h3>Instructor</h3>
                                <p>Create, update, publish, and delete their own courses and lessons through a dedicated management area.</p>
                            </div>
                        </div>
                        <div class="process-item">
                            <span class="process-number">03</span>
                            <div>
                                <h3>Student</h3>
                                <p>Browse published courses, enroll, watch lessons, unlock progress, and receive learning notifications.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <figure class="image-frame" data-animate>
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1400&q=80" alt="Team reviewing learning dashboards">
                </figure>
            </div>
        </section>

        <section class="section" id="courses">
            <div class="container">
                <div class="section-head" data-animate>
                    <div>
                        <span class="eyebrow">Course Experience</span>
                        <h2>Professional course pages with lesson progression.</h2>
                        <p class="section-lead">Students can explore courses, enroll, and move through lessons in order. Video lessons use a 5-second required watch timer before the next step opens.</p>
                    </div>
                    <a class="btn" href="courses.php"><i data-lucide="book-open-check"></i> View Courses</a>
                </div>
                <div class="grid three">
                    <article class="card" data-animate>
                        <span class="feature-icon"><i data-lucide="video"></i></span>
                        <h3>Video Lessons</h3>
                        <p>Each lesson can include a direct video URL, duration, supporting content, and a published or draft status.</p>
                    </article>
                    <article class="card" data-animate>
                        <span class="feature-icon"><i data-lucide="timer"></i></span>
                        <h3>5 Second Gate</h3>
                        <p>The next lesson remains locked until the student has actively watched the required time in the player.</p>
                    </article>
                    <article class="card" data-animate>
                        <span class="feature-icon"><i data-lucide="chart-no-axes-combined"></i></span>
                        <h3>Progress Tracking</h3>
                        <p>Completed lessons update the enrollment progress percentage and can mark courses as completed.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section alt" id="workflow">
            <div class="container split">
                <figure class="image-frame" data-animate>
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1400&q=80" alt="Professional planning workspace">
                </figure>
                <div data-animate>
                    <span class="eyebrow">Operational Flow</span>
                    <h2>From course creation to student completion.</h2>
                    <div class="process-list">
                        <div class="process-item">
                            <span class="process-number">01</span>
                            <div>
                                <h3>Instructor builds course</h3>
                                <p>Course information, cover image, status, and lessons are managed through protected screens.</p>
                            </div>
                        </div>
                        <div class="process-item">
                            <span class="process-number">02</span>
                            <div>
                                <h3>Student enrolls</h3>
                                <p>Published courses are available to students, and enrollment creates a tracked learning record.</p>
                            </div>
                        </div>
                        <div class="process-item">
                            <span class="process-number">03</span>
                            <div>
                                <h3>Progress is saved</h3>
                                <p>Lesson completion, course progress, and notifications are written to MySQL with prepared statements.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="security">
            <div class="container">
                <div class="section-head" data-animate>
                    <div>
                        <span class="eyebrow">Security and Data</span>
                        <h2>Built with clean procedural PHP and safe database access.</h2>
                        <p class="section-lead">The code uses your requested PDO connection style, prepared statements, role checks, password hashing, and clear file organization without custom PHP functions.</p>
                    </div>
                </div>
                <div class="grid four">
                    <article class="card" data-animate>
                        <h3>PDO Prepared Queries</h3>
                        <p>User input is sent through prepared statements for login, registration, CRUD, progress, and notifications.</p>
                    </article>
                    <article class="card" data-animate>
                        <h3>Password Hashing</h3>
                        <p>New accounts store hashed passwords and verify credentials through PHP's secure password API.</p>
                    </article>
                    <article class="card" data-animate>
                        <h3>Role Protection</h3>
                        <p>Pages redirect users when they do not have the required role or ownership access.</p>
                    </article>
                    <article class="card" data-animate>
                        <h3>Clean Structure</h3>
                        <p>Pages are simple, readable, and direct so you can expand them easily as the LMS grows.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section alt">
            <div class="container split">
                <div data-animate>
                    <span class="eyebrow">Start Now</span>
                    <h2>Access the LMS dashboard and begin managing learning.</h2>
                    <p class="section-lead">Create a student or instructor account, or import the database seed and use the admin account to manage the complete platform.</p>
                </div>
                <div class="panel" data-animate>
                    <h3>Ready to enter LearnPro?</h3>
                    <p>Use the platform as a student, instructor, or administrator and experience the full workflow from one system.</p>
                    <div class="actions">
                        <?php if ($is_logged_in): ?>
                            <a class="btn primary" href="dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                        <?php else: ?>
                            <a class="btn primary" href="register.php"><i data-lucide="user-plus"></i> Register</a>
                            <a class="btn" href="login.php"><i data-lucide="log-in"></i> Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
