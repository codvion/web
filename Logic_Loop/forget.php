<?php
require 'config/database.php';
require 'mail.php';
session_start();
$pagetitle="Forget Password";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("SELECT * FROM login_system WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $update = $conn->prepare("UPDATE login_system SET reset_token=?, token_expire=? WHERE id=?");
        $update->execute([$hashedToken, $expiry, $user['id']]);
        
        $link = "https://logicloop.wuaze.com/reset_password?token=" . urlencode($token) . "&uid=" . $user['id'];
        sendMail($email, "Password Reset, It expires in 5 minutes.", "Click this link to reset your password: $link");
        $success = "Reset link sent to your email!";
        } else {
            $error = "Email not found!";
        }
}
include 'components/header_page.php';
?>
<link rel="stylesheet" href="style/login.css">
<style>
    .Login-container-right {
        width: 100%;
    margin: 0 auto;
    padding: 80px 10px;
}

.email-message {
    font-size: 0.72rem;
    font-weight: 500;
    min-height: 16px;
    padding-left: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
    opacity: 0;
    transform: translateY(-8px);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.email-message.show{
    opacity: 1;
    transform: translateY(0);
}

.email-message.success {
    color: #22c55e !important;
}

.email-message.error {
    color: #f87171 !important;
}

@media (max-width: 600px) {
    .Login-container-right {
        padding: 40px 10px;
    }
}

</style>

<?php if(isset($success)){ ?>
    <div class="login-message success">
        <?php echo htmlspecialchars($success); 
        unset($success); ?>
    </div>
<?php } elseif(isset($error)){ ?>
    <div class="login-message error">
        <?php echo htmlspecialchars($error); 
        unset($error); ?>
    </div>
<?php } elseif(isset($_SESSION['invalid_token'])){ ?>
    <div class="login-message error">
        <?php echo htmlspecialchars($_SESSION['invalid_token']);
        unset($_SESSION['invalid_token']); ?>
    </div>
<?php } ?>

<div class="Login-container-right">
    <div class="login-group">
        <h2>Forgot Password</h2>
        <form method="POST" id="ForgetForm" novalidate>
            <div class="field-system">
                <div class="field-setup">
                    <input type="email" name="email" id="email" class="<?php if(isset($error)){ echo htmlspecialchars('input-invalid'); }?>" required>
                    <label for="email">Enter your Email</label>
                </div>
                <div class="email-message 
                <?php if(isset($error)){ echo htmlspecialchars('show error'); }?>" id="email-message"><?php if(isset($error)){ echo htmlspecialchars("$error"); } ?></div>
            </div>
            <button type="submit" class="btn-login">Send OTP</button>
        </form>
        <div class="login-link">
            Remembered your password? <a href="login">Login here</a>
        </div>
    </div>
</div>

<script>
    const email = document.getElementById("email");  
    const email_message = document.getElementById('email-message');
document.getElementById("ForgetForm").addEventListener("submit", function(e){
    if(email.value.trim() === ""){
        e.preventDefault();
       email_message.textContent='Email is Required.';
       email_message.classList.add('show', 'error');
        email.classList.add('input-invalid');
    } else {
       email_message.textContent='';
       email_message.classList.remove('show', 'error');
        email.classList.remove('input-invalid');
    }

});
function validateEmail(email) {
    const checkemail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return checkemail.test(email);
}
email.addEventListener('input', function () {

    if (email.value === "") {
        email_message.textContent = "Email is Required.";
        email_message.classList.add('show', 'error');
        email.classList.add('input-invalid');
    } else if (!validateEmail(email.value)) {
        email_message.classList.add('show', 'error');
        email_message.innerText = "Enter A Correct Email";
        email.classList.add('input-invalid');
        Valid = false;
    } else {
        email_message.classList.remove('show', 'error');
        email_message.innerText = "";
        email.classList.remove('input-invalid');
    }

});


const inputs = document.querySelectorAll(".field-system input");
inputs.forEach(input => {
    input.addEventListener("blur", function () {
        if (this.value.trim() !== "") {
            this.classList.add("filled");
        } else {
            this.classList.remove("filled");
        }
    });
});


</script>
<?php if(isset($redirect) && $redirect === true){ ?>
<script>
    setTimeout(function(){
        window.location.href = 'verify_otp';
    }, 1500); 
</script>
<?php } ?>
<?php if(isset($mail_redirect) && $mail_redirect === true){ ?>
    <script>
        setTimeout(() => {
            window.location.href = 'login';
        }, 2500);
    </script>
<?php } ?>
<script src="scripts/script.js"></script>
<?php 
include 'components/footer.php';
?>

