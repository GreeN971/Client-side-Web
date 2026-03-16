<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['emotion'])) {
    $userId  = $_SESSION['userid'];
    $emotion = $_POST['emotion'];
    $note    = isset($_POST['note']) ? $_POST['note'] : '';

    require_once "../classes/dbh.classes.php";
    require_once "../classes/checkin.classes.php";
    require_once "../classes/checkin-contr.classes.php";

    $checkin = new CheckinContr($userId, $emotion, $note);
    $checkin->addCheckin();
} else {
    header("Location: ../analytics.php");
    exit();
}
