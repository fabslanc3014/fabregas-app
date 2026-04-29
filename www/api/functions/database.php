<?php
require_once __DIR__ . '../../vendor/autoload.php';
require_once __DIR__ . '/config.php';
//connection
ORM::configure('mysql:host=localhost;dbname=fabregaslanceairon');
ORM::configure('username', 'fabregas');
ORM::configure('password', 'Lance014');
ORM::configure('logging', true);
ORM::configure('return_result_sets', true);