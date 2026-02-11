// Error alerts from URL parameters (set by PHP backend)
class Errors {
    static #messages = {
        emptyinput: "Please fill in all fields!",
        failedtogetdatafromdb: "Failed to get data from the database.",
        usernotfound: "User not found.",
        wrongpassword: "Incorrect password.",
        invalidusername: "Invalid username. Only letters and numbers allowed.",
        invalidemail: "Invalid email address.",
        passworddoesnotmatch: "Passwords do not match.",
        emailusernameused: "Email or username is already taken.",
        none: null // Successful action, no error
    };

    static showFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const error = params.get("error");
        if (!error) return;

        const message = this.#messages[error];
        if (message) {
            alert(message);
        }

        // Clean the URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

document.addEventListener("DOMContentLoaded", () => Errors.showFromUrl());
