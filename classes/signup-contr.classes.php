<?php
//For changing something inside db aka INSERT or UPDATE
class SignupContr  extends Signup{
    //private variables
    private $uid;
    private $pwd;
    private $pwdRepeat;
    private $email;

    //Constructor
    public function __construct($uid, $pwd, $pwdRepeat,$email){
        $this->uid = $uid; #This points to the class instance and uid is the value that has been passed to the constructor
        $this->pwd = $pwd;
        $this->pwdRepeat = $pwdRepeat;
        $this->email = $email;
    }

    //Methods of the class
    public function signupUser(){
        if($this->emptyInput() == true){
            echo "Empty input";
            header("location: ../index.php?error=emptyinput");
            exit();
        } 

        if($this->invalidUid() == false){
            echo "Invalid uId";
            header("location: ../index.php?error=invaliduid");
            exit();
        }

        if($this->invalidEmail() == false){
            echo "Invalid email";
            header("location: ../index.php?error=invalidemail");
            exit();
        }

        if($this->pwdMatch() == false){
            echo "Password does not match";
            header("location: ../index.php?error=passworddoesnotmatch");
            exit();
        }

        if($this->uidTakenCheck() == false){
            echo "Email or username is already being used";
            header("location: ../index.php?error=emailusernameused");
            exit();
        }

        $this->setUser($this->uid, $this->pwd, $this->email);
    }

    private function emptyInput(){
        if(!empty($this->uid || $this->pwd || $this->pwdRepeat || $this->email))
            return $result = true;
        return $result = false;
    }

    private function invalidUid(){
        if(!preg_match("/^[a-zA-Z0-9]*$/", $this->uid))
            return $result = false;
        return $result = true;
    }

    private function invalidEmail(){
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL))
            return $result = false;
        return $result = true;
    }

    private function pwdMatch(){
        if($this->pwd !== $this->pwdRepeat)
            return $result = false;
        return $result = true;
    }

    private function uidTakenCheck(){
        if(!$this->checkUser($this->uid, $this->email))
            return $result = false;
        return $result = true;
    }
}