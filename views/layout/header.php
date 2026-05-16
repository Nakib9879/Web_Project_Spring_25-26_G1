<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Food Order System</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<header>
    <nav>
        <div class="logo">🍔 FoodSystem</div>
        <div class="links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="index.php?action=admin_dashboard">Dashboard</a>
                    <a href="index.php?action=admin_categories">Categories</a>
                    <a href="index.php?action=admin_menu">Menus</a>
                    <a href="index.php?action=admin_orders">Orders</a>
                <?php else: ?>
                    <a href="index.php?action=browse">Menu</a>
                    <a href="index.php?action=cart">Cart (<span id="cart-count"><?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?></span>)</a>
                    <a href="index.php?action=my_orders">My Orders</a>
                    <a href="index.php?action=profile">Profile</a>
                <?php endif; ?>
                <a href="index.php?action=logout" class="btn-logout">Logout (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a>
            <?php else: ?>
                <a href="index.php?action=login">Login</a>
                <a href="index.php?action=register">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main class="container">
    <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert success"><?php echo $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
<?php endif; ?>