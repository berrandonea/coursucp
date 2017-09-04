<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function xmldb_block_ucpslotbooking_upgrade($oldversion) {
    
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2017083000) {

        // Define field enableunbooking to be added to block_ucpslotbooking_list.
        $table = new xmldb_table('block_ucpslotbooking_list');
        $field = new xmldb_field('enableunbooking', XMLDB_TYPE_INTEGER, 10, null, true, null, 1);

        // Conditionally launch add field enableunbooking.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}