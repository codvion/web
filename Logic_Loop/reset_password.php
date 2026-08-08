<?php
require 'config/database.php';
session_start();
$pagetitle="Reset Password";
$token = $_GET['token'] ?? '';
$uid   = $_GET['uid'] ?? '';

if (!$token || !$uid) {
    header('Location: 404');
}

$hashedToken = hash('sha256', $token);
$stmt = $conn->prepare("
    SELECT * FROM login_system 
    WHERE id=? AND reset_token=?
");
$stmt->execute([$uid, $hashedToken]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || strtotime($user['token_expire']) < time()) {
    $_SESSION['invalid_token'] = 'Link expired or invalid. Please request a new one.';
    header('Location: forget');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);
    $update = $conn->prepare("
        UPDATE login_system 
        SET password=?, reset_token=NULL, token_expire=NULL 
        WHERE id=?
    ");
    $update->execute([$password, $user['id']]);
    session_destroy();
    header("Location: login?reset=success");
    exit;
}
include 'components/header_page.php';
?>
<link rel="stylesheet" href="style/login.css">
<style>
.Login-container-right {
    width: 100%;
    margin: 0px auto;
    padding: 80px 10px;
}
.field-setup{
    align-self: stretch;
    margin-bottom: 5px;
}
.field-system{
    display: block;
    margin-bottom: 0px;
}
.otp-message {
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

.otp-message.show {
    opacity: 1;
    transform: translateY(0);
}

.otp-message.successs {
    color: #22c55e !important;
}

.otp-message.error {
    color: #f87171 !important;
}

.password-message,
.confirm-password-message {
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
.password-message.show,
.confirm-password-message.show {
    opacity: 1;
    transform: translateY(0);
}

.password-message.success,
.confirm-password-message.success {
    color: #22c55e !important;
}

.password-message.error,
.confirm-password-message.error {
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
<?php } ?>

<div class="Login-container-right">
    <div class="login-group">
        <h2>Reset Password</h2>
        <form method="POST" id="ResetForm" novalidate>
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
            <button type="submit" class="btn-login">Reset Password</button>
        </form>
    </div>
</div>

<script>
const form = document.getElementById('ResetForm');
const password = document.getElementById('password');
const password_message = document.getElementById('password-message');
const confirm_password = document.getElementById('confirm_password');
const confirm_password_message = document.getElementById('confirm_password_message');

// Toggle Password
function togglePassword(id, icon) {
    const input = document.getElementById(id);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

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

// Password Validation
function validatePasswordStrong(password) {
    const checkpassword = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;
    return checkpassword.test(password);
}
function validatePasswordMedium(password) {
    const checkpassword = /^(?=.*[a-z])(?=.*\d).{8,}$/;
    return checkpassword.test(password);

} function validatePasswordweek(password) {
    const checkpassword = /^.{6,}$/;
    return checkpassword.test(password);
}

function checkPasswordMatch() {

    if (validatePasswordStrong(password.value)) {
        password_message.classList.add('show');
        password_message.classList.remove('error');
        password_message.innerText = "Strong";
        password_message.style = "Color: #10b981";
        password.classList.remove('input-invalid');
    } else if (validatePasswordMedium(password.value)) {
        password_message.innerText = "Medium";
        password_message.classList.add('show');
        password_message.classList.remove('error');
        password_message.style = "Color: #f59e0b";
        password.classList.remove('input-invalid');
        Valid = false;
    } else if (validatePasswordweek(password.value)) {
        password_message.innerText = "Weak";
        password_message.classList.add('show');
        password_message.classList.remove('error');
        password_message.style = "Color: #ef4444";
        password.classList.remove('input-invalid');
        Valid = false;
    } else {
        password_message.classList.add('show', 'error');
        password_message.textContent = "Min 8 chars, include special letters.";
        password.classList.remove('input-invalid');
    }

    if (confirm_password.value === password.value) {
        confirm_password_message.innerText = "Passwords match";
        confirm_password_message.classList.add('show', 'success');
        confirm_password_message.classList.remove('error');
        confirm_password.classList.remove('input-invalid');
    } else {
        confirm_password_message.innerText = "Passwords do not match";
        confirm_password_message.classList.add('show', 'error');
        confirm_password_message.classList.remove('success');
        confirm_password.classList.add('input-invalid');
        Valid = false;
    }
}

confirm_password.addEventListener('input', checkPasswordMatch);
password.addEventListener('input', checkPasswordMatch);

form.addEventListener('submit', function (e) {
    is_Valid = true;

    // Password
    if (password.value === '') {
        password_message.textContent = "Password is Required.";
        password_message.classList.add('show', 'error');
        password.classList.add('input-invalid');
        is_Valid = false;
    } else if (password.value.length < 8) {
        password_message.textContent = "Password Must be At Least 8 Chars.";
        password_message.classList.add('show', 'error');
        password.classList.add('input-invalid');
        is_Valid = false;
    }

    // Confirm Password
    if (confirm_password.value === '') {
        confirm_password_message.textContent = "Confirm Password is Required.";
        confirm_password_message.classList.add('show', 'error');
        confirm_password.classList.add('input-invalid');
        is_Valid = false;
    } else if (confirm_password.value === password.value) {
        confirm_password_message.innerText = "Passwords match";
        confirm_password_message.classList.add('show', 'success');
        confirm_password_message.classList.remove('error');
        confirm_password.classList.remove('input-invalid');
    }
    else {
        confirm_password_message.innerText = "Passwords do not match";
        confirm_password_message.classList.add('show', 'error');
        confirm_password.classList.add('input-invalid');
        confirm_password_message.classList.remove('success');
        is_Valid = false;
    }

    if (!is_Valid) {
        e.preventDefault();
    }
});

</script>

<script src="scripts/script.js"></script>
<?php 
include 'components/footer.php';
?>
