<?php
require_once('./app/helpers.php')
displaySessionMessage();
?>

<!DOCTYPE html>
<html>
<head><title>Create League</title></head>
<body>
	<h2>Create League</h2>
	<form method="POST" action="createLeagueRequest.php">
		<input type="text" name="League Name" placeholder="Your League Name" maxlength=50 required><br>
		<input type="text" name="Description" placeholder="Your League Description" maxlength=200 required><br>
		<button type="submit">Create League</button>
	</form>
	<?php if (isset($_GET['error'])): ?>
		<p><?php echo $_GET['error'] === 'server' ? 'Server unavailable. Try again later.' : 'There was an error, Please try again later'; ?></p>

	<a href="leagues.php">Already have a league?</a>
</body>
</html>
