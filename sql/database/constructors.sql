CREATE TABLE IF NOT EXISTS `website_data`.`Constructors`(
	`id` INT NOT NULL AUTO_INCREMENT, 
	`api_constructor_id` VARCHAR(100), -- api contructor id so you cna search them up 
	`wiki_url` VARCHAR(255), 
	`name` VARCHAR(100) NOT NULL,  
       	`nationality` VARCHAR(100), 	
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE(`api_constructor_id`)

);
