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

// Form Elements
const form = document.getElementById('LoginForm');
const uep = document.getElementById('email');
const password = document.getElementById('password');

const uep_message = document.getElementById('uep-message');
const p_message = document.getElementById('p-message');

uep.addEventListener('input', function () {
    if (uep.value.trim() === "") {
        uep_message.textContent = "This Field is required.";
        uep_message.classList.add('show', 'error');
        uep.classList.add('input-invalid');
    } else {
        uep_message.textContent = "";
        uep_message.classList.remove('show', 'error');
        uep.classList.remove('input-invalid');
    }
});

password.addEventListener('input', function () {
    if (password.value === "") {
        p_message.textContent = "Password is required.";
        p_message.classList.add('show', 'error');
        password.classList.add('input-invalid');
    } else {
        p_message.textContent = "";
        p_message.classList.remove('show', 'error');
        password.classList.remove('input-invalid');
    }
});

form.addEventListener('submit', function (e) {
    let is_Valid = true;

    if (uep.value.trim() === "") {
        uep_message.textContent = "This Field is required.";
        uep_message.classList.add('show', 'error');
        uep.classList.add('input-invalid');
        is_Valid = false;
    }

    if (password.value === "") {
        p_message.textContent = "Password is required.";
        p_message.classList.add('show', 'error');
        password.classList.add('input-invalid');
        is_Valid = false;
    }

    if (!is_Valid) {
        e.preventDefault();
    } else {
        document.querySelector('.btn-login').disabled = true;
    }
});