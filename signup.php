<?php
    session_start();

    // Map error codes to user-friendly messages
    $errorMessages = [
        'emptyinput' => 'Please fill in all fields!',
        'invalidemail' => 'Invalid email address.',
        'passworddoesnotmatch' => 'Passwords do not match.',
        'emailusernameused' => 'Email or username is already taken.',
        'failedtogetdatafromdb' => 'Something went wrong. Please try again.',
    ];

    $error = isset($_GET['error']) ? $_GET['error'] : null;
    $errorMessage = ($error && isset($errorMessages[$error])) ? $errorMessages[$error] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emotional Flow - Sign Up</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <main class="auth-container">
        <div class="auth-card">
            <!-- Signup Section -->
            <section class="auth-form-section">
                <div class="auth-form-wrapper">
                    <!-- Header -->
                    <div class="welcome-text">
                        <h1 class="welcome-title">Create Account</h1>
                        <p class="welcome-subtitle">Join us to track your mood</p>
                    </div>

                    <?php if ($errorMessage): ?>
                        <div class="error-banner" style="background:#fee;color:#c00;padding:10px 15px;border-radius:6px;margin-bottom:15px;text-align:center;font-size:14px;">
                            <?php echo htmlspecialchars($errorMessage); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form id="signupForm" class="auth-form" action="includes/signup.inc.php" method="post">
                        <!-- Email Input -->
                        <div class="form-group">
                            <label for="email-input" class="form-label">Email address</label>
                            <div class="input-wrapper">
                                <input
                                    id="email-input"
                                    name="email"
                                    placeholder="your@mail.com"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    class="form-input"
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="username-input" class="form-label">Username</label>
                            <div class="input-wrapper">
                                <input
                                    id="username-input"
                                    name="username"
                                    placeholder="User username"
                                    type="text"
                                    required
                                    class="form-input"
                                />
                            </div>
                            <span class="error-text hidden" id="username-error">Username must be at least 3 characters.</span>
                        </div>

                        <!-- Password Input -->
                        <div class="form-group">
                            <label for="password-input" class="form-label">Password</label>
                            <div class="input-wrapper input-with-icon">
                                <input
                                    id="password-input"
                                    name="password"
                                    placeholder="Your password"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                    class="form-input"
                                />
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-off-icon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="password-requirements" id="password-requirements">
                            <div class="requirement" id="req-length">
                                <span class="requirement-check"></span>
                                <span>8+ characters</span>
                            </div>
                            <div class="requirement" id="req-upper">
                                <span class="requirement-check"></span>
                                <span>1 uppercase</span>
                            </div>
                            <div class="requirement" id="req-special">
                                <span class="requirement-check"></span>
                                <span>1 special char</span>
                            </div>
                        </div>

                        <!-- Confirm Password Input -->
                        <div class="form-group">
                            <label for="confirm-password" class="form-label">Confirm Password</label>
                            <div class="input-wrapper input-with-icon" id="confirm-password-wrapper">
                                <input
                                    id="confirm-password"
                                    name="confirm-password"
                                    placeholder="Confirm your password"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                    class="form-input"
                                />
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-off-icon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            <span class="error-text hidden" id="password-mismatch">Passwords do not match</span>
                        </div>

                        <!-- Pet Name Input -->
                        <div class="form-group">
                            <label for="pet-name" class="form-label">Enter your pet's name</label>
                            <div class="input-wrapper">
                                <input
                                    id="pet-name"
                                    name="pet-name"
                                    placeholder="Its name is..."
                                    type="text"
                                    class="form-input"
                                />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <button type="submit" name="submit" class="btn btn-primary btn-full">Sign up</button>
                            
                            <div class="form-links centered">
                                <p class="form-link-text">
                                    Already have an account?
                                    <a href="index.php" class="link-primary">Log in</a>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Illustration Section -->
            <aside class="illustration-section">
                <div class="illustration-placeholder">
                    <img src="images/listening-to-feedback.png" alt="Track Your Emotions" class="illustration-image">
                </div>
            </aside>
        </div>
    </main>

    <script src="js/auth.js"></script>
</body>
</html>
