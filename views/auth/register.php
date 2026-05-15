<form method="POST">
<h2>Register</h2>

<input name="name" placeholder="Name"><br>
<input name="email" placeholder="Email"><br>
<input name="password" type="password" placeholder="Password"><br>
<input name="address" placeholder="Address"><br>

<button type="submit">Register</button>

<?php if (!empty($errors)) foreach($errors as $e) echo "<p>$e</p>"; ?>
</form>