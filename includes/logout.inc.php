<?php

session_start();

if (!empty($_SESSION['userid'])) {
    require_once __DIR__ . "/../classes/dbh.classes.php";
    require_once __DIR__ . "/../classes/admin.classes.php";
    (new Admin())->touchLastActive((int) $_SESSION['userid']);
}

session_unset();
session_destroy();

header("Location: ../index.php");
