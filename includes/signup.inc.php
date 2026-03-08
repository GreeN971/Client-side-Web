<?php
if(isset($_POST["submit"]))
{
    $username = $_POST["username"];
    $email = $_POST["email"];
    $pwd = $_POST["password"];
    $pwdRepeat = $_POST["confirm-password"];
    $petsname = $_POST["pet-name"];

    require_once "../classes/dbh.classes.php";
    require_once "../classes/signup.classes.php";
    require_once "../classes/signup-contr.classes.php";
    $signup = new SignupContr($username, $pwd, $pwdRepeat, $email, $petsname);

    $signup->signupUser();

    header("Location: ../index.php?error=none");
    exit();
}
