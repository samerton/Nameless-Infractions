<?php
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  Infractions page
 */

// Always define page name
define('PAGE', 'infractions');

// Get page
if(isset($_GET['p'])){
	if(!is_numeric($_GET['p'])){
		Redirect::to(URL::build('/infractions'));
		die();
	} else $p = $_GET['p'];
} else $p = 1;

$timeago = new Timeago(TIMEZONE);
$paginator = new Paginator();

require('core/integration/uuid.php');

$inf_plugin = 'bat';
$infractions = new BungeeAdminTools($inf_db, $infractions_language, $cache);
?>
<!DOCTYPE html>
<html lang="<?php echo (defined('HTML_LANG') ? HTML_LANG : 'en'); ?>">
  <head>
    <!-- Standard Meta -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- Site Properties -->
	<?php 
	$title = $infractions_language->get('infractions', 'infractions');
	require('core/templates/header.php'); 
	?>
  
  </head>
  <body>
    <?php 
	require('core/templates/navbar.php'); 
	require('core/templates/footer.php');
	
	if(!isset($_GET['view']) && !isset($_GET['id'])){
		$infractions_list = $infractions->listInfractions();
		
		// Pagination
		$results = $paginator->getLimited($infractions_list, 10, $p, count($infractions_list));
		$pagination = $paginator->generate(7, URL::build('/infractions'));
		
		$smarty->assign('PAGINATION', $pagination);
		
		$infractions_array = array();
		for($n = 0; $n < count($results->data); $n++){
			switch($inf_plugin){
				case 'bat':
					// Get username from UUID
					$username = $infractions->getUsername($results->data[$n]->UUID);
					
					if(empty($username)){
						// Not stored in BAT database, check Nameless cache
						$cache_check = $queries->getWhere('uuid_cache', array('uuid', '=', $results->data[$n]->UUID));
						
						if(!count($cache_check)){
							// Not cached locally, get it now
							$profile = ProfileUtils::getProfile($results->data[$n]->UUID);
							if(empty($profile)){
								$username = 'Unknown';
							} else {
								$result = $profile->getProfileAsArray();
								if(isset($result['username'])){
									$username = $result['username'];
									
									try {
										$queries->create("uuid_cache", array(
											'mcname' => Output::getClean($result['username']),
											'uuid' => Output::getClean($results->data[$n]->UUID)
										));
									} catch(Exception $e){
										die($e->getMessage());
									}
									
								} else $username = 'Unknown';
							}
							
						} else {
							$username = $cache_check[0]->mcname;
						}

						$cache_check = null;
					}
					
					// Does the user have an account on the website?
					$account = $queries->getWhere('users', array('uuid', '=', $results->data[$n]->UUID));
					if(count($account)){
						$username_link = URL::build('/profile/' . Output::getClean($account[0]->username));
						$username_style = $user->getGroupClass($account[0]->id);
					} else {
						$username_link = '#';
						$username_style = '';
					}
				
					if(isset($results->data[$n]->ban_id)){
						// Get staff member's website account
						if($results->data[$n]->ban_staff != 'CONSOLE'){
							$staff_member = $queries->getWhere('users', array('username', '=', $results->data[$n]->ban_staff));
							
							if(count($staff_member)){
								$staff_member = $staff_member[0];
								
								$staff_member_link = URL::build('/profile/' . Output::getClean($staff_member->username));
								$staff_member_style = $user->getGroupClass($staff_member->id);
							} else {
								$staff_member_link = '#';
								$staff_member_style = '';
							}
							
						} else {
							$staff_member_link = '#';
							$staff_member_style = '';
						}
						
						$infractions_array[] = array(
							'username' => Output::getClean($username),
							'username_link' => $username_link,
							'username_style' => $username_style,
							'staff_member' => Output::getClean($results->data[$n]->ban_staff),
							'staff_member_link' => $staff_member_link,
							'staff_member_style' => $staff_member_style,
							'issued' => $timeago->inWords(date('d M Y, H:i', strtotime($results->data[$n]->ban_begin)), $language->getTimeLanguage()),
							'issued_full' => date('d M Y, H:i', strtotime($results->data[$n]->ban_begin)),
							'action' => ((!is_null($results->data[$n]->ban_end)) ? $infractions_language->get('infractions', 'temp_ban') : $infractions_language->get('infractions', 'ban')),
							'action_id' => ((!is_null($results->data[$n]->ban_end)) ? 2 : 1),
							'expires' => ((!is_null($results->data[$n]->ban_end)) ? $timeago->inWords(date('d M Y, H:i', strtotime($results->data[$n]->ban_end)), $language->getTimeLanguage()) : null),
							'expires_full' => ((!is_null($results->data[$n]->ban_end)) ? date('d M Y, H:i', strtotime($results->data[$n]->ban_end)) : null),
							'revoked' => (($results->data[$n]->ban_state == 0) ? 1 : 0),
							'status' => (($results->data[$n]->ban_state == 0) ? $infractions_language->get('infractions', 'expired') : $infractions_language->get('infractions', 'active')),
							'reason' => Output::getPurified($results->data[$n]->ban_reason),
							'view_link' => URL::build('/infractions/', 'id=' . $results->data[$n]->ban_id . '&amp;type=ban')
						);
					} else if(isset($results->data[$n]->mute_id)){
						// Get username from UUID
						
					} else if(isset($results->data[$n]->kick_id)){
						// Get username from UUID
						
					}
				break;
				
				case 'bm':
				
				break;
				
				default:
					die('Invalid infractions plugin selected!');
				break;
			}
		}
		//$infractions_list = null;
		
		// Smarty variables
		$smarty->assign(array(
			'INFRACTIONS' => $infractions_language->get('infractions', 'infractions'),
			'INFRACTIONS_LIST' => $infractions_array,
			'SEARCH' => $infractions_language->get('infractions', 'search'),
			'TOKEN' => Token::generate(),
			'USERNAME' => $infractions_language->get('infractions', 'username'),
			'STAFF_MEMBER' => $infractions_language->get('infractions', 'staff_member'),
			'ACTION' => $infractions_language->get('infractions', 'action'),
			'REASON' => $infractions_language->get('infractions', 'reason'),
			'VIEW' => $infractions_language->get('infractions', 'view'),
			'ISSUED' => $infractions_language->get('infractions', 'issued')
		));
		
		$smarty->display('custom/templates/' . TEMPLATE . '/infractions/infractions.tpl');
		
		//echo '<pre>', print_r($infractions_list), '</pre>';
	} else if(isset($_GET['view'])) {
		
	} else if(isset($_GET['id'])) {
		
	}
	
    require('core/templates/scripts.php'); ?>
	
  </body>
</html>