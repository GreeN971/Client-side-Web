// Auth Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initPasswordToggle();
    initLoginForm();
    initSignupForm();
});

/**
 * Initialize password visibility toggle
 */
function initPasswordToggle() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const inputWrapper = this.closest('.input-wrapper');
            const input = inputWrapper.querySelector('input');
            const eyeIcon = this.querySelector('.eye-icon');
            const eyeOffIcon = this.querySelector('.eye-off-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        });
    });
}

function initSignupForm() {
    const signupForm = document.getElementById('signupForm');
    if (!signupForm) return;

    const usernameInput   = document.getElementById('username-input');
    const passwordInput   = document.getElementById('password-input');
    const confirmInput    = document.getElementById('confirm-password');
    const usernameError   = document.getElementById('username-error');
    const mismatchError   = document.getElementById('password-mismatch');

    const reqLength  = document.getElementById('req-length');
    const reqUpper   = document.getElementById('req-upper');
    const reqSpecial = document.getElementById('req-special');

    function checkPasswordRules(val) {
        return {
            length:  val.length >= 8,
            upper:   /[A-Z]/.test(val),
            special: /[^A-Za-z0-9]/.test(val),
        };
    }

    function updateRequirements() {
        const rules = checkPasswordRules(passwordInput.value);
        reqLength.classList.toggle('valid', rules.length);
        reqUpper.classList.toggle('valid', rules.upper);
        reqSpecial.classList.toggle('valid', rules.special);
    }

    passwordInput.addEventListener('input', function() {
        updateRequirements();
        if (confirmInput.value) {
            mismatchError.classList.toggle('hidden', confirmInput.value === passwordInput.value);
        }
    });

    confirmInput.addEventListener('input', function() {
        mismatchError.classList.toggle('hidden', confirmInput.value === passwordInput.value);
    });

    usernameInput.addEventListener('blur', function() {
        usernameError.classList.toggle('hidden', usernameInput.value.trim().length >= 3);
    });

    signupForm.addEventListener('submit', function(e) {
        const username = usernameInput.value.trim();
        const password = passwordInput.value;
        const confirm  = confirmInput.value;

        if (!username || !password || !confirm) {
            e.preventDefault();
            alert('Please fill in all fields.');
            return;
        }

        if (username.length < 3) {
            e.preventDefault();
            usernameError.classList.remove('hidden');
            usernameInput.closest('.input-wrapper').classList.add('error');
            return;
        }

        const rules = checkPasswordRules(password);
        if (!rules.length || !rules.upper || !rules.special) {
            e.preventDefault();
            updateRequirements();
            passwordInput.closest('.input-wrapper').classList.add('error');
            return;
        }

        if (password !== confirm) {
            e.preventDefault();
            mismatchError.classList.remove('hidden');
            confirmInput.closest('.input-wrapper').classList.add('error');
            return;
        }
    });
}

function initLoginForm() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) return;

    loginForm.addEventListener('submit', function(e) {
        const email    = document.getElementById('email-input').value.trim();
        const password = document.getElementById('password-input').value;
        if (!email || !password) {
            e.preventDefault();
            showLoginError('Please fill in all fields.');
        }
    });
}

function showLoginError(message) {
    const el = document.getElementById('login-error');
    if (!el) return;
    el.textContent = message;
    el.classList.remove('hidden');
}
