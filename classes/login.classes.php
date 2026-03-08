<?php
require_once "dbh.classes.php";

class Login extends Dbh {
    protected function getUser($username, $email, $pwd){
        $identifier;
        if($username == NULL)
        {
            $stmt = $this->connect()->prepare('SELECT * FROM users WHERE users_email = ?;');
            $identifier = $email;
        }
        else 
        {
            $stmt = $this->connect()->prepare('SELECT * FROM users WHERE username = ?;');
            $identifier = $username;
        }
        echo "<script>console.log('Debug Objects: " . $identifier . "' );</script>";

        if(!$stmt->execute(array($identifier)))
        { 
            $stmt = null;
            //header("Location: ../index.php?error=failedtogetdatafromdb");
            exit();
        }
        
        if($stmt->rowCount() == 0)
        {
            $stmt = null;
            //header("Location: ../index.php?error=usernotfound");
            exit();
        }
        $pwdHashed = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //$stmt = null;
        $checkPassword = password_verify($pwd, $pwdHashed[0]["users_pwd"]);

        if(!$checkPassword)
        {
            $stmt = null;
            header("Location: ../index.php?error=wrongpassword");
            exit();
        }
        else
        {
            $stmt = $this->connect()->prepare('SELECT * FROM users WHERE users_email = ? AND users_pwd = ?;');
            if(!$stmt->execute(array($identifier, $pwdHashed[0]["users_pwd"]))) 
            { 
                $stmt = NuLl;
                header("Location: ../index.php?error=failedtogetdatafromdb");
                exit();
            }

            if($stmt->rowCount() == 0)
            {
                $stmt = null;
                header("Location: ../index.php?error=usernotfound");
                exit();
            }

            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

            session_start();
            $_SESSION["userid"] = $user[0]["users_id"];
            $_SESSION["userusername"] = $user[0]["username"];
        }
        $stmt = null;
    }

}
