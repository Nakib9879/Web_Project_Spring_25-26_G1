<?php
require_once __DIR__ . '/../models/User.php';

class Auth {

    public static function user() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['user'] ?? null;
    }

    public static function check() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user']);
    }

    public static function login($user) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['user'] = $user;
    }

    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        setcookie("remember_token", "", time() - 3600, "/");
    }

    // AUTO LOGIN FROM COOKIE
    public static function checkRemember() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['user'])) return;

        if (!empty($_COOKIE['remember_token'])) {
            $userModel = new User();
            $hash = hash('sha256', $_COOKIE['remember_token']);
            $user = $userModel->findByToken($hash);

            if ($user) {
                $_SESSION['user'] = $user;
            }
        }
    }
}