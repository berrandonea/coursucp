<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require('../config.php');

global $DB, $CFG;

require_once($CFG->libdir . '/csvlib.class.php');

if (is_siteadmin()) {

    $csvexporter = new csv_export_writer();

    $csvexporter->set_filename('badlyRegisteredTeachers');

    $title = array(utf8_decode('Profs sans Code Etape ou avec Code Etape valant 9'));
    $csvexporter->add_data($title);

    $columnname = array(utf8_decode('Nom'), utf8_decode('Prénom'), utf8_decode('Nom d\'utilisateur'),
        utf8_decode('Mail'), utf8_decode('Numéro d\'identification'), utf8_decode('Code Etape'));
    $csvexporter->add_data($columnname);

    $sqlnostepcode = "SELECT * FROM `mdl_user` WHERE `id` IN"
            . " (SELECT distinct `userid` FROM `mdl_ufr_teacher` WHERE `ufrcode` = '0')"
            . " AND lastlogin > 0 AND `id` IN "
            . "(SELECT distinct `userid` FROM `mdl_role_assignments` WHERE `roleid` = 3 OR `roleid` = 4)";
    $listnostepcode = $DB->get_records_sql($sqlnostepcode);

    foreach ($listnostepcode as $teachernostepcode) {

        $teacherdata = array(utf8_decode($teachernostepcode->lastname),
            utf8_decode($teachernostepcode->firstname), utf8_decode($teachernostepcode->username),
            utf8_decode($teachernostepcode->email), $teachernostepcode->idnumber, "Aucun");
        $csvexporter->add_data($teacherdata);
    }
    $separator = array(utf8_decode("Séparateur"));
    $csvexporter->add_data($separator);

    $sqlstepcodenine = "SELECT * FROM `mdl_user` WHERE `id` IN"
            . " (SELECT `userid` FROM `mdl_ufr_teacher` WHERE `ufrcode` = 9) AND lastlogin > 0 AND `id` IN "
            . "(SELECT distinct `userid` FROM `mdl_role_assignments` WHERE `roleid` = 3 OR `roleid` = 4)";
    $liststepcodenine = $DB->get_records_sql($sqlstepcodenine);

    foreach ($liststepcodenine as $teacherstepcodenine) {

        $teacherdata = array(utf8_decode($teacherstepcodenine->lastname),
            utf8_decode($teacherstepcodenine->firstname), utf8_decode($teacherstepcodenine->username),
            utf8_decode($teacherstepcodenine->email), $teacherstepcodenine->idnumber, "9");
        $csvexporter->add_data($teacherdata);
    }

    $csvexporter->download_file();
}