<?php

if (php_sapi_name() !== 'cli') {
	exit("Run from the command line only.\n");
}

chdir(__DIR__ . '/../frontend');

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

require_once(__DIR__ . '/emailFunctions.php');

if (isset($argv[1]) && $argv[1] === '--test') {

	$users = [['username' => 'Test', 'email' => $argv[2] ?? '']];
} else {
	

	$users = getEmailList();
	if ($users === null) {
		exit(date('Y-m-d H:i') . " Couldn't get the user list from the backend.\n");
	}
}

$result = sendResultsEmail($users);

foreach ($result['errors'] as $err) {
	echo $err . "\n";

}
echo date('Y-m-d H:i') . " Sent: {$result['sent']}, Failed: {$result['failed']}\n";
