<?php
/* ── index.php ── */

if (!function_exists('get_magic_quotes_gpc')) {
    function get_magic_quotes_gpc() {
        return false;
    }
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/database.php';
require_once __DIR__ . '/functions/functions.php';

$app = new \Slim\Slim();

// ── CORS — allow the front-end origin to reach this API ──────────────────────
$app->add(new \Slim\Middleware\ContentTypes());

$app->hook('slim.before', function () use ($app) {
    $app->response->headers->set('Access-Control-Allow-Origin', '*');
    $app->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $app->response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
});

// Handle pre-flight OPTIONS requests
$app->options('/(:path+)', function () use ($app) {
    $app->response->headers->set('Access-Control-Allow-Origin', '*');
    $app->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $app->response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
    $app->response->setStatus(200);
});

// ── Helper ───────────────────────────────────────────────────────────────────
function jsonResponse($app, $data) {
    $app->response->headers->set('Content-Type', 'application/json');
    echo json_encode($data);
}

// ── GET /users ────────────────────────────────────────────────────────────────
// Called by: loadAndRenderUsers(), allUsersCache refresh, search cache
$app->get('/users', function () use ($app) {
    jsonResponse($app, getAllUsers());
});

// ── GET /users/:username ──────────────────────────────────────────────────────
// Called by: App.fetchUser() on the profile page
$app->get('/users/:username', function ($username) use ($app) {
    jsonResponse($app, getUserByUsername($username));
});

// ── POST /ajax/login ──────────────────────────────────────────────────────────
// Called by: login form submit
$app->post('/ajax/login', function () use ($app) {
    $data = json_decode($app->request->getBody(), true);
    jsonResponse($app, loginUser($data['username'] ?? '', $data['password'] ?? ''));
});

// ── POST /ajax/register ───────────────────────────────────────────────────────
// Called by: signup form submit
$app->post('/ajax/register', function () use ($app) {
    $data = json_decode($app->request->getBody(), true);
    jsonResponse($app, createUser($data));
});

// ── POST /users/delete ────────────────────────────────────────────────────────
// Called by: .btn-remove click
$app->post('/users/delete', function () use ($app) {
    $data = json_decode($app->request->getBody(), true);
    jsonResponse($app, deleteUser($data['username'] ?? ''));
});

// ── POST /users/:username/update ──────────────────────────────────────────────
// Called by: profile form submit
$app->post('/users/:username/update', function ($username) use ($app) {
    $data = json_decode($app->request->getBody(), true);
    $data['original_username'] = $username;
    jsonResponse($app, updateUser($data));
});

$app->run();