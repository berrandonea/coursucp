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
 * Block to book time slots
 *
 * @package    block_ucpslotbooking
 * @author     Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * File : addslotslist.php
 * Page to create a new list of time slots
 */

require_once('../../config.php');
require_once('addslotslist_form.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check params.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));

// Check access.
$course = get_course($courseid);
require_login($course);

$coursecontext = context_course::instance($courseid);
require_capability('block/ucpslotbooking:addinstance', $coursecontext);

// Header code.
$PAGE->set_url('/blocks/ucpslotbooking/addslotslist.php', array('courseid' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('newlist', 'block_ucpslotbooking'));

// Navigation node.
$settingsnode = $PAGE->settingsnav->add(get_string('newlist', 'block_ucpslotbooking'));
$params = array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid);
$editurl = new moodle_url('/blocks/ucpslotbooking/addslotslist.php', $params);
$editnode = $settingsnode->add(get_string('addlistheader', 'block_ucpslotbooking'), $editurl);
$editnode->make_active();

// Form instanciation.
$mform = new addfile_form();
$formdata['blockid'] = $blockid;
$formdata['courseid'] = $courseid;
$mform->set_data($formdata);

// Three possible states.
$extract = 0;
if ($mform->is_cancelled()) { // First scenario : the form has been canceled.
    if (!$id) {
        $id = 1;
    }
    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) { // Second scenario : the form was validated.
    $submitteddata->blockid = $blockid;
    if (!$submitteddata->justclick) {
        $submitteddata->justclick = ' ';
    }
    if (!$submitteddata->msgbefore) {
        $submitteddata->msgbefore = ' ';
    }
    if (!$submitteddata->msgafter) {
        $submitteddata->msgafter = ' ';
    }
    $listid = $DB->insert_record('block_ucpslotbooking_list', $submitteddata);

    $newurl = new moodle_url('/blocks/ucpslotbooking/editslots.php', array('listid' => $listid, 'courseid' => $courseid, 'blockid' => $blockid));
    redirect($newurl);
}

$site = get_site();
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
