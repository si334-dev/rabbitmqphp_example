CREATE TABLE IF NOT EXISTS `website_data`.`constructorStats`(
	`id` INT NOT NULL AUTO_INCREMENT, 
	`api_constructor_id` VARCHAR(100) NOT NULL, -- api contructor id 
	`combinedDriverPoints` VARCHAR(255), -- points they earned at the last weekend race
        `currentPlacement` VARCHAR(20),
	`currentPoints` VARCHAR(20),
	`currentDraftPower` INT, 	
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE(`api_constructor_id`),

	FOREIGN KEY(`api_constructor_id`)
	REFERENCES `website_data`.`Constructors` (`api_constructor_id`)
	ON DELETE CASCADE


);
