<?php

require_once('../config.php');

$othercourseid = required_param('id', PARAM_INT);

// Check permission.
$coursecontext = context_course::instance($othercourseid);
require_capability('moodle/course:managegroups', $coursecontext);

$othercoursegroups = $DB->get_records('groups', array('courseid' => $othercourseid));

// New content to send back
echo "<label><strong>Groupe à copier : </strong></label>";
echo "<select name='groupeAcopier' id='groupeAcopier'>";
echo "<option value='0'>Choisissez un groupe</option>";
foreach ($othercoursegroups as $othercoursegroup) {
    echo "<option value='$othercoursegroup->id'>$othercoursegroup->name</option>";
}
echo "</select>";
echo "&nbsp;&nbsp;<input type = 'submit' value='Copier le groupe'>";
