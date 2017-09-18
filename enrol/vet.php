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
 * Main course enrolment management UI.
 *
 * @package    core_enrol
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');

//BRICE pour inscription de toute une VET
require_once($CFG->dirroot.'/group/lib.php'); 
require_once($CFG->dirroot.'/course/lib.php'); 
require_once($CFG->dirroot.'/lib/coursecatlib.php');
//FIN

$id         = required_param('id', PARAM_INT); // course id
$action     = optional_param('action', '', PARAM_ALPHANUMEXT);
$instanceid = optional_param('instance', 0, PARAM_INT);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);
$confirm2   = optional_param('confirm2', 0, PARAM_BOOL);


$allpromo = optional_param('allpromo', 0, PARAM_ALPHANUMEXT); //BRICE pour inscrire d'un coup la totalité d'une promotion
if ($allpromo) {
	$prefix = substr($allpromo, 0, 4);
	if ($prefix != 'Y201') {
		$allpromo = 'Y2017-'.$allpromo;
	}
}
$codevet = substr($allpromo, 6); //Par exemple, $allpromo = 'Y2017-A2T5C' et $codevet = 'A2T5C'

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

require_login($course);
require_capability('moodle/course:enrolreview', $context);

$canconfig = has_capability('moodle/course:enrolconfig', $context);

$PAGE->set_url('/enrol/vet.php', array('id'=>$course->id));
$PAGE->set_pagelayout('admin');
$PAGE->set_title('Inscription par VET');
$PAGE->set_heading($course->fullname);

$instances = enrol_get_instances($course->id, false);
$plugins   = enrol_get_plugins(false);

echo $OUTPUT->header();
echo $OUTPUT->heading('Inscription par VET');

//BRICE Inscrire toute une promotion d'après son code VET
echo "<h3>Inscrire TOUS les étudiants d'une certaine VET</h3>";

