<?php include 'views/layout/header.php'; ?>
    <div class="admin-container" style="max-width: 800px; margin: 0 auto;">
        <h2>Manage Categories</h2>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert error"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 30px;">
            <h3>Add New Category</h3>
            <form action="index.php?action=admin_categories" method="POST" style="display: flex; gap: 15px; align-items: center;">
                <input type="hidden" name="action_type" value="add_category">
                <input type="text" name="category_name" placeholder="Enter Category Name" required style="flex: 1;">
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Add Category</button>
            </form>
        </div>

        <div class="card">
            <h3>Current Categories</h3>
            <table style="width: 100%;">
                <tr>
                    <th>Category Name</th>
                    <th style="text-align: right;">Action</th>
                </tr>
                <?php foreach($categories as $cat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat['name']); ?></td>
                        <td style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 10px; align-items: center;">
                                <form action="index.php?action=admin_categories" method="POST" id="edit-form-<?php echo $cat['id']; ?>" style="margin: 0;">
                                    <input type="hidden" name="action_type" value="edit_category">
                                    <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                    <input type="hidden" name="category_name" id="edit-name-<?php echo $cat['id']; ?>">
                                    <button type="button" class="status" onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>')" style="background: var(--primary); border: none; cursor: pointer; padding: 8px 15px;">Edit</button>
                                </form>

                                <form action="index.php?action=admin_categories" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action_type" value="delete_category">
                                    <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" class="status" style="background: var(--danger); border: none; cursor: pointer; padding: 8px 15px;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
<?php include 'views/layout/footer.php'; ?>