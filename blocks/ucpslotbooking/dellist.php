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
 * File : editslots.php 
 * Edit the bookable time slots in a given list
 */

require_once('../../config.php');
require_once('locallib.php');

global $DB, $OUTPUT, $PAGE, $USER, $CFG;

// Check params.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$listid = required_param('listid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));

// Check access.
$course = get_course($courseid);
require_login($course);
$coursecontext = context_course::instance($courseid);
require_capability('block/ucpslotbooking:addinstance', $coursecontext);

if ($confirm) {
    $listslots = $DB->get_records('block_ucpslotbooking_slot', array('listid' => $listid));
    foreach ($listslots as $listslot) {
        $DB->delete_records('block_ucpslotbooking_booking', array('slotid' => $listslot->id));
        $DB->delete_records('block_ucpslotbooking_slot', array('id' => $listslot->id));
    }
    $DB->delete_records('block_ucpslotbooking_list', array('id' => $listid));
    header("Location: $courseurl");
}

//// Get slots list.
//$list = $DB->get_record('block_ucpslotbooking_list', array('id' => $listid));
//if ($list->blockid != $blockid) {
//    print_error('invalidcourse', 'block_simplehtml', $courseid);
//}

// Header code.
$list = $DB->get_record('block_ucpslotbooking_list', array('id' => $listid));
$title = get_string('dellist', 'block_ucpslotbooking').' - '.$list->name;
$PAGE->set_url('/blocks/ucpslotbooking/dellist.php', array('courseid' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($title);

// Navigation node.
$settingsnode = $PAGE->settingsnav->add(get_string('dellist', 'block_ucpslotbooking'));
$params = array('listid' => $listid, 'courseid' => $courseid, 'blockid' => $blockid);
$editurl = new moodle_url('/blocks/ucpslotbooking/dellist.php', $params);
$editnode = $settingsnode->add(get_string('dellist', 'block_ucpslotbooking'), $editurl);
$editnode->make_active();

echo $OUTPUT->header();
echo get_string('confirmdelete', 'block_ucpslotbooking');
echo '<p> </p>';
$args = array('courseid' => $courseid, 'blockid' => $blockid, 'listid' => $listid, 'confirm' => 1);
$confirmurl = new moodle_url('/blocks/ucpslotbooking/dellist.php', $args);
echo "<a href='$confirmurl'><button>".get_string('delete', 'block_ucpslotbooking')."</button></a>";
echo "&nbsp;<a href='$courseurl'>".get_string('cancel', 'block_ucpslotbooking')."</a>";
echo $OUTPUT->footer();
?>
