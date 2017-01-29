<?php
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  Infractions class
 */
 
abstract class Infractions {
	private $_db,
			$_data;
			
	protected $_language,
			  $_prefix,
			  $_cache;
	
	// Connect to database
	public function __construct($inf_db, $language, $cache){
		$this->_db = new DB_Custom($inf_db['address'], $inf_db['name'], $inf_db['username'], $inf_db['password']);
		$this->_prefix = $inf_db['prefix'];
		$this->_language = $language;
		$this->_cache = $cache;
	}
	
	// Select from database
	protected function query($table, $where){
		$data = $this->_db->get($this->_prefix . $table, $where);
		return $data->results();
	}
	
	// Order from database
	protected function orderQuery($table, $where, $order, $sort = 'DESC'){
		$data = $this->_db->orderWhere($this->_prefix . $table, $where, $order, $sort);
		return $data->results();
	}
	
	// Query database
	protected function updateQuery(){
		if(!$this->_db->update($this->_prefix . $table, $id, $fields)) {
			throw new Exception('There was a problem performing that action.');
		}
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
	abstract public function listInfractions();

}