<?php include 'views/layout/header.php'; ?>
    <div class="card" style="max-width: 600px; margin: 20px auto;">
        <h2>My Profile</h2>
        <form action="index.php?action=profile" method="POST">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Delivery Address</label>
                <textarea name="address" rows="3" required><?php echo htmlspecialchars($user['delivery_address']); ?></textarea>
            </div>
            <hr style="margin: 20px 0;">
            <h3>Change Password</h3>
            <div class="form-group">
                <label>Current Password (required to save changes)</label>
                <input type="password" name="current_password">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password">
            </div>
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
<?php include 'views/layout/footer.php'; ?>