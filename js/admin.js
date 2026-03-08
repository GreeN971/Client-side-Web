// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    checkAdminAccess();
    initLogout();
});

/**
 * Check if user has admin access
 */
function checkAdminAccess() {
    const isAdmin = localStorage.getItem('isAdmin');
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    
    // Redirect to login if not logged in or not admin
    if (!isLoggedIn || isAdmin !== 'true') {
        window.Location.href = 'index.php';
    }
}

/**
 * Initialize logout button
 */
function initLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            // Clear localStorage login state
            localStorage.removeItem('isLoggedIn');
            localStorage.removeItem('isAdmin');
            localStorage.removeItem('userEmail');
            
            // Also hit the PHP logout to destroy session
            window.Location.href = 'includes/logout.inc.php';
        });
    }
}
