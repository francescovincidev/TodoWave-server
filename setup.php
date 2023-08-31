<?php
require_once __DIR__ . '/includes/CORS.php';
require_once __DIR__ . '/includes/Db.php';

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Todos.php';


$method = $_SERVER['REQUEST_METHOD'];
