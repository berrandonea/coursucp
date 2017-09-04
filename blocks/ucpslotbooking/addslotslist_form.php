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
 * File : addslotslist_form.php 
 * Form fot addslotslist.php
 */
require_once("{$CFG->libdir}/formslib.php");

class addfile_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'addfileheader', get_string('addlistheader', 'block_ucpslotbooking'));

        $mform->addElement('text', 'name', get_string('name', 'block_ucpslotbooking'));
        $mform->setDefault('listname', '');
        $mform->setType('listname', PARAM_TEXT);

        $this->add_action_buttons();
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);
    }
}



