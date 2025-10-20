<?php
//As this is suppose to be pure php file with no HTML there is not need for closing brackets
if(isset($_POST["submit"])) #name="submit"
{
    //Grabs the data
    $uid = $_POST["uid"];
    $pwd = $_POST["pwd"];
    $pwdRepeat = $_POST["pwdRepeat"]; #there may be a typo and its suppose to be pwdRepeat
    $email = $_POST["email"];


    //Inicialize SignContr class
    require_once "../classes/dbh.classes.php"; #include order is important database first 
    require_once "../classes/signup.classes.php";
    require_once "../classes/signup-contr.classes.php";
    $signup = new SignupContr($uid, $pwd, $pwdRepeat, $email); //Creates new object instance of the class

    //Error handlers 
    $signup->signupUser();
    //Going back to front page
    //init_set('display errors', 1);
    //error_reporting(E_ALL);
    header("location: ../index.php?error=none");
    exit();
}
