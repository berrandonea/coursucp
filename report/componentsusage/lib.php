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
 * Shows how much each Moodle component (mod, report or gradereport) is used on your site, month after month.
 *
 * @package   report_componentsusage
 * @copyright 2016 Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : lib.php
 * Functions library.
 */

defined('MOODLE_INTERNAL') || die;

function report_componentsusage_html($months, $components, $action) {
    global $DB, $OUTPUT;
    echo $OUTPUT->header();
    echo '<h3>'.get_string('nbviews', 'report_componentsusage').'</h3>';
    echo get_string('explainnumbers', 'report_componentsusage');
    echo '<table>';
    echo '<tr>';
    echo '<th></th>';
    foreach ($months as $month) {
        echo '<th>'.$month->month.'</th>';
    }
    echo '</tr>';
    foreach ($components as $component) {
        reset($months);
        echo '<tr>';
        echo '<td>'.get_string('pluginname', $component->component).'</td>';
        foreach ($months as $month) {
            $stats = $DB->get_record('componentsusage', array('action' => $action, 'component' => $component->component, 'month' => $month->month));
//            $cell = '<table>';
//            $cell .= '<tr><td>'.get_string('teachers').'</td><td>'.$nblogs[$component][$month]->coursecreator.'</td></tr>';
//            $cell .= '<tr><td>'.get_string('students').'</td><td>'.$nblogs[$component][$month]->other.'</td></tr>';
//            $cell .= '</table>';
            $cell = $stats->total.' ('.$stats->coursecreator.')';
        //    $cell = $stats->total - $stats->coursecreator;
            echo '<td>'.$cell.'</td>';
        }
    }
    echo '</table>';
    echo $OUTPUT->footer();
}
