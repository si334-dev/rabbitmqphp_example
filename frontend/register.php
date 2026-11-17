<!DOCTYPE html>
<html>
<head>
	<title>Register</title>
	<?php include 'header.php'?>
</head>
<body>
<main class="container min-vh-100 d-flex align-items-center justify-content-center">
<div class="col-7 col-sm-5 col-md-4 col-lg-3 text-center">
	<h2 class="mb-2 mb-md-3">Register</h2>
	<form method="POST" action="registerRequest.php">
		<div class="mb-2">
		<input class="form-control-sm" type ="text" name="username" placeholder="Username" required><br>
		</div>
		
		<div class="mb-2">
		<input class="form-control-sm" type="email" name="email" placeholder="Email" required><br>
		</div>
		
		<div class="mb-2">
		<input class="form-control-sm" type ="password" name="password" placeholder="Password" required><br>
		</div>

		<button class="btn btn-info btn-sm" type="submit">Register</button>
	</form>
	<?php if (isset($_GET['error'])): ?>
		<p><?php echo $_GET['error'] === 'server' ? 'Server unavailable. Try Again Later.' : 'Registration failed. Try again.'; ?></p>
	<?php endif; ?>

	<p class="mt-3 small"><a href="login.php">Already have an account? Log In</a></p>
</div>
</div>
</main>
</body>
</html>
