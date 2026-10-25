ALTER TABLE `website_data`.`UserTeams` -- use this to add points colum if you alrweayd mande the table
ADD COLUMN `currentPoints` INT NOT NULL DEFAULT 0 AFTER `name`;

