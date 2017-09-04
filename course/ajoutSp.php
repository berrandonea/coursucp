<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("../config.php");
require_once("lib.php");
require_once("../lib/accesslib.php");
//require_once($CFG->dirroot . '/mod/assign/feedback/file/locallib.php');
require_once("$CFG->dirroot/group/lib.php");
require_once("$CFG->dirroot/mod/etherpadlite/lib.php");
global $DB;


$courseid = required_param("id", PARAM_INT);

//On récupère les paramètres de la situation problème à créer
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$courseformatoptions = course_get_format($course)->get_format_options();

// Authorisation checks.
require_login($course);
require_capability('moodle/course:update', context_course::instance($course->id));
//require_sesskey();

$name = optional_param("name", "", PARAM_TEXT);

$d = optional_param('description', '', PARAM_TEXT);
if ($d) {
    $descr = $d['text'];
    $descrformat = $d['format'];
}

//if (isset($_POST['description'])) {    
//    $d = $_POST['description'];
//    $descr = $d['text'];
//}

if (isset($_POST['Resultatattendu'])) {
    $d = $_POST['Resultatattendu'];
    $resultat = $d['text'];
}

if (isset($_POST['datedu'])) {
    $datedu = $_POST['datedu'];
    $du = $datedu["year"].'-'.$datedu["month"].'-'.$datedu["day"];    
}

if (isset($_POST['dateau'])) {
    $dateau = $_POST['dateau'];
    $au = $dateau["year"].'-'.$dateau["month"].'-'.$dateau["day"];;
}




if (isset($_POST['maxgroupe'])) {
    $nbgroups = $_POST['maxgroupe'];    
} else {
    $nbgroups = 1;
}

//Vraiment au cas où...
if ($nbgroups == 0) {
    $nbgroups = 1;
}

if(isset($_POST['chat'])) {
  $bchat= 1;
} else {
  $bchat=0;
}

if(isset($_POST['forum'])) {
    $bforum= 1;
} else {
    $bforum=0;
}

if(isset($_POST['depotetudiant'])) {
    $bdepotetudiant = 1;
} else {
    $bdepotetudiant = 0;
}

if(isset($_POST['etherpadlite'])) {
    $betherpadlite = 1;
} else {
    $betherpadlite = 0;
}

if(isset($_POST['wiki'])) {
    $bwiki= 1;
} else {
    $bwiki=0;
}

//Liste des étudiants du cours
$req = 'SELECT DISTINCT mdl_user.id, lastname, firstname, username, department
        FROM mdl_user, mdl_role_assignments, mdl_context, mdl_course
        WHERE mdl_user.id = mdl_role_assignments.userid
        AND mdl_role_assignments.contextid = mdl_context.id
        AND mdl_role_assignments.roleid = 5 
        AND mdl_context.instanceid = mdl_course.id
        AND mdl_course.id='. $courseid . ' ORDER BY lastname, firstname';

$users = $DB->get_records_sql($req,null);


$usercnt = count($users);

//Nombre de sections dans le cours
$maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($courseid));
$sectionincourse = $maxsection + 1;

//Calcul du nombre de groupes à créer et du nombre d'étudiants dans chaque groupe
/*if($usenbuserpargroupe)
{
    $nbgroups = floor($usercnt/$nbuserpargroup);
    $userpargroupe =$nbuserpargrp;
}
else{  */    
    $nbuserspergroup = floor($usercnt/$nbgroups);
//}

    
//Préparation des groupes (pas encore de création)
for($i = 0 ; $i < $nbgroups ; $i++) {
    $groups[$i] = array();

    //$groups[$i]['name']    = $name.'-Gr' . ($i +1);
    // ICI, ON PREND LE POINTER DU FOREACH POUR NOMMER LE GROUPE : gr1, gr2, gr3...
    $groups[$i]['name']    = $name.'-Gr '.($i + 1);
    $groups[$i]['members'] = array();

    for ($j = 0; $j < $nbuserspergroup; $j++) {
      $user = array_shift($users);
      $groups[$i]['members'][$user->id] = $user;
    }                        
}
                   
for ($i=0; $i<$nbgroups; $i++) {
    if (empty($users)) {
       break 1;
    }
    $user = array_shift($users);
    $groups[$i]['members'][$user->id] = $user;
}
    
$now = time();

