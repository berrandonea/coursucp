<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('CLI_SCRIPT', true);
require(__DIR__.'/../config.php');

global $DB;

$liststudentiut = $DB->get_records('ufr_student', array('ufrcode' => 7));

foreach ($liststudentiut as $studentiut) {

    if (!$DB->record_exists('role_assignments',
            array('roleid' => 5, 'contextid' => 46535, 'userid' => $studentiut->userid))) {

        $newroleassignment = array();
        $newroleassignment['roleid'] = 5;
        $newroleassignment['contextid'] = 46535;
        $newroleassignment['userid'] = $studentiut->userid;
        $newroleassignment['timemodified'] = time();
        $newroleassignment['modifierid'] = 2;
        $newroleassignment['component'] = "";
        $newroleassignment['itemid'] = 0;
        $newroleassignment['sortorder'] = 0;

        $DB->insert_record('role_assignments', $newroleassignment);
    }

    if (!$DB->record_exists('user_enrolments',
            array('enrolid' => 3081, 'userid' => $studentiut->userid))) {

        $newuserenrolment = array();
        $newuserenrolment['status'] = 0;
        $newuserenrolment['enrolid'] = 3081;
        $newuserenrolment['userid'] = $studentiut->userid;
        $newuserenrolment['timestart'] = time();
        $newuserenrolment['timeend'] = 0;
        $newuserenrolment['modifierid'] = 2;
        $newuserenrolment['timecreated'] = time();
        $newuserenrolment['timemodified'] = time();

        $DB->insert_record('user_enrolments', $newuserenrolment);
    }
}

$sqlcategories = "SELECT * from {course_categories} WHERE idnumber LIKE '7_____'";
$categories = $DB->get_records_sql($sqlcategories);

foreach ($categories as $category) {

    if (!$DB->record_exists('groups', array('courseid' => 1031, 'idnumber' => $category->idnumber,
        'name' => $category->name))) {

        $newgroup = array();
        $newgroup['courseid'] = 1031;
        $newgroup['idnumber'] = $category->idnumber;
        $newgroup['name'] = $category->name;
        $newgroup['description'] = "";
        $newgroup['descriptionformat'] = 1;
        $newgroup['enrolmentkey'] = "";
        $newgroup['picture'] = 0;
        $newgroup['hidepicture'] = 0;
        $newgroup['timecreated'] = time();
        $newgroup['timemodified'] = time();
        $newgroup['codegroupe'] = "";

        $groupid = $DB->insert_record('groups', $newgroup);
    } else {

        $groupid = $DB->get_record('groups',
                array('courseid' => 1031, 'idnumber' => $category->idnumber, 'name' => $category->name))->id;
    }

    $listcoursesincategory = $DB->get_records('course', array('category' => $category->id));

    foreach ($listcoursesincategory as $courseincategory) {

        $coursecontextid = $DB->get_record('context',
                array('instanceid' => $courseincategory->id, 'contextlevel' => 50))->id;

        $liststudentsincourse = $DB->get_records('role_assignments',
                array('roleid' => 5, 'contextid' => $coursecontextid));

        foreach ($liststudentsincourse as $studentincourse) {

            if (!$DB->record_exists('groups_members',
                    array('groupid' => $groupid, 'userid' => $studentincourse->userid))) {

                $newgroupmember = array();
                $newgroupmember['groupid'] = $groupid;
                $newgroupmember['userid'] = $studentincourse->userid;
                $newgroupmember['timeadded'] = time();
                $newgroupmember['component'] = "";
                $newgroupmember['itemid'] = 0;

                $DB->insert_record('groups_members', $newgroupmember);
            }
        }
    }
}

// 1) Lister les groupes du cours se finissant par 1
// 2) Pour chacun des ces groupes, inscrire tous les utilisateurs au groupe DUT 1
// 3) Vérifier pendant la procédure que l'utilisateur n'y est pas déjà inscrit.
// 4) Faire la même chose pour DUT 2 et Licences professionnelles.

$sqlgroupsdut1 = "SELECT * FROM {groups} WHERE courseid = 1031 AND idnumber LIKE '7____1'";
$listiutgroupsdut1 = $DB->get_records_sql($sqlgroupsdut1);

foreach ($listiutgroupsdut1 as $iutgroupdut1) {

    $liststudentsingroupdut1 = $DB->get_records('groups_members', array('groupid' => $iutgroupdut1->id));

    foreach ($liststudentsingroupdut1 as $studentingroupdut1) {

        if (!$DB->record_exists('groups_members', array('groupid' => 2818,
            'userid' => $studentingroupdut1->userid))) {

            $newstudentdut1 = array();
            $newstudentdut1['groupid'] = 2818;
            $newstudentdut1['userid'] = $studentingroupdut1->userid;
            $newstudentdut1['timeadded'] = time();
            $newstudentdut1['component'] = "";
            $newstudentdut1['itemid'] = 0;

            $DB->insert_record('groups_members', $newstudentdut1);
        }
    }
}

$sqlgroupsdut2 = "SELECT * FROM {groups} WHERE courseid = 1031 AND idnumber LIKE '7____2'";
$listiutgroupsdut2 = $DB->get_records_sql($sqlgroupsdut2);

foreach ($listiutgroupsdut2 as $iutgroupdut2) {

    $liststudentsingroupdut2 = $DB->get_records('groups_members', array('groupid' => $iutgroupdut2->id));

    foreach ($liststudentsingroupdut2 as $studentingroupdut2) {

        if (!$DB->record_exists('groups_members', array('groupid' => 2819,
            'userid' => $studentingroupdut2->userid))) {

            $newstudentdut2 = array();
            $newstudentdut2['groupid'] = 2819;
            $newstudentdut2['userid'] = $studentingroupdut2->userid;
            $newstudentdut2['timeadded'] = time();
            $newstudentdut2['component'] = "";
            $newstudentdut2['itemid'] = 0;

            $DB->insert_record('groups_members', $newstudentdut2);
        }
    }
}

$sqlgroupslp = "SELECT * FROM {groups} WHERE courseid = 1031 AND idnumber LIKE '7____3'";
$listiutgroupslp = $DB->get_records_sql($sqlgroupslp);

foreach ($listiutgroupslp as $iutgrouplp) {

    $liststudentsingrouplp = $DB->get_records('groups_members', array('groupid' => $iutgrouplp->id));

    foreach ($liststudentsingrouplp as $studentingrouplp) {

        if (!$DB->record_exists('groups_members', array('groupid' => 2822,
            'userid' => $studentingrouplp->userid))) {

            $newstudentlp = array();
            $newstudentlp['groupid'] = 2822;
            $newstudentlp['userid'] = $studentingrouplp->userid;
            $newstudentlp['timeadded'] = time();
            $newstudentlp['component'] = "";
            $newstudentlp['itemid'] = 0;

            $DB->insert_record('groups_members', $newstudentlp);
        }
    }
}

