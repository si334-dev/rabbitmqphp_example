CREATE TABLE IF NOT EXISTS `deploy`.`Bundles`(
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL, 
	`version` INT NOT NULL,
       	`status` ENUM('new', 'passed', 'failed') DEFAULT 'new' NOT NULL, 
       	`machine` ENUM('dmz', 'rabbit', 'frontend') NOT NULL,
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE (`name`, `version`)

);	
