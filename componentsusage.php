<?php
define('CLI_SCRIPT', true);
require_once('config.php');

// Prepare data.
$thismonth = date('Y/m', time());
$prefixes = array('mod', 'report', 'gradereport');
$filteredlogs = array();
$months = array();
$components = array();
$action = 'viewed';

$viewlogs = $DB->get_recordset('logstore_standard_log', array('action' => $action));
foreach ($viewlogs as $viewlog) {
    $componentnameparts = explode('_', $viewlog->component);
    if (!in_array($componentnameparts[0], $prefixes)) {
        continue;
    }
    $filteredlog = new stdClass();
    $filteredlog->month = date('Y/m', $viewlog->timecreated);
    if ($filteredlog->month == $thismonth) {
        continue;
    }
    //$filteredlog->prefix = $componentnameparts[0];
    $filteredlog->name = $viewlog->component;
    if (!in_array($filteredlog->name, $components)) {
        $components[] = $filteredlog->name;
    }
    $filteredlog->coursecreator = $DB->record_exists('role_assignments', array('roleid' => 2, 'userid' => $viewlog->userid));

    if (!in_array($filteredlog->month, $months)) {
        $months[] = $filteredlog->month;
    }
    $filteredlogs[] = $filteredlog;
}
$viewlogs->close();

sort($months);
sort($components);

$nblogs = array();
foreach($components as $component) {
    $nblogs[$component] = array();
    foreach($months as $month) {
        $nblog = new stdClass();
        $nblog->coursecreator = 0;
        $nblog->other = 0;
        $nblog->total = 0;
        $nblogs[$component][$month] = $nblog;
    }
    reset($months);
}

foreach ($filteredlogs as $filteredlog) {
    $nblogs[$filteredlog->name][$filteredlog->month]->total++;
    if ($filteredlog->coursecreator) {
        $nblogs[$filteredlog->name][$filteredlog->month]->coursecreator++;
    } else {
        $nblogs[$filteredlog->name][$filteredlog->month]->other++;
    }
}

reset($components);
foreach ($components as $component) {
    foreach ($months as $month) {
        $already = $DB->record_exists('componentsusage', array('action' => $action, 'component' => $component, 'month' => $month));
        if (!$already) {
            $componentsusage = new stdClass();
            $componentsusage->action = $action;
            $componentsusage->component = $component;
            $componentsusage->month = $month;
            $componentsusage->coursecreator = $nblogs[$component][$month]->coursecreator;
            $componentsusage->total = $nblogs[$component][$month]->total;
            $componentsusage->id = $DB->insert_record('componentsusage', $componentsusage);
            print_object($componentsusage);
        }
    }
    reset($months);
}