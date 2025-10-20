<?php
//For changing something inside db aka INSERT or UPDATE
class LoginContr  extends Login{
    //private variables
    private $uid;
    private $pwd;

    //Constructor
    public function __construct($uid, $pwd){
        $this->uid = $uid; #This points to the class instance and uid is the value that has been passed to the constructor
        $this->pwd = $pwd;
    }

    //Methods of the class
    public function loginUser(){
        // if($this->emptyInput() == false){
        //     echo "Empty input";
        //     header("location: ../index.php?error=emptyinput");
        //     exit();
        // }

        $this->getUser($this->uid, $this->pwd);
    }

    private function emptyInput(){
        if(empty($this->uid || $this->pwd))
            return $result = true;
        return $result = false;
    }
}