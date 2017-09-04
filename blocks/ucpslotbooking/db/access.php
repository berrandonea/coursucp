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
 * File : db/access.php 
 * Rights for the block
 * 
 */

$capabilities = array(
    'block/ucpslotbooking:myaddinstance' => array('captype' => 'write', 'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array('user' => CAP_ALLOW), 'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ),

    'block/ucpslotbooking:addinstance' => array('riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write', 'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array('editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW),
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),

    'block/ucpslotbooking:addlist' => array('captype' => 'write', 'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array('manager' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW),
    ),
);
