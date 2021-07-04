<?php
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr10
 *
 *  License: MIT
 *
 *  LibertyBans class
 */

class LibertyBans extends Infractions {

    // Variables
    protected $_extra;
    private $_playerCache;

    // Constructor
    public function __construct($inf_db, $language) {
        parent::__construct($inf_db, $language);

        if(file_exists(ROOT_PATH . '/modules/Infractions/extra.php'))
            require_once(ROOT_PATH . '/modules/Infractions/extra.php');
        else {
            $inf_extra = array('libertybans');

            $inf_extra['libertybans'] = array(
                'active_table' => 'libertybans_simple_active',
                'history_table' => 'libertybans_simple_history',
                'name_table' => 'libertybans_names'
            );
        }

        $this->_extra = $inf_extra['libertybans'];
        $this->_playerCache = array();
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
            $mapped_punishments = array();

            $total = $this->getTotal()->first()->total;
            $mapped_punishments['total'] = $total;

            $infractions = $this->listAll($page, $limit)->results();
            if ($infractions && count($infractions)) {
                foreach($infractions as $punishment) {
                    $mapped_punishments[] = (object) array(
                        'id' => $punishment->id,
                        'name' => $punishment->victim_name ?: $this->getNameFromUUID(strtolower($punishment->victim)),
                        'uuid' => $punishment->victim,
                        'reason' => $punishment->reason,
                        'banned_by_uuid' => $punishment->operator,
                        'banned_by_name' => $punishment->operator == '00000000000000000000000000000000' ? 'CONSOLE' : $punishment->operator_name ?: $this->getNameFromUUID(strtolower($punishment->operator)),
                        'removed_by_uuid' => '',
                        'removed_by_name' => '',
                        'removed_by_date' => '',
                        'time' => $punishment->start * 1000,
                        'until' => $punishment->end > 0 ? ($punishment->end * 1000) : null,
                        'ipban' => '',
                        'active' => $punishment->active,
                        'type' => $this->mapType($punishment->type)
                    );
                }
            }

            $cache->setCache('infractions_infractions');
            $cache->store('infractions' . $page, $mapped_punishments, 120);
        }

        return $mapped_punishments;
    }

    // List all infractions
    public function listAll($page, $limit){
        $start = ($page - 1) * $limit;

        return $this->_db->query(
            'SELECT h.id AS `id`, h.type AS `type`, HEX(h.victim) AS `victim`, vn.name AS `victim_name`, HEX(h.operator) AS `operator`, sn.name AS `operator_name`, h.reason AS `reason`, h.start AS `start`, h.end AS `end`, IF(a.id IS NULL, FALSE, TRUE) AS `active` FROM ' . $this->_extra['history_table'] . ' AS `h` LEFT JOIN ' . $this->_extra['active_table'] . ' AS `a` ON (h.id = a.id) LEFT JOIN ' . $this->_extra['name_table'] . ' AS `vn` ON (h.victim = vn.uuid) LEFT JOIN ' . $this->_extra['name_table'] . ' AS `sn` ON (h.operator = sn.uuid) WHERE h.victim_type = \'PLAYER\' ORDER BY `start` DESC LIMIT ?,?',
            array($start, $limit)
        );
    }

    // Get total number of infractions
    protected function getTotal(){
        return $this->_db->query(
            'SELECT COUNT(*) AS total FROM ' . $this->_extra['history_table'] . ' WHERE victim_type = \'PLAYER\'', array()
        );
    }

    // Get name from UUID
    private function getNameFromUUID($uuid){
        if ($this->_playerCache[$uuid]) return $this->_playerCache[$uuid];
        $profile = ProfileUtils::getProfile($uuid);
        $username = $profile ? $profile->getUsername() : 'Unknown';
        return $this->_playerCache[$uuid] = $username;
    }

    // Map punishment type
    private function mapType($type){
        switch($type){
            case 'BAN':
                return 'ban';

            case 'KICK':
                return 'kick';

            case 'MUTE':
                return 'mute';

            case 'WARN':
                return 'warning';
        }

        return 'unknown';
    }
}