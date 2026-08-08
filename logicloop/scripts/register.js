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
let Valid = true;
// Form System
const form = document.getElementById('registerForm');
const full_name = document.getElementById('fullname');
const full_name_message = document.getElementById('fullname-message');
const username = document.getElementById('username');
const username_message = document.getElementById('username-message');
const email = document.getElementById('email');
const email_message = document.getElementById('email-message');
const phone = document.getElementById('phone');
const phone_message = document.getElementById('phone-message');
const password = document.getElementById('password');
const password_message = document.getElementById('password-message');
const confirm_password = document.getElementById('confirm_password');
const confirm_password_message = document.getElementById('confirm_password_message');

// Email Validation
function validateEmail(email) {
    const checkemail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return checkemail.test(email);
}

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

full_name.addEventListener('input', function () {

    if (full_name.value === "") {
        full_name_message.textContent = "Full Name is Required.";
        full_name_message.classList.add('show', 'error');
        full_name.classList.add('input-invalid');
    } else {
        full_name_message.textContent = "";
        full_name_message.classList.remove('show', 'error');
        full_name.classList.remove('input-invalid');
    }

});


username.addEventListener('input', function () {

    if (username.value === "") {
        username_message.textContent = "Username is Required.";
        username_message.classList.add('show', 'error');
        username.classList.add('input-invalid');
    } else {
        username_message.textContent = "";
        username_message.classList.remove('show', 'error');
        username.classList.remove('input-invalid');
    }

});

phone.addEventListener('input', function () {

    if (phone.value === "") {
        phone_message.textContent = "Phone is Required.";
        phone_message.classList.add('show', 'error');
        phone.classList.add('input-invalid');
    } else {
        phone_message.textContent = "";
        phone_message.classList.remove('show', 'error');
        phone.classList.remove('input-invalid');
    }

});

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

form.addEventListener('submit', function (e) {
    is_Valid = true;
    // Full Name
    if (full_name.value === '') {
        full_name_message.textContent = "Full Name is Required.";
        full_name_message.classList.add('show', 'error');
        full_name.classList.add('input-invalid');
        is_Valid = false;
    }

    // Username
    if (username.value === '') {
        username_message.textContent = "Username is Required.";
        username_message.classList.add('show', 'error');
        username.classList.add('input-invalid');
        is_Valid = false;
    }

    // Email
    if (email.value === '') {
        email_message.textContent = "Email is Required.";
        email_message.classList.add('show', 'error');
        email.classList.add('input-invalid');
        is_Valid = false;
    } else if (!validateEmail(email.value)) {
        email_message.classList.add('show', 'error');
        email.classList.add('input-invalid');
        email_message.innerText = "Enter A Correct Email";
        is_Valid = false;
    }

    // Phone
    if (phone.value === '') {
        phone_message.textContent = "Phone is Required.";
        phone_message.classList.add('show', 'error');
        phone.classList.add('input-invalid');
        is_Valid = false;
    } else {
        phone_message.textContent = "";
        phone_message.classList.remove('show', 'error');
        phone.classList.remove('input-invalid');
    }

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

