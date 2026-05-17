<?php include 'views/layout/header.php'; ?>
    <div class="container">
        <h2>My Orders</h2>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert success"><?php echo $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="card"><p>You haven't placed any orders yet. <a href="index.php?action=browse">Browse menu</a></p></div>
        <?php else: ?>
            <div class="grid-cards" style="grid-template-columns: 1fr;">
                <?php foreach($orders as $order): ?>
                    <div class="card" style="display: flex; flex-direction: column; gap: 15px;">

                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                            <div>
                                <h3 style="margin: 0 0 5px 0;">Order #<?php echo $order['id']; ?></h3>
                                <small style="color: #666;"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div style="text-align: right;">
                                <h3 style="margin: 0 0 5px 0;">$<?php echo number_format($order['total_amount'], 2); ?></h3>
                                <?php
                                $badgeColor = '#f39c12'; // Pending
                                if($order['status'] == 'Preparing') $badgeColor = '#3498db';
                                if($order['status'] == 'Out for Delivery') $badgeColor = '#9b59b6';
                                if($order['status'] == 'Delivered') $badgeColor = '#27ae60';
                                ?>
                                <span id="status-badge-<?php echo $order['id']; ?>" class="status" data-status="<?php echo $order['status']; ?>" style="background: <?php echo $badgeColor; ?>; display: inline-block;">
                                <?php echo $order['status']; ?>
                            </span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #666;"><strong><?php echo count($order['items']); ?></strong> unique items</span>

                            <button onclick="toggleItems(<?php echo $order['id']; ?>)" class="btn" style="background: #95a5a6; padding: 6px 12px; font-size: 13px;">View Details ▼</button>
                        </div>

                        <div id="items-<?php echo $order['id']; ?>" style="display: none; margin-top: 10px; background: #f8f9fa; padding: 15px; border-radius: 6px;">
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach($order['items'] as $item): ?>
                                    <li style="display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">
                                        <span><strong><?php echo $item['quantity']; ?>x</strong> <?php echo htmlspecialchars($item['name']); ?></span>
                                        <span>$<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div style="margin-top: 15px; font-size: 13px; color: #666;">
                                <strong>Delivery Address:</strong><br>
                                <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                            </div>

                            <?php if($order['status'] === 'Pending'): ?>
                                <div style="margin-top: 15px; text-align: right;">
                                    <button onclick="cancelOrder(<?php echo $order['id']; ?>)" class="btn" style="background: var(--danger); font-size: 12px; padding: 5px 10px;">Cancel Order</button>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleItems(orderId) {
            let el = document.getElementById('items-' + orderId);
            if(el.style.display === 'none') {
                el.style.display = 'block';
            } else {
                el.style.display = 'none';
            }
        }

        function cancelOrder(orderId) {
            if(!confirm("Are you sure you want to cancel this order? This cannot be undone.")) return;

            let formData = new FormData();
            formData.append('order_id', orderId);

            fetch('index.php?action=api/orders/cancel', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }

        function pollOrderStatus() {
            let badges = document.querySelectorAll('[id^="status-badge-"]');

            badges.forEach(badge => {
                let currentStatus = badge.getAttribute('data-status');
                let orderId = badge.id.replace('status-badge-', '');

                if (currentStatus !== 'Delivered') {
                    fetch('index.php?action=api/orders/status&id=' + orderId)
                        .then(res => res.json())
                        .then(data => {
                            if(data.status && data.status !== currentStatus) {
                                badge.setAttribute('data-status', data.status);
                                badge.innerText = data.status;

                                if(data.status === 'Preparing') badge.style.backgroundColor = '#3498db';
                                if(data.status === 'Out for Delivery') badge.style.backgroundColor = '#9b59b6';
                                if(data.status === 'Delivered') badge.style.backgroundColor = '#27ae60';
                            }
                        })
                        .catch(err => console.error(err));
                }
            });
        }

        setInterval(pollOrderStatus, 10000);
    </script>

<?php include 'views/layout/footer.php'; ?>