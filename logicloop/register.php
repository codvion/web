<?php
$seo_description = 'Create your account on Logic Loop. Secure registration with user authentication and account management features.';
$seo_keywords = 'register account, signup, user registration, secure signup, create account, Logic Loop';
$seo_canonical = 'https://logicloop.wuaze.com/register';

require_once 'config/database.php';
require 'mail.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_user'])) {
    header('Location: index');
    exit();
}
$pagetitle = 'Register - Logic Loop';
$header_nav['register'] = 'active';

include 'components/header.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $full_name = htmlspecialchars(trim($_POST['fullname']));
    $user_name = htmlspecialchars(trim($_POST['username']));
    $email     = htmlspecialchars(trim($_POST['email']));
    $phone     = htmlspecialchars(trim($_POST['phone']));
    $password  = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $stmt = $conn->prepare('SELECT user_name, email, phone FROM login_system WHERE user_name=? OR email=? OR phone=?');
    $stmt->execute([$user_name, $email, $phone]);
    if($stmt->rowCount() > 0){
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user['user_name'] == $user_name){
            $error['username'] = 'Username Already Registered';
        }
        if($user['email'] == $email){
            $error['email'] = 'Email Already Registered';
        }
        if($user['phone'] == $phone){
            $error['phone'] = 'Phone Already Registered';
        }
    }

    if(empty($error)){
        $stmt = $conn->prepare('INSERT INTO login_system (full_name, user_name, email, phone, password) VALUES (?, ?, ?, ?, ?)');
        if($stmt->execute([$full_name, $user_name, $email, $phone, $password])){
            $success['registration'] = 'Registered Successfully! Redirecting...';
            $redirectre = true;
            $link = "https://logicloop.wuaze.com/login";
            $message = "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Welcome to CodVion</title>
</head>
<body style='margin:0; padding:0; background-color:#050a0f; font-family: Arial, sans-serif;'>

  <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#050a0f; padding: 40px 20px;'>
    <tr>
      <td align='center'>
        <table width='600' cellpadding='0' cellspacing='0' style='max-width:600px; width:100%;'>
          <tr>
            <td style='background-color:#0a1520; border-top: 3px solid #00f5ff; border-radius: 12px 12px 0 0; padding: 36px 40px 28px; text-align:center;'>
              <h1 style='margin:0; font-size:28px; color:#00f5ff; letter-spacing:2px;'>CodVion</h1>
              <p style='margin:8px 0 0; font-size:13px; color:#3a86b9; letter-spacing:1px; text-transform:uppercase;'>Your Code. Your Vision.</p>
            </td>
          </tr>
          <tr>
            <td style='background-color:#0d2035; padding: 36px 40px;'>

              <h2 style='margin:0 0 8px; font-size:22px; color:#c8e8ff;'>Welcome aboard, $full_name!</h2>
              <p style='margin:0 0 24px; font-size:15px; color:#3a86b9; line-height:1.6;'>Your account has been successfully created. Here are your account details:</p>

              <table width='100%' cellpadding='0' cellspacing='0' style='border-radius:8px; overflow:hidden; margin-bottom:28px;'>
                <tr>
                  <td style='background-color:#0a1520; padding:14px 18px; font-size:13px; color:#3a86b9; width:35%; border-bottom:1px solid #0d2035;'>Username</td>
                  <td style='background-color:#0a1520; padding:14px 18px; font-size:14px; color:#c8e8ff; border-bottom:1px solid #0d2035;'>$user_name</td>
                </tr>
                <tr>
                  <td style='background-color:#091420; padding:14px 18px; font-size:13px; color:#3a86b9; width:35%; border-bottom:1px solid #0d2035;'>Email</td>
                  <td style='background-color:#091420; padding:14px 18px; font-size:14px; color:#c8e8ff; border-bottom:1px solid #0d2035;'>$email</td>
                </tr>
                <tr>
                  <td style='background-color:#0a1520; padding:14px 18px; font-size:13px; color:#3a86b9; width:35%;'>Phone</td>
                  <td style='background-color:#0a1520; padding:14px 18px; font-size:14px; color:#c8e8ff;'>$phone</td>
                </tr>
              </table>
              <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                  <td align='center' style='padding-bottom:28px;'>
                    <a href='$link' style='display:inline-block; background-color:#00f5ff; color:#050a0f; font-size:15px; font-weight:bold; text-decoration:none; padding:14px 36px; border-radius:6px; letter-spacing:0.5px;'>
                      Login to Your Account
                    </a>
                  </td>
                </tr>
              </table>

              <p style='margin:0; font-size:13px; color:#3a86b9; line-height:1.7; border-top:1px solid #0d2035; padding-top:20px;'>
                Keep your credentials secure and do not share them with anyone. If you didn't create this account, please ignore this email.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style='background-color:#0a1520; border-radius:0 0 12px 12px; padding:24px 40px; text-align:center; border-top:1px solid #0d2035;'>
              <p style='margin:0 0 4px; font-size:16px; font-weight:bold; color:#00f5ff;'>CodVion</p>
              <p style='margin:0; font-size:12px; color:#3a86b9;'>Thanks for joining us. We're excited to have you!</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
";
            sendMail($email, "Welcome to Our Website", $message);
        } else {
            $error['registration'] = 'Registration Failed. Try Again';
        }
    }
}
?>

