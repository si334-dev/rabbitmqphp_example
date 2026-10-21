CREATE TABLE IF NOT EXISTS `website_data`.`UserTeams`(
	`id` INT NOT NULL AUTO_INCREMENT,
	`league_id` INT NOT NULL,
	`user_id` INT NOT NULL, 
	`name` VARCHAR(100) NOT NULL, -- stores user defined team name, mayber default to "users team"
	`currentPoints` INT NOT NULL, -- sotres the points they have accrued over the season
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	

	PRIMARY KEY(`id`),
	UNIQUE(`league_id`, `user_id`),

	FOREIGN KEY(`league_id`, `user_id`)
	REFERENCES `website_data`.`LeagueUsers` (`league_id`, `user_id`)
	ON DELETE CASCADE

);
