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
 * Main entry point for export
 *
 * @package   local_wikiexport
 * @copyright 2014 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once('wksport_form.php');

$action = required_param('action', PARAM_ALPHA);
$cmid = required_param('cmid', PARAM_INT);

$args = array('action' => $action, 'cmid' => $cmid);
$url = new moodle_url('/local/wikiexport/wksport.php', $args);
$PAGE->set_url($url);

$cm = get_coursemodule_from_id('wiki', $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cmid);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$wiki = $DB->get_record('wiki', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);
require_capability('mod/wiki:managewiki', $context);

$wikiurl = new moodle_url('/mod/wiki/view.php', array('id' => $cmid));

switch ($action) {
    case 'export':
        $content = wks_export($wiki);
        create_wks_file($content, $wiki->id);
        break;
    case 'import':
        wks_import($wiki, $cmid);
        break;
    default:
        header("Location: $wikiurl");
}

function create_wks_file($content, $wikiid) {
    $now = time();
    $outputfilename = "wiki$wikiid-$now.wks";
    file_put_contents("wks/$outputfilename", $content);
    header('Content-type:force-download');
    header("Content-Disposition: attachment; filename=wiki$wikiid-$now.wks");
    readfile("wks/$outputfilename");
}

function list_fields($record, $fields, $delimiter) {
    $string = "";
    foreach ($fields as $field) {
        $string .= $field.':'.$record->$field.$delimiter;
    }
    return $string;
}

function wks_export($wiki) {
    global $DB;
    $mainseparator = '£µ£1';
    $secondseparator = '£µ£2';
    $content = list_fields($wiki, array('name', 'intro', 'introformat', 'firstpagetitle', 'wikimode', 'defaultformat', 'forceformat'), $mainseparator);
    $subwikis = $DB->get_records('wiki_subwikis', array('wikiid' => $wiki->id));
    foreach($subwikis as $subwiki) {
        $pages = $DB->get_records('wiki_pages', array('subwikiid' => $subwiki->id));
        foreach ($pages as $page) {
            $content .= 'page:'.list_fields($page, array('id', 'title', 'cachedcontent', 'readonly'), $secondseparator);
            $sql = "SELECT contentformat, MAX(version) FROM {wiki_versions} WHERE pageid = $page->id";
            $lastversion = $DB->get_record_sql($sql);
            if ($lastversion) {
                $format = $lastversion->contentformat;
            } else {
                $format = 'html';
            }
            $content .= "format:$format".$secondseparator;
            $content .= $mainseparator;
        }
        $links = $DB->get_records('wiki_links', array('subwikiid' => $subwiki->id));
        foreach ($links as $link) {
            $content .= 'link:'.list_fields($link, array('frompageid', 'topageid', 'tomissingpage'), $secondseparator);
            $content .= $mainseparator;
        }
    }
    return $content;
}

function wks_import($wiki, $cmid) {
    global $OUTPUT;
    $mform = new wksport_form();
    $formdata['action'] = 'import';
    $formdata['cmid'] = $cmid;
    $mform->set_data($formdata);
    if($mform->is_cancelled()) {
        $courseurl = new moodle_url('/mod/wiki/view.php', array('id' => $cmid));
        redirect($courseurl);
    } else {
        $submitteddata = $mform->get_data();
        if ($submitteddata = $mform->get_data()) {
            $importedstring = $mform->get_file_content('importfile');
            if ($importedstring) {
                load_imported_data($wiki, $importedstring);
            }
        }
    }
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

function load_imported_data($wiki, $data) {
    global $DB;
    $mainseparator = '£µ£1';
    $newpageid = array();
    $subwiki = $DB->get_record('wiki_subwikis', array('wikiid' => $wiki->id));
    $firstpage = $DB->get_record('wiki_pages', array('subwikiid' => $subwiki->id, 'title' => $wiki->firstpagetitle));
    $firstpagetitle = '';
    $mainleveldatas = explode($mainseparator, $data);
    foreach ($mainleveldatas as $mainleveldata) {
        $label = strstr($mainleveldata, ':', true);
        $value = substr(strstr($mainleveldata, ':', false), 1);

        if ($label == 'page') {
            $newpageid = load_page($subwiki->id, $value, $newpageid, $firstpage->id, $firstpagetitle);
        } else if ($label == 'link') {
            load_link($subwiki->id, $value, $newpageid);
        } else if ($label) {
            $DB->set_field('wiki', $label, $value, array('id' => $wiki->id));
            if ($label == 'firstpagetitle') {
                $firstpagetitle = $value;
            }
        }
    }
    $now = time();
    $DB->set_field('wiki', 'timemodified', $now, array('id' => $wiki->id));
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'wiki'));
    $cmid = $DB->get_field('course_modules', 'id', array('module' => $moduleid, 'instance' => $wiki->id));
    $wikiurl = new moodle_url('/mod/wiki/view.php', array('id' => $cmid));
    header("Location: $wikiurl");
}

