<?php
class Menu {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllCategories() {
        return $this->conn->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllItems() {
        $sql = "SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN categories c ON m.category_id = c.id ORDER BY m.created_at DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItemById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateItem($id, $category_id, $name, $desc, $price, $image_path, $is_available) {
        $sql = "UPDATE menu_items SET category_id = ?, name = ?, description = ?, price = ?, image_path = ?, is_available = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$category_id, $name, $desc, $price, $image_path, $is_available, $id]);
    }

    public function addItem($category_id, $name, $desc, $price, $image_path, $is_available) {
        $sql = "INSERT INTO menu_items (category_id, name, description, price, image_path, is_available) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$category_id, $name, $desc, $price, $image_path, $is_available]);
    }

    public function toggleAvailability($id) {
        $stmt = $this->conn->prepare("SELECT is_available FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();

        $new_status = $current ? 0 : 1;
        $update = $this->conn->prepare("UPDATE menu_items SET is_available = ? WHERE id = ?");
        $update->execute([$new_status, $id]);

        return $new_status;
    }

    public function addCategory($name) {
        $stmt = $this->conn->prepare("INSERT INTO categories (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function deleteCategory($id) {
        $check = $this->conn->prepare("SELECT COUNT(*) FROM menu_items WHERE category_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            return false;
        }

        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getDashboardStats() {
        $stats = [];
        $stats['total_categories'] = $this->conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $stats['total_items'] = $this->conn->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
        $stats['inactive_items'] = $this->conn->query("SELECT COUNT(*) FROM menu_items WHERE is_available = 0")->fetchColumn();
        $stats['total_orders'] = $this->conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();

        $stats['total_revenue'] = $this->conn->query("SELECT SUM(total_amount) FROM orders")->fetchColumn() ?: 0;

        return $stats;
    }

    public function searchAvailableItems($keyword = '', $category = '', $min_price = '', $max_price = '', $sort = '') {
        $sql = "SELECT * FROM menu_items WHERE is_available = 1";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $keyword_param = "%$keyword%";
            $params[] = $keyword_param;
            $params[] = $keyword_param;
        }

        if (!empty($category)) {
            $sql .= " AND category_id = ?";
            $params[] = $category;
        }

        if ($min_price !== '') {
            $sql .= " AND price >= ?";
            $params[] = $min_price;
        }

        if ($max_price !== '') {
            $sql .= " AND price <= ?";
            $params[] = $max_price;
        }

        if ($sort === 'price_asc') {
            $sql .= " ORDER BY price ASC";
        } elseif ($sort === 'price_desc') {
            $sql .= " ORDER BY price DESC";
        } elseif ($sort === 'name_asc') {
            $sql .= " ORDER BY name ASC";
        } elseif ($sort === 'name_desc') {
            $sql .= " ORDER BY name DESC";
        } else {
            $sql .= " ORDER BY created_at DESC";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteItem($id) {
        return $this->conn->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$id]);
    }

    public function updateCategory($id, $name) {
        $stmt = $this->conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }
}
?>