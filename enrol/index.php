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
 * This page shows all course enrolment options for current user.
 *
 * @package    core_enrol
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once("$CFG->libdir/formslib.php");

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', 0, PARAM_LOCALURL);
$demande = optional_param('demande', 0, PARAM_INT); //BRICE

if (!isloggedin()) {
    $referer = clean_param(get_referer(), PARAM_LOCALURL);
    if (empty($referer)) {
        // A user that is not logged in has arrived directly on this page,
        // they should be redirected to the course page they are trying to enrol on after logging in.
        $SESSION->wantsurl = "$CFG->wwwroot/course/view.php?id=$id";
    }
    // do not use require_login here because we are usually coming from it,
    // it would also mess up the SESSION->wantsurl
    redirect(get_login_url());
}

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Everybody is enrolled on the frontpage
if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
    print_error('coursehidden');
}

$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/enrol/index.php', array('id'=>$course->id));

// do not allow enrols when in login-as session
if (\core\session\manager::is_loggedinas() and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
    print_error('loginasnoenrol', '', $CFG->wwwroot.'/course/view.php?id='.$USER->loginascontext->instanceid);
}

// get all enrol forms available in this course
$enrols = enrol_get_plugins(true);
$enrolinstances = enrol_get_instances($course->id, true);
$forms = array();
foreach($enrolinstances as $instance) {
    //print_object($instance);
    if (!isset($enrols[$instance->enrol])) {
        continue;
    }
    $form = $enrols[$instance->enrol]->enrol_page_hook($instance);
    if ($form) {
        $forms[$instance->id] = $form;
    }
}

// Check if user already enrolled
if (is_enrolled($context, $USER, '', true)) {
    if (!empty($SESSION->wantsurl)) {
        $destination = $SESSION->wantsurl;
        unset($SESSION->wantsurl);
    } else {
        $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
    }
    redirect($destination);   // Bye!
}

$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('enrolmentoptions','enrol'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('enrolmentoptions','enrol'));

$courserenderer = $PAGE->get_renderer('core', 'course');
echo $courserenderer->course_info_box($course);

//TODO: find if future enrolments present and display some info

foreach ($forms as $form) {
    echo $form;
}

if (!$forms) {
    if (isguestuser()) {
        notice(get_string('noguestaccess', 'enrol'), get_login_url());
    }
    /*BRICE else if ($returnurl) {
        notice(get_string('notenrollable', 'enrol'), $returnurl);
    } else {
        $url = clean_param(get_referer(false), PARAM_LOCALURL);
        if (empty($url)) {
            $url = new moodle_url('/index.php');
        }
        notice(get_string('notenrollable', 'enrol'), $url);
    } */
}

//BRICE
if (!$returnurl) {
    $returnurl = new moodle_url('/index.php');
}
//notice(get_string('notenrollable', 'enrol'), $returnurl);

//VET du cours
$sql = "SELECT a.id, a.name, a.idnumber "
     . "FROM mdl_course c, mdl_course_categories a "
     . "WHERE c.id = $id AND a.id = c.category";
$vet = $DB->get_record_sql($sql);

echo "<hr>";
echo "Ce cours concerne la promotion d'étudiants <strong>$vet->name</strong>. ";

//L'étudiant est-il inscrit à cette VET ?
$sql = "SELECT id FROM mdl_student_vet WHERE studentid = $USER->id AND categoryid = $vet->id";
$studentinvet = $DB->get_record_sql($sql);
if ($studentinvet) {
    echo "Vous en faites partie.";
} else {
    echo "Vous n'en faites pas partie.";
}
echo "<br><br>";

//BRICE Ajout pour l'UFR Droit
$ufrdroit = false;
$parentcontextids = explode('/', $context->path);
$ufrdroitcontext = 51045;
if (in_array($ufrdroitcontext, $parentcontextids)) {
	$ufrdroit = true;
}
if ($ufrdroit) {
	echo "Ce cours fait partie de l'UFR Droit. L'équipe de l'UFR Droit ne vous autorise pas à demander votre inscription via cette plateforme. Merci de vous adresser au secrétariat pédagogique de votre formation.";
	echo $OUTPUT->footer;
	exit;
}
//FIN

//Cet utilisateur a-t-il déjà fait une demande d'inscription à ce cours ?
$sql = "SELECT id FROM mdl_asked_enrolments WHERE courseid = $id AND studentid = $USER->id";
$asked = $DB->get_record_sql($sql);

if (($demande)&&(!$asked)) {

    //Création de la demande dans la table mdl_asked_enrolments
    $timestamp = time();
    $sql = "INSERT INTO mdl_asked_enrolments (courseid, studentid, askedat) VALUES ($id, $USER->id, $timestamp)";
    //echo "$sql<br>";
    $DB->execute($sql);
	//Send mail
	$studentdata = "select email from mdl_user where id =$USER->id";
	$resstudent = $DB->get_record_sql($studentdata);
	$coursedata = "select fullname from mdl_course where id =$id";
	$resdatacourse = $DB->get_record_sql($coursedata);
	$query = "SELECT u.firstname, u.lastname, u.email
                FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c
                WHERE u.id = r.userid
                AND r.contextid = cx.id
                AND cx.instanceid = c.id
                AND r.roleid =3
                AND cx.contextlevel =50
                AND c.id = $id";
	$resquery = $DB->get_records_sql($query);
	$to      = "$resstudent->email";
    $subject = "CoursUCP : Votre demande d'inscription";
    $message1 = "Bonjour, \n\nVous venez de créer une demande d'inscription au cours $resdatacourse->fullname\nVotre demande a bien été transmise, et est en cours de traitement, vous recevrez un message lorsque celle-ci sera traitée.\nCe cours est géré par :\n";
	$var ='';
	foreach($resquery as $data)
	{
		//$message2 = "$data->firstname $data->lastname $data->email";
		$var.= "$data->firstname $data->lastname $data->email \n";
	}
	$message3 = "\nVous pouvez consulter à tout moment l'état de votre demande, depuis https://cours.u-cergy.fr : Accueil --> Bloc Demandes d'inscription --> En attente.\n\nBien cordialement,\nCoursUCP, votre plateforme pédagogique";
	$message = "$message1 $var $message3";
    $headers = 'From: noreply@cours.u-cergy.fr'."\r\n".'MIME-Version: 1.0'.'\r\n'.
    'Reply-To: noreply@cours.u-cergy.fr'."\r\n".'Content-type: text/html; charset=utf-8'.'\r\n'.
    'X-Mailer: PHP/'.phpversion();
    mail($to, $subject, $message, $headers);
}

if (($asked)||($demande)) {

    echo "<fieldset style='padding : 10px; width: 98%;font-weight : bold; background-color:green; color:white;''>
           Votre demande a bien été transmise. Un enseignant de ce cours va l'accepter ou la rejeter.</fieldset>";
} else {

    echo "Si vous pensez que vous devriez être inscrit(e) à ce cours, vous pouvez déposer une demande ci-dessous. Un(e) enseignant(e) vous répondra.";
    echo "<br><br>";
}

//BRICE echo $OUTPUT->continue_button($returnurl);
echo "<br><p style='text-align:center'>";
echo "<a href='index.php?id=$id&returnurl=$returnurl&demande=1'><button>Demander mon inscription</button></a>";
echo "&nbsp; &nbsp;";
echo "<a href='$CFG->wwwroot/course/index.php?categoryid=$vet->id'><button>Retour aux catégories</button></a>";
echo "</p>";

//FIN


echo $OUTPUT->footer();
