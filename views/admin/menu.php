<?php
/** @var array $categories
 * @var array $items
 * @var array|null $edit_item
 */
include 'views/layout/header.php';
?>
    <div class="admin-container">
        <h2>Manage Menu Items</h2>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert error"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <?php $is_editing = isset($edit_item) && $edit_item; ?>

        <div class="card" style="margin-bottom: 30px; max-width: 900px;">
            <h3><?php echo $is_editing ? 'Edit Menu Item' : 'Add New Menu Item'; ?></h3>

            <form action="index.php?action=admin_menu" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action_type" value="<?php echo $is_editing ? 'edit_item' : 'add_item'; ?>">

                <?php if($is_editing): ?>
                    <input type="hidden" name="item_id" value="<?php echo $edit_item['id']; ?>">
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_item['image_path']); ?>">
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($is_editing && $edit_item['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Item Name</label>
                        <input type="text" name="name" value="<?php echo $is_editing ? htmlspecialchars($edit_item['name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" step="0.01" name="price" value="<?php echo $is_editing ? $edit_item['price'] : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Image (JPEG/PNG, Max 2MB)</label>
                        <input type="file" name="image" accept="image/png, image/jpeg" <?php echo $is_editing ? '' : 'required'; ?> style="padding: 7px;">
                        <?php if($is_editing): ?>
                            <small style="color: #666; display: block; margin-top: 5px;">Leave blank to keep current image.</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label>Description</label>
                    <textarea name="description" rows="2"><?php echo $is_editing ? htmlspecialchars($edit_item['description']) : ''; ?></textarea>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                    <input type="checkbox" name="is_available" value="1" <?php echo (!$is_editing || $edit_item['is_available']) ? 'checked' : ''; ?> style="width: auto;">
                    <label style="margin: 0; font-weight: normal;">Item is Active & Available</label>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <?php echo $is_editing ? 'Update Menu Item' : 'Add Menu Item'; ?>
                    </button>
                    <?php if($is_editing): ?>
                        <a href="index.php?action=admin_menu" class="btn" style="background-color: #95a5a6; text-align: center; line-height: 20px;">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Current Menu</h3>
            <table>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php foreach($items as $item): ?>
                    <tr>
                        <td><img src="public/uploads/menu/<?php echo $item['image_path']; ?>" width="50" style="border-radius:4px;"></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <button id="badge-<?php echo $item['id']; ?>"
                                    onclick="toggleAvailability(<?php echo $item['id']; ?>)"
                                    class="status"
                                    style="border:none; cursor:pointer; background-color: <?php echo $item['is_available'] ? '#27ae60' : '#e74c3c'; ?>;">
                                <?php echo $item['is_available'] ? 'Active' : 'Inactive'; ?>
                            </button>
                        </td>
                        <td>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <a href="index.php?action=admin_menu&edit_id=<?php echo $item['id']; ?>" class="status" style="background: var(--primary); text-decoration: none;">Edit</a>
                                <form action="index.php?action=admin_menu" method="POST" onsubmit="return confirm('Delete this item?');" style="margin: 0;">
                                    <input type="hidden" name="action_type" value="delete_item">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="status" style="background: var(--danger); border: none; cursor: pointer;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <script>
        function toggleAvailability(itemId) {
            fetch('index.php?action=api/menu-items/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + itemId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        let badge = document.getElementById('badge-' + itemId);
                        badge.style.backgroundColor = data.is_available ? 'var(--success)' : 'var(--danger)';
                        badge.innerText = data.is_available ? 'Active' : 'Inactive';
                    } else {
                        alert('Error updating status.');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
<?php include 'views/layout/footer.php'; ?>