<?php 
/*
 *	Made by Samerton and Partydragen
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr6
 *
 *  License: MIT
 *
 *  Infractions initialisation file
 */
 
 // Initialise infractions language
$infractions_language = new Language(ROOT_PATH . '/modules/Infractions/language', LANGUAGE);

// Initialise module
require_once(ROOT_PATH . '/modules/Infractions/module.php');
$module = new Infractions_Module($language, $infractions_language, $pages, $cache);