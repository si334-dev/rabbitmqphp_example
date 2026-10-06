<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>
	<h2>Register</h2>
	<form method="POST" action="registerRequest.php">
		<input type="email" name="email" placeholder="Email" required><br>
		<input type ="password" name="password" placeholder="Password" required><br>
		<button type="submit">Register</button>
	</form>
	<?php if (isset($_GET['error'])): ?>
		<p><?php echo $_GET['error'] === 'server' ? 'Server unavailable. Try Again Later.' : 'Registration failed. Try again.'; ?></p>
	<?php endif; ?>
	<a href="login.php">Already have an account? Log In</a>
</body>
</html>
