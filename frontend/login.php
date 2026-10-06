<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
	<h2>Login</h2>
	<form method="POST" action="loginRequest.php">
		<input type="email" name="email" placeholder="Email" required><br>
		<input type="password" name="password" placeholder="Password" required><br>
		<button type="submit">Log In</button>
	</form>
	<?php if (isset($_GET['error'])): ?>
		<p><?php echo $_GET['error'] === 'server' ? 'Server unavailable. Try again later.' : 'Invalid credentials.'; ?></p>
	<?php endif; ?>
	<?php if (isset($_GET['registered'])): ?>
		<p>Account created. Please log in.</p>
	<?php endif; ?>
	<a href="register.php">Register</a>
</body>
</html>
