<?php 
/*
 *	Made by Samerton and Partydragen
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr6
 *
 *  License: MIT
 *
 *  Panel infractions settings page
 */

// Can the user view the panel?
if($user->isLoggedIn()){
	if(!$user->canViewStaffCP()){
		// No
		Redirect::to(URL::build('/'));
		die();
	}
	if(!$user->isAdmLoggedIn()){
		// Needs to authenticate
		Redirect::to(URL::build('/panel/auth'));
		die();
	} else {
		if(!$user->hasPermission('admincp.infractions.settings')){
			require_once(ROOT_PATH . '/403.php');
			die();
		}
	}
} else {
	// Not logged in
	Redirect::to(URL::build('/login'));
	die();
}

define('PAGE', 'panel');
define('PARENT_PAGE', 'infractions');
define('PANEL_PAGE', 'infractions');
$page_title = $infractions_language->get('infractions', 'infractions');
require_once(ROOT_PATH . '/core/templates/backend_init.php');

// Handle input
if(Input::exists()){
	$errors = array();
	if(Token::check(Input::get('token'))){
		// Get link location
		if(isset($_POST['link_location'])){
			switch($_POST['link_location']){
				case 1:
				case 2:
				case 3:
				case 4:
					$location = $_POST['link_location'];
					break;
				default:
				$location = 1;
			}
		} else
		$location = 1;

		$guests_view = $_POST['guests_view'] == '1' ? '1' : '0';
	
        // Update Link location cache
        $cache->setCache('infractions_module_cache');
		$cache->store('link_location', $location);
		$cache->store('guests_view', $guests_view);
		
		// Update config
		$config_path = ROOT_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Infractions' . DIRECTORY_SEPARATOR . 'config.php';
		if(file_exists($config_path)){
			if(is_writable($config_path)){
				require(ROOT_PATH . '/modules/Infractions/config.php');
				// Build new email config
				$config = '<?php' . PHP_EOL .
					'$inf_db = array(' . PHP_EOL .
					'    \'address\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['host']) ? $_POST['host'] : $inf_db['address'])) . '\',' . PHP_EOL .
					'    \'port\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['port']) ? $_POST['port'] : $inf_db['port'])) . '\',' . PHP_EOL .
					'    \'name\' => \'' . str_replace('\'', '\\\'', ((!empty($_POST['name'])) ? $_POST['name'] : $inf_db['name'])) . '\',' . PHP_EOL .
					'    \'username\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['username']) ? $_POST['username'] : $inf_db['username'])) . '\',' . PHP_EOL .
					'    \'password\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['password']) ? $_POST['password'] : $inf_db['password'])) . '\',' . PHP_EOL .
					');' . PHP_EOL .
					'$inf_config = array(' . PHP_EOL .
					'    \'plugin\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['plugin']) ? $_POST['plugin'] : $inf_config['plugin'])) . '\',' . PHP_EOL .
					'    \'guests_view\' => \'' . str_replace('\'', '\\\'', $guests_view) . '\',' . PHP_EOL .
					');';
				$file = fopen($config_path, 'w');
				fwrite($file, $config);
				fclose($file);
				
			} else {
				// Permissions incorrect
				$errors[] = $infractions_language->get('infractions', 'unable_to_write_infractions_config');
			}

		} else {
			// Create one now
			if(is_writable(ROOT_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Infractions')){
				// Build new email config
				$config = '<?php' . PHP_EOL .
					'$inf_db = array(' . PHP_EOL .
					'    \'address\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['host']) ? $_POST['host'] : '')) . '\',' . PHP_EOL .
					'    \'port\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['port']) ? $_POST['port'] : 3306)) . '\',' . PHP_EOL .
					'    \'name\' => \'' . str_replace('\'', '\\\'', ((!empty($_POST['name'])) ? $_POST['name'] : '')) . '\',' . PHP_EOL .
					'    \'username\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['username']) ? $_POST['username'] : '')) . '\',' . PHP_EOL .
					'    \'password\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['password']) ? $_POST['password'] : '')) . '\',' . PHP_EOL .
					');' . PHP_EOL .
					'$inf_config = array(' . PHP_EOL .
					'    \'plugin\' => \'' . str_replace('\'', '\\\'', (!empty($_POST['plugin']) ? $_POST['plugin'] : 'litebans')) . '\',' . PHP_EOL .
					'    \'guests_view\' => \'' . str_replace('\'', '\\\'', $_POST['guests_view']) . '\',' . PHP_EOL .
					');';
				$file = fopen($config_path, 'w');
				fwrite($file, $config);
				fclose($file);

			} else {
				$errors[] = $infractions_language->get('admin', 'unable_to_write_infractions_config');
			}
		}
		
		if(!count($errors)){
			// Redirect to refresh config values
			Session::flash('infractions_success', $infractions_language->get('infractions', 'infractions_settings_updated_successfully'));
			Redirect::to(URL::build('/panel/infractions'));
			die();
		}
	} else {
		// Invalid token
		$errors[] = $language->get('general', 'invalid_token');
	}
}

if(!isset($inf_db) && file_exists(ROOT_PATH . '/modules/Infractions/config.php')){
	require_once(ROOT_PATH . '/modules/Infractions/config.php');
}

// Retrive link_location from cache
$cache->setCache('infractions_module_cache');
$link_location = $cache->retrieve('link_location');

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets);

