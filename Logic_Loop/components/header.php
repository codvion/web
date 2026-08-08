<?php
$seo_description = $page_description ?? 'LogicLoop delivers cutting-edge electrical engineering services: power systems, circuit design, automation, embedded systems, and renewable energy solutions.';
$seo_keywords = $page_keywords ?? 'electrical engineering, power systems, circuit design, automation, embedded systems, renewable energy, PCB design, PLC programming';
$seo_author = 'LogicLoop Engineering';
$seo_canonical = $page_canonical ?? 'https://logicloop.wuaze.com' . $_SERVER['REQUEST_URI'];
$og_image = $og_image ?? 'https://logicloop.wuaze.com/img/logicloop.png'; 

if (isset($_SESSION['user_user'])) {
    $stmt = $conn->prepare("SELECT * FROM login_system WHERE user_name=?");
    $stmt->execute([$_SESSION['user_user']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>
        <?php if(isset($pagetitle)){ echo htmlspecialchars($pagetitle); } ?>
    </title>
    <link rel="icon" href="img/logicloop.png" type="image/x-icon">
    <meta name="description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($seo_keywords); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($seo_author); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($seo_canonical); ?>">
    
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($seo_canonical); ?>">
    <meta property="og:title" content="<?php if(isset($pagetitle)){ echo htmlspecialchars($pagetitle); } ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php if(isset($pagetitle)){ echo htmlspecialchars($pagetitle); } ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($og_image); ?>">

    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="7 days">

    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Exo+2:wght@300;400;500;600&display=swap"
        rel="stylesheet">

</head>

<body>
    <div class="cursor" id="cursor"></div>
    <div class="cursor-ring" id="cursorRing"></div>
    <nav>
        <a class="logo-wrap" onclick="showPage('home')" href="index">
            <svg width="46" height="46" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="22" stroke="url(#l1)" stroke-width="1.2" opacity=".7" />
                <path d="M8 24L14 24L14 16L20 16L20 20L28 20L28 16L34 16L34 24L40 24" stroke="url(#l2)"
                    stroke-width="1.8" stroke-linecap="round" fill="none" />
                <path d="M14 24L14 32L20 32L20 28L28 28L28 32L34 32L34 24" stroke="url(#l3)" stroke-width="1.8"
                    stroke-linecap="round" fill="none" />
                <circle cx="24" cy="20" r="3.5" fill="#00f5ff" opacity=".9">
                    <animate attributeName="opacity" values=".9;.4;.9" dur="2s" repeatCount="indefinite" />
                </circle>
                <circle cx="24" cy="28" r="2" fill="#f5e642" opacity=".8" />
                <circle cx="14" cy="24" r="1.8" fill="#00f5ff" />
                <circle cx="34" cy="24" r="1.8" fill="#00f5ff" />
                <circle cx="20" cy="16" r="1.5" fill="#f5e642" opacity=".7" />
                <circle cx="28" cy="16" r="1.5" fill="#f5e642" opacity=".7" />
                <defs>
                    <linearGradient id="l1" x1="2" y1="2" x2="46" y2="46">
                        <stop offset="0%" stop-color="#00f5ff" />
                        <stop offset="100%" stop-color="#f5e642" />
                    </linearGradient>
                    <linearGradient id="l2" x1="8" y1="16" x2="40" y2="16">
                        <stop offset="0%" stop-color="#00f5ff" />
                        <stop offset="100%" stop-color="#f5e642" />
                    </linearGradient>
                    <linearGradient id="l3" x1="14" y1="32" x2="34" y2="32">
                        <stop offset="0%" stop-color="#f5e642" />
                        <stop offset="100%" stop-color="#00f5ff" />
                    </linearGradient>
                </defs>
            </svg>
            <div>
                <div class="logo-text">LOGIC <span class="logo-text-span">LOOP</span></div><span
                    class="logo-sub">Electrical Engineering</span>
            </div>
        </a>
        <div class="nav-link-system">
            <ul class="header_ul <?php if(isset($_SESSION['user_user'])){ echo htmlspecialchars('profile'); }?>">
                <?php if(isset($_SESSION['user_user'])){ ?>
                <li>
                    <a href="profile" class="profileheader <?php if(isset($header_nav['profile'])){ echo $header_nav['profile']; }?>">
                        <img style="height:100%" class=""
                        src="<?php echo !empty($user['profile_pic']) ? 'uploads/'.$user['profile_pic'] : 'images/default.png'; ?>"
                        alt="Profile">
                    </a>
                    <div class="profile-message">Profile</div>
                </li>
                <?php } else { ?>
                <li><a href="login"
                        class="<?php if(isset($header_nav['login'])){ echo $header_nav['login']; }?>">Login</a></li>
                <li><a href="register"
                        class="<?php if(isset($header_nav['register'])){ echo $header_nav['register']; }?>">Register</a>
                </li>
                <?php } ?>
            </ul>
            <div class="hamburger" id="hamburger" onclick="toggleMenu()"><span></span><span></span><span></span></div>
        </div>
    </nav>
    <div class="nav-links" id="navLinks">
            <ul>
                <li><a href="index" id="nav-home"
                        class="<?php if(isset($header_nav['home'])){ echo $header_nav['home']; }?>">Home</a></li>
                <li><a href="about" id="nav-about"
                        class="<?php if(isset($header_nav['about'])){ echo $header_nav['about']; }?>">About</a></li>
                <li><a href="services" id="nav-services"
                        class="<?php if(isset($header_nav['service'])){ echo $header_nav['service']; }?>">Services</a>
                </li>
                <li><a href="portfolio" id="nav-portfolio"
                        class="<?php if(isset($header_nav['portfolio'])){ echo $header_nav['portfolio']; }?>">Portfolio</a>
                </li>
                <li><a href="contact" id="nav-about"
                        class="<?php if(isset($header_nav['contact'])){ echo $header_nav['contact']; }?>">Contact</a>
                </li>
            </ul>
            <ul class="header_ul">
                <?php if(isset($_SESSION['user_user'])){ ?>
                <li>
                    <a href="profile" class="profileheader <?php if(isset($header_nav['profile'])){ echo $header_nav['profile']; }?>">
                        <img style="height:100%" class=""
                        src="<?php echo !empty($user['profile_pic']) ? 'uploads/'.$user['profile_pic'] : 'images/default.png'; ?>"
                        alt="Profile">
                    </a>
                    <div class="profile-message">Profile</div>
                </li>
                <li><a href="logout" class="profileheader"><i class="fa-solid fa-right-from-bracket"></i></a>
                    <div class="profile-message">Logout</div>
            </li>
                <?php } else { ?>
                <li><a href="login"
                        class="<?php if(isset($header_nav['login'])){ echo $header_nav['login']; }?>">Login</a></li>
                <li><a href="register"
                        class="<?php if(isset($header_nav['register'])){ echo $header_nav['register']; }?>">Register</a>
                </li>
                <?php } ?>
            </ul>
    </div>