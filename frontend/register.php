<?php
function sendRequest($request) {
	if ($request['email'] === 'taken@example.com') {
		return ['returnCode' => '0'];
	}
	return ['returnCode' => '1'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$response = sendRequest([
	'type' => 'Registration',
	'email' => $_POST['email'] ?? '',
	'password' => $_POST['password'] ?? '',
	]);

	if (isset($response['returnCode']) && $response['returnCode'] === '1') {
	header('Location: login.php');
	exit();
} else {
	header('Location: register.php?error=1');
	exit();
	}
}
?>
<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>
	<h2>Register</h2>
	<form method="POST">
	<input type="email" name="email" placeholder="Email" required><br>
	<input type="password" name="password" placeholder="Password" required><br>
	<button type="submit">Register</button>
</form>
<?php if (isset($_GET['error'])): ?>
	<p>Registration failed. Try Again.</p>
<?php endif; ?>
<a href="login.php">Already have an account? Log In</a>
</body>
</html>
