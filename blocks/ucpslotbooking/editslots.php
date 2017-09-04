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

$getadddate = optional_param('adddate', 0, PARAM_INT);
$getaddtime = optional_param('addtime', 0, PARAM_INT);
$geteditdate = optional_param('editdate', 0, PARAM_INT);
$getedittime = optional_param('edittime', 0, PARAM_INT);
$postdate = optional_param('date', '', PARAM_TEXT);
$poststarttime = optional_param('start', '', PARAM_TEXT);
$postendtime = optional_param('end', '', PARAM_TEXT);
$postid = optional_param('postid', 0, PARAM_INT);
$getdeldate = optional_param('deldate', 0, PARAM_INT);
$getdeltime = optional_param('deltime', 0, PARAM_INT);
$postusedslotid = optional_param('usedslotid', 0, PARAM_INT);
$postcomment = optional_param('comment', '', PARAM_TEXT);
$newtitle = optional_param('title', '', PARAM_TEXT);

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));

// Check access.
$course = get_course($courseid);
require_login($course);
$coursecontext = context_course::instance($courseid);
require_capability('block/ucpslotbooking:addinstance', $coursecontext);

// Get slots list.
$list = $DB->get_record('block_ucpslotbooking_list', array('id' => $listid));
if ($list->blockid != $blockid) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

// Header code.
$title = get_string('editslots', 'block_ucpslotbooking').' - '.$list->name;
$PAGE->set_url('/blocks/ucpslotbooking/editslots.php', array('courseid' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($title);

// Navigation node.
$settingsnode = $PAGE->settingsnav->add(get_string('editslots', 'block_ucpslotbooking'));
$params = array('listid' => $listid, 'courseid' => $courseid, 'blockid' => $blockid);
$editurl = new moodle_url('/blocks/ucpslotbooking/editslots.php', $params);
$editnode = $settingsnode->add(get_string('editslots', 'block_ucpslotbooking'), $editurl);
$editnode->make_active();

echo $OUTPUT->header();

// To change the list's name
if ($newtitle) {
    $DB->set_field('block_ucpslotbooking_list', 'name', $newtitle, array('id' => $list->id));
    $list->name = $newtitle;
}
echo "<form method='post' action='$editurl' enctype='multipart/form-data'>";
echo get_string('changetitle', 'block_ucpslotbooking').' : <input type="text" name="title" size="40" value="'.$list->name.'">&nbsp&nbsp';
echo "<input type='submit' value='OK'>";
echo"</form><br><br>";

// Adding buttons.
$backurl = "slots.php?list=$listid&courseid=$courseid&blockid=$blockid";
echo "<a href='$backurl'><button>".get_string('back', 'block_ucpslotbooking')."</button></a>&nbsp";
echo "<a href='$editurl&adddate=1'>";
echo "<button>";
echo get_string('adddate', 'block_ucpslotbooking');
echo "</button>";
echo "</a>";
echo "&nbsp";
echo "<a href='$editurl&addtime=1'>";
echo "<button>";
echo get_string('addtime', 'block_ucpslotbooking');
echo "</button>";
echo "</a>";
echo "<br><br>";

$cautionstyle = "style='font-weight:bold;color:red'";

if ($postusedslotid) {
    $DB->set_field('block_ucpslotbooking_slot', 'shortcomment', $postcomment, array('id' => $postusedslotid));
}

if ($getadddate == 1) {
    echo "<form method='post' action='$editurl' enctype='multipart/form-data'>";
    echo get_string('newdate', 'block_ucpslotbooking')." : <input type='text' name='date' size='15' required>&nbsp&nbsp";
    echo get_string('dateformat', 'block_ucpslotbooking');
    echo "<br><br>";
    echo "<input type='submit' value='OK'>";
    echo"</form><br><br>";
}

if ($getaddtime == 1) {
    echo "<form method='post' action='$editurl' enctype='multipart/form-data'>";
    echo get_string('newstarttime', 'block_ucpslotbooking')." : <input type='text' name='start' size='15' required>&nbsp&nbsp";
    echo get_string('timeformat', 'block_ucpslotbooking');
    echo "<br><br>";
    echo get_string('newendtime', 'block_ucpslotbooking')." : <input type='text' name='end' size='15' required>&nbsp&nbsp";
    echo get_string('timeformat', 'block_ucpslotbooking');
    echo "<br><br>";
    echo "<input type='submit' value='OK'>";
    echo"</form><br><br>";
}

if ($geteditdate) {
    $currentdate = $DB->get_record('block_ucpslotbooking_date', array('id' => $geteditdate));

    if ($currentdate) {
        echo "<form method='post' action='$editurl' enctype='multipart/form-data'>";
        echo get_string('newdate', 'block_ucpslotbooking').
                " : <input type='text' name='date' size='15' value='$currentdate->datetext' required>&nbsp&nbsp";
        echo get_string('dateformat', 'block_ucpslotbooking');
        echo "<br><br>";
        echo "<input type='hidden' name='postid' value='$geteditdate'>";
        echo "<input type='submit' value='OK'>";
        echo"</form><br><br>";
    }
}

if ($getedittime) {
    $currenttime = $DB->get_record('block_ucpslotbooking_time', array('id' => $getedittime));

    if ($currenttime) {
        echo "<form method='post' action='$editurl' enctype='multipart/form-data'>";
        echo get_string('newstarttime', 'block_ucpslotbooking')
                ." : <input type='text' name='start' size='15' value='$currenttime->starttime' required>&nbsp&nbsp";
        echo get_string('timeformat', 'block_ucpslotbooking');
        echo "<br><br>";
        echo get_string('newendtime', 'block_ucpslotbooking')
                ." : <input type='text' name='end' size='15' value='$currenttime->endtime' required>&nbsp&nbsp";
        echo get_string('timeformat', 'block_ucpslotbooking');
        echo "<br><br>";
        echo "<input type='hidden' name='postid' value='$getedittime'>";
        echo "<input type='submit' value='OK'>";
        echo"</form><br><br>";
    }
}

if ($postdate) {
    if ($postid) {
        $DB->set_field('block_ucpslotbooking_date', 'datetext', $postdate, array('id' => $postid, 'blockid' => $blockid));
    } else {
        $newdate = new stdClass();
        $newdate->datetext = $postdate;
        $newdate->blockid = $blockid;
        $DB->insert_record('block_ucpslotbooking_date', $newdate);
    }
}

if ($poststarttime && $postendtime) {
    if ($postid) {
        $DB->set_field('block_ucpslotbooking_time', 'starttime', $poststarttime, array('id' => $postid, 'blockid' => $blockid));
        $DB->set_field('block_ucpslotbooking_time', 'endtime', $postendtime, array('id' => $postid, 'blockid' => $blockid));
    } else {
        $newtime = new stdClass();
        $newtime->starttime = $poststarttime;
        $newtime->endtime = $postendtime;
        $newtime->blockid = $blockid;
        $DB->insert_record('block_ucpslotbooking_time', $newtime);
    }
}

if ($getdeldate) {
    $slots = $DB->get_records('block_ucpslotbooking_slot', array('dateid' => $getdeldate));
    foreach ($slots as $slot) {
        $DB->delete_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));
    }
    $DB->delete_records('block_ucpslotbooking_slot', array('dateid' => $getdeldate));
    $DB->delete_records('block_ucpslotbooking_date', array('id' => $getdeldate));

    echo get_string('datedeleted', 'block_ucpslotbooking')."<br><br>";
}

