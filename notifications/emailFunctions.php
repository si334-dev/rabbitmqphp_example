<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/vendor/autoload.php';

function getEmailList() {

	try {
		$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

		$response = $client->send_request(['type' => 'get_user_emails']);

	} catch (\Exception $e) {
		return null;
	}
	if (($response['returnCode'] ?? '0') != '1') {
		return null;
	}
	return $response['users'] ?? [];
}


function sendResultsEmail($users) {

	$config = parse_ini_file(__DIR__ . '/mail.ini');

	$mail = new PHPMailer(true);

	$mail->isSMTP();

	$mail->Host = $config['SMTP_HOST'];
	$mail->SMTPAuth = true;

	$mail->Username = $config['SMTP_USER'];
	$mail->Password = $config['SMTP_PASS'];
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

	$mail->Port = (int)$config['SMTP_PORT'];

	$mail->SMTPKeepAlive = true;

	$mail->setFrom($config['SMTP_USER'], $config['FROM_NAME']);
	$mail->Subject = "F1 Fantasy: Evaluations are out!";

	$sent = 0;
	$failed = 0;
$errors = [];
	$erros = [];

	foreach ($users as $user) {

		$email = $user['email'] ?? '';
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			continue;

		}
		$name = $user['username'] ?? 'there';

		try {
			$mail->clearAddresses();

			$mail->addAddress($email, $name);
			$mail->Body = "Hi $name, \n\n"
				. "Evaluations for all of the leagues are now out! Log in and see how your team did:\n"
				. $config['SITE_URL'] . "\n\n"
				. "-F1 Fantasy League";
			$mail->send();
			$sent++;
		} catch (\Exception $e) {
			$errors[] = "Failed for $email: " . $mail->ErrorInfo;

			$failed++;
		}
	}

	$mail->smtpClose();

	return ['sent' => $sent, 'failed' => $failed, 'errors' => $errors];
}
