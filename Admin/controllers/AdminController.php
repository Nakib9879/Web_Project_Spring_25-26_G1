<?php
require_once 'models/Menu.php';
require_once 'models/Order.php';

class AdminController {
    private $db;
    private $menuModel;

    public function __construct() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php?action=login");
            exit;
        }
        $this->db = (new Database())->getConnection();
        $this->menuModel = new Menu($this->db);
    }
    public function dashboard() {
        $stats = $this->menuModel->getDashboardStats();

        $orderModel = new Order($this->db);
        $salesData = $orderModel->getRecentSalesData();

        $chartDates = [];
        $chartTotals = [];
        foreach ($salesData as $row) {
            $chartDates[] = date('M d', strtotime($row['order_date']));
            $chartTotals[] = $row['daily_total'];
        }

        include 'views/admin/dashboard.php';
    }

    public function manageCategories() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['action_type']) && $_POST['action_type'] === 'edit_category') {
                $name = trim($_POST['category_name']);
                if (!empty($name) && $this->menuModel->updateCategory($_POST['category_id'], $name)) {
                    $_SESSION['flash'] = "Category updated successfully.";
                }
                header("Location: index.php?action=admin_categories");
                exit;
            }

            if (isset($_POST['action_type']) && $_POST['action_type'] === 'add_category') {
                $name = trim($_POST['category_name']);
                if (!empty($name) && $this->menuModel->addCategory($name)) {
                    $_SESSION['flash'] = "Category added successfully.";
                }
                header("Location: index.php?action=admin_categories");
                exit;
            }

            if (isset($_POST['action_type']) && $_POST['action_type'] === 'delete_category') {
                $cat_id = $_POST['category_id'];
                if ($this->menuModel->deleteCategory($cat_id)) {
                    $_SESSION['flash'] = "Category deleted.";
                } else {
                    $_SESSION['flash_error'] = "Cannot delete category: Menu items are still attached to it.";
                }
                header("Location: index.php?action=admin_categories");
                exit;
            }
        }
        $categories = $this->menuModel->getAllCategories();
        include 'views/admin/categories.php';
    }

    public function manageMenu() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (isset($_POST['action_type']) && $_POST['action_type'] === 'add_item') {
                $name = $_POST['name'];
                $price = $_POST['price'];
                $category_id = $_POST['category_id'];
                $desc = $_POST['description'];

                $is_available = isset($_POST['is_available']) ? 1 : 0;

                $image_path = 'default.png';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed_mimes = ['image/jpeg', 'image/png'];
                    $max_size = 2 * 1024 * 1024;
                    $file_mime = mime_content_type($_FILES['image']['tmp_name']);
                    $file_size = $_FILES['image']['size'];

                    if (in_array($file_mime, $allowed_mimes) && $file_size <= $max_size) {
                        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $image_path = uniqid() . '.' . $ext;
                        move_uploaded_file($_FILES['image']['tmp_name'], 'public/uploads/menu/' . $image_path); //
                    } else {
                        $_SESSION['flash_error'] = "Image must be JPEG/PNG and under 2MB."; //
                        header("Location: index.php?action=admin_menu");
                        exit;
                    }
                }

                if ($this->menuModel->addItem($category_id, $name, $desc, $price, $image_path, $is_available)) {
                    $_SESSION['flash'] = "Menu item added successfully!";
                    header("Location: index.php?action=admin_menu");
                    exit;
                }
            }

            if (isset($_POST['action_type']) && $_POST['action_type'] === 'edit_item') {
                $id = $_POST['item_id'];
                $name = $_POST['name'];
                $price = $_POST['price'];
                $category_id = $_POST['category_id'];
                $desc = $_POST['description'];
                $image_path = $_POST['existing_image'];

                $is_available = isset($_POST['is_available']) ? 1 : 0;

                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed_mimes = ['image/jpeg', 'image/png'];
                    $max_size = 2 * 1024 * 1024;
                    $file_mime = mime_content_type($_FILES['image']['tmp_name']);
                    $file_size = $_FILES['image']['size'];

                    if (in_array($file_mime, $allowed_mimes) && $file_size <= $max_size) {
                        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $image_path = uniqid() . '.' . $ext;
                        move_uploaded_file($_FILES['image']['tmp_name'], 'public/uploads/menu/' . $image_path); //
                    } else {
                        $_SESSION['flash_error'] = "Image must be JPEG/PNG and under 2MB."; //
                        header("Location: index.php?action=admin_menu&edit_id=" . $id);
                        exit;
                    }
                }

                if ($this->menuModel->updateItem($id, $category_id, $name, $desc, $price, $image_path, $is_available)) {
                    $_SESSION['flash'] = "Menu item updated successfully!";
                    header("Location: index.php?action=admin_menu");
                    exit;
                }
            }
        }

        $edit_item = null;
        if (isset($_GET['edit_id'])) {
            $edit_item = $this->menuModel->getItemById($_GET['edit_id']);
        }

        $categories = $this->menuModel->getAllCategories();
        $items = $this->menuModel->getAllItems();
        include 'views/admin/menu.php';

        if (isset($_POST['action_type']) && $_POST['action_type'] === 'delete_item') {
            $item = $this->menuModel->getItemById($_POST['item_id']);
            if ($item) {
                if ($item['image_path'] !== 'default.png' && file_exists('public/uploads/menu/' . $item['image_path'])) {
                    unlink('public/uploads/menu/' . $item['image_path']);
                }
                $this->menuModel->deleteItem($_POST['item_id']);
                $_SESSION['flash'] = "Menu item deleted successfully.";
            }
            header("Location: index.php?action=admin_menu");
            exit;
        }
    }

    public function toggleAvailability() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
            $new_status = $this->menuModel->toggleAvailability($_POST['id']);
            echo json_encode(["ok" => true, "is_available" => (bool)$new_status]);
            exit;
        }
        echo json_encode(["ok" => false]);
    }
}
?>