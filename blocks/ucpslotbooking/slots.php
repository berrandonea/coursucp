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
 * File : slots.php
 * Page where the students book time slots
 */

require_once('../../config.php');
require_once('locallib.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

// Check params.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$listid = required_param('list', PARAM_INT);
$cancel = optional_param('cancel', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
$coursecontext = context_course::instance($courseid);

// Check access.
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}
require_login($course);

// Get slots list.
$list = $DB->get_record('block_ucpslotbooking_list', array('id' => $listid));
if ($list->blockid != $blockid) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}
$nbshown = $list->nbshown;

// Header code.
$PAGE->set_url('/blocks/ucpslotbooking/slots.php', array('id' => $courseid));
$PAGE->set_title($list->name);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($list->name);

// Navigation node.
$settingsnode = $PAGE->settingsnav->add($list->name);
$editurl = new moodle_url('/blocks/ucpslotbooking/slots.php', array('list' => $listid, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add($list->name, $editurl);
$editnode->make_active();

$site = get_site();

if ($cancel) {
    $DB->delete_records('block_ucpslotbooking_booking', array('id' => $cancel));
}

// Page display.
echo $OUTPUT->header();

if ($delete == 205) {
    echo get_string('confirmdelete', 'block_ucpslotbooking');
    echo "&nbsp;&nbsp;";
    echo "<a href='$editurl&delete=457'><button>OK</button></a>&nbsp";
    echo "<a href='$editurl'>".get_string('cancel', 'block_ucpslotbooking')."</a><br><br>";
}

if ($delete == 457) {
    $slots = $DB->get_records('block_ucpslotbooking_slot', array('listid' => $listid));
    foreach ($slots as $slot) {        
        $DB->delete_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));
        $DB->delete_records('block_ucpslotbooking_slot', array('id' => $slot->id));
    }
    $DB->delete_records('block_ucpslotbooking_list', array('id' => $listid));
    header("Location: $CFG->wwwroot/course/view.php?id=$courseid");
}

if (has_capability('block/ucpslotbooking:addinstance', $coursecontext)) {
    showadminbuttons($listid, $courseid, $blockid, $editurl);
}
showavailableslots($listid, $blockid, $editurl, $nbshown);
echo $OUTPUT->footer();

function showadminbuttons($listid, $courseid, $blockid, $editurl) {
    ?>
    <a href='editslots.php?<?php echo "listid=$listid&courseid=$courseid&blockid=$blockid"; ?>'>
        <button>
            <?php echo get_string('editslots', 'block_ucpslotbooking'); ?>
        </button>
    </a>
    &nbsp;&nbsp;
    <a href='<?php echo "$editurl&delete=205"; ?>'>
        <button>
            <?php echo get_string('dellist', 'block_ucpslotbooking'); ?>
        </button>
    </a>
    &nbsp;&nbsp;
    <a href='bookings.php?<?php echo "listid=$listid&courseid=$courseid&blockid=$blockid"; ?>'>
        <button>
            <?php echo get_string('watchbookings', 'block_ucpslotbooking'); ?>
        </button>
    </a>
    <br><br>
    <p></p>
    <?php
}

function showavailableslots($listid, $blockid, $editurl, $nbshown) {
    global $DB, $PAGE;

    $justclick = $DB->get_field('block_ucpslotbooking_list', 'justclick', array('id' => $listid));
    echo "<h2>$justclick</h2>";

    // Slots booked by this user.
    mybookedslots($editurl, $listid);

    // Table of bookable slots.
    ?>
    <div id='slotstable' style='overflow-x:auto;'>
        <table>
            <tr style='color:white'>
                <?php                
                $firstlinestyle = "style = 'font-weight:bold;background-color:#731472'";
                echo "<td $firstlinestyle></td>";

                $dates = $DB->get_records('block_ucpslotbooking_date', array('blockid' => $blockid), 'datetext');
                $dateids = array();
                $nbdates = 0;
                foreach ($dates as $date) {
                    $dateused = $DB->record_exists('block_ucpslotbooking_slot', array('dateid' => $date->id, 'listid' => $listid));
                    if ($dateused) {
                        echo "<td $firstlinestyle>".displaydate($date->datetext)."</td>";
                        $dateids[$nbdates] = $date->id;
                        $nbdates++;
                    }
                }
                ?>
            </tr>
            <?php
            $times = $DB->get_records('block_ucpslotbooking_time', array('blockid' => $blockid), 'starttime, endtime');
            foreach ($times as $time) {
                $timeused = $DB->record_exists('block_ucpslotbooking_slot', array('timeid' => $time->id, 'listid' => $listid));
                if ($timeused) {
                    displayrow($time, $dateids, $listid, $nbshown);
                }
            }
            ?>
        </table>
    </div>
    <p id="confirmation"></p>
    <?php
}

