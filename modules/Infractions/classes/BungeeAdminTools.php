<?php
/*
 *	Made by Samerton
 *  https://github.com/samerton/Nameless-Infractions
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  BungeeAdminTools class
 */
 
class BungeeAdminTools extends Infractions {

	// Constructor
	public function __construct($inf_db, $language, $cache) {
		parent::__construct($inf_db, $language, $cache);
		
		// Force-set prefix to bat_
		$this->_prefix = 'bat_';
	}
	
	// Retrieve a list of all infractions, either from cache or database
	public function listInfractions(){
		// Cached?
		$this->_cache->setCache('infractions_infractions');
		if($this->_cache->isCached('infractions')){
			$infractions = $this->_cache->retrieve('infractions');
		} else {
			$bans = $this->listBans();
			$kicks = $this->listKicks();
			$mutes = $this->listMutes();
			
			// Merge
			$infractions = array_merge($bans, $kicks, $mutes);
			
			// Sort by date
			usort($infractions, array($this, 'date_compare'));
	
			$this->_cache->store('infractions', $infractions, 120);
		}

		return $infractions;
	}
	
	// List all bans
	public function listBans(){
		// Cached?
		$this->_cache->setCache('infractions_bans');
		if($this->_cache->isCached('bans')){
			$bans = $this->_cache->retrieve('bans');
		} else {
			$bans = $this->orderQuery('ban', 'ban_id <> 0', 'ban_begin', 'DESC');
			
			if(count($bans))
				$this->_cache->store('bans', $bans, 120);
			else $bans = array();
		}
		
		return $bans;
	}
	
	// List all kicks
	public function listKicks(){
		// Cached?
		$this->_cache->setCache('infractions_kicks');
		if($this->_cache->isCached('kicks')){
			$kicks = $this->_cache->retrieve('kicks');
		} else {
			$kicks = $this->orderQuery('kick', 'kick_id <> 0', 'kick_date', 'DESC');
			
			if(count($kicks))
				$this->_cache->store('kicks', $kicks, 120);
			else $kicks = array();
		}
		
		return $kicks;
	}
	
	// List all mutes
	public function listMutes(){
		// Cached?
		$this->_cache->setCache('infractions_mutes');
		if($this->_cache->isCached('mutes')){
			$mutes = $this->_cache->retrieve('mutes');
		} else {
			$mutes = $this->orderQuery('mute', 'mute_id <> 0', 'mute_begin', 'DESC');
			
			if(count($mutes))
				$this->_cache->store('mutes', $mutes, 120);
			else $mutes = array();
		}
		
		return $mutes;
	}
	
	// Get a username from a UUID
	public function getUsername($uuid){
		$user = $this->query('players', array('uuid', '=', $uuid));
		
		if(count($user)) return $user[0]->BAT_player;
		else return false;
	}
	
	// Get creation time from infraction
	public static function getCreationTime($item){
		if(isset($item->ban_begin)){
			return $item->ban_begin;
		} else if(isset($item->kick_date)){
			return $item->kick_date;
		} else if(isset($item->mute_begin)) {
			return $item->mute_begin;
		} else return false;
	}

}