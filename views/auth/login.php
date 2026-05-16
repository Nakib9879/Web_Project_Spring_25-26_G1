<?php include 'views/layout/header.php'; ?>
    <div class="card login-card" style="max-width: 400px; margin: 40px auto;">
        <h2>Login</h2>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form action="index.php?action=login" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="remember_me" id="remember" style="width: auto;">
                <label for="remember" style="margin: 0; font-weight: normal;">Remember Me for 30 days</label>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>

        <p style="text-align: center; margin-top: 15px;">
            Don't have an account? <a href="index.php?action=register">Register here</a>
        </p>
    </div>
<?php include 'views/layout/footer.php'; ?>