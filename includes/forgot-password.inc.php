<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_POST['forgot_submit'])) {
    header("Location: ../forgot-password.php");
    exit();
}

$identification  = $_POST['identification']       ?? '';
$petsName        = $_POST['pet-name']             ?? '';
$newPassword     = $_POST['new-password']         ?? '';
$confirmPassword = $_POST['confirm-new-password'] ?? '';

require_once "../classes/dbh.classes.php";
require_once "../classes/forgot-password.classes.php";
require_once "../classes/forgot-password-contr.classes.php";

$reset = new ForgotPasswordContr($identification, $petsName, $newPassword, $confirmPassword);
$reset->resetPassword();
