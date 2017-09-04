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
 * File : bookings.php 
 * Lists the slots booked by students.
 */

require_once('../../config.php');
require_once('locallib.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check params.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$listid = required_param('listid', PARAM_INT);
$getremove = optional_param('remove', 0, PARAM_INT);
$empty = optional_param('empty', 0, PARAM_INT);
$emptyall = optional_param('all', 0, PARAM_INT);
$postcsv = optional_param('csv', '', PARAM_TEXT);

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
$coursecontext = context_course::instance($courseid);

// Check access.
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}
require_login($course);
require_capability('block/ucpslotbooking:addinstance', $coursecontext);

// Get slots list.
$list = $DB->get_record('block_ucpslotbooking_list', array('id' => $listid));
if ($list->blockid != $blockid) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

// Header code.
$title = get_string('bookings', 'block_ucpslotbooking').' - '.$list->name;
$PAGE->set_url('/blocks/ucpslotbooking/bookings.php', array('courseid' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($title);

// Navigation node.
$settingsnode = $PAGE->settingsnav->add($list->name);
$params = array('listid' => $listid, 'courseid' => $courseid, 'blockid' => $blockid);
$editurl = new moodle_url('/blocks/ucpslotbooking/bookings.php', $params);
$editnode = $settingsnode->add($list->name, $editurl);
$editnode->make_active();

$site = get_site();

// EXPORT CSV.
if ($postcsv) {
    $tabcsv = explode("£µ£", $postcsv);
    $filename = "csv/bookings_".time().".csv";
    $exportedfile = fopen($filename, 'w');

    foreach ($tabcsv as $csvline) {
        $tabcsvline = explode(";", $csvline);
        fputcsv($exportedfile, $tabcsvline, ";");
    }

    fclose($exportedfile);
    header('Location: '.$filename);
}

if ($empty) {
    $DB->delete_records('block_ucpslotbooking_booking', array('slotid' => $empty));
}

if ($getremove) {
    $DB->delete_records('block_ucpslotbooking_booking', array('id' => $getremove));
}

// Page display.
echo $OUTPUT->header();

if ($emptyall == 205) {
    echo get_string('confirmempty', 'block_ucpslotbooking');
    echo "&nbsp;&nbsp;";
    echo "<a href='$editurl&all=457'><button>OK</button></a>&nbsp";
    echo "<a href='$editurl'>".get_string('cancel', 'block_ucpslotbooking')."</a><br><br>";
}

if ($emptyall == 457) {
    $slots = $DB->get_records('block_ucpslotbooking_slot', array('listid' => $listid));
    foreach ($slots as $slot) {
        $DB->delete_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));
    }
}

$backurl = "slots.php?list=$listid&courseid=$courseid&blockid=$blockid";
echo "<a href='$backurl'><button>".get_string('back', 'block_ucpslotbooking')."</button></a>&nbsp";
echo "<a href='$editurl&all=205'><button>".get_string('emptyall', 'block_ucpslotbooking')."</button></a><br><br>";

$sql = "SELECT d.datetext, t.starttime, t.endtime, s.* "
        . "FROM {block_ucpslotbooking_slot} s, {block_ucpslotbooking_date} d, {block_ucpslotbooking_time} t "
        . "WHERE s.listid = $listid AND d.id = s.dateid AND t.id = s.timeid "
        . "ORDER BY d.datetext, t.starttime, t.endtime";
$slots = $DB->get_recordset_sql($sql);

$csvlist = '';

foreach ($slots as $slot) {
    $csvslot = '';

    echo "<h3>";
    if ($slot->shortcomment) {
        echo "$slot->shortcomment, ";
        $csvslot .= "$slot->shortcomment, ";
    }
    $date = displaydate($slot->datetext).", ";
    echo $date;
    $csvslot .= $date;
    echo get_string('from', 'block_ucpslotbooking')." $slot->starttime ";
    $csvslot .= get_string('from', 'block_ucpslotbooking')." $slot->starttime ";
    echo get_string('to', 'block_ucpslotbooking')." $slot->endtime.";
    $csvslot .= get_string('to', 'block_ucpslotbooking')." $slot->endtime.";
    echo "&nbsp;<a href='$editurl&empty=$slot->id' style='font-size:14'>".get_string('empty', 'block_ucpslotbooking')."</a>";
    echo "</h3>";
    $csvslot .= "£µ£";
    ?>
    <div style="overflow-x:auto;">
    <table style='border:2px solid white;border_collapse:collapse'>
        <tr style='color:white'>
            <?php
            $firstlinestyle = "style = 'font-weight:bold;background-color:#731472'";
            echo "<td $firstlinestyle>".get_string('name', 'block_ucpslotbooking')."</td>";
            $csvslot .= get_string('name', 'block_ucpslotbooking').";";
            echo "<td $firstlinestyle>".get_string('firstname', 'block_ucpslotbooking')."</td>";
            $csvslot .= get_string('firstname', 'block_ucpslotbooking').";";
            echo "<td $firstlinestyle>".get_string('login', 'block_ucpslotbooking')."</td>";
            $csvslot .= get_string('login', 'block_ucpslotbooking').";";
            echo "<td $firstlinestyle>".get_string('email', 'block_ucpslotbooking')."</td>";
            $csvslot .= get_string('email', 'block_ucpslotbooking')."£µ£";
            echo "<td $firstlinestyle>".get_string('unenrol', 'block_ucpslotbooking')."</td>";
            ?>
        </tr>
        <?php
        $bookings = $DB->get_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));
        foreach ($bookings as $booking) {
            $student = $DB->get_record('user', array('id' => $booking->userid));
            echo "<tr>";
            echo "<td>$student->lastname</td>";
            $csvslot .= "$student->lastname;";
            echo "<td>$student->firstname</td>";
            $csvslot .= "$student->firstname;";
            echo "<td>$student->username</td>";
            $csvslot .= "$student->username;";
            echo "<td>$student->email</td>";
            $csvslot .= "$student->email;£µ£";
            echo "<td><a href='$editurl&remove=$booking->id'>".get_string('unenrol', 'block_ucpslotbooking')."</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
    </div>
    <?php
    echo '<form enctype="multipart/form-data" action="'.$editurl.'" method="post">'
            . '<input name="csv" type="hidden" value="'.$csvslot.'" />'
            . '<p style="text-align: right;"><input type="submit" value="'.get_string('csvslot', 'block_ucpslotbooking').'"/></p>'
            . '</form>'
            . '<br><br>';

    $csvlist .= "$csvslot;£µ££µ£";
}
$slots->close();

echo '<form enctype="multipart/form-data" action="'.$editurl.'" method="post">'
        . '<input name="csv" type="hidden" value="'.$csvlist.'" />'
        . '<p style="text-align: center;"><input type="submit" value="'.get_string('csvlist', 'block_ucpslotbooking').'"/></p>'
        . '</form>';

?>

<?php
echo $OUTPUT->footer();
