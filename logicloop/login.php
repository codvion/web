<?php
$seo_description = 'Secure user login system with authentication, password reset, and account management features.';
$seo_keywords = 'login system, user authentication, secure login, password reset, signup, user account management';
$seo_canonical = 'https://logicloop.wuaze.com/login';
session_start();
require 'config/database.php';
if (isset($_SESSION['user_user'])) {
    header('Location: index');
    exit();
}
$pagetitle = 'Login - Logic Loop';
$header_nav['login'] = 'active';
include 'components/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uep = htmlspecialchars(trim($_POST['uep']));
    $password = trim($_POST['password']);
    $stmt = $conn->prepare('SELECT user_name, email, phone, password FROM login_system WHERE user_name=? OR email=? OR phone=?');
    $stmt->execute([$uep, $uep, $uep]);
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($password === $user['password']) {
            $_SESSION['user_user'] = $user['user_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            $success['login'] = 'Login Successful! Redirecting...';
            $redirect = true;
        } else {
            $error['password'] = 'Invalid Password';
        }
    } else {
        $error['login'] = 'Account Does not Exist, Register Now';
    }
} if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    $reset_password = "Your password has been reset successfully. You can now log in.";
}
?> 
<link rel="stylesheet" href="style/login.css"> 
<?php if (isset($success['login'])) { ?> 
    <div class="login-message success">    
             <?php echo htmlspecialchars($success['login']);
    unset($success['login']); ?>  
   </div> <?php } elseif (isset($error['password'])) { ?> 
       <div class="login-message error">    
             <?php echo htmlspecialchars($error['password']);
       unset($error['password']); ?>  
       </div> <?php } elseif (isset($error['login'])) { ?>   
         <div class="login-message error">        
             <?php echo htmlspecialchars($error['login']);
           unset($error['login']); ?>    
         </div> <?php } elseif (isset($reset_password)) { ?>   
           <div class="login-message success">      
               <?php echo htmlspecialchars($reset_password);
             unset($reset_password); ?>  
                   </div> <?php } ?>  
                    <div class="Login-container"> 
                            <div class="Login-container-left">  
                                   </div>    
                                    <div class="Login-container-right"> 
                                                <div class="login-group">    
                                                         <h2>Login Your Account</h2>     
                                                             <form method="POST" id="LoginForm" novalidate>         <div class="field-system">               <div class="field-setup">                 <input type="text" name="uep" id="email" required>                 <label for="email">Username, Email, Phone</label>             </div>             <div class="uep-message" id="uep-message"></div>         </div>         <div class="field-system">             <div class="field-setup">                 <input type="password" name="password" id="password" required>                 <label for="password">Password</label>                 <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('password', this)"></i>             </div>             <div class="p-message" id="p-message" ></div>         </div>             <button type="submit" name="login" class="btn-login">Login</button>         </form>             <div class="login-link">                 Forgot your password? <a href="forget">Reset it here</a>.             </div>             <div class="login-link">                 Don’t have an account yet? <a href="register">Create now</a>.             </div>         </div>     </div> </div>  <?php if (isset($redirect) && $redirect === true) { ?> <script>     setTimeout(function(){         window.location.href = 'index';     }, 1500);  </script> <?php } ?>  <script src="scripts/login.js"></script> <script src="scripts/script.js"></script>
 <?php include 'components/footer.php'; ?>