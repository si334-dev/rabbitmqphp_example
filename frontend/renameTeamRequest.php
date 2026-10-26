
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$sessionKey = $_COOKIE['session_key'] ?? '';
$league_id = (int)($_POST['league_id'] ?? 0);
$name = trim($_POST['name'] ?? '');

if ($sessionKey === '' || $league_id <= 0) {
    header('Location: login.php');
    exit();
}

if ($name === '' || strlen($name) > 100) {
    header('Location: team_management.php?league_id=' . $league_id . '&status=' . urlencode('Team name must be 1-100 characters.'));
    exit();
}

try {
    $client = new rabbitMQClient('testRabbitMQ.ini', 'testServer');

    $res = $client->send_request([
        'type' => 'renameTeam',
        'token' => $sessionKey,
        'league_id' => $league_id,
        'name' => $name
    ]);

    if (is_array($res) && (string)($res['returnCode'] ?? '0') === '1') {
        $status = 'Team name updated successfully.';
    } else {
        $status = is_array($res)
            ? ($res['message'] ?? 'Could not rename team.')
            : 'Unexpected response from server.';
    }
} catch (Throwable $e) {
    error_log('Rename team request failed: ' . $e->getMessage());
    $status = 'Could not contact the server. Please try again.';
}

header('Location: team_management.php?league_id=' . $league_id . '&status=' . urlencode($status));
exit();