<link rel="stylesheet" href="style/register.css">

<div class="register-container">

    <div class="register-container-right">
        <h2>Register Your Account</h2>
        <form action="register" method="POST" id="registerForm" novalidate>
            <div class="form-group">
                <div class="field-setup">
                <div class="field-system">   
                <input type="text" name="fullname" id="fullname" required>
                    <label for="fullname">Full Name</label>
                    </div>
                    <div class="fullname-message" id="fullname-message"></div>
                </div>
                <div class="field-setup">
                <div class="field-system">   
                <input type="text" name="username" id="username" class="<?php if(isset($error['username'])){ echo htmlspecialchars('input-invalid'); } else {
                    echo htmlspecialchars('');
                } ?>" required>
                    <label for="username">Username</label>
                    </div>
                    <div class="username-message <?php if(isset($error['username'])){ echo htmlspecialchars('error show'); } else {
                        echo htmlspecialchars('');
                    } ?>" id="username-message">
                    <?php
                    if(isset($error['username'])){
                        echo htmlspecialchars($error['username']);
                    }
                    ?>
                    </div>
                </div>
                <div class="field-setup">
                <div class="field-system">  
                <input type="email" name="email" id="email" class="<?php if(isset($error['email'])){ echo htmlspecialchars('input-valid'); } else { echo htmlspecialchars(''); } ?>" required>
                    <label for="email">Email</label>
                    </div>
                    <div class="email-message <?php if(isset($error['email'])){ echo htmlspecialchars('error show'); } else { echo htmlspecialchars(''); } ?>" id="email-message">
                    <?php
                    if(isset($error['email'])){
                        echo htmlspecialchars($error['email']);
                    }
                    ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="field-setup">
                <div class="field-system"> 
                <input type="tel" name="phone" id="phone" class="<?php if(isset($error['phone'])){ echo htmlspecialchars('input-invalid'); } else {
                    echo htmlspecialchars('');
                } ?>" pattern="[0-9]{7,15}" maxlength="15" required>
                    <label for="phone">Phone</label>
                    </div>
                    <div class="phone-message <?php if(isset($error['phone'])){ echo htmlspecialchars('error show'); } else {
                        echo htmlspecialchars('');
                    } ?>" id="phone-message">
                    <?php
                    if(isset($error['phone'])){
                        echo htmlspecialchars($error['phone']);
                    }
                    ?>
                    </div>
                </div>

                <div class="field-setup">
                <div class="field-system">  
                <input type="password" name="password" id="password" required>
                    <label for="password">Password</label>
                    <i class="fa-solid fa-eye toggle-password"onclick="togglePassword('password', this)"></i>
                </div>
                    <div class="password-message" id="password-message"></div>
                </div>
                
                <div class="field-setup">
                <div class="field-system">  
                <input type="password" name="confirm_password" id="confirm_password" required>
                    <label for="confirm_password">Confirm Password</label>
                    <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('confirm_password', this)"></i>
                </div>
                    <div class="confirm-password-message" id="confirm_password_message"></div>
                </div>
            </div>
            <button type="submit" name="register" class="btn-register">Register</button>
        </form>
        <?php if(isset($success['registration'])){ ?>
    <div class="register-message success">
        <?php echo htmlspecialchars($success['registration']); ?>
    </div>
<?php } elseif(isset($error['registration'])){ ?>
    <div class="register-message error">
        <?php echo htmlspecialchars($error['registration']); ?>
    </div>
<?php } ?>

        <div class="login-link">
            Already have an account? <a href="login">Login here</a>
        </div>
    </div>
        <div class="register-container-left">
    </div>
</div>
<?php if(isset($redirectre) && $redirectre === true){ ?>
<script>
    setTimeout(function(){   
        window.location.href = 'login'; 
    }, 1500); 
</script>
<?php } ?>
<script src="scripts/register.js"></script>
<script src="scripts/script.js"></script>
<?php 
include 'components/footer.php';
?>