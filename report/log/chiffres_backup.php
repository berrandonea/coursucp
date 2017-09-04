<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Displays different views of the logs.
 *
 * @package    report_log
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/report/log/locallib.php');
require_once($CFG->libdir.'/adminlib.php');

$id          = optional_param('id', 0, PARAM_INT);// Course ID
$host_course = optional_param('host_course', '', PARAM_PATH);// Course ID

if (empty($host_course)) {
    $hostid = $CFG->mnet_localhost_id;
    if (empty($id)) {
        $site = get_site();
        $id = $site->id;
    }
} else {
    list($hostid, $id) = explode('/', $host_course);
}



$PAGE->set_url('/report/log/chiffres.php');
$PAGE->set_pagelayout('report');
 


$course = $DB->get_record('course', array('id'=>1), '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);

$sql = "SELECT COUNT(id) AS isteacher FROM mdl_role_assignments WHERE (roleid = 2 OR roleid = 1) AND userid = $USER->id";
$isteacher = $DB->get_record_sql($sql)->isteacher;

if ($isteacher == 0) {
    require_capability('report/log:view', $context);
}

/* Commenté par BRICE lors du passage à ENP15
// Trigger a content view event.
$event = \report_log\event\content_viewed::create(array('courseid' => $course->id,
                                                        'other'    => array('content' => 'chiffres')));
$event->set_page_detail();
$event->set_legacy_logdata(array($course->id, 'course', 'report log',
        "report/log/chiffres.php", $course->id));
$event->trigger(); */

//echo "Test 3<br>"; exit;
//admin_externalpage_setup('reportstats', '', null, '', array('pagelayout'=>'report'));
//admin_externalpage_setup('reportlog');

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_pagelayout('frontpage');

$editing = $PAGE->user_is_editing();
$PAGE->set_title("La plateforme en chiffres");
$PAGE->set_heading("La plateforme en chiffres");

$courserenderer = $PAGE->get_renderer('core', 'course');





echo $OUTPUT->header();
echo $OUTPUT->heading('La plateforme en chiffres :');
//report_log_print_selector_form($course, $user, $date, $modname, $modid, $modaction, $group, $showcourses, $showusers, $logformat);

/*$composantes = array(
                    array("id" => "1" ,"idcat" => "1", "code" => "1", "name" => "  UFR DROIT", "nbknowncourses" => 0),
                    array("id" => "12" ,"idcat" => "5222", "code" => "2", "name" => "  UFR ECONOMIE ET GESTION", "nbknowncourses" => 0),
                    array("id" => "13" ,"idcat" => "6874", "code" => "3", "name" => "  UFR LANGUES ETUDES INTERNATIONALES", "nbknowncourses" => 0),
                    array("id" => "116" ,"idcat" => "9033", "code" => "4", "name" => "  UFR LETTRES ET SCIENCES HUMAINES", "nbknowncourses" => 0),
                    array("id" => "11" ,"idcat" => "11443", "code" => "5", "name" => "  UFR SCIENCES ET TECHNIQUES", "nbknowncourses" => 0),                        
                    array("id" => "16" ,"idcat" => "14963", "code" => "7", "name" => "  INSTITUT UNIVERSITAIRE DE TECHNOLOGIE", "nbknowncourses" => 0),                    
                    array("id" => "211" ,"idcat" => "16356", "code" => "A", "name" => "  ECOLE SUPERIEURE DU PROFESSORAT ET DE L'EDUCATION", "nbknowncourses" => 0),
                    array("id" => "142" ,"idcat" => "20080", "code" => "B", "name" => "  SCIENCES-PO ST-GERMAIN-EN-LAYE", "nbknowncourses" => 0),                        
                    //array("code" => "C", "name" => "  SCUIO-IP", "nbknowncourses" => 0)
               );*/

$composantes = array(
    array("id" => "3" ,"idcat" => "1", "code" => "1", "name" => "  UFR DROIT", "nbknowncourses" => 0),
    array("id" => "8" ,"idcat" => "5222", "code" => "2", "name" => "  UFR ECONOMIE ET GESTION", "nbknowncourses" => 0),
    array("id" => "9" ,"idcat" => "6874", "code" => "3", "name" => "  UFR LANGUES ETUDES INTERNATIONALES", "nbknowncourses" => 0),
    array("id" => "10" ,"idcat" => "9033", "code" => "4", "name" => "  UFR LETTRES ET SCIENCES HUMAINES", "nbknowncourses" => 0),
    array("id" => "11" ,"idcat" => "11443", "code" => "5", "name" => "  UFR SCIENCES ET TECHNIQUES", "nbknowncourses" => 0),                        
    array("id" => "13" ,"idcat" => "14963", "code" => "7", "name" => "  INSTITUT UNIVERSITAIRE DE TECHNOLOGIE", "nbknowncourses" => 0),                    
    array("id" => "16" ,"idcat" => "16356", "code" => "A", "name" => "  ECOLE SUPERIEURE DU PROFESSORAT ET DE L'EDUCATION", "nbknowncourses" => 0),
    array("id" => "17" ,"idcat" => "20080", "code" => "B", "name" => "  SCIENCES-PO ST-GERMAIN-EN-LAYE", "nbknowncourses" => 0),                        
    //array("code" => "C", "name" => "  SCUIO-IP", "nbknowncourses" => 0)
);


echo "<h3>Nombre de promotions, de cours et d'étudiants déclarés dans Apogée</h3>";
echo "<p style='text-align:justify'>Les cours déclarés dans Apogée apparaissent automatiquement dans l'outil \"Demande de création de cours\", en bas de la page d'accueil. ";
echo "Les étudiants déclarés dans Apogée sont automatiquement inscrits à la plateforme pédagogique (mais pas forcément à des cours).</p>";

echo "<table><tr>";
echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Promotions déclarées</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Cours déclarés</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Etudiants déclarés</h3></td>";

$totalknowncourses = 0;
$totalknownstudents = 0;
$numcomposante = 0;
$totalnbrvets =0;

foreach ($composantes as $composante) {
    echo "<tr>";
	
    //Nombre des vets par composantes
    $sql = "SELECT count(distinct vets.id) as nbrvets FROM `mdl_cat_demandecours` niveau, mdl_cat_demandecours vets where niveau.id = vets.parent_id and niveau.parent_id = '".$composante["idcat"]."'";
    $nbrvets = $DB->get_record_sql($sql)->nbrvets;
    $composantes[$numcomposante]["nbrvets"] = $nbrvets;
    $totalnbrvets += $nbrvets; 
    echo "<td>".$composante["name"]."</td>";	
    echo "<td style='text-align:center'>$nbrvets</td>";
    
    //Nbr cours déclarés
    $sql = "SELECT COUNT(id) AS nbknowncourses FROM `mdl_cat_demandecours` WHERE code LIKE '".$composante["code"]."%-%'";    
    $sql .= " AND (name LIKE '%[UE]' OR name LIKE '%[EC]')"; //On compte uniquement les UE et les EC
    $nbknowncourses = $DB->get_record_sql($sql)->nbknowncourses;
    $composantes[$numcomposante]["nbknowncourses"] = $nbknowncourses;
    $totalknowncourses += $nbknowncourses; 
    //echo "<td>".$composante["name"]."</td>";    
    echo "<td style='text-align:center'>$nbknowncourses</td>";
    
    
    //Nombre d'étudiants inscrits à la plateforme pour cette composante
    $sql = "SELECT COUNT(userid) AS nb FROM mdl_ufr_student WHERE ufrcode = '".$composante["code"]."' AND student = 1";
    $nbknownstudentsincomposante = $DB->get_record_sql($sql)->nb;   
    $composante["nbknownstudentsincomposante"] = $nbknownstudentsincomposante;
    $totalknownstudents += $nbknownstudentsincomposante;
    
    echo "<td style='text-align:center'>$nbknownstudentsincomposante</td>";  	
    
    echo "</tr>";
    $numcomposante++;
}
echo "<tr style ='font-weight:bold'><td>TOTAL</td><td style='text-align:center'>$totalnbrvets</td><td style='text-align:center'>$totalknowncourses</td><td style='text-align:center'>$totalknownstudents</td></tr>";
echo "</table><br><br>";


echo "<h3>Nombre de cours disponibles à la création</h3>";
echo "<p style='text-align:justify'>Pour qu'un cours apparaisse automatiquement dans la rubrique \"Cours disponibles à la création\", "
. "la composante doit avoir saisi les emplois du temps dans Celcat et fait la \"mise en groupes\" (Cours[ELP]-Enseignant(s)-Etudiants).<br><br>";

echo "Voici, pour chaque composante, combien de cours sont disponibles à la création (y compris ceux qui sont déjà créés) :</p>";


echo "<table><tr>";
echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Cours disponibles</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Promotions concernées</h3></td>";
$totalavailcourses = 0;
$numcomposante = 0;
$totalpromotions = 0;
foreach ($composantes as $composante) {
    echo "<tr>";
    
    $sql = "SELECT avail_courses FROM mdl_ufr WHERE code = '".$composante["code"]."'";    
    $nbavailcourses = $DB->get_record_sql($sql)->avail_courses;
    //$composante["nbavailcourses"] = $nbavailcourses;
    $totalavailcourses += $nbavailcourses; 
    $nbknowcourses = $composantes[$numcomposante]["nbknowncourses"];
    if ($nbknowcourses) {
        $availability = round(100 * $nbavailcourses / $nbknowcourses, 1);
    } else {
        $availability = 0;
    }
    
    echo "<td>".$composante["name"]."</td>";    
    echo "<td style='text-align:center'>$nbavailcourses / $nbknowcourses ($availability%)</td>"; 
	
	 $sql = "SELECT promotions FROM mdl_ufr WHERE code = '".$composante["code"]."'"; 
         
	 $nbrpromotions = $DB->get_record_sql($sql)->promotions;
	 $totalpromotions += $nbrpromotions;
	 /* $nbrvets = $composantes[$numcomposante]["nbknowncourses"]; */
	/*  $availability1 = round(100 * $nbrpromotions / $nbrvets, 1); */
	$sql = "SELECT count(distinct vets.id) as nbrvets FROM `mdl_cat_demandecours` niveau, mdl_cat_demandecours vets where niveau.id = vets.parent_id and niveau.parent_id = '".$composante["idcat"]."'";        
	$nbrvets = $DB->get_record_sql($sql)->nbrvets;
        
        if ($nbrvets) {
            $promotionspercent = round(100 * $nbrpromotions / $nbrvets, 1);
        } else {
            $promotionspercent = 0;
        }
	
	echo "<td style='text-align:center'>$nbrpromotions / $nbrvets ($promotionspercent%)</td>"; 	
    echo "</tr>";
    $numcomposante++;
}
$totalavailability = round($totalavailcourses * 100 / $totalknowncourses, 1); 
$totalnombrevets = round($totalpromotions *100 / $totalnbrvets, 1);
echo "<tr style ='font-weight:bold'><td>TOTAL</td><td style='text-align:center'>$totalavailcourses / $totalknowncourses ($totalavailability%)</td><td style='text-align:center'>$totalpromotions / $totalnbrvets ($totalnombrevets%)</td></tr>";
echo "</table><br><br>";

echo "<h3>Nombre de cours créés, d'étudiants inscrits à ces cours et d'étudiants réellement actifs</h3><br>";
//echo "Et d'étudiants inscrits à ces cours.<br><br>";

$sql = "SELECT COUNT(id) AS nbcourses FROM mdl_course WHERE category <> 122 AND category <> 243 AND category <> $CFG->catbrouillonsid";
$nbcourses = $DB->get_record_sql($sql)->nbcourses;
$totalcourses = $nbcourses;
//echo "<strong>$nbcourses</strong> espaces de cours créés.<br><br>";

echo "<table><tr>";
echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Cours créés sur la plateforme</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Etudiants inscrits</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Etudiants actifs</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Promotions concernées</h3></td>";
//echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Etudiants dans la composante</h3></td>";
//echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Etudiants inscrits à des cours sur la plateforme</h3></td>";
//echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Pourcentage de numérisation</h3></td>";
echo "</tr>";
$nbstudents = 0;     
$totalactivestudents = 0;
$promotions = 0;
foreach ($composantes as $composante) {
    echo "<tr>";
    $sql = "SELECT COUNT(id) AS nbcourses FROM mdl_course WHERE shortname LIKE '".$composante["code"]."%-%'";
    $nbcoursesincomposante = $DB->get_record_sql($sql)->nbcourses;    
    $sql = "SELECT COUNT(id) AS nbknowncourses FROM `mdl_cat_demandecours` WHERE code LIKE '".$composante["code"]."%-%'";
    $nbknowncourses = $DB->get_record_sql($sql)->nbknowncourses;
    echo "<td>".$composante["name"]."</td>";
    if ($nbknowncourses) {
        $coursepercent = round($nbcoursesincomposante * 100 /$nbknowncourses, 1);
    } else {
        $coursepercent = 0;
    }
    
    echo "<td style='text-align:center'>$nbcoursesincomposante / $nbknowncourses ($coursepercent%)</td>";
    $nbcourses -= $nbcoursesincomposante;                        
    
    //Nombre d'étudiants actifs pour la composante    
    $sql = "SELECT COUNT(distinct l.userid) AS nbactivestudentsincomposante "
            . "FROM `mdl_log` l, mdl_ufr_student u WHERE l.course > 1 AND u.userid = l.userid "
            . "AND u.student = 1 AND u.ufrcode = '".$composante["code"]."'";
    
    $nbactivestudentsincomposante = $DB->get_record_sql($sql)->nbactivestudentsincomposante;
    $totalactivestudents += $nbactivestudentsincomposante;
    
    $sql = "SELECT COUNT(DISTINCT ra.userid) AS nbstudentsincomposante "
            . "FROM mdl_role_assignments ra, mdl_context x, mdl_course c "
            . "WHERE c.shortname LIKE '".$composante["code"]."%' AND x.contextlevel = 50 AND x.instanceid = c.id AND ra.contextid = x.id AND ra.roleid = 5";
   // echo "$sql<br>";
    $nbstudentsincomposante = $DB->get_record_sql($sql)->nbstudentsincomposante;
    
    if ($nbstudentsincomposante < $nbactivestudentsincomposante) {
        $nbstudentsincomposante = $nbactivestudentsincomposante;
    }
   // echo "$nbstudents + $nbstudentsincomposante = ";
    $nbstudents += $nbstudentsincomposante;
    //echo "$nbstudents<br>";
    //Nombre d'étudiants inscrits à la plateforme pour cette composante
    $sql = "SELECT COUNT(userid) AS nb FROM mdl_ufr_student WHERE ufrcode = '".$composante["code"]."' AND student = 1";
    
    $nbknownstudentsincomposante = $DB->get_record_sql($sql)->nb;   
    if ($nbstudentsincomposante > $nbknownstudentsincomposante) {
        $nbstudentsincomposante = $nbknownstudentsincomposante;                
    }
    if ($nbknownstudentsincomposante) {
        $studentpercent = round($nbstudentsincomposante * 100 /$nbknownstudentsincomposante, 1); 
        $activepercent = round($nbactivestudentsincomposante * 100 /$nbknownstudentsincomposante, 1); 
    } else {
        $studentpercent = 0;
        $activepercent = 0;
    }
    
    echo "<td style='text-align:center'>$nbstudentsincomposante / $nbknownstudentsincomposante ($studentpercent%)</td>";    
    
    echo "<td style='text-align:center'>$nbactivestudentsincomposante / $nbknownstudentsincomposante ($activepercent%)</td>";    
   
    //Promotions
    $sql= "SELECT count(distinct course.category) as promotions
					FROM mdl_course course, mdl_course_categories niveaux, mdl_course_categories vets
					WHERE niveaux.parent = '".$composante["id"]."'
					AND vets.parent = niveaux.id
					AND vets.id = course.category";  
    $nbpromotions = $DB->get_record_sql($sql)->promotions;  
    	
    $sql = "SELECT count(distinct vets.id) as nbrvets FROM `mdl_cat_demandecours` niveau, mdl_cat_demandecours vets where niveau.id = vets.parent_id and niveau.parent_id = '".$composante["idcat"]."'";
    $nbrvets = $DB->get_record_sql($sql)->nbrvets;
    if ($nbrvets) {
        $promotionspercent = round($nbpromotions * 100 /$nbrvets, 1);
    } else {
        $promotionspercent = 0;
    }

	
    echo "<td style='text-align:center'>$nbpromotions / $nbrvets ($promotionspercent%)</td>";   
    $promotions -= $nbpromotions;
    echo "</tr>";
}

$sql = "SELECT COUNT(id) AS nbsefiapcourses FROM mdl_course "
        . "WHERE category = 4 OR category = 45 OR category = 48 OR category = 20";
$nbsefiapcourses = $DB->get_record_sql($sql)->nbsefiapcourses;
$nbcourses -= $nbsefiapcourses;

echo "<tr><td>SEFIAP</td><td style='text-align:center'>$nbsefiapcourses</td><td style='text-align:center' colspan = 2 rowspan=2>Comptés dans leurs composantes respectives</td></tr>";
echo "<tr><td>AUTRES</td><td style='text-align:center'>$nbcourses</td></tr>";

$percentcreatedcourses = round($totalcourses * 100 / $totalknowncourses, 1);
$percentstudents = round($nbstudents * 100 / $totalknownstudents, 1);
$percentactivestudents = round($totalactivestudents * 100 / $totalknownstudents, 1);
//Add
$sql= "select count(distinct category) as total from mdl_course";
$totalpromotion = $DB->get_record_sql($sql) ->total;
$percentpromotionss = round($totalpromotion * 100 / $totalnbrvets, 1);


echo "<tr style ='font-weight:bold'><td>TOTAL</td>"
        . "<td style='text-align:center'>$totalcourses / $totalknowncourses ($percentcreatedcourses%)</td>"
        . "<td style='text-align:center'>$nbstudents / $totalknownstudents ($percentstudents%)</td>"
        . "<td style='text-align:center'>$totalactivestudents / $totalknownstudents ($percentactivestudents%)</td>"
        ."<td style='text-align:center'>$totalpromotion / $totalnbrvets ($percentpromotionss%)</td>"
        . "</tr>";        
echo "</table><br>";
/*
echo "<h3>Activités utilisées</h3><br>";

echo "Certaines activités sont créées automatiquement avec chaque nouveau cours. Celles qui ne servent pas ne sont pas comptabilisées ici.<br><br>";

echo "<table>";
echo "<tr>";
echo "<td bgcolor='#780D68'></td>";
echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Type d'activité</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Instances utilisées</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Utilisateurs par mois</h3></td>";
echo "</tr>";

//Ressources
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/pix/f/pdf-24.png'/></td>";
echo "<td>Ressources</td>";
$sql = "SELECT COUNT(id) AS nbfiles FROM mdl_resource";
$nbfiles = $DB->get_record_sql($sql)->nbfiles;
echo "<td style='text-align:center'>$nbfiles</td>";
$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as res  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 19
and log.time > ($mysql_datetime - 2592000)";
$ressources = $DB->get_record_sql($sql)->res;
echo "<td style='text-align:center'>$ressources</td>";
echo "</tr>";

//Devoirs
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/assign/pix/icon.png'/></td>";
echo "<td>Devoirs</td>";
$sql = "SELECT COUNT(id) AS nbassigns FROM mdl_assign";
$nbassigns = $DB->get_record_sql($sql)->nbassigns;
echo "<td style='text-align:center'>$nbassigns</td>";
$mysql_datetime = time();
$sql = "SELECT count(distinct userid) as nbruser FROM `mdl_assign_submission` where timecreated > ($mysql_datetime - 2592000)";
$devoirs = $DB->get_record_sql($sql)->nbruser;
echo "<td style='text-align:center'>$devoirs</td>";
echo "</tr>";

//Forum
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/forum/pix/icon.png'/></td>";
echo "<td>Forums</td>";
$sql = "SELECT COUNT(DISTINCT forum) AS nbforums FROM `mdl_forum_discussions`";
$nbforums = $DB->get_record_sql($sql)->nbforums;
echo "<td style='text-align:center'>$nbforums</td>";
$mysql_datetime = time();
$sql="SELECT count(distinct userid) as nbrforum  FROM `mdl_forum_posts` where created > ($mysql_datetime - 2592000)";
$nbrforum = $DB->get_record_sql($sql)->nbrforum;
echo "<td style='text-align:center'>$nbrforum</td>";
echo "</tr>";

//Chat
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/chat/pix/icon.png'/></td>";
echo "<td>Salons de tchat</td>";
$sql = "SELECT COUNT(DISTINCT chatid) AS nbchats FROM `mdl_chat_messages`";
$nbchats = $DB->get_record_sql($sql)->nbchats;
echo "<td style='text-align:center'>$nbchats</td>";
$mysql_datetime = time();
$sql="SELECT count(distinct userid) as nbrchat FROM `mdl_chat_messages` where timestamp > ($mysql_datetime - 2592000) ";
$nbrchat = $DB->get_record_sql($sql)->nbrchat;
echo "<td style='text-align:center'>$nbrchat</td>";
echo "</tr>";

//Page
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/page/pix/icon.png'/></td>";
echo "<td>Pages web</td>";
$sql = "SELECT COUNT(id) AS nbpages FROM `mdl_page`";
$nbpages = $DB->get_record_sql($sql)->nbpages;
echo "<td style='text-align:center'>$nbpages</td>";
$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as nbruserpages  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 16
and log.time > ($mysql_datetime - 2592000)";
$nbruserpages = $DB->get_record_sql($sql)->nbruserpages;
echo "<td style='text-align:center'>$nbruserpages</td>";
echo "</tr>";

//Quiz
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/quiz/pix/icon.png'/></td>";
echo "<td>Quiz</td>";
//$sql = "SELECT COUNT(DISTINCT quiz) AS nbquiz FROM mdl_quiz_question_instances";
$sql = "SELECT COUNT(DISTINCT quiz) AS nbquiz FROM `mdl_quiz_attempts`";
$nbquiz = $DB->get_record_sql($sql)->nbquiz;
echo "<td style='text-align:center'>$nbquiz</td>";
//echo "<td>D'autres quiz contiennent des questions mais aucun étudiant n'y a répondu.</td>";
$mysql_datetime = time();
$sql="SELECT count(distinct userid)as nbruserquiz  FROM `mdl_quiz_attempts` where `timestart`> ($mysql_datetime - 2592000)";
$nbruserquiz = $DB->get_record_sql($sql)->nbruserquiz;
echo "<td style='text-align:center'>$nbruserquiz</td>";
echo "</tr>";

//Présence
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/attendance/pix/icon.gif'/></td>";
echo "<td>Présence (feuilles d'appel)</td>";
$sql = "SELECT COUNT(id) AS nbattendances FROM mdl_attendance";
$nbattendances = $DB->get_record_sql($sql)->nbattendances;
echo "<td style='text-align:center'>$nbattendances</td>";
$mysql_datetime = time();
$sql = "SELECT count(distinct studentid) as nbruserpresence FROM `mdl_attendance_log` where `timetaken` > ($mysql_datetime - 2592000)";
$nbruserpresence = $DB->get_record_sql($sql)->nbruserpresence;
echo "<td style='text-align:center'>$nbruserpresence</td>";
echo "</tr>";

//SCORM
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/scorm/pix/icon.png'/></td>";
echo "<td>Paquetages SCORM</td>";
$sql = "SELECT COUNT(id) AS nbscorms FROM mdl_scorm";
$nbscorms = $DB->get_record_sql($sql)->nbscorms;
echo "<td style='text-align:center'>$nbscorms</td>";
$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as nbruserscormscorm  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 20
and log.time > ($mysql_datetime - 2592000)";
$nbruserscormscorm = $DB->get_record_sql($sql)->nbruserscormscorm;
echo "<td style='text-align:center'>$nbruserscormscorm</td>";
echo "</tr>";

//Feedback
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/feedback/pix/icon.png'/></td>";
echo "<td>Feedbacks (questionnaires)</td>";
$sql = "SELECT COUNT(id) AS nbfeedbacks FROM mdl_feedback";
$nbfeedbacks = $DB->get_record_sql($sql)->nbfeedbacks;
echo "<td style='text-align:center'>$nbfeedbacks</td>";
$mysql_datetime = time();
$sql = "SELECT count(distinct userid) as nbruserfeedback FROM `mdl_feedback_completed` where `timemodified` > ($mysql_datetime - 2592000)";
$nbruserfeedback = $DB->get_record_sql($sql)->nbruserfeedback;
echo "<td style='text-align:center'>$nbruserfeedback</td>";
echo "</tr>";

//Leçon
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/lesson/pix/icon.png'/></td>";
echo "<td>Leçons (scénarios)</td>";
$sql = "SELECT COUNT(id) AS nblessons FROM mdl_lesson";
$nblessons = $DB->get_record_sql($sql)->nblessons;
echo "<td style='text-align:center'>$nblessons</td>";
$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as nbruserlesson  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 14
and log.time > ($mysql_datetime - 2592000)";
$nbruserlesson = $DB->get_record_sql($sql)->nbruserlesson;
echo "<td style='text-align:center'>$nbruserlesson</td>";
echo "</tr>";

//Visioconférence
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/bigbluebuttonbn/pix/icon.gif'/></td>";
echo "<td>Visioconférences</td>";
$sql = "SELECT COUNT(id) AS nbvisios FROM mdl_bigbluebuttonbn";
$nbvisios = $DB->get_record_sql($sql)->nbvisios;
echo "<td style='text-align:center'>$nbvisios</td>";
$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as nbruservisio  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 3
and log.time > ($mysql_datetime - 2592000)";
$nbruservisio = $DB->get_record_sql($sql)->nbruservisio;
echo "<td style='text-align:center'>$nbruservisio</td>";

echo "</tr>";

//Atelier
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/workshop/pix/icon.png'/></td>";
echo "<td>Ateliers</td>";
$sql = "SELECT COUNT(id) AS nbworkshops FROM mdl_workshop";
$nbworkshops = $DB->get_record_sql($sql)->nbworkshops;
echo "<td style='text-align:center'>$nbworkshops</td>";
$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as nbruserwork  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 24
and log.time > ($mysql_datetime - 2592000)";
$nbruserwork = $DB->get_record_sql($sql)->nbruserwork;
echo "<td style='text-align:center'>$nbruserwork</td>";
echo "</tr>";


//Wiki
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/wiki/pix/icon.png'/></td>";
echo "<td>Wikis</td>";
$sql = "SELECT COUNT(DISTINCT wikiid) AS nbwikis FROM `mdl_wiki_subwikis`";
$nbwikis = $DB->get_record_sql($sql)->nbwikis;
echo "<td style='text-align:center'>$nbwikis</td>";
$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as nbruserwiki  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 23
and log.time > ($mysql_datetime - 2592000)";
$nbruserwiki = $DB->get_record_sql($sql)->nbruserwiki;
echo "<td style='text-align:center'>$nbruserwiki</td>";
echo "</tr>";

//Sondage
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/choice/pix/icon.png'/></td>";
echo "<td>Sondages</td>";
$sql = "SELECT COUNT(id) AS nbchoices FROM mdl_choice";
$nbchoices = $DB->get_record_sql($sql)->nbchoices;
echo "<td style='text-align:center'>$nbchoices</td>";
$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as nbrusersondage  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 6
and log.time > ($mysql_datetime - 2592000)";
$nbrusersondage = $DB->get_record_sql($sql)->nbrusersondage;
echo "<td style='text-align:center'>$nbrusersondage</td>";
echo "</tr>";

//Glossaire
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/glossary/pix/icon.png'/></td>";
echo "<td>Glossaires</td>";
$sql = "SELECT COUNT(id) AS nbglossarys FROM `mdl_glossary`";
$nbglossarys = $DB->get_record_sql($sql)->nbglossarys;
echo "<td style='text-align:center'>$nbglossarys</td>";

$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as nbruserglossaire  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 11
and log.time > ($mysql_datetime - 2592000)";
$nbruserglossaire = $DB->get_record_sql($sql)->nbruserglossaire;
echo "<td style='text-align:center'>$nbruserglossaire</td>";
echo "</tr>";

//Consultation
echo "<tr>";
echo "<td><img src='$CFG->wwwroot/mod/survey/pix/icon.png'/></td>";
echo "<td>Consultations</td>";
$sql = "SELECT COUNT(id) AS nbsurveys FROM mdl_survey";
$nbsurveys = $DB->get_record_sql($sql)->nbsurveys;
echo "<td style='text-align:center'>$nbsurveys</td>";
$mysql_datetime = time();
$sql ="SELECT count( distinct(log.userid)) as nbruserconsul  FROM `mdl_log` log , mdl_course_modules cmodules , mdl_modules modules WHERE log.cmid = cmodules.id
and cmodules.module = 21
and log.time > ($mysql_datetime - 2592000)";
$nbruserconsul = $DB->get_record_sql($sql)->nbruserconsul;
echo "<td style='text-align:center'>$nbruserconsul</td>";
echo "</tr>";


//Projet
//echo "<tr>";
//echo "<td><img src='$CFG->wwwroot/mod/techproject/pix/icon.gif'/></td>";
//echo "<td>Projets</td>";
//$sql = "SELECT COUNT(id) AS nbprojects FROM `mdl_techproject`";
//$nbprojects = $DB->get_record_sql($sql)->nbprojects;
//echo "<td style='text-align:center'>$nbprojects</td>";
//echo "</tr>";

//Bases de données
//echo "<tr>";
//echo "<td><img src='$CFG->wwwroot/mod/data/pix/icon.png'/></td>";
//echo "<td>Bases de données</td>";
//$sql = "SELECT COUNT(id) AS nbdatas FROM `mdl_data`";
//$nbdatas = $DB->get_record_sql($sql)->nbdatas;
//echo "<td style='text-align:center'>$nbdatas</td>";
//echo "</tr>";

echo "</table>";
echo "<br><br>";
*/
echo "<h3>Divers</h3><br>";
$sql = "SELECT COUNT(DISTINCT userid) AS nbdistinctteachers FROM mdl_role_assignments WHERE roleid = 3";
$nbdistinctteachers = $DB->get_record_sql($sql)->nbdistinctteachers;
echo "<strong>".format_number($nbdistinctteachers)."</strong> enseignants distincts ont créé des cours sur la plateforme.<br><br>";


$sql = "SELECT COUNT(DISTINCT userid) AS nbusers FROM mdl_role_assignments WHERE roleid > 2 AND roleid < 6 AND contextid <> 48617";
$nbusers = $DB->get_record_sql($sql)->nbusers;
$sql = "SELECT COUNT(DISTINCT userid) AS nbusers FROM mdl_role_assignments WHERE roleid = 3";
$nbteachers = $DB->get_record_sql($sql)->nbusers;
$nbstudents = $nbusers - $nbteachers;

/*echo "<strong>$nbusers</strong> utilisateurs inscrits à au moins un de ces cours "
        . "(autre que <a href='$CFG->wwwroot/course/view.php?id=678'>Communauté des usagers du numérique</a>), "
        . "dont <strong>$nbstudents</strong> étudiants.<br>";*/
        
$sql = "SELECT COUNT(id) AS nbusers FROM mdl_user";
$nbusers = $DB->get_record_sql($sql)->nbusers;
$sql = "SELECT COUNT(id) AS nbusers FROM mdl_user "
                    . "WHERE (email LIKE '%@u-cergy.fr' OR email LIKE '%@iufm.u-cergy.fr' OR email LIKE '%@sciencespo-saintgermainenlaye.fr')";
$nbteachers = $DB->get_record_sql($sql)->nbusers;
$nbstudents = $nbusers - $nbteachers;

//echo "<strong>$nbusers</strong> utilisateurs inscrits à la plateforme, dont <strong>$nbstudents</strong> étudiants.<br><br>";

$sql = "SELECT number FROM mdl_chiffres WHERE name = 'weekconnections'";
$weekconnections = $DB->get_record_sql($sql)->number;
echo "<strong>".format_number($weekconnections)."</strong> connexions depuis une semaine.<br><br>";

$sql = "SELECT number FROM mdl_chiffres WHERE name = 'nbgrades'";
$nbgrades = $DB->get_record_sql($sql)->number;
echo "<strong>".format_number($nbgrades)."</strong> copies virtuelles notées.<br><br>";

$sql = "SELECT COUNT(id) AS nbfiles FROM mdl_files";
$nbfiles = $DB->get_record_sql($sql)->nbfiles;
echo "<strong>".format_number($nbfiles)."</strong> fichiers déposés.<br><br>";


$sql = "SELECT number FROM mdl_chiffres WHERE name = 'nbviews'";
$nbviews = $DB->get_record_sql($sql)->number;
echo "<strong>".format_number($nbviews)."</strong> consultations de cours ou documents depuis le 24/08/2015.<br><br>";



$sql = "SELECT number FROM mdl_chiffres WHERE name = 'nblogs'";
$nblogs = $DB->get_record_sql($sql)->number;
echo "<strong>".format_number($nblogs)."</strong> actions réalisées sur la plateforme "
        . "(consultation d'un document, envoi d'un message, remise d'un devoir, etc.) depuis le 24/08/2015.<br><br>";








echo $OUTPUT->footer();

//Groupe les chiffres d'un grand nombre par 3
function format_number($nb) {    
    $nblength = strlen($nb);    
    $nbgroups = ceil($nblength / 3);    
    
    $modulo = $nblength % 3;
    
    $return = substr($nb, 0, $modulo);    
    $nbremains = substr($nb, $modulo);
    
        
    for ($i = 1; $i < $nbgroups; $i++) {
        $return .= " ".substr($nbremains, 0, 3);        
        $nbremains = substr($nbremains, 3);        
    }    
    
    if (!$modulo) {
        $return .= " ".$nbremains;
    }
    
    
    return $return;
}
