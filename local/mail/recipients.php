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
 * @package    local-mail
 * @copyright  Albert Gasset <albert.gasset@gmail.com>
 * @copyright  Marc Català <reskit@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');
require_once('recipients_selector.php');

$messageid = required_param('m', PARAM_INT);

// Fetch message

$message = local_mail_message::fetch($messageid);
if (!$message or !$message->editable($USER->id)) {
    print_error('invalidmessage', 'local_mail');
}

// Set up page

$params = array('m' => $messageid);
$url = new moodle_url('/local/mail/recipients.php', $params);
$activeurl = new moodle_url('/local/mail/compose.php', $params);
local_mail_setup_page($message->course(), $url);
navigation_node::override_active_url($activeurl);

// Check group

$groupid = groups_get_course_group($COURSE, true);

if (!$groupid and $COURSE->groupmode == SEPARATEGROUPS and
    !has_capability('moodle/site:accessallgroups', $PAGE->context)) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('notingroup', 'local_mail'));
    echo $OUTPUT->continue_button($activeurl);
    echo $OUTPUT->footer();
    exit;
}

// Set up selector

if ($message->course()->id != 1) {

    $options = array('courseid' => $COURSE->id, 'groupid' => $groupid);
    $participants = new mail_recipients_selector('recipients', $options);
    $participants->exclude(array_keys($message->recipients()));
    $participants->exclude(array($message->sender()->id));
} else {

	// HACK FOR COURSUCP 2016-2017
    // This part of the code retrieves the list of possible recipients when we are in 'allcourses'
	// This is for a page that is normally never displayed by the user but better safe than sorry.
    // HACK BEGINNING

    $options = array('courseid' => $COURSE->id, 'groupid' => $groupid);
    $participants = new mail_recipients_selector('recipients', $options);

    // Récupérer tous les contextes où l'utilisateur à un rôle (sauf 1)

    print_object($USER->id);

    $sql1 = "SELECT distinct contextid FROM {role_assignments} WHERE userid = ? AND contextid != 1";
    $result1 = $DB->get_fieldset_sql($sql1, array($USER->id));

    $result1list = implode(', ', $result1);

    print_object($result1);

    // Récupérer tous les utilisateurs qui ont un rôle dans au moins un de ces contextes

    $sql2 = "SELECT distinct userid FROM {role_assignments} WHERE contextid IN ($result1list)";
    $result2 = $DB->get_fieldset_sql($sql2, array());
    $result2list = implode(', ', $result2);

    print_object($result2);

    // Exclure tous les utilisateurs qui ne sont pas dans cette liste.

    $sql3 = "SELECT id FROM {user} WHERE id NOT IN ($result2list) AND id != 1 AND id != 2";
    $result3 = $DB->get_fieldset_sql($sql3, array());

    print_object($result3);

//    $sql = "SELECT id FROM {user} WHERE id NOT IN ("
//            . "SELECT distinct userid FROM {role_assignments} WHERE contextid IN ("
//            . "SELECT distinct contextid FROM {role_assignments} WHERE userid = ? AND contextid != 1))"
//            . " AND id != 1 AND id !=2";
//    $result = $DB->get_fieldset_sql($sql3, array($USER->id));

    if (isset($result3)) {
        $participants->exclude($result3);
    }

    $participants->exclude(array_keys($message->recipients()));
    $participants->exclude(array($message->sender()->id));
}

	// HACK END

// Process data

if ($data = data_submitted()) {
    require_sesskey();

    // Cancel
    if (!empty($data->cancel)) {
        $url = new moodle_url('/local/mail/compose.php', array('m' => $messageid));
        redirect($url);
    }

    // Add
    $userids = array_keys($participants->get_selected_users());
    if (!empty($data->addto)) {
        foreach ($userids as $userid) {
            $message->add_recipient('to', $userid);
        }
    } else if (!empty($data->addcc)) {
        foreach ($userids as $userid) {
            $message->add_recipient('cc', $userid);
        }
    } else if (!empty($data->addbcc)) {
        foreach ($userids as $userid) {
            $message->add_recipient('bcc', $userid);
        }
    }

    $url = new moodle_url('/local/mail/compose.php', array('m' => $messageid));
    redirect($url);
}

// Display page

echo $OUTPUT->header();

echo $OUTPUT->container_start('mail-recipients');

echo $OUTPUT->heading(get_string('addrecipients', 'local_mail'), 2);

groups_print_course_menu($COURSE, $PAGE->url);

echo html_writer::start_tag('form', array('method' => 'post', 'action' => $url));

$participants->display();

echo $OUTPUT->container_start('buttons');

$label = get_string('addto', 'local_mail');
$attributes = array('type' => 'submit', 'name' => 'addto', 'value' => $label);
echo html_writer::empty_tag('input', $attributes);

$label = get_string('addcc', 'local_mail');
$attributes = array('type' => 'submit', 'name' => 'addcc', 'value' => $label);
echo html_writer::empty_tag('input', $attributes);

$label = get_string('addbcc', 'local_mail');
$attributes = array('type' => 'submit', 'name' => 'addbcc', 'value' => $label);
echo html_writer::empty_tag('input', $attributes);

$label = get_string('cancel');
$attributes = array('type' => 'submit', 'name' => 'cancel', 'value' => $label);
echo html_writer::empty_tag('input', $attributes);

echo html_writer::empty_tag('input', array(
    'type' => 'hidden',
    'name' => 'sesskey',
    'value' => sesskey(),
));

echo $OUTPUT->container_end();

echo html_writer::end_tag('form');

echo $OUTPUT->container_end();

echo $OUTPUT->footer();
