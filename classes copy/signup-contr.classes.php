<?php

class SignupContr extends Signup {
    private $uid;
    private $pwd;
    private $pwdRepeat;
    private $email;

    public function __construct($uid, $pwd, $pwdRepeat, $email){
        $this->uid = $uid;
        $this->pwd = $pwd;
        $this->pwdRepeat = $pwdRepeat;
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

        if($this->uidTakenCheck() == false){
            header("location: ../signup.php?error=emailusernameused");
            exit();
        }

        $this->setUser($this->uid, $this->pwd, $this->email);
    }

    private function emptyInput(){
        if(empty($this->uid) || empty($this->pwd) || empty($this->pwdRepeat) || empty($this->email))
            return true;
        return false;
    }

    private function invalidUid(){
        if(!preg_match("/^[a-zA-Z0-9]*$/", $this->uid))
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

    private function uidTakenCheck(){
        if(!$this->checkUser($this->uid, $this->email))
            return false;
        return true;
    }
}
