<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Auth.php';

class AuthController {

    // REGISTER
    public function register() {
        $user = new User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $address = trim($_POST['address']);

            $errors = [];

            if (!$name || !$email || !$password || !$address)
                $errors[] = "All fields required";

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $errors[] = "Invalid email";

            if (strlen($password) < 8)
                $errors[] = "Password must be at least 8 chars";

            if ($user->findByEmail($email))
                $errors[] = "Email already exists";

            if (empty($errors)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $user->create([$name, $email, $hash, $address]);

                header("Location: /login.php?success=1");
                exit;
            }
        }

        include __DIR__ . '/../views/auth/register.php';
    }

    // LOGIN
    public function login() {
        $user = new User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = $_POST['email'];
            $password = $_POST['password'];

            $data = $user->findByEmail($email);

            if ($data && password_verify($password, $data['password_hash'])) {

                session_start();
                $_SESSION['user'] = $data;

                // Remember Me
                if (!empty($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    $hash = hash('sha256', $token);

                    $user->saveToken($data['id'], $hash);
                    setcookie("remember_token", $token, time() + (86400 * 30), "/");
                }

                header("Location: /profile.php");
                exit;
            } else {
                $error = "Invalid credentials";
            }
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    // PROFILE UPDATE
    public function profile() {

        Auth::checkRemember();

        if (!Auth::check()) {
            header("Location: /login.php");
            exit;
        }

        $userModel = new User();
        $user = $_SESSION['user'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $name = $_POST['name'];
            $email = $_POST['email'];
            $address = $_POST['address'];

            $userModel->updateProfile($user['id'], $name, $email, $address);

            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['delivery_address'] = $address;

            $success = "Profile updated";
        }

        include __DIR__ . '/../views/auth/profile.php';
    }

    // LOGOUT
    public function logout() {
        Auth::logout();
        header("Location: /login.php");
    }
}