if ($getdeltime) {
    $slots = $DB->get_records('block_ucpslotbooking_slot', array('timeid' => $getdeltime));
    foreach ($slots as $slot) {
        $DB->delete_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));
    }
    $DB->delete_records('block_ucpslotbooking_slot', array('timeid' => $getdeltime));
    $DB->delete_records('block_ucpslotbooking_time', array('id' => $getdeltime));

    echo get_string('timedeleted', 'block_ucpslotbooking')."<br><br>";
}

echo "<h2>".get_string('slotscapacity', 'block_ucpslotbooking')."</h2>";

?>
<div style="overflow-x:auto;">
<table style='border:2px solid white;border_collapse:collapse'>
    <tr style='color:white'>
        <?php
        $firstlinestyle = "style = 'font-weight:bold;background-color:#731472'";
        echo "<td $firstlinestyle>DATE</td>";
        echo "<td $firstlinestyle></td>";

        $dates = $DB->get_records('block_ucpslotbooking_date', array('blockid' => $blockid), 'datetext');
        $dateids = array();
        $nbdates = 0;
        foreach ($dates as $date) {
            echo "<td $firstlinestyle>".displaydate($date->datetext)."</td>";
            $dateids[$nbdates] = $date->id;
            $nbdates++;
        }
        ?>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <?php
        foreach ($dateids as $dateid) {
            echo "<td>".
                    "<a href='$editurl&deldate=$dateid'>".
                    "<img alt='".get_string('delete', 'block_ucpslotbooking')."' "
                        . "title='".get_string('delete', 'block_ucpslotbooking')."' "
                        . "src='$CFG->wwwroot/theme/image.php/archaius/core/1452503826/t/delete'>".
                    "</a>".
                    "&nbsp".
                    "<a href='$editurl&editdate=$dateid'>".
                    "<img alt='".get_string('edit', 'block_ucpslotbooking')."' "
                        . "title='".get_string('edit', 'block_ucpslotbooking')."' "
                        . "src='$CFG->wwwroot/theme/image.php/archaius/core/1452503826/t/edit'>".
                    "</a>".
                 "</td>";
        }
        rewind($dateids);
        ?>
    </tr>
    <?php
    $times = $DB->get_records('block_ucpslotbooking_time', array('blockid' => $blockid), 'starttime, endtime');
    foreach ($times as $time) {
        echo "<tr>";
        echo "<td style='font-weight:bold'>$time->starttime - $time->endtime</td>";
        echo "<td>".
                "<a href='$editurl&deltime=$time->id'>".
                "<img alt='".get_string('delete', 'block_ucpslotbooking')."' "
                    . "title='".get_string('delete', 'block_ucpslotbooking')."' "
                    . "src='$CFG->wwwroot/theme/image.php/archaius/core/1452503826/t/delete'>".
                "</a>".
                "&nbsp".
                "<a href='$editurl&edittime=$time->id'>".
                "<img alt='".get_string('edit', 'block_ucpslotbooking')."' "
                    . "title='".get_string('edit', 'block_ucpslotbooking')."' "
                    . "src='$CFG->wwwroot/theme/image.php/archaius/core/1452503826/t/edit'>".
                "</a>".
                "</td>";
        foreach ($dateids as $dateid) {
            echo "<td>";
            $slot = $DB->get_record('block_ucpslotbooking_slot',
                    array('dateid' => $dateid, 'timeid' => $time->id, 'listid' => $listid));
            if ($slot) {
                if ($slot->shortcomment) {
                    echo "$slot->shortcomment<br><br>";
                }
            } else {
                $slot = new stdClass();
                $slot->id = 0;
                $slot->maxnumber = 0;
            }

            echo "<input type='text' id='slot".$time->id."d$dateid' size='5' "
                    . "value='$slot->maxnumber' onchange='updatecapa(this.value, $time->id, $dateid, $listid)'>";
            echo "</td>";
        }
        echo "</tr>";
    }
    ?>
