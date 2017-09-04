<?php

/**
 * Call when module page is closed
 * Author:
 * 	Adrien Jamot  (adrien_jamot [at] symetrix [dt] fr)
 * 
 * @package   mod_richmedia
 * @copyright 2011 Symetrix
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

require_once("../../config.php");

$richmediaid = required_param('richmediaid', PARAM_INT);
$time = optional_param('time', 0, PARAM_INT);

require_login();

if ($track = $DB->get_record('richmedia_track', array('userid' => $USER->id, 'richmediaid' => $richmediaid))) {
    $track->last = time();
    $track->time = $time;
    $DB->update_record('richmedia_track', $track);
    echo 1;
}
else {
    $track = new stdClass();
    $track->userid = $USER->id;
    $track->richmediaid  = $richmediaid;
    $track->attempt = 1;
    $track->start = time();
    $track->last = $track->start;
    $track->time = $time;
    $DB->insert_record('richmedia_track', $track);
    echo 1;
}
