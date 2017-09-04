<title>
Transfert de cours vers la nouvelle plateforme pédagogique
</title>

<?php
require_once('config.php');
require_once 'course/lib.php';
$receivedid = required_param('id', PARAM_INT);
$previouscourseid = $receivedid % 100000;
$newcourseid = ($receivedid - $previouscourseid) / 100000;
$transfer = 1;

//On va chercher des infos sur l'ancien cours
$oldcourse = $DB->get_record('oldcourses', array('oldcourseid' => $previouscourseid, 'username' => $USER->username));
//require_once('bddenp14.php');
//$sql = "SELECT fullname, idnumber, category, format FROM mdl_course WHERE id = $previouscourseid";    
//$previouscourseresult = db_query($sql);    
//$previouscourse = mysql_fetch_array($previouscourseresult);    
if (!$oldcourse) {
    echo "ERREUR : vous n'étiez pas enseignant dans ce cours !<br>";
    exit;
} 
print_object($oldcourse);

//$sql = "SELECT name, idnumber FROM mdl_course_categories WHERE id = ".$previouscourse['category'];
//$previousvetresult = db_query($sql);
//$previousvet = mysql_fetch_array($previousvetresult);
//print_object($previousvet);

////L'utilisateur était-il enseignant dans cet ancien cours ?
//$sql = "SELECT ra.id "
//        . "FROM mdl_role_assignments ra, mdl_context x, mdl_user u "
//        . "WHERE ra.roleid = 3 AND ra.contextid = x.id AND ra.userid = u.id "
//        . "AND u.username = '$USER->username' "
//        . "AND x.contextlevel = 50 AND x.instanceid = $previouscourseid";
//
//echo "$sql<br><br>";    
//$previousteacherresult = db_query($sql);
//$previousteacher = mysql_fetch_array($previousteacherresult);
////print_object($previousteacher);
//
//if (!$previousteacher['id']) {
//    echo "ERREUR : vous n'étiez pas enseignant dans ce cours !<br>";
//    exit;
//
//}
//

//Si le cours n'existe pas encore, on doit d'abord créer un cours vierge dans la bonne VET (qu'on crée aussi si nécessaire)
if ($newcourseid == 1) {
    echo "Le cours ($oldcourse->courseidnumber) $oldcourse->coursename n'existait pas encore sur cette plateforme.<br><br>";
    echo "Création d'un cours vierge intitulé ($oldcourse->courseidnumber) $oldcourse->coursename<br><br>";    
    $idnumberarray = explode('-', $oldcourse->courseidnumber);
    $vetidnumber = $idnumberarray[0];
    $oldcategory = explode(' ', $oldcourse->categoryname);
    unset($oldcategory[0]);
    $vetname = '';
    foreach($oldcategory as $namepart) {
        $vetname .= $namepart.' ';
    }
    $vetname = trim($vetname);    
    
    require_once 'lib/coursecatlib.php';
    require_once 'lib/accesslib.php';

    $vetcategoryid = createvetifnew($vetidnumber, $vetname);
    echo "vetcategoryid: $vetcategoryid<br>";
    $coursedata = array();
    $coursedata = new stdClass;
    $coursedata->fullname = $oldcourse->coursename;
    $coursedata->category = $vetcategoryid;
    $coursedata->shortname = $oldcourse->courseidnumber;
    $coursedata->idnumber = $oldcourse->courseidnumber;
    $coursedata->format = $oldcourse->format;
    print_object($coursedata);
    echo "Création proprement dite<br><br>";
    $newcourse = create_course($coursedata);
    echo "Création du contexte<br><br>";
    $newcoursecontext = context_course::instance($newcourse->id, MUST_EXIST);   
    $newcourseid = $newcourse->id;            
    $contextid = $newcoursecontext->id;    
} else {
    //On cherche le contexte du cours dans lequel on va faire la restauration
//    $sql = "SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid = $newcourseid";
//    echo "$sql<br>";
//    $contextid = $DB->get_record_sql($sql)->id;
    $contextid = $DB->get_field('context', 'id', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $newcourseid));
    echo "contextid : $contextid<br>";
}


$params = array('courseid' => $newcourseid, 'name' => 'numsections');
$currentnbsections = $DB->get_record('course_format_options', $params);
if ($oldcourse->nbsections > $currentnbsections->value) {
    echo "Création des sections manquantes<br><br>";
    $DB->set_debug(true);
    $DB->set_field('course_format_options', 'value', $oldcourse->nbsections, $params);
    $DB->set_debug(false);
    $sectionnum = $currentnbsections - 2;
    if ($sectionnum < 0) {
        $sectionnum = 0;
    }
    while ($sectionnum < $oldcourse->nbsections) {
        echo "($newcourseid, $sectionnum)";
        course_create_sections_if_missing($newcourseid, $sectionnum);    
        $sectionnum++;
    }
    echo "sectionnum : $sectionnum";
}

//On inscrit l'utilisateur comme enseignant dans ce cours (s'il ne l'est pas déjà)
$now = time();
$sql = "SELECT id FROM mdl_enrol WHERE courseid = $newcourseid AND enrol = 'manual'";
$enrolmethods = $DB->get_recordset_sql($sql);
foreach($enrolmethods as $enrolmethod) {
    $enrolid = $enrolmethod->id;
}
$sql = "SELECT id FROM mdl_user_enrolments WHERE enrolid = $enrolid AND userid = $USER->id";
$ue = $DB->get_record_sql($sql);
if (!$ue) {
    $sql = "INSERT INTO mdl_user_enrolments (status, enrolid,          userid,   timestart, timeend, modifierid, timecreated, timemodified) "
                              . "VALUES (0,      $enrolid, $USER->id, $now,     0,       $USER->id,  $now,        $now)";     
    echo "$sql<br>";
    //exit;
    $DB->execute($sql);
}
$sql = "SELECT id FROM mdl_role_assignments WHERE roleid = 3 AND contextid = $contextid AND userid = $USER->id";
$role = $DB->get_record_sql($sql);
if (!$role) {
    $sql = "INSERT INTO mdl_role_assignments (roleid, contextid,             userid,    timemodified, modifierid) "
                                   . "VALUES (3,      $contextid,            $USER->id, $now,         $USER->id)";       
    echo "$sql<br>";
    //exit;
    $DB->execute($sql);
}

//On marque le cours comme transféré
$sql = "UPDATE mdl_course SET transfered = 2015 WHERE id = $newcourseid";
$DB->execute($sql);

//On lance la restauration
$DB->set_field('oldcourses', 'beingtransferedby', $USER->id, array('id' => $oldcourse->id));
$restoreurl = "$CFG->wwwroot/backup/restorefile.php?contextid=$contextid&transfer=$USER->id";
echo "$restoreurl<br>";
header("Location: $restoreurl");
?>
