<?php
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr7
 *
 *  License: MIT
 *
 *  LiteBans class
 */

class LiteBans extends Infractions {

    // Variables
    protected $_extra;

    // Constructor
    public function __construct($inf_db, $language) {
        parent::__construct($inf_db, $language);

        if(file_exists(ROOT_PATH . '/modules/Infractions/extra.php'))
            require_once(ROOT_PATH . '/modules/Infractions/extra.php');
        else {
            $inf_extra = array('litebans');

            $inf_extra['litebans'] = array(
                'bans_table' => 'litebans_bans',
                'kicks_table' => 'litebans_kicks',
                'mutes_table' => 'litebans_mutes',
                'warnings_table' => 'litebans_warnings',
                'history_table' => 'litebans_history'
            );
        }

        $this->_extra = $inf_extra['litebans'];
    }

    // Retrieve a list of all infractions, either from cache or database
    public function listInfractions($page, $limit){
        // Cached?
        $cache = $this->_cache;
        $cache->setCache('infractions_infractions');
        if($cache->isCached('infractions' . $page)){
            $infractions = $cache->retrieve('infractions' . $page);
        } else {
            $this->initDB();

            $total = $this->getTotal()->first()->total;
            $infractions = $this->listAll($page, $limit)->results();
	        $infractions['total'] = $total;

            $cache->setCache('infractions_infractions');
            $cache->store('infractions' . $page, $infractions, 120);
        }

        return $infractions;
    }

    // List all infractions
	public function listAll($page, $limit){
    	$start = ($page - 1) * $limit;

    	return $this->_db->query(
    		'(' . $this->getBansQuery() . ') UNION ' .
    		'(' . $this->getKicksQuery() . ') UNION ' .
    		'(' . $this->getMutesQuery() . ') UNION ' .
    		'(' . $this->getWarningsQuery() . ') ORDER BY `time` DESC LIMIT ?,?',
		    array($start, $limit)
	    );
	}

    // List all bans
    public function listBans(){
        // Cached?
        $cache = $this->_cache;
        $cache->setCache('infractions_bans');
        if($cache->isCached('bans')){
            $bans = $cache->retrieve('bans');
        } else {
            $bans = $this->_db->query($this->getBansQuery(), array());

            if($bans->count()){
                $cache->store('bans', $bans->results(), 120);
                $bans = $bans->results();
            } else $bans = array();
        }

        return $bans;
    }

    // List all kicks
    public function listKicks(){
        // Cached?
        $cache = $this->_cache;
        $cache->setCache('infractions_kicks');
        if($cache->isCached('kicks')){
            $kicks = $this->_cache->retrieve('kicks');
        } else {
            $kicks = $this->_db->query($this->getKicksQuery(), array());

            if($kicks->count()){
                $cache->store('kicks', $kicks->results(), 120);
                $kicks = $kicks->results();
            } else $kicks = array();
        }

        return $kicks;
    }

    // List all mutes
    public function listMutes(){
        // Cached?
        $cache = $this->_cache;
        $cache->setCache('infractions_mutes');
        if($cache->isCached('mutes')){
            $mutes = $this->_cache->retrieve('mutes');
        } else {
            $mutes = $this->_db->query($this->getMutesQuery(), array());

            if($mutes->count()){
                $cache->store('mutes', $mutes->results(), 120);
                $mutes = $mutes->results();
            } else $mutes = array();
        }

        return $mutes;
    }

    // List all warnings
    public function listWarnings(){
        // Cached?
        $cache = $this->_cache;
        $cache->setCache('infractions_warnings');
        if($cache->isCached('warnings')){
            $warnings = $this->_cache->retrieve('warnings');
        } else {
            $warnings = $this->_db->query($this->getWarningsQuery(), array());

            if($warnings->count()){
                $cache->store('warnings', $warnings->results(), 120);
                $warnings = $warnings->results();
            } else $warnings = array();
        }

        return $warnings;
    }

    // Get a username from a UUID
    public function getUsername($uuid){
        $user = $this->_db->query('SELECT `name` FROM ' . $this->_extra['history_table'] . ' WHERE uuid = ?', array($uuid));

        if($user->count()) return $user->first()->name;
        else return false;
    }

    // Get creation time from infraction
    public static function getCreationTime($item){
        if(isset($item->time)){
            return $item->time;
        } else return false;
    }

    // Get total rows
	protected function getTotal(){
    	return $this->_db->query(
    		'SELECT (SELECT COUNT(*) FROM ' . $this->_extra['bans_table'] . ') + (SELECT COUNT(*) FROM ' . $this->_extra['kicks_table'] . ') + (SELECT COUNT(*) FROM ' . $this->_extra['mutes_table'] . ') + (SELECT COUNT(*) FROM ' . $this->_extra['warnings_table'] . ') AS total', array()
	    );
	}

    // Get bans query
	private function getBansQuery(){
    	return 'SELECT bans.id, bans.ip, bans.uuid, bans.reason, bans.banned_by_uuid, bans.banned_by_name, bans.removed_by_uuid, bans.removed_by_name, bans.removed_by_date, bans.time, bans.until, bans.ipban, bans.active, bans.server_scope, bans.server_origin, history.name, "ban" as type' .
		    ' FROM ' . $this->_extra['bans_table'] . ' AS bans' .
		    ' LEFT JOIN (SELECT name, uuid FROM ' . $this->_extra['history_table'] . ') AS history ON bans.uuid = history.uuid' .
		    ' ORDER BY bans.time DESC';
	}

	// Get kicks
	private function getKicksQuery(){
		return 'SELECT bans.id, bans.ip, bans.uuid, bans.reason, bans.banned_by_uuid, bans.banned_by_name, "" as removed_by_uuid, "" as removed_by_name, "" as removed_by_date, bans.time, "" as until, "" as ipban, "" as active, bans.server_scope, bans.server_origin, history.name, "kick" as type' .
			' FROM ' . $this->_extra['kicks_table'] . ' AS bans' .
			' LEFT JOIN (SELECT name, uuid FROM ' . $this->_extra['history_table'] . ') AS history ON bans.uuid = history.uuid' .
			' ORDER BY bans.time DESC';
	}

	// Get mutes
	private function getMutesQuery(){
    	return 'SELECT bans.id, bans.ip, bans.uuid, bans.reason, bans.banned_by_uuid, bans.banned_by_name, bans.removed_by_uuid, bans.removed_by_name, bans.removed_by_date, bans.time, bans.until, bans.ipban, bans.active, bans.server_scope, bans.server_origin, history.name, "mute" as type' .
		    ' FROM ' . $this->_extra['mutes_table'] . ' AS bans' .
		    ' LEFT JOIN (SELECT name, uuid FROM ' . $this->_extra['history_table'] . ') AS history ON bans.uuid = history.uuid' .
		    ' ORDER BY bans.time DESC';
	}

	// Get warnings
	private function getWarningsQuery(){
    	return 'SELECT bans.id, bans.ip, bans.uuid, bans.reason, bans.banned_by_uuid, bans.banned_by_name, bans.removed_by_uuid, bans.removed_by_name, bans.removed_by_date, bans.time, bans.until, bans.ipban, bans.active, bans.server_scope, bans.server_origin, history.name, "warning" as type' .
		    ' FROM ' . $this->_extra['warnings_table'] . ' AS bans' .
		    ' LEFT JOIN (SELECT name, uuid FROM ' . $this->_extra['history_table'] . ') AS history ON bans.uuid = history.uuid' .
		    ' ORDER BY bans.time DESC';
	}

}