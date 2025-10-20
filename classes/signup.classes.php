<?php
require_once "../classes/dbh.classes.php";

class Signup extends Dbh {

    protected function setUser($uid, $pwd, $email){
        $stmt = $this->connect()->prepare('INSERT INTO users (users_uid, users_pwd, users_email) VALUES (?,?,?)');

        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
        
        if(!$stmt->execute(array($uid, $hashedPwd, $email))) 
        { 
            $stmt = null;
            header("location: ../index.php?error=stmtfailed");
            exit();
        }
        $stmt = null;
    }

    protected function checkUser($uid, $email){
        $stmt = $this->connect()->prepare('SELECT users_uid FROM users WHERE users_uid = ? OR users_email = ?;'); #user_id = $uid UNSAFE
        //? works as placeholder, since actuall data are separated from the query, it protecs from SQL injection
        //So bassically I get an array with all the users
        
        if(!$stmt->execute(array($uid, $email))) #Reason for being false is when any data is grabbed then its true and user found
        { 
            $stmt = null; #delete data from the statement
            header("location: ../index.php?error=stmtfailed"); #sends back to index page
            exit();
        }

        if($stmt->rowCount() > 0)
            return $result = false;
        return $result = true;
    }

}
