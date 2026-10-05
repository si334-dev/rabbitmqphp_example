<?php
function sendRequest($request) {
	if ($request['email'] === 'test@example.com' && $request['password'] === 'test') {
		return ['returnCode' => '1'];
	}
	return ['returnCode' => '0'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$response = sendRequest([
	'type' => 'Login',
	'email' => $_POST['email'] ?? '',
	'password' => $_POST['password']  ?? '',
	]);

	if (isset($response['returnCode']) && $response['returnCode'] === '1') {
		setcookie('session_key', 'FAKEKEY123', time() + 3600, '/');
		header('Location: home.php');
		exit();
	} else {
		header('Location: login.php?error=1');
		exit();
	}
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
	<h2>Login</h2>
	<form method="POST">
	<input type="email" name="email" placeholder="Email" required><br>
	<input type="password" name="password" placeholder="Password" required><br>
	<button type="submit">Log In</button>
	</form>
	<?php if (isset($_GET['error'])): ?>
		<p>Invalid credentials.</p>
	<?php endif; ?>
	<a href="register.php">Register</a>
</body>
</html>
