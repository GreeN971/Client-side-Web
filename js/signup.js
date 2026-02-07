// Signup Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initPasswordToggle();
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

/**
 * Initialize signup form
 * Client-side validation only — form submits to PHP backend via action="includes/signup.inc.php"
 */
function initSignupForm() {
    const signupForm = document.getElementById('signupForm');
    
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email-input').value.trim();
            const password = document.getElementById('password-input').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            // Basic client-side validation
            if (!email || !password || !confirmPassword) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                const mismatchEl = document.getElementById('password-mismatch');
                if (mismatchEl) mismatchEl.classList.remove('hidden');
                alert('Passwords do not match');
                return;
            }
            
            // Store pet name in localStorage (for forgot password feature)
            const petName = document.getElementById('pet-name').value;
            if (petName) {
                localStorage.setItem('petName', petName);
            }
            
            // Let the form submit naturally to PHP backend
        });
    }
}
