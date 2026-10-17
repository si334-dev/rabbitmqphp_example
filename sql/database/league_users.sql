CREATE TABLE IF NOT EXISTS `website_data`.`LeagueUsers`(
	`league_id` INT NOT NULL,
	`user_id` INT NOT NULL, 
	`isOwner` BOOLEAN DEFAULT FALSE, -- set to true if this user is the owner of the league
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	

	PRIMARY KEY(`league_id`, `user_id`),

	FOREIGN KEY(`league_id`)
	REFERENCES `website_data`.`Leagues` (`id`)
	ON DELETE CASCADE,

	FOREIGN KEY(`user_id`)
	REFERENCES `website_data`.`Users` (`id`)
	ON DELETE CASCADE

);
