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
 * File : index.php
 * Report page.
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');

$export = optional_param('export', 'html', PARAM_ALPHA);
$sitecourse = $DB->get_record('course', array('id' => 1), '*', MUST_EXIST);
require_login($sitecourse);

// Setup page.
$title = get_string('pluginname' , 'report_componentsusage');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_url('/report/componentsusage/index.php', array('export' => $export));
$PAGE->set_pagelayout('report');

// Check permissions.
$context = context_system::instance(0);
require_capability('report/componentsusage:view', $context);

// Prepare data.
$table = 'componentsusage';
$stats = $DB->get_recordset($table, array('action' => 'viewed'));

$months = $DB->get_records_sql("SELECT DISTINCT month FROM mdl_$table WHERE month > '2016/08' ORDER BY month ASC");
$components = $DB->get_records_sql("SELECT DISTINCT component FROM mdl_$table WHERE month > '2016/07' ORDER BY component ASC");

// Output.
switch ($export) {
    case 'csv':
        report_componentsusage_csv();
        break;

    case 'pdf':
        report_componentsusage_pdf();
        break;

    default:
        report_componentsusage_html($months, $components, 'viewed');
}
