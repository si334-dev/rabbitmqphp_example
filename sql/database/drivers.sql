CREATE TABLE IF NOT EXISTS `website_data`.`Drivers`(
	`id` VARCHAR(100) NOT NULL AUTO_INCREMENT, --user api driver id for this field to make things simpler
	`wiki_url` VARCHAR(255), 
	`permNum` INT, --maybe remove idk
	`givenName` VARCHAR(100) NOT NULL,
       	`familyName` VARCHAR(100) NOT NULL, 
       	`dateOfBirth` VARCHAR(100), 
       	`nationality` VARCHAR(100), 
       	`currentPoints` INT,
        `lastResultPoints` INT, --maybe remove idk 	
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)

);
