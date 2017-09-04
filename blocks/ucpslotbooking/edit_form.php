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
 * File : edit_form.php
 * 
 * Configuration form for the block
 *  
 */

class block_ucpslotbooking_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        // Titre d'une section à ajouter à l'écran de configuration du bloc.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_changetitle', get_string('changetitle', 'block_ucpslotbooking'));
        $mform->setDefault('config_changetitle', $this->block->title);
        $mform->setType('config_changetitle', PARAM_TEXT);

        $mform->addElement('textarea', 'config_descr', get_string('description', 'block_ucpslotbooking'));
        $mform->setDefault('config_descr', "");
        $mform->setType('config_descr', PARAM_TEXT);
        
        $mform->addElement('editor', 'config_justclick', get_string('justclicksettinglabel', 'block_ucpslotbooking'));
        $mform->setDefault('config_justclick', get_string('justclick', 'block_ucpslotbooking'));
        $mform->setType('config_justclick', PARAM_RAW);
                
        $options = array(
            0 => get_string('nbbooked', 'block_ucpslotbooking'),
            1 => get_string('nbfree', 'block_ucpslotbooking'),
            2 => get_string('nbboth', 'block_ucpslotbooking')
        );        
        $mform->addElement('select', 'config_nbshown', get_string('nbshown', 'block_ucpslotbooking'), $options);
        $mform->setType('config_nbshown', PARAM_INT);
        
        $mform->addElement('editor', 'config_msgbefore', get_string('msgbefore', 'block_ucpslotbooking'));
        $mform->setDefault('config_msgbefore', '');
        $mform->setType('config_msgbefore', PARAM_RAW);
        
        $mform->addElement('editor', 'config_msgafter', get_string('msgafter', 'block_ucpslotbooking'));
        $mform->setDefault('config_msgafter', '');
        $mform->setType('config_msgafter', PARAM_RAW);
    }
}

