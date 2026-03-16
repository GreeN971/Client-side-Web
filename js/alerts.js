// Error / success alerts from URL parameters (set by PHP backend)
class Errors {
    static #messages = {
        emptyinput: "Please fill in all fields!",
        failedtogetdatafromdb: "Failed to get data from the database.",
        usernotfound: "User not found.",
        wrongpassword: "Incorrect password.",
        invalidusername: "Invalid username. Only letters and numbers allowed.",
        invalidemail: "Invalid email address.",
        passworddoesnotmatch: "Passwords do not match.",
        passwordtooshort: "Password must be at least 8 characters.",
        emailusernameused: "Email or username is already taken.",
        banned: "Your account has been suspended. Please contact support.",
        none: null
    };

    static #successMessages = {
        passwordreset: "Password reset successfully! Please log in.",
    };

    static #showBanner(el, message) {
        if (!el) return;
        el.textContent = message;
        el.classList.remove('hidden');
    }

    static showFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const errorEl   = document.getElementById('login-error');
        const successEl = document.getElementById('login-success');

        const error = params.get("error");
        if (error) {
            const message = this.#messages[error];
            if (message) {
                if (errorEl) {
                    this.#showBanner(errorEl, message);
                } else {
                    alert(message);
                }
            }
        }

        const success = params.get("success");
        if (success) {
            const message = this.#successMessages[success];
            if (message) {
                if (successEl) {
                    this.#showBanner(successEl, message);
                } else {
                    alert(message);
                }
            }
        }

        // Clean the URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

document.addEventListener("DOMContentLoaded", () => Errors.showFromUrl());
