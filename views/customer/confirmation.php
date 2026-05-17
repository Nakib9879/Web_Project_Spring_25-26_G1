<?php include 'views/layout/header.php'; ?>
    <div class="container" style="text-align: center; max-width: 600px; margin: 40px auto;">
        <div class="card" style="padding: 40px;">
            <div style="font-size: 60px; color: var(--success); margin-bottom: 20px;">✅</div>
            <h2 style="color: var(--primary);">Order Placed Successfully!</h2>
            <p style="font-size: 18px; color: #666;">Thank you for your purchase.</p>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 30px 0; border: 1px dashed #ccc;">
                <h3 style="margin: 0 0 10px 0;">Order Reference</h3>
                <h1 style="margin: 0; color: var(--secondary);">#<?php echo htmlspecialchars($_GET['id'] ?? 'UNKNOWN'); ?></h1>
            </div>

            <p style="margin-bottom: 30px;">Your kitchen is now preparing your food. You can track its live status on your orders dashboard.</p>

            <a href="index.php?action=my_orders" class="btn btn-primary" style="padding: 12px 25px; font-size: 16px;">Track My Order</a>
        </div>
    </div>
<?php include 'views/layout/footer.php'; ?>