// Forgot Password Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initPasswordToggle();
    initForgotPasswordForm();
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
 * Initialize forgot password form
 */
function initForgotPasswordForm() {
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const petName = document.getElementById('pet-name').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmNewPassword = document.getElementById('confirm-new-password').value;
            
            // Basic validation
            if (!petName || !newPassword || !confirmNewPassword) {
                alert('Please fill in all fields');
                return;
            }
            
            // Verify pet name (mock - just check if it's stored)
            const storedPetName = localStorage.getItem('petName');
            if (storedPetName && petName.toLowerCase() !== storedPetName.toLowerCase()) {
                alert('Pet name does not match our records');
                return;
            }
            
            if (newPassword.length < 8) {
                alert('Password must be at least 8 characters');
                return;
            }
            
            if (newPassword !== confirmNewPassword) {
                alert('Passwords do not match');
                return;
            }
            
            // Mock password reset
            console.log('Password reset submitted', { petName });
            
            alert('Password reset successfully! Please log in with your new password.');
            window.location.href = 'index.php';
        });
    }
}
