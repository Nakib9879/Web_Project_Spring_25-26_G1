<form method="POST">
<h2>Login</h2>

<input name="email"><br>
<input name="password" type="password"><br>

<label>
<input type="checkbox" name="remember"> Remember Me
</label>

<button type="submit">Login</button>

<?php if (!empty($error)) echo "<p>$error</p>"; ?>
</form>