//Création proprement dite de la situation problème dans la BDD
$sp = new stdClass();
$sp->course = $courseid;
$sp->name = $name;
$sp->description = $descr;
$sp->result = $resultat;
$sp->nbgroup = $nbgroups;
$sp->du = $du;
$sp->au = $au;
$sp->chat = $bchat;
$sp->forum = $bforum;
$sp->depotetudiant = $bdepotetudiant;
$sp->etherpadlite = $betherpadlite;
$sp->wiki = $bwiki;
$spid = $DB->insert_record('situation_problemes', $sp);

//Création du groupement associé
$grouping = new stdClass();
$grouping->courseid = $courseid;
$grouping->name = $name;
$grouping->description = "Groupes travaillant sur la situation problème $name";
$grouping->timecreated = $now;
$grouping->timemodified = $now;
$grouping->spid = $spid;
$grouping->id = $DB->insert_record('groupings', $grouping);
$DB->set_field('groupings', 'spid', $spid, array('id' => $grouping->id));

//Création de la section 
$section = new stdClass();
$section->course = $courseid;
$section->section = $sectionincourse;
$section->name = $name;
$section->summaryformat = 1;
$section->visible = 1;
$section->id = $DB->insert_record('course_sections', $section);

$courseformatoptions = course_get_format($course)->get_format_options();
    $courseformatoptions['numsections']++;

if ($courseformatoptions['numsections'] >= 0) {
    course_get_format($course)->update_course_format_options(
        array('numsections' => $courseformatoptions['numsections'])
    );
}

$sql = "UPDATE mdl_course_sections SET spid = $spid WHERE id = $section->id";
$DB->execute($sql);


//Création du devoir
$assignmodule = $DB->get_record('modules', array('name' => 'assign'));
$assigninstance = new stdClass();
$assigninstance->course = $courseid;
$assigninstance->name = $name;
$assigninstance->intro = "<u>Description :</u><br>$descr<br><u>Résultat attendu :</u><br>$resultat<br>";
$assigninstance->timemodified = time();
$assigninstance->id = $DB->insert_record('assign', $assigninstance);

$assigncm = new stdClass();
$assigncm->course = $courseid;
$assigncm->module = $assignmodule->id;
$assigncm->instance = $assigninstance->id;
$assigncm->section = $section->id;
$assigncm->added = $now;
$assigncm->indent = 0;
$assigncm->groupmode = 0;
$assigncm->id = $DB->insert_record('course_modules', $assigncm);

$sequence = "$assigncm->id";

$exchanges = "Echanges entre les étudiants d'un même groupe, au sujet de la situation problème $name."; 

if ($sp->forum) {
    //Création du forum, associé au groupement
    $forummodule = $DB->get_record('modules', array('name' => 'forum'));
    
    $foruminstance = new stdClass();
    $foruminstance->course = $courseid;
    $foruminstance->name = "Forum";
    $foruminstance->intro = $exchanges;
    $foruminstance->timemodified = $now;
    $foruminstance->id = $DB->insert_record('forum', $foruminstance);

    $forumcm = new stdClass();
    $forumcm->course = $courseid;
    $forumcm->module = $forummodule->id;
    $forumcm->instance = $foruminstance->id;        
    $forumcm->section = $section->id;
    $forumcm->added = $now;
    $forumcm->indent = 0;
    $forumcm->groupmode = 1;
    $forumcm->groupingid = $grouping->id;
    $forumcm->id = $DB->insert_record('course_modules', $forumcm);
    
    $sequence .= ",$forumcm->id";
}

if ($sp->chat) {
    $chatmodule = $DB->get_record('modules', array('name' => 'chat'));
    
    //Création du chat, associé au groupement
    $chatinstance = new stdClass();
    $chatinstance->course = $courseid;
    $chatinstance->name = "Chat";
    $chatinstance->intro = $exchanges;
    $chatinstance->chattime = $now;
    $chatinstance->id = $DB->insert_record('chat', $chatinstance);

    $chatcm = new stdClass();
    $chatcm->course = $courseid;
    $chatcm->module = $chatmodule->id;
    $chatcm->instance = $chatinstance->id;
    $chatcm->section = $section->id;
    $chatcm->added = $now;
    $chatcm->indent = 0;
    $chatcm->groupmode = 1;
    $chatcm->groupingid = $grouping->id;
    $chatcm->id = $DB->insert_record('course_modules', $chatcm);
    
    $sequence .= ",$chatcm->id";
}

