<?php
/*
 *	Made by Samerton and Partydragen
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr6
 *
 *  License: MIT
 *
 *  Infractions page
 */

// Can the user view the panel?
if ($user->isLoggedIn()) {
    if (!$user->hasPermission('infractions.view')) {
        require_once(ROOT_PATH . '/404.php');
        die();
    }
} else {
    $cache->setCache('infractions_module_cache');
    if (!$cache->isCached('guests_view')){
        $guests_view = 0;
        $cache->store('guests_view', 0);
    } else {
        $guests_view = $cache->retrieve('guests_view');
    }
    if (!$guests_view) {
        require_once(ROOT_PATH . '/404.php');
        die();
    }
}

// Always define page name
define('PAGE', 'infractions');
$page_title = $infractions_language->get('infractions', 'infractions');
require_once(ROOT_PATH . '/core/templates/frontend_init.php');

// Get page
if(isset($_GET['p'])){
    if(!is_numeric($_GET['p'])){
        Redirect::to(URL::build('/infractions'));
        die();
    } else $p = $_GET['p'];
} else $p = 1;

$timeago = new Timeago(TIMEZONE);

if(!file_exists(ROOT_PATH . '/modules/Infractions/config.php')){
	die('Please configure the Infractions module in the StaffCP first!');
}
require(ROOT_PATH . '/modules/Infractions/config.php');
if(!isset($inf_db)) {
	die('Please configure the Infractions module in the StaffCP first!');
}
require(ROOT_PATH . '/core/integration/uuid.php');
require_once(ROOT_PATH . '/modules/Infractions/classes/Infractions.php');
switch($inf_config['plugin']) {
	case 'litebans':
		// Litebans integration
		require_once(ROOT_PATH . '/modules/Infractions/classes/LiteBans.php');
		$infractions = new LiteBans($inf_db, $infractions_language);
	break;
	case 'advancedban':
		// AdvancedBan integration
		require_once(ROOT_PATH . '/modules/Infractions/classes/AdvancedBan.php');
		$infractions = new AdvancedBan($inf_db, $infractions_language);
		break;
	default:
		die('Plugin not supported!');
	break;
}