//BRICE Inscription de toute la promotion courante
if ($allpromo) {
    //Promotion à inscrire
    $sql = "SELECT name, id FROM mdl_course_categories WHERE idnumber = '$allpromo'";
    $enroledpromo = $DB->get_record_sql($sql);
    $newvet = 0;
        
    //Si la promotion demandée n'existe pas encore sur la plateforme, il faut la créer
    if ($enroledpromo) {
        echo "<p style='font-weight:bold;color:green'>La VET ($allpromo) $enroledpromo->name existait déjà sur la plateforme.</p>";
    } else {
        //Recherche de la VET dans le fichier XML
        $xmldoc = new DOMDocument();
        $xmldoc->load('/home/referentiel/dokeos_offre_pedagogique.xml');
        $xpathvar = new Domxpath($xmldoc);            
        $queryvet = $xpathvar->query("//Etape[@Code_etape='$codevet']");
        
        foreach ($queryvet as $vetdata) {            
            $nomvet = $vetdata->getAttribute('Lib_etape');            
            echo "nomvet : $nomvet<br>";
        }
        
        if ($nomvet) {
            echo "<p style='font-weight:bold;color:red'>La VET ($allpromo) $nomvet n'existe pas encore sur la plateforme.</p>";
                
            //Création de la VET
            echo "Création de la VET ($allpromo) $nomvet ... ";
            $enroledpromo = new stdClass();
            $enroledpromo->name = $nomvet;
            $enroledpromo->id = createvetifnew($allpromo, $nomvet);
            $newvet = 1;
            echo "<span style='font:weight:bold;color:green'>Création réussie.</span><br><br>";

            echo "<span style='font:weight:bold;color:green'>Les étudiants de la nouvelle VET ($allpromo) $nomvet seront inscrits à votre cours d'ici quelques heures puis au fur et à mesure de leur inscription dans APOGEE/CELCAT.</span><br><br>";
        } else {
            echo "<p style='color:red;font-weight:bold'>ERREUR : ce code VET n'existe pas.</p>";
            exit;
        }
    }
    
    
    //Y a-t-il déjà, dans ce cours, un groupe portant l'idnumber de la promotion ?
    $sql = "SELECT id AS groupid FROM mdl_groups WHERE idnumber = '$allpromo' AND courseid = $course->id";
    $groupid = $DB->get_record_sql($sql)->groupid;
    //Si non, on le crée
    if (!$groupid) {
        $newgroupdata = new stdClass();
        $newgroupdata->courseid = $course->id;
        $newgroupdata->idnumber = $allpromo;
        $newgroupdata->name = $enroledpromo->name;        
        $newgroupdata->description = 'Toute la VET';
        $groupid = groups_create_group($newgroupdata);
        
        //$groupid = $DB->insert_record('groups',array('courseid'=>$course->id,'idnumber'=>"$enroledpromo->idnumber",'name'=>$enroledpromo->name,'description'=>'Tous les étudiants de la VET', 'descriptionformat'=>1, 'timecreated'=>time(),'timemodified'=>time()));
    }
    
    //Pour chaque étudiant de cette promotion
    $sql = "SELECT studentid FROM mdl_student_vet WHERE categoryid = $enroledpromo->id";
    $vetstudents = $DB->get_recordset_sql($sql);
    $now = time();    
    $nbenroledstudents = 0;

    foreach ($vetstudents as $vetstudent) {
        //S'il n'est pas encore inscrit au cours, on l'y inscrit
        $sql = "SELECT ue.id "
             . "FROM mdl_user_enrolments ue, mdl_enrol e "
             . "WHERE ue.userid = $vetstudent->studentid "
             . "AND ue.enrolid = e.id AND e.roleid = 5 AND e.courseid = $course->id";        
        $ue = $DB->get_record_sql($sql);
        if (!$ue) {
            $sql = "SELECT id FROM mdl_enrol WHERE courseid = $course->id AND enrol = 'manual'";            
            $enrolid = $DB->get_record_sql($sql)->id;            
            $inserted = array('enrolid'=>$enrolid,'userid'=>$vetstudent->studentid,'timestart'=>$now,'modifierid'=>$USER->id,'timecreated'=>$now,'timemodified'=>$now);            
            $ueid = $DB->insert_record('user_enrolments', $inserted);
            //On lui donne le rôle étudiant
            $inserted = array('roleid'=>5,'contextid'=>$context->id,'userid'=>$vetstudent->studentid,'timemodified'=>$now,'modifierid'=>$USER->id);            
            $raid = $DB->insert_record('role_assignments', $inserted);
            $nbenroledstudents++;
        }
        //S'il n'est pas encore inscrit dans le groupe de la VET, on l'y inscrit
        $sql = "SELECT id FROM mdl_groups_members WHERE groupid = $groupid AND userid = $vetstudent->studentid";        
        $gm = $DB->get_record_sql($sql);
        if (!$gm) {
            $inserted = array('groupid'=>$groupid,'userid'=>$vetstudent->studentid,'timeadded'=>$now);            
            $gmid = $DB->insert_record('groups_members', $inserted);
        }
    }

    if (!$newvet) {
        echo "<p style='font-weight:bold;color:red'>$nbenroledstudents étudiants inscrits.</p>";
    }
    
    echo "<p style='text-align:center'>--------------------------------</p>";
        
}
//FIN

$sql = "SELECT idnumber FROM mdl_course_categories WHERE id = $COURSE->category";
$currentvetcode = $DB->get_record_sql($sql)->idnumber;


$action = "vet.php?id=$COURSE->id";
echo '<form enctype="multipart/form-data" action="'.$action.'" method="post">            
        Indiquez le code de la VET dont vous souhaitez inscrire les étudiants. La valeur proposée par défaut est le code de la VET à laquelle ce cours est associé.<br><br>
        <p style="text-align:center">    
            <input type="text" name="allpromo" value="'.$currentvetcode.'"/>    <input type="submit" value="Valider"/><br>
        </p>
      </form><br>';

echo "ATTENTION : si la VET que vous demandez n'a pas encore de cours sur la plateforme, ce processus peut prendre quelques minutes !<br>";

echo $OUTPUT->footer();
