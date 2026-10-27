<?php

require_once('session.php');
//$user = requireLogin();

require_once(__DIR__ . '/../notifications/emailFunctions.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$mode = $_POST['mode'] ?? '';

	if ($mode === 'test') {

		$users = [['username' => $user['username'] ?? 'Test', 'email' => $_POST['email'] ?? '']];
	} else {

		$users = getEmailList();
	}

	if ($users === null) {
		$message = 'Could not  get the user list from the backend.';
	} else {
		$result = sendResultsEmail($users);

		$message = "Sent: {$result['sent']}, Failed: {$result['failed']}";
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<head><title>Email Demo</title></head>
<body>
	<?php include('nav.php'); ?>

	<h2>Weekly Email Demo</h2>
	<p>This sends the same "evaluations are out" email that will go out automatically every Monday.</p>

	<?php if ($message): ?>
		<p><strong><?php echo htmlspecialchars($message); ?></strong></p>
	<?php endif; ?>

	<h3>Send a test email</h3>
	<form method="POST">
		<input type ="hidden" name="mode" value="test">
		<input type="email" name="email" placeholder="Your email" required>
		<button type="submit">Send Test</button>
	</form>

	<h3>Send to all of the users</h3>
	<form method="POST">
		<input type="hidden" name="mode" value="all">
		<button type="submit">Send to Everyone Now</button>
	</form>
</body>
</html>
