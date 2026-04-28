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

/* ── CORS Headers (required for Cordova/mobile app) ── */
$app->hook('slim.before', function () use ($app) {
    $app->response->headers->set('Access-Control-Allow-Origin', '*');
    $app->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
    $app->response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

/* ── Handle OPTIONS preflight requests ── */
$app->map('/(:path+)', function () use ($app) {
    $app->response->setStatus(200);
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(['status' => 'ok']);
})->via('OPTIONS');


/* ── GET /users ── */
$app->get('/users', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(getAllUsers());
});


/* ── GET /users/:username ── */
$app->get('/users/:username', function ($username) use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(getUserByUsername($username));
});


/* ── POST /register ── */
$app->post('/register', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(createUser($data));
});


/* ── POST /login ── */
$app->post('/login', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(loginUser($data['username'] ?? '', $data['password'] ?? ''));
});


/* ── POST /users/delete ── */
$app->post('/users/delete', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(deleteUser($data['username'] ?? ''));
});


/* ── POST /users/:username/update ── */
$app->post('/users/:username/update', function ($username) use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    $data['original_username'] = $username;
    echo json_encode(updateUser($data));
});


/* ── POST /ajax/users/search ── */
$app->post('/ajax/users/search', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data     = json_decode($app->request->getBody(), true);
    $username = $data['username'] ?? '';
    $result   = !empty($username) ? getUserByUsername($username) : getAllUsers();
    echo json_encode($result);
});


/* ── POST /ajax/login ── */
$app->post('/ajax/login', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(loginUser($data['username'] ?? '', $data['password'] ?? ''));
});


/* ── POST /ajax/register ── */
$app->post('/ajax/register', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(createUser($data));
});


$app->run();
?>