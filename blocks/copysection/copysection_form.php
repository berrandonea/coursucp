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
 * Used to copy sections titles and descriptions from one course to another
 *
 * @package    block_copysection
 * @author     Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : copysection_form.php
 * Form for main page
 */

require_once("{$CFG->libdir}/formslib.php");

class copysection_form extends moodleform {
    function definition() {
        global $COURSE, $DB, $USER;
        $mform =& $this->_form;
        $mform->addElement('header','addfileheader', "ATTENTION : les descriptions et titres actuels des sections de ce cours-ci seront écrasés et remplacés.");
        $options = array();
        $enrolments = $DB->get_records('user_enrolments', array('userid' => $USER->id));
        foreach($enrolments as $enrolment) {
            $courseid = $DB->get_field('enrol', 'courseid', array('id' => $enrolment->enrolid));
            if ($courseid != $COURSE->id) {
                $srccourse = $DB->get_record('course', array('id' => $courseid));
		if ($srccourse) {
	            $coursecontext = context_course::instance($courseid);
                    if (has_capability('moodle/course:update', $coursecontext)) {
                        $options[$courseid] = "($srccourse->shortname) $srccourse->fullname";
  		    }
                }
            }
        }
        $mform->addElement('select', 'from', "Cours d'origine", $options);
        $mform->addElement('hidden', 'to');
        $mform->setType('to', PARAM_INT);
        $this->add_action_buttons();
    }
}
