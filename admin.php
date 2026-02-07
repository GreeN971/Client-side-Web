<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emotional Flow - Admin Panel</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <div class="logo">EF</div>
                <h1 class="brand-name-sm">Admin Panel</h1>
            </div>
            <nav class="header-nav">
                <button class="logout-button" id="logoutBtn" aria-label="Logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </button>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-welcome">
                <h2>Welcome, Admin</h2>
                <p>This is the admin panel. You can customize this page later.</p>
            </div>

            <!-- Placeholder sections for future content -->
            <div class="admin-grid">
                <div class="admin-card">
                    <h3>Users</h3>
                    <p>Manage user accounts</p>
                </div>
                <div class="admin-card">
                    <h3>Analytics</h3>
                    <p>View system analytics</p>
                </div>
                <div class="admin-card">
                    <h3>Settings</h3>
                    <p>Configure system settings</p>
                </div>
            </div>
        </main>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>
