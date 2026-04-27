<?php
// register.php
header("Content-Type: application/json");
require_once 'functions/database.php';
require_once 'functions/functions.php';

$data   = json_decode(file_get_contents("php://input"), true);
$result = createUser($data);

echo json_encode($result);
?>