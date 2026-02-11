<?php
if(isset($_POST["submit"]))
{
    $username = $_POST["email"];
    $pwd = $_POST["password"];

    require_once "../classes/dbh.classes.php";
    require_once "../classes/login.classes.php";
    require_once "../classes/login-contr.classes.php";
    $login = new LoginContr($username, $pwd);

    $login->loginUser();

    header("location: ../analytics.php");
    exit();
}
else
{
    header("location: ../index.php");
    exit();
}
