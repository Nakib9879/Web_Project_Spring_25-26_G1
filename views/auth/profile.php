<h2>Profile</h2>

<?php if (!empty($success)) echo "<p>$success</p>"; ?>

<form method="POST">
<input name="name" value="<?= $_SESSION['user']['name'] ?>"><br>
<input name="email" value="<?= $_SESSION['user']['email'] ?>"><br>
<input name="address" value="<?= $_SESSION['user']['delivery_address'] ?>"><br>

<button type="submit">Update</button>
</form>

<a href="/public/index.php?page=logout">Logout</a>