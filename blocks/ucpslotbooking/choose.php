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
 * File : choose.php
 * Called within AJAX process when a student chooses a time slot.
 */

require_once('../../config.php');
require_once('locallib.php');

global $DB, $USER, $SITE;

$slotid = required_param('slotid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid));

// Has this user already booked this time slot ?
$already = $DB->record_exists('block_ucpslotbooking_booking', array('slotid' => $slotid, 'userid' => $USER->id));

// If he hasn't.
if (!$already) {
    // Can this slot be booked ?
    $maxnumber = $DB->get_field('block_ucpslotbooking_slot', 'maxnumber', array('id' => $slotid));
    $nbbooked = $DB->count_records('block_ucpslotbooking_booking', array('slotid' => $slotid));

    // If it can, book it.
    if ($maxnumber - $nbbooked > 0) {
        $booking = new stdClass();
        $booking->slotid = $slotid;
        $booking->userid = $USER->id;
        $booking->id = $DB->insert_record('block_ucpslotbooking_booking', $booking);

        $slot = $DB->get_record('block_ucpslotbooking_slot', array('id' => $slotid));
        $time = $DB->get_record('block_ucpslotbooking_time', array('id' => $slot->timeid));
        $date = $DB->get_record('block_ucpslotbooking_date', array('id' => $slot->dateid));
        $list = $DB->get_record('block_ucpslotbooking_list', array('id' => $slot->listid));

        $message = get_string('booked', 'block_ucpslotbooking')." $slot->shortcomment. "
                .get_string('On', 'block_ucpslotbooking')." ".displaydate($date->datetext).", "
                .get_string('from', 'block_ucpslotbooking')." ".$time->starttime." "
                .get_string('to', 'block_ucpslotbooking')." ".$time->endtime.".";
        echo $message;
	echo " <a href='$CFG->wwwroot/blocks/ucpslotbooking/slots.php?list=$list->id&courseid=$courseid&blockid=$list->blockid&cancel=$booking->id'>Annuler</a>";

        $message = "
            <html>
                <head>
                   <title>$course->fullname&nbsp;-s&nbsp;$list->name</title>
                </head>
                <body>
                    $list->msgbefore<br><br>".
                    "Bonjour $USER->firstname $USER->lastname,<br><br>".
                    get_string('booked', 'block_ucpslotbooking')." $list->name. ".
                    get_string('On', 'block_ucpslotbooking')." ".displaydate($date->datetext).", ".
                    get_string('from', 'block_ucpslotbooking')." ".$time->starttime." ".
                    get_string('to', 'block_ucpslotbooking')." ".$time->endtime.".<br><br>".
                    $list->msgafter.
               "</body>
            </html>";

        // Send mail to user.
        $to = $DB->get_field('user', 'email', array('id' => $USER->id));
        $subject = $SITE->fullname.get_string('ucpslotbooking', 'block_ucpslotbooking');
        $message = "<html>"
                . "<head>"
                . "<title>"
                .$SITE->fullname.get_string('ucpslotbooking', 'block_ucpslotbooking')
                ."</title>"
                . "</head>"
                . "<body>$message</body>"
                . "</html>";

        $from = "noreply@positionnement.u-cergy.fr";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Reply-To: '.$from.'\n';
        $headers .= 'From: "'.$SITE->fullname.'"<'.$from.'>'."\n";
        $headers .= 'Delivered-to: '.$to."\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

        if (mail($to, $subject, $message, $headers)) {
            echo "<fieldset style='padding : 10px; width: 98%;font-weight : bold; background-color:green; color:white;''>"
            .get_string('mailsent', 'block_ucpslotbooking')." $to.</fieldset>";
        } else {
            echo "<fieldset style='padding : 10px; width: 98%;font-weight : bold; background-color:red; color:white;''>"
            .get_string('from', 'block_ucpslotbooking')." ($to) </fieldset>";
        }
    }
}
