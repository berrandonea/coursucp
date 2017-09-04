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
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * Used to copy sections titles and descriptions from one course to another
 *
 * @package    block_copysection
 * @author     Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : view.php
 * Main page
 */

require_once('../../config.php');
require_once('copysection_form.php');
global $DB, $OUTPUT, $PAGE, $USER;

// Check params.
$toid = required_param('to', PARAM_INT);
$fromid = optional_param('from', '', PARAM_INT);

$course = get_course($toid);
require_login($course);
$coursecontext = context_course::instance($toid);
require_capability('moodle/course:update', $coursecontext);
$courseurl = new moodle_url('/course/view.php', array('id' => $toid));

// Header code.
$args = array('to' => $toid, 'from' => $fromid);
$moodlefilename = '/blocks/copysection/view.php';
$PAGE->set_url($moodlefilename, $args);
$title = "Copie de sections vers ($course->shortname) $course->fullname";
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);

echo $OUTPUT->header();

// Form instanciation
$mform = new copysection_form();
$formdata['to'] = $toid;
$mform->set_data($formdata);

// Three possible states
if($mform->is_cancelled()) { // First scenario : the form has been canceled
    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) { // Second scenario : the form was validated
    //Proceed to copy if permitted
    $srccontext = context_course::instance($submitteddata->from);
    $srccourse = $DB->get_record('course', array('id' => $submitteddata->from));
    $srctitle = "<span style='font-weight:bold'>($srccourse->shortname) $srccourse->fullname</span>";
    if (has_capability('moodle/course:update', $srccontext)) {
        echo "Copie des titres et descriptions des sections de $srctitle"
            . " vers <span style='font-weight:bold'>($course->shortname) $course->fullname</span>.<br>";
        $srcsections = $DB->get_records('course_sections', array('course' => $srccourse->id));
        foreach ($srcsections as $srcsection) {
            $targetsection = $DB->get_record('course_sections', array('course' => $COURSE->id, 'section' => $srcsection->section));
            if ($targetsection) {
                if ($srcsection->name) {
                    $targetsection->name = $srcsection->name;
                }
                if ($srcsection->summary) {
                    $targetsection->summary = $srcsection->summary;
                    $targetsection->summaryformat = $srcsection->summaryformat;
                }
                $DB->set_debug(true);
                $DB->update_record('course_sections', $targetsection);
                $DB->set_debug(false);
            } else {
                $targetsection = new stdClass();
                $targetsection->course = $COURSE->id;
                $targetsection->section = $srcsection->section;
                $targetsection->name = $srcsection->name;
                $targetsection->summary = $srcsection->summary;
                $targetsection->summaryformat = $srcsection->summaryformat;
                $targetsection->visible = 1;
                $DB->insert_record('course_sections', $targetsection);
            }            
        }
        echo "<p style='color:green'>Copie terminée</p>";
        echo "<p><a href=$courseurl><button>Retour au cours</button></a></p>";
    } else {
        echo "ERREUR : vous n'êtes pas enseignant dans le cours $srctitle";
    }   
} else { // Third scenario : first form display
    echo '<h3>Depuis quel autre cours voulez-vous copier les titres et descriptions des sections ?</h3>';
    $mform->display();
}

// Footer.
echo $OUTPUT->footer();
