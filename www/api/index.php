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

// ── GET /users ────────────────────────────────────────────────────────────────
// Called by: loadAndRenderUsers(), allUsersCache refresh
$app->get('/users', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(getAllUsers());
});

// ── GET /users/:username ──────────────────────────────────────────────────────
// Called by: App.fetchUser() on profile page
$app->get('/users/:username', function ($username) use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode(getUserByUsername($username));
});

// ── POST /ajax/login ──────────────────────────────────────────────────────────
// Called by: login form submit
$app->post('/ajax/login', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(loginUser($data['username'] ?? '', $data['password'] ?? ''));
});

// ── POST /ajax/register ───────────────────────────────────────────────────────
// Called by: signup form submit
$app->post('/ajax/register', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(createUser($data));
});

// ── POST /users/delete ────────────────────────────────────────────────────────
// Called by: .btn-remove click
$app->post('/users/delete', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    echo json_encode(deleteUser($data['username'] ?? ''));
});

// ── POST /users/:username/update ──────────────────────────────────────────────
// Called by: profile form submit
$app->post('/users/:username/update', function ($username) use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    $data = json_decode($app->request->getBody(), true);
    $data['original_username'] = $username;
    echo json_encode(updateUser($data));
});

$app->run();