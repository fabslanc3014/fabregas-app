<?php
// delete_user.php
header("Content-Type: application/json");
require_once 'functions/database.php';
require_once 'functions/functions.php';

$data   = json_decode(file_get_contents("php://input"), true);
$result = deleteUser($data['username'] ?? '');

echo json_encode($result);
?>