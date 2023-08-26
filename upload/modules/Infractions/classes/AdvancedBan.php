<?php
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.1.0
 *
 *  Licence: MIT
 *
 *  AdvancedBan class
 */

class AdvancedBan extends Infractions {

    // Variables
    protected array $_extra;

    // Constructor
    public function __construct($inf_db, $language) {
        parent::__construct($inf_db, $language);

        if (file_exists(ROOT_PATH . '/modules/Infractions/extra.php'))
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

    /**
     * Retrieve a list of all infractions, either from cache or database
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function listInfractions(int $page, int $limit): array {
        // Cached?
        $cache = $this->_cache;
        $cache->setCache('infractions_infractions');
        if ($cache->isCached('infractions' . $page)) {
            $mapped_punishments = $cache->retrieve('infractions' . $page);
        } else {
            $this->initDB();

            $total = $this->getTotal();
            $infractions = $this->listAll($page, $limit)->results();

	        $mapped_punishments = [];
	        $staff_usernames = [];

	        $mapped_punishments['total'] = $total;

	        if (count($infractions)) {
		        foreach ($infractions as $punishment) {
			        $staff_uuid = $punishment->operator;

			        if (!isset($staff_usernames[$punishment->operator])) {
				        $staff_query = DB::getInstance()->query(
                            'SELECT identifier FROM nl2_users_integrations WHERE username = ?',
                            [$punishment->operator]
                        );
				        if ($staff_query->count()) {
					        $staff_uuid = $staff_query->first()->identifier;
					        $staff_usernames[$punishment->operator] = $staff_uuid;
				        }
			        } else {
				        $staff_uuid = $staff_usernames[$punishment->operator];
			        }

                    $type = $this->mapType($punishment->punishmentType);

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
				        'time' => $punishment->start / 1000,
				        'until' => $punishment->end > 0 ? ($punishment->end / 1000) : null,
				        'ipban' => $type === 'ipban',
				        'active' => $punishment->pstart ? 1 : 0,
				        'type' => $type,
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

    /**
     * Retrieve total number of infractions
     * @return int
     */
	protected function getTotal(): int {
    	return $this->_db->query(
    		'SELECT (SELECT COUNT(*) FROM ' . $this->_extra['punishment_history_table'] . ') AS total'
	    )->first()->total;
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
		    	return 'ban';

            case 'IP_BAN':
            case 'TEMP_IP_BAN':
                return 'ipban';

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
