<?php
//BRICE
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
 * Prints a particular instance of workshop
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_workshop
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id         = optional_param('id', 0, PARAM_INT); // course_module ID, or
$w          = optional_param('w', 0, PARAM_INT);  // workshop instance ID
$editmode   = optional_param('editmode', null, PARAM_BOOL);
$page       = optional_param('page', 0, PARAM_INT);
$perpage    = optional_param('perpage', null, PARAM_INT);
$sortby     = optional_param('sortby', 'lastname', PARAM_ALPHA);
$sorthow    = optional_param('sorthow', 'ASC', PARAM_ALPHA);
$eval       = optional_param('eval', null, PARAM_PLUGIN);

if ($id) {
    $cm             = get_coursemodule_from_id('workshop', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $workshoprecord = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $workshoprecord = $DB->get_record('workshop', array('id' => $w), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $workshoprecord->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('workshop', $workshoprecord->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);
require_capability('mod/workshop:viewallassessments', $PAGE->context);

$workshop = new workshop($workshoprecord, $cm, $course);

// Mark viewed
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$eventdata = array();
$eventdata['objectid']         = $workshop->id;
$eventdata['context']          = $workshop->context;

$PAGE->set_url($workshop->view_url());
$event = \mod_workshop\event\course_module_viewed::create($eventdata);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('workshop', $workshoprecord);
$event->add_record_snapshot('course_modules', $cm);
$event->trigger();

// If the phase is to be switched, do it asap. This just has to happen after triggering
// the event so that the scheduled allocator had a chance to allocate submissions.
if ($workshop->phase == workshop::PHASE_SUBMISSION and $workshop->phaseswitchassessment
        and $workshop->submissionend > 0 and $workshop->submissionend < time()) {
    $workshop->switch_phase(workshop::PHASE_ASSESSMENT);
    // Disable the automatic switching now so that it is not executed again by accident
    // if the teacher changes the phase back to the submission one.
    $DB->set_field('workshop', 'phaseswitchassessment', 0, array('id' => $workshop->id));
    $workshop->phaseswitchassessment = 0;
}

if (!is_null($editmode) && $PAGE->user_allowed_editing()) {
    $USER->editing = $editmode;
}

$PAGE->set_title($workshop->name);
$PAGE->set_heading($course->fullname);

if ($perpage and $perpage > 0 and $perpage <= 1000) {
    require_sesskey();
    set_user_preference('workshop_perpage', $perpage);
    redirect($PAGE->url);
}

if ($eval) {
    require_sesskey();
    require_capability('mod/workshop:overridegrades', $workshop->context);
    $workshop->set_grading_evaluation_method($eval);
    redirect($PAGE->url);
}

$output = $PAGE->get_renderer('mod_workshop');




$sql = "SELECT auteur.firstname AS Prénom_auteur, auteur.lastname AS Nom_auteur, copie.title AS Titre_copie, copie.grade AS Note_finale_copie, 
    correcteur.firstname AS Prénom_correcteur, correcteur.lastname AS Nom_correcteur, correction.grade AS Note_attribuee
FROM mdl_user auteur, mdl_user correcteur, mdl_workshop_submissions copie, mdl_workshop_assessments correction
WHERE auteur.id = copie.authorid AND correction.submissionid = copie.id AND correcteur.id = correction.reviewerid AND copie.workshopid = 32
ORDER BY auteur.lastname ASC, auteur.firstname ASC, copie.title ASC";

$sql = "SELECT auteur.firstname AS Prénom_auteur, auteur.lastname AS Nom_auteur, copie.title AS Titre_copie, copie.grade AS Note_finale_copie, correcteur.firstname AS Prénom_correcteur, correcteur.lastname AS Nom_correcteur, correction.grade AS Note_attribuee
FROM mdl_user auteur, mdl_user correcteur, mdl_workshop_submissions copie, mdl_workshop_assessments correction
WHERE auteur.id = copie.authorid AND correction.submissionid = copie.id AND correcteur.id = correction.reviewerid AND copie.workshopid = 32
ORDER BY correcteur.lastname ASC, correcteur.firstname ASC, copie.title ASC";

/// Output starts here

echo $output->header();
echo $output->heading_with_help(format_string($workshop->name), 'userplan', 'workshop');

//Tableau des notes attribuées
echo "<h2>Tableau des notes attribuées</h2>";

//Liste des correcteurs
$sql = "SELECT DISTINCT reviewer.id, reviewer.firstname, reviewer.lastname "
        . "FROM mdl_workshop_assessments correction, mdl_workshop_submissions copie, mdl_user reviewer "
        . "WHERE copie.workshopid = $workshop->id AND correction.submissionid = copie.id AND correction.reviewerid = reviewer.id "
        . "ORDER BY lastname, firstname";
$reviewers = $DB->get_recordset_sql($sql);

$csv = "Prenom correcteur;Nom correcteur;";
$csv .= "Prenom auteur1;Nom auteur1;Note auteur1 par ce correcteur;Note finale auteur1;Ecart auteur1;";
$csv .= "Prenom auteur2;Nom auteur2;Note auteur2 par ce correcteur;Note finale auteur2;Ecart auteur2;";
$csv .= "Prenom auteur3;Nom auteur3;Note auteur3 par ce correcteur;Note finale auteur3;Ecart auteur3;";
$csv .= "Prenom auteur4;Nom auteur4;Note auteur4 par ce correcteur;Note finale auteur4;Ecart auteur4;";
$csv .= "Prenom auteur5;Nom auteur5;Note auteur5 par ce correcteur;Note finale auteur5;Ecart auteur5;";
$csv .= "Moyenne ecarts;£µ£";

foreach($reviewers as $reviewer) {    
    $csv .= "$reviewer->firstname;$reviewer->lastname;";
    
    $sql = "SELECT author.firstname, author.lastname, copie.title, correction.grade AS correctgrade, copie.grade AS finalgrade "
        . "FROM mdl_workshop_assessments correction, mdl_workshop_submissions copie, mdl_user author "
        . "WHERE copie.workshopid = $workshop->id AND correction.submissionid = copie.id AND copie.authorid = author.id AND correction.reviewerid = $reviewer->id "
        . "ORDER BY lastname, firstname";    
    $reviews = $DB->get_recordset_sql($sql);
    $gaps = array();
    $nbgaps = 0;
    foreach ($reviews as $review) {
        //print_object($review);
        $gaps[$nbgaps] = abs($review->correctgrade - $review->finalgrade);
        $csv .= "$review->firstname;$review->lastname;$review->correctgrade;$review->finalgrade;".$gaps[$nbgaps].";";
        $nbgaps++;
    }    
    for ($i = $nbgaps; $i <= 4; $i++) {
        $csv .= ";;;;;";
    }
    $averagegap = array_sum($gaps) / $nbgaps;
    $csv .= "$averagegap;";
    $csv .= "£µ£";    
    
    if ($nbgaps > 5) {
        echo "$reviewer->firstname $reviewer->lastname : $nbgaps<br><br>";
    }
    
}

echo '<form enctype="multipart/form-data" action="downloadcsv.php" method="post">
            <fieldset><input name="csv" type="hidden" value="'.$csv.'" />
            <p style="text-align: center;"><input type="submit" value="Télécharger au format CSV"/></p></fieldset>
          </form>';

//Tableau des notes reçues
echo "<h2>Tableau des notes reçues</h2>";

//Liste des auteurs
$sql = "SELECT DISTINCT author.id, author.firstname, author.lastname, copie.grade FROM mdl_user author, mdl_workshop_submissions copie "
        . "WHERE author.id = copie.authorid AND copie.workshopid = $workshop->id";
$authors = $DB->get_recordset_sql($sql);

$csv = "Prenom auteur;Nom auteur;Note finale auteur;";
$csv .= "Prenom correcteur1;Nom correcteur1;Note correcteur1 pour cet auteur;";
$csv .= "Prenom correcteur2;Nom correcteur2;Note correcteur2 pour cet auteur;";
$csv .= "Prenom correcteur3;Nom correcteur3;Note correcteur3 pour cet auteur;";
$csv .= "Prenom correcteur4;Nom correcteur4;Note correcteur4 pour cet auteur;";
$csv .= "Prenom correcteur5;Nom correcteur5;Note correcteur5 pour cet auteur;";
$csv .= "Prenom correcteur6;Nom correcteur6;Note correcteur6 pour cet auteur;";
$csv .= "£µ£";

foreach ($authors as $author) {
    $csv .= "$author->firstname;$author->lastname;$author->grade;";
    
    $sql = "SELECT reviewer.firstname, reviewer.lastname, correction.grade "
            . "FROM mdl_user reviewer, mdl_workshop_submissions copie, mdl_workshop_assessments correction "
            . "WHERE copie.workshopid = $workshop->id AND correction.submissionid = copie.id AND correction.reviewerid = reviewer.id AND copie.authorid = $author->id";
    $authorreviews = $DB->get_recordset_sql($sql);
    
    foreach ($authorreviews as $authorreview) {
        $csv .= "$authorreview->firstname;$authorreview->lastname;$authorreview->grade;";
    }
    $csv .= "£µ£";
}



echo '<form enctype="multipart/form-data" action="downloadcsv.php" method="post">
            <fieldset><input name="csv" type="hidden" value="'.$csv.'" />
            <p style="text-align: center;"><input type="submit" value="Télécharger au format CSV"/></p></fieldset>
          </form>';



echo $output->footer();
