<?php
require_once 'models/Menu.php';
require_once 'models/Order.php';
require_once 'models/User.php';

class CartController {
    private $db;
    private $menuModel;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->menuModel = new Menu($this->db);
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function browse() {
        $categories = $this->menuModel->getAllCategories();
        $items = $this->menuModel->searchAvailableItems();
        include 'views/customer/browse.php';
    }

    public function apiSearch() {
        header('Content-Type: application/json');

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $cat = isset($_GET['category']) ? $_GET['category'] : '';
        $min = isset($_GET['min_price']) ? $_GET['min_price'] : '';
        $max = isset($_GET['max_price']) ? $_GET['max_price'] : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';

        echo json_encode($this->menuModel->searchAvailableItems($q, $cat, $min, $max, $sort));
        exit;
    }

    public function addToCart() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $item_id = $_POST['item_id'];
            $price = $_POST['price'];

            if (isset($_SESSION['cart'][$item_id])) {
                $_SESSION['cart'][$item_id]['quantity']++;
            } else {
                $_SESSION['cart'][$item_id] = ['quantity' => 1, 'price' => $price];
            }

            echo json_encode(["status" => "success", "total_cart_count" => array_sum(array_column($_SESSION['cart'], 'quantity'))]);
            exit;
        }
    }

    public function updateCart() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $item_id = $_POST['item_id'];
            $action = $_POST['update_action'];

            if (isset($_SESSION['cart'][$item_id])) {
                if ($action == 'increase') {
                    $_SESSION['cart'][$item_id]['quantity']++;
                } elseif ($action == 'decrease' && $_SESSION['cart'][$item_id]['quantity'] > 1) {
                    $_SESSION['cart'][$item_id]['quantity']--;
                }
            }
            $this->sendCartTotals($item_id);
        }
    }

    public function removeCart() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $item_id = $_POST['item_id'];
            if (isset($_SESSION['cart'][$item_id])) unset($_SESSION['cart'][$item_id]);
            $this->sendCartTotals();
        }
    }

    public function applyDiscount() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $code = strtoupper(trim($_POST['code']));
            if ($code === 'SAVE10') {
                $_SESSION['discount'] = 0.10;
                echo json_encode(["status" => "success", "message" => "10% Discount Applied!"]);
            } else {
                $_SESSION['discount'] = 0;
                echo json_encode(["status" => "error", "message" => "Invalid Promo Code."]);
            }
            $this->sendCartTotals();
        }
    }

    private function sendCartTotals($item_id = null) {
        $sub_total = 0; $line_total = 0; $cart_count = 0;
        foreach ($_SESSION['cart'] as $id => $item) {
            $sub = $item['quantity'] * $item['price'];
            $sub_total += $sub;
            $cart_count += $item['quantity'];
            if ($id == $item_id) $line_total = $sub;
        }

        $discount_amount = $sub_total * 0.10;
        $grand_total = $sub_total - $discount_amount;

        $qty = isset($_SESSION['cart'][$item_id]) ? $_SESSION['cart'][$item_id]['quantity'] : 0;
        echo json_encode([
            "cart_count" => $cart_count,
            "line_total" => number_format($line_total, 2),
            "sub_total" => number_format($sub_total, 2),
            "discount" => number_format($discount_amount, 2),
            "grand_total" => number_format($grand_total, 2),
            "quantity" => $qty
        ]);
        exit;
    }

    public function viewCart() {
        $userModel = new User($this->db);
        $user = $userModel->getUserById($_SESSION['user_id']);
        include 'views/customer/cart.php';
    }

    public function checkout() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['cart'])) {
            $orderModel = new Order($this->db);
            $sub_total = 0;
            foreach ($_SESSION['cart'] as $item) $sub_total += $item['quantity'] * $item['price'];

            $final_total = $sub_total * 0.90;

            $order_id = $orderModel->createOrder($_SESSION['user_id'], $final_total, $_POST['delivery_address'], $_POST['payment_method'], $_SESSION['cart']);

            if ($order_id) {
                $_SESSION['cart'] = [];
                // Reroute specifically to the confirmation view
                header("Location: index.php?action=confirmation&id=" . $order_id);
                exit;
            }
        }
    }
}
?>