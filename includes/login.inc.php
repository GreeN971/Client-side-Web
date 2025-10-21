<?php
//As this is suppose to be pure php file with no HTML there is not need for closing brackets
if(isset($_POST["submit"])) #name="submit"
{
    //Grabs the data
    $uid = $_POST["uid"];
    $pwd = $_POST["pwd"];


    //Inicialize SignContr class
    require_once "../classes/dbh.classes.php"; #include order is important database first 
    require_once "../classes/login.classes.php";
    require_once "../classes/login-contr.classes.php";
    $signup = new LoginContr($uid, $pwd); //Creates new object instance of the class

    //Error handlers 
    $signup->loginUser();
    //Going back to front page
    //init_set('display errors', 1);
    //error_reporting(E_ALL);
    header("location: ../pages/profile.php");
    exit();
}