function load_page($subwikiid, $data, $newpageid, $firstpageid, $firstpagetitle) {
    global $DB, $USER;

    $secondseparator = '£µ£2';
    $table = 'wiki_pages';
    $page = new stdClass();
    $page->subwikiid = $subwikiid;

    $pagedatas = explode($secondseparator, $data);
    foreach ($pagedatas as $pagedata) {
        $label = strstr($pagedata, ':', true);
        $value = substr(strstr($pagedata, ':', false), 1);
        if ($label == 'id') {
            $oldpageid = $value;
            if (isset($newpageid[$oldpageid])) {
                return $newpageid;
            }
        } else if ($label) {
            $page->$label = $value;
        }
    }

    if ($page->format) {
        $pageformat = $page->format;
        unset($page->format);
    } else {
        $pageformat = 'html';
    }
    
    $now = time();
    $page->timecreated = $now;
    $page->timemodified = $now;
    $page->userid = $USER->id;

    if ($page->title == $firstpagetitle) {
        $page->id = $firstpageid;
        $DB->update_record($table, $page);
    } else {
        $already = $DB->get_record($table, array('subwikiid' => $subwikiid,
                                                    'title' => $page->title,
                                                    'userid' => $USER->id));
        if ($already) {
            $page->id = $already->id;
            $DB->update_record($table, $page);
        } else {
            $page->id = $DB->insert_record($table, $page);
        }
    }
    $newpageid[$oldpageid] = $page->id;
    
    //Version
    $versionnumber = 0;
    $haspreviousversion = $DB->record_exists('wiki_versions', array('pageid' => $page->id));
    if ($haspreviousversion) {
        $sql = "SELECT MAX(version) AS number FROM {wiki_versions} WHERE pageid = $page->id";
        $previousversion = $DB->get_record_sql($sql);
        $versionnumber = $previousversion->number + 1;
    }    
    $version = new stdClass();
    $version->pageid = $page->id;
    $version->content = $page->cachedcontent;
    $version->contentformat = $pageformat;
    $version->version = $versionnumber;
    $version->timecreated = $now;    
    $version->userid = $USER->id;
    $DB->insert_record('wiki_versions', $version);

    return $newpageid;
}

function load_link($subwikiid, $data, $newpageid) {
    global $DB;
    $secondseparator = '£µ£2';
    $table = 'wiki_links';
    $link = new stdClass();
    $link->subwikiid = $subwikiid;

    $linkdatas = explode($secondseparator, $data);
    foreach ($linkdatas as $linkdata) {
        $label = strstr($linkdata, ':', true);
        $value = substr(strstr($linkdata, ':', false), 1);
        if ($label == 'tomissingpage') {
            $link->tomissingpage = $value;
        } else if ($label == 'frompageid') {
            $fromoldpageid = $value;
        } else if ($label == 'topageid') {
            $tooldpageid = $value;
        }
    }

    if (isset($fromoldpageid) && isset($tooldpageid)) {
        if ($newpageid[$fromoldpageid] && $newpageid[$tooldpageid]) {
            echo "$fromoldpageid   $tooldpageid  ";            
            $link->frompageid = $newpageid[$fromoldpageid];
            $link->topageid = $newpageid[$tooldpageid];
            $DB->insert_record($table, $link);
        }
    }
}
