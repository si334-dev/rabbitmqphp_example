<?php

function displaySessionMessage(){
	if (isset($_COOKIE['message']) && $_COOKIE['message'] !== ''){
		$message = $_COOKIE['message'];	
		echo "<p>$message</p>";
		
		setcookie('message', '', time() - 3600, '/');
		unset($_COOKIE['message']);

	}	 
}
