<?php

displaySessionMessage(){
	if (isset($_COOKIE['message'])){
		$message = $_COOKIE['message'];	
		echo "<p>$message</p>";
		unset($_COOKIE['message']);

	}	 
}
