<?php 
/*
 *	Made by Samerton and Partydragen
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr13
 *
 *  License: MIT
 *
 *  Infractions module info file
 */

class Infractions_Module extends Module {
	private $_infractions_language, $_language;
	
	public function __construct($language, $infractions_language, $pages){
		$this->_language = $language;
		$this->_infractions_language = $infractions_language;

		$name = 'Infractions';
		$author = '<a href="https://samerton.me" target="_blank" rel="nofollow noopener">Samerton</a>';
		$module_version = '1.4.3';
		$nameless_version = '2.2.1';
		
		parent::__construct($this, $name, $author, $module_version, $nameless_version);

		// Define URLs which belong to this module
		$pages->add('Infractions', '/panel/infractions', 'pages/panel/index.php');
		$pages->add('Infractions', '/infractions', 'pages/infractions.php');
	}
	
	public function onInstall(){
		// Install module
		try {
			// Update main admin group permissions
			$group = DB::getInstance()->get('groups', array('id', '=', 2));
			$group = $group->first();
			
			$group_permissions = json_decode($group->permissions, true);
			$group_permissions['admincp.infractions.settings'] = 1;
			$group_permissions['infractions.view'] = 1;
			
			$group_permissions = json_encode($group_permissions);
			DB::getInstance()->update('groups', 2, array('permissions' => $group_permissions));
		} catch(Exception $e){
			// Error
		}
	}
	
	public function onUninstall(){
		// Uninstall module
	}
	
	public function onEnable(){
		// No actions necessary
	}

	public function onDisable(){
		// No actions necessary
	}
	
	public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template){
		// Permissions
		PermissionHandler::registerPermissions('Infractions', array(
			'infractions.view' => $this->_infractions_language->get('infractions', 'infractions') . ' &raquo; ' . $this->_infractions_language->get('infractions', 'view_infractions'),
			'admincp.infractions.settings' => $this->_language->get('moderator', 'staff_cp') . ' &raquo; ' . $this->_infractions_language->get('infractions', 'infractions_settings')
		));

		// Permission check for admins - only temporary until group ID 2 is made into a super group
		if ($user->isLoggedIn() && !$user->hasPermission('infractions.view')) {
            $groups = $user->getGroups();

            foreach ($groups as $group) {
                if ($group->id == 2) {
                    // Update main admin group permissions
                    $group = DB::getInstance()->get('groups', array('id', '=', 2));
                    $group = $group->first();

                    $group_permissions = json_decode($group->permissions, true);
                    $group_permissions['infractions.view'] = 1;

                    $group_permissions = json_encode($group_permissions);
                    DB::getInstance()->update('groups', 2, array('permissions' => $group_permissions));
                    break;
                }
            }
        }

		// navigation link location
		$cache->setCache('infractions_module_cache');
		if(!$cache->isCached('link_location')){
			$link_location = 1;
			$cache->store('link_location', 1);
		} else {
			$link_location = $cache->retrieve('link_location');
		}
		if (!$cache->isCached('guests_view')){
			$guests_view = 0;
			$cache->store('guests_view', 0);
		} else {
			$guests_view = $cache->retrieve('guests_view');
		}

		if (($user->isLoggedIn() && ($user->hasPermission('infractions.view'))) || (!$user->isLoggedIn() && $guests_view)) {
			// Add link to navbar
			$cache->setCache('navbar_order');
			if(!$cache->isCached('infractions_order')){
				$order = 14;
				$cache->store('infractions_order', 14);
			} else {
				$order = $cache->retrieve('infractions_order');
			}
			$cache->setCache('navbar_icons');
			if(!$cache->isCached('infractions_icon'))
				$icon = '';
			else
				$icon = $cache->retrieve('infractions_icon');

			switch($link_location){
				case 1:
					// Navbar
					$navs[0]->add('infractions', $this->_infractions_language->get('infractions', 'infractions'), URL::build('/infractions'), 'top', null, $order, $icon);
				break;
				case 2:
					// "More" dropdown
					$navs[0]->addItemToDropdown('more_dropdown', 'infractions', $this->_infractions_language->get('infractions', 'infractions'), URL::build('/infractions'), 'top', null, $icon, $order);
				break;
				case 3:
					// Footer
					$navs[0]->add('infractions', $this->_infractions_language->get('infractions', 'infractions'), URL::build('/infractions'), 'footer', null, $order, $icon);
				break;
			}
		}

		if (defined('BACK_END')) {
			if($user->hasPermission('admincp.infractions.settings')){
				$cache->setCache('panel_sidebar');
				if(!$cache->isCached('infractions_order')){
					$order = 23;
					$cache->store('infractions_order', 23);
				} else {
					$order = $cache->retrieve('infractions_order');
				}
				if(!$cache->isCached('infractions_icon')){
					$icon = '<i class="nav-icon fas fa-cogs"></i>';
					$cache->store('infractions_icon', $icon);
				} else {
					$icon = $cache->retrieve('infractions_icon');
				}
				$navs[2]->add('infractions_divider', mb_strtoupper($this->_infractions_language->get('infractions', 'infractions'), 'UTF-8'), 'divider', 'top', null, $order, '');
				$navs[2]->add('infractions', $this->_infractions_language->get('infractions', 'infractions'), URL::build('/panel/infractions'), 'top', null, $order + 0.1, $icon);
			}
		}
			
	}

    public function getDebugInfo(): array {
        // Nothing to do here (yet)
        return [];
    }
}