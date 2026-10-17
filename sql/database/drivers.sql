CREATE TABLE IF NOT EXISTS `website_data`.`Drivers`(
	`id` INT NOT NULL AUTO_INCREMENT, 
	`api_driver_id` VARCHAR(100) NOT NULL, -- api driver id so you cna search them up 
	`wiki_url` VARCHAR(255), 
	`permNum` INT, -- maybe remove idk
	`givenName` VARCHAR(100) NOT NULL,
       	`familyName` VARCHAR(100) NOT NULL, 
       	`dateOfBirth` VARCHAR(100), 
       	`nationality` VARCHAR(100), 	
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)

);
