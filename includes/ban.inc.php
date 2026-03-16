<?php
session_start();

if (!isset($_POST['ban_submit'])) {
    header("Location: ../admin.php");
    exit();
}

if (empty($_SESSION['userid']) || empty($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    header("Location: ../index.php?error=notauthorized");
    exit();
}

$targetUserId = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;
$action       = (isset($_POST['action']) && $_POST['action'] === 'unban') ? 'unban' : 'ban';

require_once "../classes/dbh.classes.php";
require_once "../classes/admin.classes.php";
require_once "../classes/admin-contr.classes.php";

$adminContr = new AdminContr($targetUserId, $action);
$adminContr->handleBan();
