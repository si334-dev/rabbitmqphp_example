<?php
setcookie('session_key', '', time() - 3600, '/');
header('Location: login.php');
exit();
?>
