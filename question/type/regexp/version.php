<?PHP
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
 * Version information for the Regexp question type.
 *
 * @package    qtype
 * @subpackage regexp
 * @copyright  2011 Joseph Rézeau moodle@rezeau.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'qtype_regexp';
$plugin->dependencies = array(
    'qbehaviour_regexpadaptivewithhelp'   => 2016071400,
    'qbehaviour_regexpadaptivewithhelpnopenalty'  => 2016071400,
);
$plugin->version  = 2016071400;
$plugin->requires = 2015051100; // Moodle version.
$plugin->release = '3.1.0 for Moodle 2.9-3.1';
$plugin->maturity  = MATURITY_STABLE;
