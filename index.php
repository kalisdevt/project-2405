<?php
session_start();
require_once 'db.php';

$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Маршрутизация API
if (strpos($route, 'api/form') === 0) {
    header('Content-Type: application/json');
    require_once 'api.php';
    exit;
}

require_once 'index.html';