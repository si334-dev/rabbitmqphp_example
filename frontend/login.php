<!DOCTYPE html>
<html>
<head><title>Login</title>
	<?php include 'header.php'; ?>
</head>
<body>
<main class="container">
    <div class="row justify-content-center mt-5">
    <div class="col-8 col-sm-6 col-md-5 col-lg-4 text-center">
	<h2 class="mb-4 text-center">Login</h2>
	<form method="POST" action="loginRequest.php">
	<div class="mb-3">
		<input class="form-control" type="email" name="email" placeholder="Email" required>
	</div>
	<div class="mb-3">
		<input class="form-control" type="password" name="password" placeholder="Password" required>
	</div>
		<button class="btn btn-primary" type="submit">Log In</button>
	</form>
	<?php if (isset($_GET['error'])): ?>
		<p><?php echo $_GET['error'] === 'server' ? 'Server unavailable. Try again later.' : 'Invalid credentials.'; ?></p>
	<?php endif; ?>
	<?php if (isset($_GET['registered'])): ?>
		<p>Account created. Please log in.</p>
	<?php endif; ?>
	<p class="mt-3"><a href="register.php">Register</a></p>
    </div>
    </div>
</main>
</body>
</html>
