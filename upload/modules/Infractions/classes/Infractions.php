<?php
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr7
 *
 *  License: MIT
 *
 *  Infractions class
 */

abstract class Infractions {
    protected array $_data;
    protected array $_db_details;
    protected Cache $_cache;
    protected ?DB $_db;
    protected Language $_language;

    // Constructor
    public function __construct(array $inf_db, Language $language){
        $this->_db_details = $inf_db;
        $this->_language = $language;
        $this->_cache = new Cache(array('name' => 'nameless', 'extension' => '.cache', 'path' => ROOT_PATH . '/cache/infractions/'));
    }

    // Connect to database
    protected function initDB() {
        $this->_db = DB::getCustomInstance($this->_db_details['address'], $this->_db_details['name'], $this->_db_details['username'], $this->_db_details['password'], $this->_db_details['port'], null, '');
    }

    // Order array of objects using created attribute
    protected function date_compare($a, $b): int {
        if (!isset($a->created) || !isset($b->created)) {
            $a->created = $this->getCreationTime($a);
            $b->created = $this->getCreationTime($b);
        }

        if ($a->created == $b->created) return 0;
        return ($a->created < $b->created) ? 1 : -1;
    }

    /**
     * Retrieve a list of all infractions, either from cache or database
     * @param int $page
     * @param int $limit
     * @return array
     */
    abstract public function listInfractions(int $page, int $limit): array;

    /**
     * Retrieve total number of infractions
     * @return int
     */
    abstract protected function getTotal(): int;

}
