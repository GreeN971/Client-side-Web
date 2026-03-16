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

    if (!empty($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1) {
        header("Location: ../admin.php");
    } else {
        header("Location: ../analytics.php");
    }
    exit();
}
else
{
    header("Location: ../index.php");
    exit();
}
?>
