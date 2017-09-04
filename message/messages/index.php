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
 * Preferences.
 *
 * @package    core_user
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/navigationlib.php');

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception();
}

$userid = optional_param('userid', $USER->id, PARAM_INT);
$currentuser = $userid == $USER->id;

// Only administrators can access another user's preferences.
if (!$currentuser && !is_siteadmin($USER)) {
    throw new moodle_exception('cannotedituserpreferences', 'error');
}

// Check that the user is a valid user.
$user = core_user::get_user($userid);
if (!$user || !core_user::is_real_user($userid)) {
    throw new moodle_exception('invaliduser', 'error');
}

$PAGE->set_context(context_user::instance($userid));
$PAGE->set_url('/user/preferences.php', array('userid' => $userid));
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('user-preferences');
$PAGE->set_title('Mails internes');
$PAGE->set_heading(fullname($user));

/* if (!$currentuser) {
    $PAGE->navigation->extend_for_user($user);
    $settings = $PAGE->settingsnav->find('userviewingsettings' . $user->id, null);
    $settings->make_active();
    $url = new moodle_url('/user/preferences.php', array('userid' => $userid));
    $navbar = $PAGE->navbar->add(get_string('preferences', 'moodle'), $url);
} else {
    // Shutdown the users node in the navigation menu.
    $usernode = $PAGE->navigation->find('users', null);
    $usernode->make_inactive();

    $settings = $PAGE->settingsnav->find('usercurrentsettings', null);
    $settings->make_active();
} */
/* $previewnode = $PAGE->navigation->add(get_string('sitepages'), navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add('Demandes d\'inscription', new moodle_url('/blocks/enrol_demands/requests.php'));
$thingnode->make_active(); */

$settingnode = $PAGE->settingsnav->add('Tableau de bord', new moodle_url('/my/index.php'), navigation_node::TYPE_CONTAINER);
$thingnode = $settingnode->add('Messages personnels', new moodle_url('/a/link/if/you/want/one.php'));
$thingnode->make_active();

echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('preferences'));
//echo $OUTPUT->render($preferences);
echo "<table><tr><td><a href = '$CFG->wwwroot/local/mail/create.php'>Rédiger</a></td><td><a href = '$CFG->wwwroot/local/mail/view.php?t=inbox'>Réception</a></td><td><a href = '$CFG->wwwroot/local/mail/view.php?t=starred'>Marqué</a></td><td><a href ='$CFG->wwwroot/local/mail/view.php?t=drafts'>Brouillons</a></td><td><a href ='$CFG->wwwroot/local/mail/view.php?t=sent'>Envoyé</a></td></tr></table>";
echo $OUTPUT->footer();
