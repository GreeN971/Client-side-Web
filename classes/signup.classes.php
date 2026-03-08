<?php
require_once "dbh.classes.php";

class Signup extends Dbh {

    protected function setUser($username, $pwd, $email, $petsname){
        $stmt = $this->connect()->prepare('INSERT INTO users (username, users_pwd, users_email, pets_name) 
            VALUES (?,?,?,?)');

        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
        
        if(!$stmt->execute([$username, $hashedPwd, $email, $petsname])) 
        { 
            $stmt = null;
            header("Location: ../index.php?error=failedRegisteringUser");
            exit();
        }

        $stmt = null;
    }

    protected function checkUser($username, $email){
        $stmt = $this->connect()->prepare('SELECT username FROM users WHERE username = ? OR users_email = ?;');
        
        if(!$stmt->execute(array($username, $email)))
        { 
            $stmt = null;
            header("Location: ../index.php?error=failedtogetdatafromdb");
            exit();
        }

        if($stmt->rowCount() > 0)
            return false;
        return true;
    }

}
