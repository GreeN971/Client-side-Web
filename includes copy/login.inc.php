<?php
if(isset($_POST["submit"]))
{
    $uid = $_POST["email"];
    $pwd = $_POST["password"];

    require_once "../classes/dbh.classes.php";
    require_once "../classes/login.classes.php";
    require_once "../classes/login-contr.classes.php";
    $login = new LoginContr($uid, $pwd);

    $login->loginUser();

    header("location: ../analytics.php");
    exit();
}
