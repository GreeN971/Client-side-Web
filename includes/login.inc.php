<?php
if(isset($_POST["submit"]))
{
    $identification = $_POST["email"];
    $pwd = $_POST["password"];

    require_once "../classes/dbh.classes.php";
    require_once "../classes/login.classes.php";
    require_once "../classes/login-contr.classes.php";
    $login = new LoginContr($identification, $pwd);

    $login->loginUser();

    header("Location: ../analytics.php");
    exit();
}
else
{
    header("Location: ../index.php");
    exit();
}
?>
