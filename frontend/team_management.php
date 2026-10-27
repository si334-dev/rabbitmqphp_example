<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$sessionKey = $_COOKIE['session_key'] ?? '';

if ($sessionKey === '') {
    header('Location: login.php');
    exit();
}

$league_id = (int)($_GET['league_id'] ?? 0);
if ($league_id <= 0) {
    header('Location: myLeagues.php');
    exit();
}

$ENABLE_TEAM_EDITING = false;

function callBackend($payload){
    try {
        $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
        return $client->send_request($payload);
    } catch (Exception $e) {
        return null;
    }
}

$message = $_GET['status'] ?? '';

// ---- handle form actions (shelved) ----
if ($ENABLE_TEAM_EDITING && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $base = ['token' => $sessionKey, 'league_id' => $league_id];
    $res = null;

    if ($action === 'remove') {
        $res = callBackend($base + [
            'type' => 'removeTeamMember',
            'kind' => $_POST['kind'] ?? '',
            'item_id' => (int)($_POST['item_id'] ?? 0),
        ]);
    } elseif ($action === 'add') {
        $res = callBackend($base + [
            'type' => 'addTeamMember',
            'kind' => $_POST['kind'] ?? '',
            'item_id' => (int)($_POST['item_id'] ?? 0),
        ]);
    } elseif ($action === 'swap') {
        $kind = $_POST['kind'] ?? '';
        $old = (int)($_POST['old_id'] ?? 0);
        $new = (int)($_POST['item_id'] ?? 0);

        $rem = callBackend($base + [
            'type' => 'removeTeamMember',
            'kind' => $kind,
            'item_id' => $old
        ]);

        if (($rem['returnCode'] ?? '0') == '1') {
            $res = callBackend($base + [
                'type' => 'addTeamMember',
                'kind' => $kind,
                'item_id' => $new
            ]);

            if (($res['returnCode'] ?? '0') != '1') {
                callBackend($base + [
                    'type' => 'addTeamMember',
                    'kind' => $kind,
                    'item_id' => $old
                ]);
            }
        } else {
            $res = $rem;
        }
    }

    $message = $res['message'] ?? 'Something went wrong, please try again.';
}

// ---- load team (one request) ----
$data = callBackend(['type' => 'getTeam', 'token' => $sessionKey, 'league_id' => $league_id]);
$ok = ($data['returnCode'] ?? '0') == '1';

$team = $data['team'] ?? [];
$drivers = $data['drivers'] ?? [];
$constructors = $data['constructors'] ?? [];
$availDrivers = $data['availableDrivers'] ?? [];
$availConstructors = $data['availableConstructors'] ?? [];

$MAX_DRIVERS = 3;
$MAX_CONSTRUCTORS = 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Management</title>
</head>
<body>

<?php include('nav.php'); ?>

<h1>Team Management</h1>
<p><a href="myLeagues.php">&larr; My Leagues</a></p>

<?php if ($message !== '') { ?>
    <p><strong><?= htmlspecialchars($message) ?></strong></p>
<?php } ?>

<?php if (!$ok) { ?>
    <p><?= htmlspecialchars($data['message'] ?? 'Could not load your team.') ?></p>
<?php } else { ?>

<a href="draft.php?league_id=<?= $league_id ?>">Draft Your Team</a>
<h2>Team name</h2>
<form method="POST" action="renameTeamRequest.php">
    <input type="hidden" name="league_id" value="<?= (int)$league_id ?>">
    <input type="text" name="name" maxlength="100" required
           value="<?= htmlspecialchars($team['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit">Rename</button>
</form>

    <p>Points: <?= (int)($team['currentPoints'] ?? 0) ?></p>

    <h2>Drivers (<?= count($drivers) ?>/<?= $MAX_DRIVERS ?>)</h2>
    <ul>
    <?php foreach ($drivers as $d) { ?>
        <li>
            <?= htmlspecialchars($d['givenName'] . ' ' . $d['familyName']) ?>
            <?php if ($ENABLE_TEAM_EDITING) { ?>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="kind" value="driver">
                <input type="hidden" name="item_id" value="<?= (int)$d['id'] ?>">
                <button type="submit">Remove</button>
            </form>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="swap">
                <input type="hidden" name="kind" value="driver">
                <input type="hidden" name="old_id" value="<?= (int)$d['id'] ?>">
                <select name="item_id">
                    <?php foreach ($availDrivers as $a) { ?>
                        <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['givenName'] . ' ' . $a['familyName']) ?></option>
                    <?php } ?>
                </select>
                <button type="submit">Swap</button>
            </form>
            <?php } ?>
        </li>
    <?php } ?>
    <?php if (empty($drivers)) { ?><li>No drivers yet.</li><?php } ?>
    </ul>

    <?php if ($ENABLE_TEAM_EDITING && count($drivers) < $MAX_DRIVERS) { ?>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="kind" value="driver">
        <select name="item_id">
            <?php foreach ($availDrivers as $a) { ?>
                <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['givenName'] . ' ' . $a['familyName']) ?></option>
            <?php } ?>
        </select>
        <button type="submit">Add driver</button>
    </form>
    <?php } ?>

    <h2>Constructors (<?= count($constructors) ?>/<?= $MAX_CONSTRUCTORS ?>)</h2>
    <ul>
    <?php foreach ($constructors as $c) { ?>
        <li>
            <?= htmlspecialchars($c['name']) ?>
            <?php if ($ENABLE_TEAM_EDITING) { ?>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="kind" value="constructor">
                <input type="hidden" name="item_id" value="<?= (int)$c['id'] ?>">
                <button type="submit">Remove</button>
            </form>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="swap">
                <input type="hidden" name="kind" value="constructor">
                <input type="hidden" name="old_id" value="<?= (int)$c['id'] ?>">
                <select name="item_id">
                    <?php foreach ($availConstructors as $a) { ?>
                        <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
                    <?php } ?>
                </select>
                <button type="submit">Swap</button>
            </form>
            <?php } ?>
        </li>
    <?php } ?>
    <?php if (empty($constructors)) { ?><li>No constructors yet.</li><?php } ?>
    </ul>

    <?php if ($ENABLE_TEAM_EDITING && count($constructors) < $MAX_CONSTRUCTORS) { ?>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="kind" value="constructor">
        <select name="item_id">
            <?php foreach ($availConstructors as $a) { ?>
                <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
            <?php } ?>
        </select>
        <button type="submit">Add constructor</button>
    </form>
    <?php } ?>

<?php } ?>

</body>
</html>
