CREATE TABLE IF NOT EXISTS `website_data`.`TeamDrivers`(
	`team_id` INT NOT NULL,
	`driver_id` INT NOT NULL, 
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	

	PRIMARY KEY('team_id', 'driver_id'),

	FOREIGN KEY('team_id')
	REFERENCES 'website_data'.'UserTeams' ('id')
	ON DELETE CASCADE,

	FOREIGN KEY('driver_id')
	REFERENCES 'website_data'.'Drivers' ('id')
	ON DELETE CASECADE --idk if this is needed bu t drivers hsouldnt be deleted anyway hopefully
	

);
