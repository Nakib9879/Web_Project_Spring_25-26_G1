<?php
class Order {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createOrder($user_id, $total, $address, $payment_method, $cart_items) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, payment_method) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $total, $address, $payment_method]);
            $order_id = $this->conn->lastInsertId();

            $item_stmt = $this->conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES (?, ?, ?, ?)");

            foreach ($cart_items as $item_id => $details) {
                $item_stmt->execute([$order_id, $item_id, $details['quantity'], $details['price']]);
            }

            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getUserOrders($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllOrders($status = null, $date = null) {
        $query = "SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1";
        $params = [];

        if (!empty($status)) {
            $query .= " AND o.status = ?";
            $params[] = $status;
        }

        if (!empty($date)) {
            $query .= " AND DATE(o.created_at) = ?";
            $params[] = $date;
        }

        $query .= " ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentSalesData() {
        $sql = "SELECT DATE(created_at) as order_date, SUM(total_amount) as daily_total 
                FROM orders 
                GROUP BY DATE(created_at) 
                ORDER BY DATE(created_at) DESC 
                LIMIT 7";
        $stmt = $this->conn->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_reverse($data);
    }

    public function getOrderItems($order_id) {
        $sql = "SELECT oi.quantity, oi.unit_price, m.name, m.image_path, c.name as category_name
                FROM order_items oi
                JOIN menu_items m ON oi.menu_item_id = m.id
                LEFT JOIN categories c ON m.category_id = c.id
                WHERE oi.order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($order_id, $status) {
        $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $order_id]);
    }

    public function cancelPendingOrder($order_id, $user_id) {
        $stmt = $this->conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);

        if ($stmt->fetchColumn() === 'Pending') {
            $this->conn->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$order_id]);
            $this->conn->prepare("DELETE FROM orders WHERE id = ?")->execute([$order_id]);
            return true;
        }
        return false;
    }

    public function getOrderStatus($order_id) {
        $stmt = $this->conn->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        return $stmt->fetchColumn();
    }
}
?>