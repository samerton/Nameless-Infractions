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
        'history_view' => 'libertybans_simple_history',
        'names_view' => 'libertybans_latest_names',
    )
);
