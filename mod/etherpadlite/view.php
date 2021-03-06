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
 * This page prints a particular instance of etherpadlite
 *
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
//Forcer http (juste pour cette page)
if (isset($_SERVER['HTTPS'])) {
    if($_SERVER['HTTPS'] == 'on') {
        $id = $_GET['id'];
        header("Location: http://cours.u-cergy.fr/mod/etherpadlite/view.php?id=$id");
    }
}*/

//require_once(dirname(dirname(dirname(__FILE__))).'/config2.php');
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // etherpadlite instance ID

if ($id) {
    $cm           = get_coursemodule_from_id('etherpadlite', $id, 0, false, MUST_EXIST);
    $course       = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $etherpadlite = $DB->get_record('etherpadlite', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($a) {
    // Doesn't it have to be array('id' => $a)?
    $etherpadlite = $DB->get_record('etherpadlite', array('id' => $n), '*', MUST_EXIST);
    $course       = $DB->get_record('course', array('id' => $etherpadlite->course), '*', MUST_EXIST);
    $cm           = get_coursemodule_from_instance('etherpadlite', $etherpadlite->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

// This must be here, so that require login doesn't throw a warning
$PAGE->set_url('/mod/etherpadlite/view.php', array('id' => $cm->id));

require_login($course, true, $cm);
$config = get_config("etherpadlite");

if($config->ssl) {
	// https_required doesn't work, if $CFG->loginhttps doesn't work
	$CFG->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
	if (!isset($_SERVER['HTTPS'])) {
		$url = $CFG->httpswwwroot.'/mod/etherpadlite/view.php?id='.$id;

		redirect($url);
    }
}
// [START] Initialise the session for the Author
// php.ini separator.output auf '&' setzen
$separator = ini_get('arg_separator.output');
ini_set('arg_separator.output', '&');

// set vars
$domain = $config->url;
$padId = $etherpadlite->uri;
$fullurl = "domain.tld";

// make a new intance from the etherpadlite client
$instance = new EtherpadLiteClient($config->apikey,$domain.'api');

// fullurl generation
if(isguestuser() && !etherpadlite_guestsallowed($etherpadlite)) {
	try {
		$readOnlyID = $instance->getReadOnlyID($padId);
		$readOnlyID = $readOnlyID->readOnlyID;
		$fullurl = $domain.'ro/'.$readOnlyID;
	}
	catch (Exception $e) {
		//echo "\n\ngetReadOnlyID failed with Message: ".$e->getMessage();
		throw $e;
	}
}
else {
	$fullurl = $domain.'p/'.$padId;
}

// get the groupID
$groupID = explode('$', $padId);
$groupID = $groupID[0];

// create author if not exists for logged in user (with full name as it is obtained from Moodle core library)
try {
    if(isguestuser() && etherpadlite_guestsallowed($etherpadlite)) {
        $author = $instance->createAuthor('Guest-'.etherpadlite_genRandomString());
    }
    else {
        $author = $instance->createAuthorIfNotExistsFor($USER->id, fullname($USER));
    }
    $authorID = $author->authorID;
    //echo "The AuthorID is now $authorID\n\n";
} catch (Exception $e) {
    // the pad already exists or something else went wrong
    //echo "\n\ncreateAuthor Failed with message:  ". $e->getMessage();
    throw $e;
}

//$validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // +1 day in the future
$validUntil = time() + $config->cookietime;
try{
    $sessionID = $instance->createSession($groupID, $authorID, $validUntil);
}
catch (Exception $e) {
    //echo "\n\ncreateSession failed with message: ".$e->getMessage();
    throw $e;
}
$sessionID = $sessionID->sessionID;

// if we reach the etherpadlite server over https, then the cookie should only be delivered over ssl
$ssl = (stripos($config->url, 'https://')===0)?true:false;

setcookie("sessionID",$sessionID,$validUntil,'/',$config->cookiedomain, $ssl); // Set a cookie

// seperator.output wieder zur�cksetzen
ini_set('arg_separator.output', $separator);

// [END] Texte partagé init

$context = context_module::instance($cm->id);

/// Print the page header
$PAGE->set_title(get_string('modulename', 'mod_etherpadlite').': '.format_string($etherpadlite->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

//print_object($context);

echo $OUTPUT->header();

/// Print the main part of the page

echo $OUTPUT->heading($etherpadlite->name);

$summary = format_module_intro('etherpadlite', $etherpadlite, $cm->id);

echo $summary;

if(isguestuser() && !etherpadlite_guestsallowed($etherpadlite)) {
	$summary.= "<br/><br/>".get_string('summaryguest','etherpadlite');
}
if(!empty($summary)) {
	echo $OUTPUT->box($summary, 'generalbox mod_introbox');
}
echo '<iframe id="etherpadiframe" src ="'.$fullurl.'" width="100%", height="500px"></iframe>';
echo '<script type="text/javascript">
YUI().use(\'resize\', function(Y) {
    var resize = new Y.Resize({
        //Selector of the node to resize
        node: \'#etherpadiframe\',
        handles: \'br\'
    });
    resize.plug(Y.Plugin.ResizeConstrained, {
        minWidth: 380,
        minHeight: 140,
        maxWidth: 1080,
        maxHeight: 1080
    });

});
</script>
';

/// Finish the page
echo $OUTPUT->footer();
