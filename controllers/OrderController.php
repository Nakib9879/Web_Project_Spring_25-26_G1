<?php
require_once 'models/Order.php';

class OrderController {
    private $db;
    private $orderModel;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new Order($this->db);
    }

    public function myOrders() {
        if (!isset($_SESSION['user_id'])) exit;
        $orders = $this->orderModel->getUserOrders($_SESSION['user_id']);

        foreach ($orders as $key => $order) {
            $orders[$key]['items'] = $this->orderModel->getOrderItems($order['id']);
        }
        include 'views/customer/orders.php';
    }

    public function cancelOrder() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $order_id = $_POST['order_id'];
            if ($this->orderModel->cancelPendingOrder($order_id, $_SESSION['user_id'])) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Order cannot be cancelled. It may already be preparing."]);
            }
            exit;
        }
    }

    public function apiStatus() {
        header('Content-Type: application/json');
        if (isset($_GET['id'])) {
            $status = $this->orderModel->getOrderStatus($_GET['id']);
            echo json_encode(["status" => $status]);
        }
        exit;
    }

    public function adminQueue() {
        if ($_SESSION['role'] !== 'admin') exit;

        $filter_status = isset($_GET['status']) ? $_GET['status'] : null;
        $filter_date = isset($_GET['date']) ? $_GET['date'] : null;

        $orders = $this->orderModel->getAllOrders($filter_status, $filter_date);

        foreach ($orders as $key => $order) {
            $orders[$key]['items'] = $this->orderModel->getOrderItems($order['id']);
        }

        include 'views/admin/orders.php';
    }

    public function updateStatus() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'PUT' && $_SESSION['role'] === 'admin') {
            parse_str(file_get_contents("php://input"), $put_vars);

            $order_id = $put_vars['id'];
            $new_status = $put_vars['status'];

            $current_status = $this->orderModel->getOrderStatus($order_id);

            $valid_transition = false;
            if ($current_status === 'Pending' && $new_status === 'Preparing') $valid_transition = true;
            if ($current_status === 'Preparing' && $new_status === 'Out for Delivery') $valid_transition = true;
            if ($current_status === 'Out for Delivery' && $new_status === 'Delivered') $valid_transition = true;

            if ($valid_transition) {
                $this->orderModel->updateStatus($order_id, $new_status);
                echo json_encode(["ok" => true, "status" => $new_status]);
            } else {
                echo json_encode(["ok" => false, "message" => "Invalid transition! Orders must follow: Pending -> Preparing -> Out for Delivery -> Delivered."]);
            }
            exit;
        }
    }
}
?>