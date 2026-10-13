CREATE TABLE IF NOT EXISTS `website_data`.`TeamConstructors`(
	`team_id` INT NOT NULL,
	`constructor_id` INT NOT NULL, 
	`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	

	PRIMARY KEY('team_id', 'constructor_id'),

	FOREIGN KEY('team_id')
	REFERENCES 'website_data'.'UserTeams' ('id')
	ON DELETE CASCADE,

	FOREIGN KEY('constructor_id')
	REFERENCES 'website_data'.'Constructors' ('id')
	ON DELETE CASECADE --idk if this is needed bu t constructors  hsouldnt be deleted anyway hopefully
	

);
