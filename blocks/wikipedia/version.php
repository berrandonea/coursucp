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
 * Version details
 *
 * @package    block
 * @subpackage wikipedia
 * @copyright  2014 Ralf Krause - http://www.moodletreff.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2014042200;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2010112400;        // Requires Moodle 2.0
$plugin->component = 'block_wikipedia'; // Full name of the plugin (used for diagnostics)
$plugin->release   = '1.3.4';           // 1.3 is the first version for Moodle 2.x
$plugin->maturity  = MATURITY_STABLE;   // yes, it should be stable :-)
$plugin->cron      = 0;
