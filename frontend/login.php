<?php

function sendRequest($request) {
	if ($request['username'] === 'test' && $request['password'] === 'test') {
		return ['status' => 'ok', 'session_key' => 'FAKEKEY123'];
	}
	return ['status' => 'fail', 'message' => 'Invalid credentials'];
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] =='POST') {
	$response = sendRequest ([
		'type' => 'Login',
		'username' => $_POST['username'] ?? '',
		'password' => $_POST['password'] ?? '',
		'message' => '',
	]);
	$message = $response['status'] === 'ok'
		? 'Logged in! Session key: ' . $response['session_key']
		: $response['message'];
}
?>
<!DOCTYPE html
<html>
<head><title>Login</title></head>
<body>
	<h2>Login</h2>
	<form method = "POST">
	<input type ="text" name="username" placeholder="Username" required><br>
	<input type="password" name="password" placeholder="Password" required><br>
	<button type="submit">Log IN</button>
	</form>
	<p><?php echo htmlspecialchars($message); ?></p>
	<a href="register.php">Register</a>
</body>
</html>
