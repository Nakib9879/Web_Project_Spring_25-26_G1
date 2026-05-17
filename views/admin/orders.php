<?php include 'views/layout/header.php'; ?>

    <div class="admin-container">
        <h2>Order Queue</h2>

        <form method="GET" action="index.php" style="margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-end; background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <input type="hidden" name="action" value="admin_orders">

            <div style="display: flex; flex-direction: column;">
                <label style="font-size: 13px; font-weight: bold; color: #666; margin-bottom: 5px;">Filter by Status</label>
                <select name="status" onchange="this.form.submit()" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; min-width: 180px;">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php if(isset($_GET['status']) && $_GET['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Preparing" <?php if(isset($_GET['status']) && $_GET['status'] == 'Preparing') echo 'selected'; ?>>Preparing</option>
                    <option value="Out for Delivery" <?php if(isset($_GET['status']) && $_GET['status'] == 'Out for Delivery') echo 'selected'; ?>>Out for Delivery</option>
                    <option value="Delivered" <?php if(isset($_GET['status']) && $_GET['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                </select>
            </div>

            <div style="display: flex; flex-direction: column;">
                <label style="font-size: 13px; font-weight: bold; color: #666; margin-bottom: 5px;">Filter by Date</label>
                <input type="date" name="date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>" onchange="this.form.submit()" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; min-width: 160px;">
            </div>

            <div style="margin-left: auto;">
                <a href="index.php?action=admin_orders" class="btn" style="background: #95a5a6; text-decoration: none;">Clear Filters</a>
            </div>
        </form>

        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Order Details (Items)</th>
                <th>Total Amount</th>
                <th>Date</th>
                <th>Update Status</th>
            </tr>
            <?php if(!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                            <small style="color: #666;"><?php echo htmlspecialchars($order['delivery_address']); ?></small>
                        </td>
                        <td>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach($order['items'] as $item): ?>
                                    <li style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                                        <img src="public/uploads/menu/<?php echo htmlspecialchars($item['image_path']); ?>" width="35" height="35" style="border-radius: 4px; object-fit: cover;">
                                        <span>
                                            <strong><?php echo $item['quantity']; ?>x</strong> <?php echo htmlspecialchars($item['name']); ?>
                                            <br><small style="color: #7f8c8d;"><?php echo htmlspecialchars($item['category_name']); ?></small>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                        <td>
                            <select data-prev="<?php echo $order['status']; ?>"
                                    onchange="updateOrderStatus(this, <?php echo $order['id']; ?>)"
                                    <?php echo ($order['status'] == 'Delivered') ? 'disabled' : ''; ?>
                                    style="padding: 5px;">
                                <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Preparing" <?php echo $order['status'] == 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="Out for Delivery" <?php echo $order['status'] == 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center;">No orders found matching your filters.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <script>
        function updateOrderStatus(selectElement, orderId) {
            let previousValue = selectElement.getAttribute('data-prev');
            let newStatus = selectElement.value;

            fetch('index.php?action=api/orders/update', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + orderId + '&status=' + encodeURIComponent(newStatus)
            })
                .then(res => res.json())
                .then(data => {
                    if(data.ok) {
                        selectElement.setAttribute('data-prev', newStatus);
                        if(newStatus === 'Delivered') {
                            selectElement.disabled = true;
                        }
                    } else {
                        alert(data.message);
                        selectElement.value = previousValue;
                    }
                })
                .catch(err => {
                    alert("Network error.");
                    selectElement.value = previousValue;
                });
        }
    </script>

<?php include 'views/layout/footer.php'; ?>