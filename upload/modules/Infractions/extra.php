<?php
/*
 *  Extra configuration for Infractions module tables
 */
$inf_extra = array(
    'litebans' => array(
        'bans_table' => 'litebans_bans',
        'kicks_table' => 'litebans_kicks',
        'mutes_table' => 'litebans_mutes',
        'warnings_table' => 'litebans_warnings',
        'history_table' => 'litebans_history'
    ),
    'advancedban' => array(
        'punishments_table' => 'Punishments',
        'punishment_history_table' => 'PunishmentHistory'
    ),
    'libertybans' => array(
        'active_table' => 'libertybans_simple_active',
        'history_table' => 'libertybans_simple_history',
        'name_table' => 'libertybans_names'
    )
);
