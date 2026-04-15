<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['userid']) || empty($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit();
}

header('Content-Type: application/json');

require_once "../../classes/dbh.classes.php";
require_once "../../classes/admin.classes.php";

$admin       = new Admin();
$activeUsers = $admin->getActiveUsers();

echo json_encode($activeUsers);
