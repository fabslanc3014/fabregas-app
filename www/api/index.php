<?php
if (!function_exists('get_magic_quotes_gpc')) {
    function get_magic_quotes_gpc() {
        return false;
    }
}
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/database.php';
require_once __DIR__ . '/functions/functions.php';

$app = new \Slim\Slim();

// ── CORS Headers ──
$app->response->headers->set('Access-Control-Allow-Origin', '*');
$app->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
$app->response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

$app->options('/:x+', function () use ($app) {
    $app->response->headers->set('Access-Control-Allow-Origin', '*');
    $app->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $app->response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
});


$app->get('/users', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(getAllUsers());
});


$app->get('/users/:username', function ($username) use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(getUserByUsername($username));
});


$app->post('/register', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(createUser($data));
});

$app->post('/login', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(loginUser($data['username'] ?? '', $data['password'] ?? ''));
});

$app->post('/users/delete', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(deleteUser($data['username'] ?? ''));
});


$app->post('/users/:username/update', function ($username) use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    $data['original_username'] = $username;
    echo json_encode(updateUser($data));
});


$app->post('/ajax/users/search', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data     = json_decode($app->request->getBody(), true);
    $username = $data['username'] ?? '';
    $result   = !empty($username) ? getUserByUsername($username) : getAllUsers();
    echo json_encode($result);
});

$app->post('/ajax/login', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(loginUser($data['username'] ?? '', $data['password'] ?? ''));
});

$app->post('/ajax/register', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(createUser($data));
});

$app->run();
?>