if(!isset($_GET['view']) && !isset($_GET['id'])){
    $infractions_list = $infractions->listInfractions($p, 10);

    if(count($infractions_list)) {
        // Pagination
        $paginator = new Paginator((isset($template_pagination) ? $template_pagination : array()));
        $paginator->setValues($infractions_list['total'], 10, $p);
        $pagination = $paginator->generate(7, URL::build('/infractions', true));

        $smarty->assign('PAGINATION', $pagination);

        unset($infractions_list['total']);

        $infractions_array = array();
        $users_array = array();

        foreach($infractions_list as $result){
            // Check if the user exists
            if(!isset($users_array[$result->name])){
                $query_user = new User($result->name, 'username');
                if($query_user->exists()){
                    $users_array[$result->name] = array(
                        'profile' => URL::build('/profile/' . Output::getClean($result->name)),
                        'style' => $query_user->getGroupClass(),
                        'avatar' => $query_user->getAvatar()
                    );
                } else {
                    $users_array[$result->name] = array(
                        'profile' => null,
                        'style' => null,
                        'avatar' => Util::getAvatarFromUUID($result->uuid)
                    );
                }
            }
            if(!isset($users_array[$result->banned_by_name])){
                $query_user = new User($result->banned_by_name, 'username');
                if($query_user->exists()){
                    $users_array[$result->banned_by_name] = array(
                        'profile' => URL::build('/profile/' . Output::getClean($result->banned_by_name)),
                        'style' => $query_user->getGroupClass(),
                        'avatar' => $query_user->getAvatar()
                    );
                } else {
                    $users_array[$result->banned_by_name] = array(
                        'profile' => null,
                        'style' => null,
                        'avatar' => Util::getAvatarFromUUID($result->banned_by_uuid)
                    );
                }
            }
            if(isset($result->removed_by_name) && !isset($users_array[$result->removed_by_name])){
                $query_user = new User($result->removed_by_name);
                if($query_user->exists()){
                    $users_array[$result->removed_by_name] = array(
                        'profile' => URL::build('/profile/' . Output::getClean($result->removed_by_name)),
                        'style' => $query_user->getGroupClass(),
                        'avatar' => $query_user->getAvatar()
                    );
                } else {
                    $users_array[$result->removed_by_name] = array(
                        'profile' => null,
                        'style' => null,
                        'avatar' => Util::getAvatarFromUUID($result->removed_by_uuid)
                    );
                }
            }

            if(isset($result->removed_by_uuid) && isset($result->removed_by_name) && isset($result->removed_by_date)){
                $removed_by_uuid = $result->removed_by_uuid;
                $removed_by_name = $result->removed_by_name;
                $removed_by_date = round($result->removed_by_date / 1000);
                $removed_by_link = $users_array[$result->removed_by_name]['profile'];
                $removed_by_style = $users_array[$result->removed_by_name]['style'];
                $removed_by_avatar = $users_array[$result->removed_by_name]['avatar'];
            } else {
                $removed_by_uuid = null;
                $removed_by_name = null;
                $removed_by_date = null;
                $removed_by_link = null;
                $removed_by_style = null;
                $removed_by_avatar = null;
            }

            switch($result->type){
                case 'ban':
                    if($result->until > 0) {
                        $type_id = 1; // temp ban
                        $type = $infractions_language->get('infractions', 'temp_ban');
                    } else {
                        $type_id = 2; // ban
                        $type = $infractions_language->get('infractions', 'ban');
                    }
                    break;
                case 'mute':
                    if($result->until > 0) {
                        $type_id = 3; // temp mute
                        $type = $infractions_language->get('infractions', 'temp_mute');
                    } else {
                        $type_id = 4; // mute
                        $type = $infractions_language->get('infractions', 'mute');
                    }
                    break;
                case 'kick':
                    $type_id = 5; // kick
                    $type = $infractions_language->get('infractions', 'kick');
                    break;
                case 'warning':
                    $type_id = 6; // warning
                    $type = $infractions_language->get('infractions', 'warning');
                    break;
                default:
                    $type_id = 7; // unknown
                    $type = $infractions_language->get('infractions', 'unknown');
                    break;
            }

            $result->active = (($type_id == 1 || $type_id == 3)) ?  time() < (int)($result->until / 1000) : ((isset($result->active) && $result->active == 1) ? 0 : 1);

            $infractions_array[] = array(
                'username' => Output::getClean($result->name),
                'profile' => $users_array[$result->name]['profile'],
                'username_style' => $users_array[$result->name]['style'],
                'avatar' => $users_array[$result->name]['avatar'],
                'staff_member' => Output::getClean($result->banned_by_name),
                'staff_member_link' => $users_array[$result->banned_by_name]['profile'],
                'staff_member_style' => $users_array[$result->banned_by_name]['style'],
                'staff_member_avatar' => $users_array[$result->banned_by_name]['avatar'],
                'revoked_staff_member' => Output::getClean($result->banned_by_name),
                'revoked_staff_member_link' => $users_array[$result->banned_by_name]['profile'],
                'revoked_staff_member_style' => $users_array[$result->banned_by_name]['style'],
                'revoked_staff_member_avatar' => $users_array[$result->banned_by_name]['avatar'],
                'issued' => $timeago->inWords(date('d M Y, H:i', (int)($result->time / 1000)), $language->getTimeLanguage()),
                'issued_full' => date('d M Y, H:i', (int)($result->time / 1000)),
                'action' => $type,
                'action_id' => $type_id,
                'expires' => (($type_id == 1 || $type_id == 3) ? $timeago->inWords(date('d M Y, H:i', (int)($result->until / 1000)), $language->getTimeLanguage()) : null),
                'expires_full' => (($type_id == 1 || $type_id == 3) ? date('d M Y, H:i', (int)($result->until / 1000)) : null),
                'revoked' => !$result->active,
                'revoked_full' => ((!isset($result->active) || $result->active == 0) ? $infractions_language->get('infractions', 'expired') : $infractions_language->get('infractions', 'active')),
                'reason' => preg_replace('(\xa7|&).', '', Output::getPurified($result->reason)),
                'view_link' => URL::build('/infractions/' . Output::getClean($result->type) . '/' . $result->id)
            );
        }
        $infractions_list = null;

        // Smarty variables
        $smarty->assign(array(
            'INFRACTIONS' => $infractions_language->get('infractions', 'infractions'),
            'INFRACTIONS_LIST' => $infractions_array,
            'SEARCH' => $infractions_language->get('infractions', 'search'),
            'TOKEN' => Token::get(),
            'USERNAME' => $infractions_language->get('infractions', 'username'),
            'STAFF_MEMBER' => $infractions_language->get('infractions', 'staff_member'),
            'ACTION' => $infractions_language->get('infractions', 'action'),
            'REASON' => $infractions_language->get('infractions', 'reason'),
            'VIEW' => $infractions_language->get('infractions', 'view'),
            'ISSUED' => $infractions_language->get('infractions', 'issued')
        ));
    } else
        $smarty->assign(array(
            'INFRACTIONS' => $infractions_language->get('infractions', 'infractions'),
            'NO_INFRACTIONS' => $infractions_language->get('infractions', 'no_infractions')
        ));

    $template_file = 'infractions/infractions.tpl';
} else if(isset($_GET['view'])) {

} else if(isset($_GET['id'])) {

}

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets);

$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));

$template->onPageLoad();
	
$smarty->assign('WIDGETS', $widgets->getWidgets());
	
require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');
	
// Display template
$template->displayTemplate($template_file, $smarty);