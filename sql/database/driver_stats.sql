CREATE TABLE IF NOT EXISTS `website_data`.`DriverStats`(
	`id` INT NOT NULL AUTO_INCREMENT, 
	`api_driver_id` VARCHAR(100) NOT NULL, -- api driver id  
	`currentPlacement` VARCHAR(20), 
	`currentPoints` VARCHAR(20),
       	`lastRaceResult` VARCHAR(20), 
       	`fastestLapRank` VARCHAR(20),  
       	`currentDraftPower` INT, 	
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE(`api_driver_id`),

	FOREIGN KEY(`api_driver_id`)
	REFERENCES `website_data`.`Drivers` (`api_driver_id`)
	ON DELETE CASCADE


);
