<?php
/**
 *	Made by Samerton and Partydragen
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.1.0
 *
 * @license MIT
 *
 * @var Cache $cache
 * @var Language $infractions_language
 * @var Language $language
 * @var Smarty $smarty
 * @var User $user
 */

// Can the user view the panel?
if ($user->isLoggedIn()) {
    if (!$user->hasPermission('infractions.view')) {
        require_once ROOT_PATH . '/404.php';
        die();
    }
} else {
    $cache->setCache('infractions_module_cache');
    if (!$cache->isCached('guests_view')) {
        $guests_view = 0;
        $cache->store('guests_view', 0);
    } else {
        $guests_view = $cache->retrieve('guests_view');
    }
    if (!$guests_view) {
        require_once ROOT_PATH . '/404.php';
        die();
    }
}

// Always define page name
const PAGE = 'infractions';
$page_title = $infractions_language->get('infractions', 'infractions');
require_once ROOT_PATH . '/core/templates/frontend_init.php';

// Get page
$p = 1;

if (isset($_GET['p'])) {
    if (!is_numeric($_GET['p'])) {
        Redirect::to(URL::build('/infractions'));
    }

    $p = intval($_GET['p']);
}

$timeago = new TimeAgo(TIMEZONE);

if (!file_exists(ROOT_PATH . '/modules/Infractions/config.php')) {
	die('Please configure the Infractions module in the StaffCP first!');
}
require ROOT_PATH . '/modules/Infractions/config.php';
if (!isset($inf_db)) {
	die('Please configure the Infractions module in the StaffCP first!');
}

/** @var array $inf_config */
require_once ROOT_PATH . '/modules/Infractions/classes/Infractions.php';

switch ($inf_config['plugin']) {
	case 'libertybans':
		// LibertyBans integration
		require_once ROOT_PATH . '/modules/Infractions/classes/LibertyBans.php';
		$infractions = new LibertyBans($inf_db, $infractions_language);
	break;

	case 'litebans':
		// LiteBans integration
		require_once ROOT_PATH . '/modules/Infractions/classes/LiteBans.php';
		$infractions = new LiteBans($inf_db, $infractions_language);
	break;

	case 'advancedban':
		// AdvancedBan integration
		require_once ROOT_PATH . '/modules/Infractions/classes/AdvancedBan.php';
		$infractions = new AdvancedBan($inf_db, $infractions_language);
		break;

	default:
		die('Plugin not supported!');
}

