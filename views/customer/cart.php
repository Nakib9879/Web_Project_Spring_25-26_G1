<?php include 'views/layout/header.php'; ?>
    <div class="container">
        <h2>Your Cart</h2>
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="card"><p>Your cart is empty. <a href="index.php?action=browse">Browse the menu.</a></p></div>
        <?php else: ?>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">

                <div class="card" style="flex: 2; min-width: 300px;">
                    <table style="width: 100%;">
                        <thead>
                        <tr><th>Item ID</th><th>Quantity</th><th>Unit Price</th><th>Subtotal</th><th style="text-align: center;">Action</th></tr>
                        </thead>
                        <tbody>
                        <?php
                        $sub_total = 0;
                        foreach($_SESSION['cart'] as $item_id => $details):
                            $line_total = $details['quantity'] * $details['price'];
                            $sub_total += $line_total;
                            ?>
                            <tr id="row-<?php echo $item_id; ?>">
                                <td>#<?php echo $item_id; ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <button onclick="updateCart(<?php echo $item_id; ?>, 'decrease')" class="btn" style="padding: 2px 8px; background: #95a5a6;">-</button>
                                        <span id="qty-<?php echo $item_id; ?>" style="font-weight: bold; min-width: 20px; text-align: center;"><?php echo $details['quantity']; ?></span>
                                        <button onclick="updateCart(<?php echo $item_id; ?>, 'increase')" class="btn" style="padding: 2px 8px;">+</button>
                                    </div>
                                </td>
                                <td>$<?php echo number_format($details['price'], 2); ?></td>
                                <td id="line-<?php echo $item_id; ?>"><strong>$<?php echo number_format($line_total, 2); ?></strong></td>
                                <td style="text-align: center;">
                                    <button onclick="removeCart(<?php echo $item_id; ?>)" class="status" style="background: var(--danger); border: none; cursor: pointer; padding: 6px 12px;">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php
                        $discount = $sub_total * 0.10;
                        $grand_total = $sub_total - $discount;
                        ?>
                        </tbody>
                        <tfoot style="border-top: 2px solid #eee;">
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Subtotal:</strong></td>
                            <td colspan="2">$<span id="sub-total"><?php echo number_format($sub_total, 2); ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right; color: var(--success);"><strong>Member Discount (10%):</strong></td>
                            <td colspan="2" style="color: var(--success);">-$<span id="discount-display"><?php echo number_format($discount, 2); ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right; font-size: 1.2em;"><strong>Grand Total:</strong></td>
                            <td colspan="2" style="font-size: 1.2em; color: var(--primary);"><strong>$<span id="grand-total"><?php echo number_format($grand_total, 2); ?></span></strong></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="card" style="flex: 1; min-width: 300px; height: fit-content;">
                    <h3>Checkout</h3>
                    <form action="index.php?action=checkout" method="POST">
                        <div class="form-group">
                            <label>Delivery Address</label>
                            <textarea name="delivery_address" rows="3" required><?php echo htmlspecialchars($user['delivery_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Payment Method</label>
                            <div style="display: flex; gap: 15px; margin-top: 10px;">
                                <label style="font-weight: normal;"><input type="radio" name="payment_method" value="Cash" required> Cash on Delivery</label>
                                <label style="font-weight: normal;"><input type="radio" name="payment_method" value="Card" required> Credit Card</label>
                            </div>
                        </div>
                        <button type="submit" class="btn" style="width: 100%; background: var(--success); font-size: 1.1em; padding: 12px;">Place Order</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateCart(itemId, actionType) {
            let formData = new FormData();
            formData.append('item_id', itemId);
            formData.append('update_action', actionType);

            fetch('index.php?action=api/cart/update', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    document.getElementById('qty-' + itemId).innerText = data.quantity;
                    document.getElementById('line-' + itemId).innerHTML = '<strong>$' + data.line_total + '</strong>';
                    document.getElementById('sub-total').innerText = data.sub_total;
                    document.getElementById('discount-display').innerText = data.discount;
                    document.getElementById('grand-total').innerText = data.grand_total;
                    document.getElementById('cart-count').innerText = data.cart_count;
                });
        }

        function removeCart(itemId) {
            let formData = new FormData();
            formData.append('item_id', itemId);

            fetch('index.php?action=api/cart/remove', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    document.getElementById('row-' + itemId).remove();
                    document.getElementById('sub-total').innerText = data.sub_total;
                    document.getElementById('discount-display').innerText = data.discount;
                    document.getElementById('grand-total').innerText = data.grand_total;
                    document.getElementById('cart-count').innerText = data.cart_count;
                    if(data.cart_count === 0) location.reload();
                });
        }
    </script>
<?php include 'views/layout/footer.php'; ?>