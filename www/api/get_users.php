<?php
// get_users.php
header("Content-Type: application/json");
require_once 'functions/database.php';
require_once 'functions/functions.php';

$data     = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';

if (!empty($username)) {
    $result = getUserByUsername($username);
    echo json_encode($result);
    exit;
}

$result = getAllUsers();
echo json_encode($result);
?>