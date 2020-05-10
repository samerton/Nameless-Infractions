<?php
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr7
 *
 *  License: MIT
 *
 *  AdvancedBan class
 */

class AdvancedBan extends Infractions {

    // Variables
    protected $_extra;

    // Constructor
    public function __construct($inf_db, $language) {
        parent::__construct($inf_db, $language);

        if(file_exists(ROOT_PATH . '/modules/Infractions/extra.php'))
            require_once(ROOT_PATH . '/modules/Infractions/extra.php');
        else {
            $inf_extra = array('advancedban');

            $inf_extra['advancedban'] = array(
                'punishments_table' => 'Punishments',
                'punishment_history_table' => 'PunishmentHistory'
            );
        }

        $this->_extra = $inf_extra['advancedban'];
    }

    // Retrieve a list of all infractions, either from cache or database
    public function listInfractions($page, $limit){
        // Cached?
        $cache = $this->_cache;
        $cache->setCache('infractions_infractions');
        if($cache->isCached('infractions' . $page)){
            $mapped_punishments = $cache->retrieve('infractions' . $page);
        } else {
            $this->initDB();

            $total = $this->getTotal()->first()->total;
            $infractions = $this->listAll($page, $limit)->results();

	        $mapped_punishments = array();
	        $staff_usernames = array();

	        $mapped_punishments['total'] = $total;

	        if(count($infractions)){
		        foreach($infractions as $punishment){
			        $staff_uuid = $punishment->operator;

			        if(!isset($staff_usernames[$punishment->operator])){
				        $staff_query = DB::getInstance()->query('SELECT uuid FROM nl2_users WHERE username = ?', array($punishment->operator));
				        if($staff_query->count()){
					        $staff_uuid = $staff_query->first()->uuid;
					        $staff_usernames[$punishment->operator] = $staff_uuid;
				        }
			        } else {
				        $staff_uuid = $staff_usernames[$punishment->operator];
			        }

			        $mapped_punishments[] = (object) array(
				        'id' => $punishment->id,
				        'name' => $punishment->name,
				        'uuid' => $punishment->uuid,
				        'reason' => $punishment->reason,
				        'banned_by_uuid' => $staff_uuid,
				        'banned_by_name' => $punishment->operator,
				        'removed_by_uuid' => '',
				        'removed_by_name' => '',
				        'removed_by_date' => '',
				        'time' => $punishment->start,
				        'until' => $punishment->end > 0 ? $punishment->end : null,
				        'ipban' => '',
				        'active' => $punishment->pstart ? 1 : 0,
				        'type' => $this->mapType($punishment->punishmentType)
			        );
		        }
	        }

            $cache->store('infractions' . $page, $mapped_punishments, 120);
        }

        return $mapped_punishments;
    }

    // List all infractions
	public function listAll($page, $limit){
    	$start = ($page - 1) * $limit;

    	return $this->_db->query(
    		$this->getPunishmentQuery() . ' LIMIT ?,?',
		    array($start, $limit)
	    );
	}

    // Get total rows
	protected function getTotal(){
    	return $this->_db->query(
    		'SELECT (SELECT COUNT(*) FROM ' . $this->_extra['punishment_history_table'] . ') AS total', array()
	    );
	}

	// Get bans query
	private function getPunishmentQuery(){
    	return 'SELECT ph.id, ph.name, ph.uuid, ph.reason, ph.operator, ph.punishmentType, p.start as pstart, ph.start, ph.end' .
		       ' FROM ' . $this->_extra['punishments_table'] . ' AS p' .
		       ' RIGHT JOIN (SELECT id, name, uuid, reason, operator, punishmentType, start, end FROM ' . $this->_extra['punishment_history_table'] . ') AS ph ON p.start = ph.start' .
		       ' WHERE ph.punishmentType <> \'IP_BAN\' ORDER BY ph.start DESC';
	}

	// Map punishment type
	private function mapType($type){
    	switch($type){
		    case 'BAN':
		    case 'TEMP_BAN':
		    case 'IP_BAN':
		    case 'TEMP_IP_BAN':
		    	return 'ban';

		    case 'KICK':
		    	return 'kick';

		    case 'MUTE':
		    case 'TEMP_MUTE':
		    	return 'mute';

		    case 'WARNING':
		    case 'TEMP_WARNING':
		    	return 'warning';
	    }

	    return 'unknown';
	}
}
