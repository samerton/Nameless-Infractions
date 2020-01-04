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
    protected $_language,
        $_cache,
        $_db,
        $_db_details,
        $_data;

    // Constructor
    public function __construct($inf_db, $language){
        $this->_db_details = $inf_db;
        $this->_language = $language;
        $this->_cache = new Cache(array('name' => 'nameless', 'extension' => '.cache', 'path' => ROOT_PATH . '/cache/infractions/'));
    }

    // Connect to database
    protected function initDB(){
        if($this->_db)
            return;

        $this->_db = new DB_Custom($this->_db_details['address'], $this->_db_details['name'], $this->_db_details['username'], $this->_db_details['password'], $this->_db_details['port']);
    }

    // Order array of objects using created attribute
    protected function date_compare($a, $b){
        if(!isset($a->created) || !isset($b->created)){
            $a->created = $this->getCreationTime($a);
            $b->created = $this->getCreationTime($b);
        }

        if($a->created == $b->created) return 0;
        return ($a->created < $b->created) ? 1 : -1;
    }

    // Abstract functions to be extended
    abstract public function listInfractions($page, $limit);
    abstract protected function getTotal();

}