if(Session::exists('infractions_success'))
	$success = Session::flash('infractions_success');

if(isset($success))
	$smarty->assign(array(
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	));

if(isset($errors) && count($errors))
	$smarty->assign(array(
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	));

// Plugin options
$plugin_options = array(
	array(
		'name' => 'LiteBans',
		'value' => 'litebans'
	),
	array(
		'name' => 'AdvancedBan',
		'value' => 'advancedban'
	),
	array(
		'name' => 'LibertyBans',
		'value' => 'libertybans'
	)
);

$smarty->assign(array(
	'PARENT_PAGE' => PARENT_PAGE,
	'PAGE' => PANEL_PAGE,
	'DASHBOARD' => $language->get('admin', 'dashboard'),
	'INFO' => $language->get('general', 'info'),
	'INFRACTIONS' => $infractions_language->get('infractions', 'infractions'),
	'DATABASE_SETTINGS' => $infractions_language->get('infractions', 'database_settings'),
	'PLUGIN' => $infractions_language->get('infractions', 'plugin'),
	'PLUGIN_VALUE' => (!empty($inf_config['plugin']) ? Output::getClean($inf_config['plugin']) : 'litebans'),
	'PLUGIN_OPTIONS' => $plugin_options,
	'LINK_LOCATION' => $infractions_language->get('infractions', 'link_location'),
	'LINK_LOCATION_VALUE' => $link_location,
	'LINK_NAVBAR' => $language->get('admin', 'page_link_navbar'),
	'LINK_MORE' => $language->get('admin', 'page_link_more'),
	'LINK_FOOTER' => $language->get('admin', 'page_link_footer'),
	'LINK_NONE' => $language->get('admin', 'page_link_none'),
	'GUESTS_VIEW' => $infractions_language->get('infractions', 'guests_view'),
	'GUESTS_VIEW_VALUE' => (!empty($inf_config['guests_view']) ? Output::getClean($inf_config['guests_view']) : 0),
	'ADDRESS' => $infractions_language->get('infractions', 'database_address'),
	'ADDRESS_VALUE' => (!empty($inf_db['address']) ? Output::getClean($inf_db['address']) : ''),
	'NAME' => $infractions_language->get('infractions', 'database_name'),
	'NAME_VALUE' => (!empty($inf_db['name']) ? Output::getClean($inf_db['name']) : ''),
	'USERNAME' => $infractions_language->get('infractions', 'database_username'),
	'USERNAME_VALUE' => (!empty($inf_db['username']) ? Output::getClean($inf_db['username']) : ''),
	'PORT' => $infractions_language->get('infractions', 'database_port'),
	'PORT_VALUE' => (!empty($inf_db['port']) ? Output::getClean($inf_db['port']) : '3306'),
	'PASSWORD' => $infractions_language->get('infractions', 'database_password'),
	'PASSWORD_HIDDEN' => $language->get('admin', 'email_password_hidden'),
	'TOKEN' => Token::get(),
	'SUBMIT' => $language->get('general', 'submit')
));

$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/panel_navbar.php');

// Display template
$template->displayTemplate('infractions/index.tpl', $smarty);