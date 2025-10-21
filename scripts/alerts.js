class Errros{
    static #messages = {
        emptyinput: "Please fill in all fields!",
        failedtogetdatafromdb: "Failed to get data from the database.",
        usernotfound: "User not found.",
        wrongpassword: "Incorrect password.",
        invaliduid: "Invalid username.",
        invalidemail: "Invalid email address.",
        passworddoesnotmatch: "Passwords do not match.",
        emailusernameused: "Email or username is already taken."
    }; //all messages through php backend with their frontend match inside private static fiend "#" means private
    
    static showFromUrl(){
        //new object with scans for the current URL which will may not be optimal for later since we will have different functionalities and the script 
        //will do nothing but run away
        const params = new URLSearchParams(window.location.search);
        //Gets the exacts parameter if its not there it will do nothing
        const error = params.get("error")
        if(!error) 
            return;
        //array lookup
        const message = this.#messages[error];
        if(message)
            alert(message);
        
        window.history.replaceState({}, document.title.window.location.pathname);
    }
}

document.addEventListener("DOMContentLoaded", () => Errros.showFromUrl());