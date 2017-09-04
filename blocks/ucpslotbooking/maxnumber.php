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
 * File : maxnumber.php 
 * Called within AJAX process when a manager sets the maximum number of students for a given time slot.
 */

require_once('../../config.php');
require_once('locallib.php');

global $DB;

$dateid = required_param('dateid', PARAM_INT);
$timeid = required_param('timeid', PARAM_INT);
$listid = required_param('listid', PARAM_INT);
$maxnumber = required_param('maxnumber', PARAM_INT);

$slotid = $DB->get_field('block_ucpslotbooking_slot', 'id', array('dateid' => $dateid, 'timeid' => $timeid, 'listid' => $listid));

if ($slotid) {
    if ($maxnumber) {
        $DB->set_field('block_ucpslotbooking_slot', 'maxnumber', $maxnumber, array('id' => $slotid));
    } else {
        $DB->delete_records('block_ucpslotbooking_slot', array('id' => $slotid));
    }
} else if ($maxnumber) {
    $slot = new stdClass();
    $slot->dateid = $dateid;
    $slot->timeid = $timeid;
    $slot->listid = $listid;
    $slot->maxnumber = $maxnumber;
    $slot->id = $DB->insert_record('block_ucpslotbooking_slot', $slot);
}

$date = $DB->get_record('block_ucpslotbooking_date', array('id' => $dateid));
$time = $DB->get_record('block_ucpslotbooking_time', array('id' => $timeid));

echo displaydate($date->datetext).", $time->starttime - $time->endtime : $maxnumber";