if ($sp->depotetudiant) {
    $depotetudiantmodule = $DB->get_record('modules', array('name' => 'depotetudiant'));

    //Création du dépôt étudiant, associé au groupement
    $instance = new stdClass();
    $instance->course = $courseid;
    $instance->name = "Dépôt étudiant";
    $instance->intro = "<p>Ici, vous pouvez mutualiser des ressources entre étudiants</p>";
    $instance->timemodified = $now;    
    $instance->id = $DB->insert_record('depotetudiant', $instance);

    $depotetudiantcm = new stdClass();
    $depotetudiantcm->course = $courseid;
    $depotetudiantcm->module = $depotetudiantmodule->id;
    $depotetudiantcm->instance = $instance->id;
    $depotetudiantcm->section = $section->id;
    $depotetudiantcm->added = $now;
    $depotetudiantcm->indent = 0;
    $depotetudiantcm->groupmode = 1;
    $depotetudiantcm->groupingid = $grouping->id;
    $depotetudiantcm->id = $DB->insert_record('course_modules', $depotetudiantcm);
    
    $sequence .= ",$depotetudiantcm->id";
}

if ($sp->etherpadlite) {
    $etherpadlitemodule = $DB->get_record('modules', array('name' => 'etherpadlite'));

    //Création du Texte partagé, associé au groupement
    $instance = new stdClass();
    $instance->course = $courseid;
    $instance->name = "Texte partagé";
    $instance->intro = "<p>Tous les membres du groupe peuvent éditer ce texte simultanément</p>";
    $instance->introformat = 1;
    //$instance->timecreated = $now;    
    $instance->id = etherpadlite_add_instance($instance); //$DB->insert_record('etherpadlite', $instance);

    $etherpadlitecm = new stdClass();
    $etherpadlitecm->course = $courseid;
    $etherpadlitecm->module = $etherpadlitemodule->id;
    $etherpadlitecm->instance = $instance->id;
    $etherpadlitecm->section = $section->id;
    $etherpadlitecm->added = $now;
    $etherpadlitecm->indent = 0;
    $etherpadlitecm->groupmode = 1;
    $etherpadlitecm->groupingid = $grouping->id;
    $etherpadlitecm->id = $DB->insert_record('course_modules', $etherpadlitecm);
    
    $sequence .= ",$etherpadlitecm->id";
}

if ($sp->wiki) {
    $wikimodule = $DB->get_record('modules', array('name' => 'wiki'));
    
    //Création du wiki, associé au groupement
    $wikiinstance = new stdClass();
    $wikiinstance->course = $courseid;
    $wikiinstance->name = "Wiki";
    $wikiinstance->intro = $exchanges;
    $wikiinstance->timemodified = $now;
    $wikiinstance->id = $DB->insert_record('wiki', $wikiinstance);

    $wikicm = new stdClass();
    $wikicm->course = $courseid;
    $wikicm->module = $wikimodule->id;
    $wikicm->instance = $wikiinstance->id;
    $wikicm->section = $section->id;
    $wikicm->added = $now;
    $wikicm->indent = 0;
    $wikicm->groupmode = 1;
    $wikicm->groupingid = $grouping->id;
    $wikicm->id = $DB->insert_record('course_modules', $wikicm);
    
    $sequence .= ",$wikicm->id";
}

//Mise à jour de la séquence de la section
$sql = "UPDATE mdl_course_sections SET sequence = '$sequence' WHERE id = $section->id";
$DB->execute($sql);



//Création des groupes, associés au groupement
$now = time();

foreach ($groups as $group) {
    
    $groupdata = new stdClass();
    $groupdata->courseid = $courseid;
    $groupdata->name = $group['name'];
    $groupdata->description = "Groupe de situation problème";
    $groupdata->descriptionformat = 1;
    $groupdata->timecreated = $now;
    $groupdata->timemodified = $now;    
    $groupid = groups_create_group($groupdata);
    
    foreach ($group['members'] as $user) {
         $DB->insert_record('groups_members',array('groupid'=>$groupid,'userid'=>$user->id,'timeadded'=>  time()));    
    }

    //On ajoute le groupe au groupement
    $groupinggroup = new stdClass();
    $groupinggroup->groupingid = $grouping->id;
    $groupinggroup->groupid = $groupid;
    $groupinggroup->timeadded = $now;
    $groupinggroup->id = $DB->insert_record("groupings_groups", $groupinggroup);   

}

header("Location: $CFG->wwwroot/group/spmembers.php?id=$courseid&spid=$spid");