<?php
require_once 'models/User.php';

class AuthController {
    private $db;
    private $userModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $address = trim($_POST['address']);
            $errors = [];

            if (empty($name) || empty($email) || empty($password)) $errors[] = "All fields are required.";
            if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";

            if (empty($errors)) {
                if ($this->userModel->register($name, $email, $password, $address)) {
                    $_SESSION['flash'] = "Registration successful! Please login.";
                    header("Location: index.php?action=login");
                    exit;
                } else {
                    $errors[] = "Email already exists.";
                }
            }
            include 'views/auth/register.php';
        } else {
            include 'views/auth/register.php';
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user = $this->userModel->login($_POST['email'], $_POST['password']);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                if (isset($_POST['remember_me'])) {
                    $token = bin2hex(random_bytes(32));
                    $token_hash = password_hash($token, PASSWORD_BCRYPT);
                    $this->userModel->updateRememberToken($user['id'], $token_hash);

                    setcookie('remember_token', $user['id'] . ':' . $token, time() + (86400 * 30), '/');
                }

                if ($user['role'] === 'admin') {
                    header("Location: index.php?action=admin_dashboard");
                } else {
                    header("Location: index.php?action=browse");
                }
                exit;
            } else {
                $error = "Invalid credentials.";
                include 'views/auth/login.php';
            }
        } else {
            include 'views/auth/login.php';
        }
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->userModel->updateRememberToken($_SESSION['user_id'], NULL);
        }
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        header("Location: index.php?action=login");
        exit;
    }

    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $address = trim($_POST['address']);
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];

            $this->userModel->updateProfile($user['id'], $name, $email, $address);
            $_SESSION['name'] = $name;

            if (!empty($new_password)) {
                if (password_verify($current_password, $user['password_hash'])) {
                    $this->userModel->updatePassword($user['id'], $new_password);
                    $_SESSION['flash'] = "Profile and password updated successfully!";
                } else {
                    $_SESSION['flash'] = "Profile updated. Error: Current password was incorrect, password not changed.";
                }
            } else {
                $_SESSION['flash'] = "Profile updated successfully!";
            }

            header("Location: index.php?action=profile");
            exit;
        }

        include 'views/auth/profile.php';
    }
}
?>