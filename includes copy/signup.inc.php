<?php
if(isset($_POST["submit"]))
{
    $email = $_POST["email"];
    $pwd = $_POST["password"];
    $pwdRepeat = $_POST["confirm-password"];

    // Use email as both the uid and email for the signup logic
    $uid = $email;

    require_once "../classes/dbh.classes.php";
    require_once "../classes/signup.classes.php";
    require_once "../classes/signup-contr.classes.php";
    $signup = new SignupContr($uid, $pwd, $pwdRepeat, $email);

    $signup->signupUser();

    header("location: ../index.php?error=none");
    exit();
}
