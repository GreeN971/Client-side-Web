<?php
require_once "../classes/dbh.classes.php";

class Login extends Dbh {

    protected function getUser($uid, $pwd){
        $stmt = $this->connect()->prepare('SELECT users_pwd FROM users WHERE users_uid = ? OR users_email = ? ;');
        
        if(!$stmt->execute(array($uid, $pwd))) 
        { 
            $stmt = null;
            header("location: ../index.php?error=failedtogetdatafromdb");
            exit();
        }

        if($stmt->rowCount() == 0)
        {
            $stmt = null;
            header("location: ../index.php?error=usernotfound");
            exit();
        }

        $pwdHashed = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $checkPassword = password_verify($pwd, $pwdHashed[0]["users_pwd"]); #true  or false
        //With this user can login via email or username
        if(!$checkPassword) //name
        {
            $stmt = null;
            header("location: ../index.php?error=wrongpassword");
            exit();
        }
        elseif($checkPassword)
        {
            $stmt = $this->connect()->prepare('SELECT * FROM users WHERE users_uid = ? OR users_email = ? and users_pwd = ?;');
            if(!$stmt->execute(array($uid, $uid, $hashedPwd))) 
            { 
                $stmt = null;
                header("location: ../index.php?error=failedtogetdatafromdb");
                exit();
            }

            if($stmt->rowCount() == 0)
            {
                $stmt = null;
                header("location: ../index.php?error=usernotfound");
                exit();
            }

            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

            session_start();
            $_SESSION["userid"] = $user[0]["users_id"];
            $_SESSION["useruid"] = $user[0]["users_uid"];
        }

        $stmt = null;
    }

}
