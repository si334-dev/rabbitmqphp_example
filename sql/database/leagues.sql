CREATE TABLE IF NOT EXISTS `website_data`.`Leagues`(
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL, 
	`description` VARCHAR(255),
       	`invite_url` VARCHAR(255) NOT NULL, --might change later 
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE (`name`),
	UNIQUE (`invite_url`)

);
