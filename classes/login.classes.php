<?php
require_once "dbh.classes.php";

class Login extends Dbh {
    protected function getUser($username, $email, $pwd) {
        if ($username === NULL) {
            $stmt = $this->connect()->prepare('SELECT * FROM users WHERE users_email = ?;');
            $identifier = $email;
        } else {
            $stmt = $this->connect()->prepare('SELECT * FROM users WHERE username = ?;');
            $identifier = $username;
        }

        if (!$stmt->execute([$identifier])) {
            $stmt = null;
            header("Location: ../index.php?error=failedtogetdatafromdb");
            exit();
        }

        if ($stmt->rowCount() == 0) {
            $stmt = null;
            header("Location: ../index.php?error=usernotfound");
            exit();
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;

        if (!password_verify($pwd, $user["users_pwd"])) {
            header("Location: ../index.php?error=wrongpassword");
            exit();
        }

        if (!empty($user["is_banned"])) {
            header("Location: ../index.php?error=banned");
            exit();
        }

        session_start();
        $_SESSION["userid"]       = $user["users_id"];
        $_SESSION["userusername"] = $user["username"];
        $_SESSION["is_admin"]     = (int) $user["is_admin"];

        // Update last_active for online-user tracking
        $upd = $this->connect()->prepare('UPDATE users SET last_active = NOW() WHERE users_id = ?');
        $upd->execute([(int) $user["users_id"]]);
    }
}