</table>
</div>
<br>
<p id="confirmation"></p>

<?php

echo "<h2>".get_string('slotscomments', 'block_ucpslotbooking')."</h2>";
echo "<a href='$editurl' style='color:white'>"
        . "<button>".get_string('updatelist', 'block_ucpslotbooking')."</button>"
        . "</a><br><br>";

$sql = "SELECT d.datetext, t.starttime, t.endtime, s.* "
        . "FROM {block_ucpslotbooking_slot} s, {block_ucpslotbooking_date} d, {block_ucpslotbooking_time} t "
        . "WHERE s.listid = $listid AND d.id = s.dateid AND t.id = s.timeid "
        . "ORDER BY d.datetext, t.starttime, t.endtime";
$usedslots = $DB->get_recordset_sql($sql);

echo "<table>";
foreach ($usedslots as $usedslot) {
    echo "<form method='post' action='$editurl' enctype='multipart/form-data'><tr>";
    echo "<td>".displaydate($usedslot->datetext).", $usedslot->starttime - $usedslot->endtime ($usedslot->maxnumber) : </td>";

    echo "<td><input type='text' name='comment' size='75' value='$usedslot->shortcomment'></td>";

    echo "<input type='hidden' name='usedslotid' value='$usedslot->id'>";
    echo "<td><input type='submit'></td><td>&nbsp;&nbsp;</td>";
    echo "</tr></form>";
}
echo "</table>";
$usedslots->close();

echo $OUTPUT->footer();
?>


<script type="text/javascript">

var xhr = null;

function getXhr(){
    if(window.XMLHttpRequest) // Firefox, etc.
       xhr = new XMLHttpRequest();
    else if(window.ActiveXObject){ // Internet Explorer
       try {
                xhr = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
    }
    else {
       alert("<?php echo get_string('browsererror', 'block_ucpslotbooking'); ?>");
       xhr = false;
    }
}

/**
* Enregistre la capacité d'un créneau
*/
function updatecapa(maxnumber, timeid, dateid, listid){
    getXhr();
    xhr.onreadystatechange = function(){
        if(xhr.readyState == 4 && xhr.status == 200){
            leselect = xhr.responseText;
            document.getElementById('confirmation').innerHTML = leselect;
        }
    }

    xhr.open("POST","maxnumber.php",true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.send("maxnumber=" + maxnumber + "&timeid=" + timeid + "&dateid=" + dateid + "&listid=" + listid);
}

</script>
