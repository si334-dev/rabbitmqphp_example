CREATE TABLE IF NOT EXISTS `website_data`.`Leagues`(
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL, 
	`description` VARCHAR(255),
       	`url_invite_code` VARCHAR(50) NOT NULL, -- might change later 
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE (`url_invite_code`)

);