function mybookedslots($editurl, $listid) {
    global $DB, $USER;

    $sql = "SELECT s.*, b.id AS bookingid "
            . "FROM {block_ucpslotbooking_booking} b, {block_ucpslotbooking_slot} s "
            . "WHERE b.userid = $USER->id AND b.slotid = s.id AND s.listid = $listid";
    $myslots = $DB->get_recordset_sql($sql);

    echo "<ul>";
    foreach ($myslots as $myslot) {
        $myslot->datetext = $DB->get_field('block_ucpslotbooking_date', 'datetext', array('id' => $myslot->dateid));
        $myslot->from = $DB->get_field('block_ucpslotbooking_time', 'starttime', array('id' => $myslot->timeid));
        $myslot->to = $DB->get_field('block_ucpslotbooking_time', 'endtime', array('id' => $myslot->timeid));
        echo "<li>";
        echo "Le $myslot->datetext, de $myslot->from à $myslot->to : $myslot->shortcomment";
        echo "&nbsp;";
        echo "<a href='$editurl&cancel=$myslot->bookingid'>".get_string('cancel', 'block_ucpslotbooking')."</a>";
        echo "</li>";
    }
    echo "</ul>";
    $myslots->close();
}

function displayrow($time, $dateids, $listid, $nbshown) {
    global $DB;

    echo "<tr>";
    echo "<td style='font-weight:bold'>$time->starttime - $time->endtime</td>";

    foreach ($dateids as $dateid) {
        $slot = $DB->get_record('block_ucpslotbooking_slot',
                array('dateid' => $dateid, 'timeid' => $time->id, 'listid' => $listid));
        if ($slot) {
            displayslot($slot, $nbshown);
        } else {
            echo "<td></td>";
        }
    }
    echo "</tr>";
}

function displayslot($slot, $nbshown) {
    global $COURSE, $DB;
    $slot->occupied = $DB->count_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));
    $slot->free = $slot->maxnumber - $slot->occupied;

    if ($slot->free > 0) {
        echo "<td onclick='javascript:choose($slot->id, $COURSE->id)' style='cursor:pointer'>";
    } else {
        echo "<td style='color:#A0A0A0'>";
    }
    echo "<table><tr><td>$slot->shortcomment</td></tr>";
    if ($nbshown % 2 == 0) {
        echo "<tr><td>$slot->occupied ";
        if ($slot->occupied == 1) {
            echo get_string('nbooked1', 'block_ucpslotbooking');
        } else {
            echo get_string('nbooked', 'block_ucpslotbooking');
        }
        echo "</td></tr>";
    }

    if ($nbshown > 0) {
        echo "<tr><td>";
        if ($slot->free) {
            echo "$slot->free ";
            if ($slot->free == 1) {
                echo get_string('freeroom', 'block_ucpslotbooking');
            } else {
                echo get_string('freerooms', 'block_ucpslotbooking');
            }
        } else {
            echo get_string('full', 'block_ucpslotbooking');
        }
        echo "</td></tr>";
    }

    echo "</table>";
    echo "</td>";
}

?>
<script type="text/javascript">
var xhr = null;

function getXhr(){
    if(window.XMLHttpRequest)
       xhr = new XMLHttpRequest();
    else if(window.ActiveXObject){
       try {
                xhr = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
    }
    else {
       alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
       xhr = false;
    }
}


/**
* Inscription à un créneau
*/
function choose(slotid, courseid){
    getXhr();

    xhr.onreadystatechange = function() {
        if(xhr.readyState == 4 && xhr.status == 200) {
            response = xhr.responseText;
            document.getElementById('confirmation').innerHTML = response;
        }
    }

    xhr.open("POST","choose.php",true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.send("slotid=" + slotid + "&courseid=" + courseid);
}
</script>

