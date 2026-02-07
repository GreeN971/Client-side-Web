// Auth Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initPasswordToggle();
    initLoginForm();
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
 * Initialize login form
 * - Hardcoded admin/admin check stays client-side
 * - All other logins go through PHP backend (form action="includes/login.inc.php")
 */
function initLoginForm() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email-input').value.trim();
            const password = document.getElementById('password-input').value;
            
            // Basic client-side validation
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return;
            }
            
            // Check for hardcoded admin login (keep as requested)
            if (email.toLowerCase() === 'admin' && password === 'admin') {
                e.preventDefault(); // Stop form from posting to PHP
                localStorage.setItem('isLoggedIn', 'true');
                localStorage.setItem('isAdmin', 'true');
                localStorage.setItem('userEmail', 'admin');
                alert('Admin login successful! Redirecting...');
                window.location.href = 'admin.php';
                return;
            }
            
            // For all other users, let the form submit naturally to PHP backend
            // The form action="includes/login.inc.php" handles DB authentication
        });
    }
}
