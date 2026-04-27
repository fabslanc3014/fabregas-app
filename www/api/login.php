<?php
// login.php
header("Content-Type: application/json");
require_once 'functions/database.php';
require_once 'functions/functions.php';

$data   = json_decode(file_get_contents("php://input"), true);
$result = loginUser($data['username'] ?? '', $data['password'] ?? '');

echo json_encode($result);
?>