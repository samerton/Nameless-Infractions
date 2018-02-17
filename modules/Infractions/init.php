<?php 
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr3
 *
 *  License: MIT
 *
 *  Infractions initialisation file
 */

// Ensure module has been installed
$cache->setCache('module_cache');
$module_installed = $cache->retrieve('module_infractions');
if(!$module_installed){
	// Hasn't been installed
	// Need to run the installer
	
	// Database stuff
	/*
	$exists = $queries->tableExists('infractions_settings');
	if(empty($exists)){
		// Create tables
		try {

		} catch(Exception $e){
			// Error
		}
	}
	
	// Add to cache
	$cache->store('module_infractions', 'true');
	*/
	
}

define('INFRACTIONS', true);
require(join(DIRECTORY_SEPARATOR, array(ROOT_PATH, 'modules', 'Infractions', 'config.php')));

// Initialise infractions language
$infractions_language = new Language(ROOT_PATH . '/modules/Infractions/language', LANGUAGE);

// Define URLs which belong to this module
$pages->add('Infractions', '/admin/infractions', 'pages/admin.php');
$pages->add('Infractions', '/infractions', 'pages/infractions.php');
$pages->add('Infractions', '/mod/infractions', 'pages/mod.php');

// Autoloader
spl_autoload_register(function($class) {
	$path = join(DIRECTORY_SEPARATOR, array(ROOT_PATH, 'modules', 'Infractions', 'classes', $class . '.php'));
	if(file_exists($path)) require_once($path);
});

// Add link to navbar
// Which navbar do we add the link to?
$navigation->add('infractions', $infractions_language->get('infractions', 'infractions'), URL::build('/infractions'));

// Add link to admin sidebar
if(!isset($admin_sidebar)) $admin_sidebar = array();
$admin_sidebar['infractions'] = array(
	'title' => $infractions_language->get('infractions', 'infractions'),
	'url' => URL::build('/admin/infractions')
);

// Add link to mod sidebar
$mod_nav->add('infractions', $infractions_language->get('infractions', 'infractions'), URL::build('/mod/infractions'));

// Profile page tab
if(!isset($profile_tabs)) $profile_tabs = array();
$profile_tabs['infractions'] = array('title' => $infractions_language->get('infractions', 'infractions'), 'smarty_template' => 'infractions/profile_tab.tpl', 'require' => 'modules' . DIRECTORY_SEPARATOR . 'Infractions' . DIRECTORY_SEPARATOR . 'profile_tab.php');
