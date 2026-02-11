<?php

class LoginContr extends Login {
    private $username = NULL;
    private $email = NULL;
    private $pwd;

    public function __construct($usernameOrEmail, $pwd){
        if(strpos($usernameOrEmail, "@") !== false)
            $this->email = $usernameOrEmail;
        else
            $this->username= $usernameOrEmail;
        $this->pwd = $pwd;
    }

    public function loginUser(){
        if($this->emptyInput() == true){
            header("location: ../index.php?error=emptyinput");
            exit();
        }
        //nakonec bylo nejlepsi vse nasetovat tady az pak v getUser resit email nebo username
        $this->getUser($this->username, $this->email, $this->pwd);
    }

    private function emptyInput(){
        if((empty($this->username) && empty($this->email)) || empty($this->pwd))
            return true;
        return false;
    }
}
