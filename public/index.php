<?php
require_once __DIR__ . '/../controllers/AuthController.php';

$page = $_GET['page'] ?? 'login';

$auth = new AuthController();

switch ($page) {
    case 'register':
        $auth->register();
        break;

    case 'login':
        $auth->login();
        break;

    case 'profile':
        $auth->profile();
        break;

    case 'logout':
        $auth->logout();
        break;

    default:
        echo "404 Not Found";
}