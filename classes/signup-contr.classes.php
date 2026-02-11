<?php

class SignupContr extends Signup {
    private $username ; //add username validation on both sides for special characters
    private $pwd;
    private $pwdRepeat;
    private $email;
    private $petsname;

    public function __construct($username, $pwd, $pwdRepeat, $email, $petsname){
        $this->username = $username;
        $this->pwd = $pwd;
        $this->pwdRepeat = $pwdRepeat;
        $this->petsname = $petsname;
        $this->email = $email;
    }

    public function signupUser(){
        if($this->emptyInput() == true){
            header("location: ../signup.php?error=emptyinput");
            exit();
        } 

        if($this->invalidEmail() == false){
            header("location: ../signup.php?error=invalidemail");
            exit();
        }

        if($this->pwdMatch() == false){
            header("location: ../signup.php?error=passworddoesnotmatch");
            exit();
        }

        if($this->usernameTakenCheck() == false){
            header("location: ../signup.php?error=emailusernameused");
            exit();
        }

        $this->setUser($this->username, $this->pwd, $this->email, $this->petsname);
    }

    private function emptyInput(){
        if(empty($this->username) || empty($this->pwd) || empty($this->pwdRepeat) || empty($this->email))
            return true;
        return false;
    }

    private function invalidusername(){
        if(!preg_match("/^[a-zA-Z0-9]*$/", $this->username))
            return false;
        return true;
    }

    private function invalidEmail(){
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL))
            return false;
        return true;
    }

    private function pwdMatch(){
        if($this->pwd !== $this->pwdRepeat)
            return false;
        return true;
    }

    private function usernameTakenCheck(){
        if(!$this->checkUser($this->username, $this->email))
            return false;
        return true;
    }
}
