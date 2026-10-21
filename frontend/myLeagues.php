
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$sessionKey = $_COOKIE['session_key'] ?? '';

if ($sessionKey === '') {
    header('Location: login.php');
    exit();
}

$leagues = [];
$error = '';

try {
    $client = new rabbitMQClient(
        "testRabbitMQ.ini",
        "testServer"
    );

    $response = $client->send_request([
        'type' => 'getMyLeagues',
        'token' => $sessionKey
    ]);

    if (($response['returnCode'] ?? '0') == '1') {
        $leagues = $response['leagues'] ?? [];
    } else {
        $error = $response['message'] ?? 'Unable to load leagues.';
    }
} catch (Exception $e) {
    $error = 'Server unavailable. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Leagues</title>
</head>
<body>

<?php include('nav.php'); ?>

<h1>My Leagues</h1>

<p>View all the leagues you belong to.</p>

<?php if ($error !== ''): ?>

    <p><?php echo htmlspecialchars($error); ?></p>

<?php elseif (empty($leagues)): ?>

    <p>You haven't joined any leagues yet.</p>
    <p>You can create a league or join one using an invite code.</p>

<?php else: ?>

    <?php foreach ($leagues as $league): ?>

        <section>
            <h2>
                <?php echo htmlspecialchars($league['name']); ?>
            </h2>

            <p>
                <?php echo htmlspecialchars(
                    $league['description'] ?? ''
                ); ?>
            </p>

            <p>
                Role:
                <?php
                echo (($league['isOwner'] ?? 0) == 1)
                    ? 'Owner'
                    : 'Member';
                ?>
            </p>

            <a href="league.php?id=<?php
                echo urlencode($league['id']);
            ?>">
                View League
            </a>
        </section>

        <hr>

    <?php endforeach; ?>

<?php endif; ?>

<p><a href="createLeague.php">Create a League</a></p>

</body>
</html>

