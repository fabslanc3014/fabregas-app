<?php
header("Content-Type: application/json");

$base = "https://m.gohumano.com/apislim4lance/";

// Build the sub-path from the URL
// e.g. /api/ajax/login  →  ajax/login
$request = $_SERVER['REQUEST_URI'];
$request = preg_replace('#^.*?/api/#', '', $request);

$url = $base . $request;
$method = $_SERVER['REQUEST_METHOD'];
$body = file_get_contents("php://input");

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

if ($method === "POST") {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
}

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Accept: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;