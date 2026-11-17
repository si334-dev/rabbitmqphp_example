<?php
require_once('./app/helpers.php');
displaySessionMessage();
?>

<!DOCTYPE html>
<html>
<head><title>Create League</title><?php include 'header.php'?></head>
<body>
<?php include 'nav.php'; ?>
<main class="container">
	<div class="row justify-content-center mt-2 mt-md-5">
	<div class="col-10 col-sm-8 col-md-6 col-lg-5 text-center">
	
	<h2>Create League</h2>

	<form method="POST" action="createLeagueRequest.php">
		<div class="mb-2 mb-md-3">
		<input class="form-control small" type="text" name="LeagueName" placeholder="Your League Name" maxlength=50 required>
		</div>
		<div class="mb-2 mb-md-3">
		<input class="form-control" type="text" name="Description" placeholder="Your League Description" maxlength=200 required>
	</div>
		<button class="btn btn-primary" type="submit">Create League</button>
	</form>
	<?php if (isset($_GET['error'])): ?>
		<p><?php echo $_GET['error'] === 'server' ? 'Server unavailable. Try again later.' : 'There was an error, Please try again later'; ?></p>
<?php endif; ?>

	<p class="mt-3"><a href="leagues.php">Already have a league?</a></p>
</body>
</html>
