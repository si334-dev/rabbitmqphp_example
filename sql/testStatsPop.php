#!/usr/bin/php
<?php

require('evaluate_league_points.php');

//Change username(testUser) and password(admin) to other credential based
//on whoever is running the database. (or just make a new user)
$db = new mysqli('127.0.0.1', "testUser", "admin", "website_data");

updateDraftPowerStats();
populateResultStats();
