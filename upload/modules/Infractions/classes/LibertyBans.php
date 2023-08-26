<?php
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.1.0
 *
 *  Licence: MIT
 *
 *  LibertyBans class
 */

class LibertyBans extends Infractions {

    // Variables
    protected array $_extra;
    private array $_playerCache;

    public function __construct($inf_db, $language) {
        parent::__construct($inf_db, $language);

        if (file_exists(ROOT_PATH . '/modules/Infractions/extra.php'))
            require_once(ROOT_PATH . '/modules/Infractions/extra.php');
        else {
            $inf_extra = array('libertybans');

            $inf_extra['libertybans'] = array(
                'history_view' => 'libertybans_simple_history',
                'names_view' => 'libertybans_latest_names',
            );
        }

        $this->_extra = $inf_extra['libertybans'];
        $this->_playerCache = array();
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
            $mapped_punishments = array();

            $total = $this->getTotal();
            $mapped_punishments['total'] = $total;

            $infractions = $this->listAll($page, $limit);

            if (!empty($infractions)) {
                $mapped_punishments = array_merge($mapped_punishments, array_map(fn ($punishment) => (object) [
                    'id' => $punishment->id,
                    'name' =>
                        $punishment->victim_name ?: (
                        $punishment->victim_uuid === '00000000000000000000000000000000'
                            ? 'Unknown'
                            : $this->getNameFromUUID(strtolower($punishment->victim_uuid))
                        ),
                    'uuid' => strtolower($punishment->victim_uuid),
                    'reason' => $punishment->reason,
                    'banned_by_uuid' =>
                        $punishment->operator == '00000000000000000000000000000000'
                            ? 'CONSOLE'
                            : strtolower($punishment->operator),
                    'banned_by_name' =>
                        $punishment->operator == '00000000000000000000000000000000'
                            ? 'CONSOLE'
                            : (
                        $punishment->operator_name ?:
                            $this->getNameFromUUID(strtolower($punishment->operator))
                        ),
                    'removed_by_uuid' => '',
                    'removed_by_name' => '',
                    'removed_by_date' => '',
                    'time' => $punishment->start,
                    'until' => $punishment->end > 0 ? $punishment->end : null,
                    'ipban' => $punishment->victim_address !== '00000000',
                    'active' => !$punishment->end || $punishment->end > date('U'),
                    'type' => $this->mapType($punishment->type, $punishment->victim_address !== '00000000')
                ], $infractions));
            }

            $cache->setCache('infractions_infractions');
            $cache->store('infractions' . $page, $mapped_punishments, 120);
        }

        return $mapped_punishments;
    }

    /**
     * List all infractions
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function listAll(int $page, int $limit): array {
        $start = ($page - 1) * $limit;

        return $this->_db->query(
            <<<SQL
                SELECT
                    h.id,
                    h.type,
                    HEX(h.victim_uuid) as victim_uuid,
                    HEX(h.operator) as operator,
                    h.reason,
                    h.start,
                    h.end,
                    HEX(h.victim_address) as victim_address,
                    no.name AS operator_name,
                    nv.name AS victim_name
                FROM {$this->_extra['history_view']} h
                LEFT JOIN {$this->_extra['names_view']} no ON h.operator = no.uuid
                LEFT JOIN {$this->_extra['names_view']} nv ON h.victim_uuid = nv.uuid
                WHERE h.victim_type IN (0, 1)
                ORDER BY h.start DESC LIMIT ?,?
            SQL,
            [
                $start,
                $limit,
            ]
        )->results();
    }

    /**
     * Retrieve total number of infractions
     * @return int
     */
    protected function getTotal(): int {
        return
            $this->_db->query(
                <<<SQL
                    SELECT COUNT(*) AS total
                    FROM {$this->_extra['history_view']}
                    WHERE victim_type IN (0, 1)
                SQL
            )->first()->total;
    }

    // Get name from UUID
    private function getNameFromUUID($uuid) {
        if ($this->_playerCache[$uuid]) {
            return $this->_playerCache[$uuid];
        }

        $profile = ProfileUtils::getProfile($uuid);
        $username = $profile ? $profile->getUsername() : 'Unknown';
        return $this->_playerCache[$uuid] = $username;
    }

    /**
     * Map punishment type
     * @param int $type
     * @param bool $ipBan
     * @return string
     */
    private function mapType(int $type, bool $ipBan): string {
        if ($ipBan) {
            return 'ipban';
        }

        switch ($type) {
            case 0:
                return 'ban';

            case 1:
                return 'mute';

            case 2:
                return 'warning';

            case 3:
                return 'kick';
        }

        return 'unknown';
    }
}