if (!isset($_GET['view']) && !isset($_GET['id'])) {
    $infractions_list = $infractions->listInfractions($p, 10);

    if (count($infractions_list)) {
        $total = $infractions_list['total'];
        unset($infractions_list['total']);

        // Pagination
        $paginator = new Paginator(($template_pagination ?? []));
        $paginator->getLimited($infractions_list, 10, $p, $total);
        $pagination = $paginator->generate(7, URL::build('/infractions', true));

        $smarty->assign('PAGINATION', $pagination);

        $infractionsArray = [];
        $usersArray = [];

        $integration = Integrations::getInstance()->getIntegration('Minecraft');

        foreach ($infractions_list as $result) {
            foreach (['', 'banned_by_', 'removed_by_'] as $key) {
                $nameKey = "{$key}name";
                $uuidKey = "{$key}uuid";

                 if (!$result->{$uuidKey}) {
                     continue;
                 }

                // Check if the punished/initiated/revoker user exists
                if (!isset($usersArray[$result->{$uuidKey}])) {
                    $exists = false;
                    $integrationUser = new IntegrationUser($integration, $result->{$uuidKey}, 'identifier');

                    if ($integrationUser->exists()) {
                        $punishedUser = $integrationUser->getUser();

                        if ($punishedUser->exists()) {
                            $exists = true;
                            $usersArray[$result->{$uuidKey}] = array(
                                'profile' => URL::build('/profile/' . Output::getClean($result->{$nameKey})),
                                'style' => $punishedUser->getGroupStyle(),
                                'avatar' => $punishedUser->getAvatar()
                            );
                        }
                    }

                    if (!$exists) {
                        $usersArray[$result->{$uuidKey}] = array(
                            'profile' => null,
                            'style' => null,
                            'avatar' => AvatarSource::getAvatarFromUUID($result->{$uuidKey} ?? $result->{$nameKey})
                        );
                    }
                }
            }

            $evaluate =
                isset($result->removed_by_uuid) &&
                isset($result->removed_by_name) &&
                isset($result->removed_by_date);

            $removed_by_uuid = $evaluate ? $result->removed_by_uuid : null;
            $removed_by_name = $evaluate ? $result->removed_by_name : null;
            $removed_by_date = $evaluate ? round(floatval($result->removed_by_date) / 1000) : null;
            $removed_by_link = $evaluate ? $usersArray[$result->removed_by_name]['profile'] : null;
            $removed_by_style = $evaluate ? $usersArray[$result->removed_by_name]['style'] : null;
            $removed_by_avatar = $evaluate ? $usersArray[$result->removed_by_name]['avatar'] : null;

            switch ($result->type) {
                case 'ban':
                    if ($result->until > 0) {
                        $type_id = 1; // temp ban
                        $type = $infractions_language->get('infractions', 'temp_ban');
                    } else {
                        $type_id = 2; // ban
                        $type = $infractions_language->get('infractions', 'ban');
                    }
                    break;

                case 'ipban':
                    if ($result->until > 0) {
                        $type_id = 8; // temp ban
                        $type = $infractions_language->get('infractions', 'temp_ipban');
                    } else {
                        $type_id = 9; // ban
                        $type = $infractions_language->get('infractions', 'ipban');
                    }
                    break;

                case 'mute':
                    if ($result->until > 0) {
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

            $infractionsArray[] = [
                'username' => Output::getClean($result->name),
                'profile' => $usersArray[$result->uuid]['profile'],
                'username_style' => $usersArray[$result->uuid]['style'],
                'avatar' => $usersArray[$result->uuid]['avatar'],
                'staff_member' => Output::getClean($result->banned_by_name),
                'staff_member_link' => $usersArray[$result->banned_by_uuid]['profile'],
                'staff_member_style' => $usersArray[$result->banned_by_uuid]['style'],
                'staff_member_avatar' => $usersArray[$result->banned_by_uuid]['avatar'],
                'revoked_staff_member' => Output::getClean($result->removed_by_name),
                'revoked_staff_member_link' => $usersArray[$result->removed_by_uuid]['profile'],
                'revoked_staff_member_style' => $usersArray[$result->removed_by_uuid]['style'],
                'revoked_staff_member_avatar' => $usersArray[$result->removed_by_uuid]['avatar'],
                'issued' => $timeago->inWords($result->time, $language),
                'issued_full' => date(DATE_FORMAT, $result->time),
                'action' => $type,
                'action_id' => $type_id,
                'expires' => (in_array($type_id, [1, 3, 8]) ? $timeago->inWords($result->until, $language) : null),
                'expires_full' => (in_array($type_id, [1, 3, 8]) ? date(DATE_FORMAT, $result->until) : null),
                'revoked' => ((isset($result->active) && $result->active == 1) ? 0 : 1),
                'revoked_full' => ((!isset($result->active) || $result->active == 0) ? $infractions_language->get('infractions', 'expired') : $infractions_language->get('infractions', 'active')),
                'reason' => Output::getPurified($result->reason),
                'view_link' => URL::build('/infractions/' . Output::getClean($result->type) . '/' . $result->id),
            ];
        }

        $infractions_list = null;

        // Smarty variables
        $smarty->assign([
            'INFRACTIONS' => $infractions_language->get('infractions', 'infractions'),
            'INFRACTIONS_LIST' => $infractionsArray,
            'SEARCH' => $infractions_language->get('infractions', 'search'),
            'TOKEN' => Token::get(),
            'USERNAME' => $infractions_language->get('infractions', 'username'),
            'STAFF_MEMBER' => $infractions_language->get('infractions', 'staff_member'),
            'ACTION' => $infractions_language->get('infractions', 'action'),
            'REASON' => $infractions_language->get('infractions', 'reason'),
            'VIEW' => $infractions_language->get('infractions', 'view'),
            'ISSUED' => $infractions_language->get('infractions', 'issued'),
        ]);
    } else {
        $smarty->assign([
            'INFRACTIONS' => $infractions_language->get('infractions', 'infractions'),
            'NO_INFRACTIONS' => $infractions_language->get('infractions', 'no_infractions')
        ]);
    }

    $template_file = 'infractions/infractions.tpl';
} else if (isset($_GET['view'])) {

} else if (isset($_GET['id'])) {

}

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

$template->onPageLoad();
	
$smarty->assign('WIDGETS_LEFT', $widgets->getWidgets('right'));
$smarty->assign('WIDGETS_RIGHT', $widgets->getWidgets('right'));

require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');
	
// Display template
$template->displayTemplate($template_file, $smarty);
