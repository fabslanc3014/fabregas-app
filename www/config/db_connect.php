<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'fabregas'); 
define('DB_PASS', 'Lance014');       
define('DB_NAME', 'fabregaslanceairon');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['success' => false]);
  exit;
  
}
?>
