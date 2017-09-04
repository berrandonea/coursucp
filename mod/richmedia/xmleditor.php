<?php

/**
 * Edit the synchronization file
 * Author:
 * 	Adrien Jamot  (adrien_jamot [at] symetrix [dt] fr)
 * 
 * @package   mod_richmedia
 * @copyright 2011 Symetrix
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */
require_once("../../config.php");
require_once("lib.php");

$update = required_param('update', PARAM_INT);

$context = context_module::instance($update);

$url = new moodle_url('/mod/richmedia/xmleditor.php', array('update' => $update));

$PAGE->set_url('/mod/richmedia/xmleditor.php');
if (!$module = get_coursemodule_from_id('richmedia', $update)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", array("id" => $module->course))) {
    print_error('coursemisconf');
}

if (!$richmedia = $DB->get_record("richmedia", array("id" => $module->instance))) {
    print_error('invalidid', 'richmedia');
}

require_login($course->id, true, $module);

require_capability('moodle/course:manageactivities', $context);

$streditxml = get_string('editxml', 'richmedia');

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/richmedia/lib/adapter/ext/ext-base.js');
$PAGE->requires->css('/mod/richmedia/lib/resources/css/ext-all.css');
$PAGE->requires->js('/mod/richmedia/lib/ext-all.js');
$PAGE->requires->js('/mod/richmedia/xmleditor.js');

$PAGE->set_title(format_string($richmedia->name));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($course->shortname, $CFG->wwwroot . "/course/view.php?id=" . $course->id);
$PAGE->navbar->add($richmedia->name, $CFG->wwwroot . "/mod/richmedia/view.php?id=" . $update);
$PAGE->navbar->add($streditxml);

$fs = get_file_storage();

$fileurl = richmedia_get_video_url($richmedia);

// Prepare file record object
$fileinfo = new stdClass();
$fileinfo->component = 'mod_richmedia';
$fileinfo->filearea = 'content';
$fileinfo->contextid = $context->id;
$fileinfo->filepath = '/';
$fileinfo->itemid = 0;
$fileinfo->filename = $richmedia->referencesxml;
// Get file
$file = $fs->get_file($fileinfo->contextid, $fileinfo->component, $fileinfo->filearea, $fileinfo->itemid, $fileinfo->filepath, $fileinfo->filename);

$urlslide = "{$CFG->wwwroot}/pluginfile.php/{$context->id}/mod_richmedia/content/slides/";
// Read contents
if ($file) {
    $contenuxml = $file->get_content();
    $contenuxml = str_replace('&', '&amp;', $contenuxml);

    $xml = simplexml_load_string($contenuxml);

    foreach ($xml->titles[0]->title[0]->attributes() as $attribute => $value) {
        if ($attribute == 'label') {
            $title = $richmedia->name;
            if (!$title) {
                $title = richmedia_encode_string($value);
            }
            break;
        }
    }
    $presentertitle = '';
    foreach ($xml->presenter[0]->attributes() as $attribute => $value) {
        if ($attribute == 'name') {
            $presentername = richmedia_encode_string($richmedia->presentor);
            if (!$presentername) {
                $presentername = $USER->firstname . ' ' . $USER->lastname;
            }
        } else if ($attribute == 'title') {
            $presentertitle = richmedia_encode_string($value);
        }
    }
    $defaultview = $richmedia->defaultview;
    $autoplay = $richmedia->autoplay;
    foreach ($xml->options[0]->attributes() as $attribute => $value) {
        if ($attribute == 'defaultview' && $value != '') {
            $defaultview = $value;
        }
    }

    foreach ($xml->design[0]->attributes() as $attribute => $value) {
        if ($attribute == 'fontcolor') {
            $fontcolor = $richmedia->fontcolor;
            if (!$fontcolor) {
                $fontcolor = substr($value, 2);
            } else if ($fontcolor[0] == '#') {
                $fontcolor = substr($fontcolor, 1);
            }
        }
        if ($attribute == 'font') {
            $font = $richmedia->font;
            if (!$font) {
                $font = $value;
            }
        }
    }
    $tabstep = array();

    $i = 0;

    foreach ($xml->steps[0]->children() as $childname => $childnode) {
        foreach ($childnode->attributes() as $attribute => $value) {
            if ($attribute == 'framein') {
                $tabstep[$i][$attribute] = richmedia_convert_time($value); // convert time
            } else if ($attribute == 'slide') {
                // Prepare video record object
                $fileinfoslide = new stdClass();
                $fileinfoslide->component = 'mod_richmedia';
                $fileinfoslide->filearea = 'content';
                $fileinfoslide->contextid = $context->id;
                $fileinfoslide->filepath = '/slides/';
                $fileinfoslide->itemid = 0;
                $fileinfoslide->filename = (String) $value;
                // Get file
                $fileslide = $fs->get_file($fileinfoslide->contextid, $fileinfoslide->component, $fileinfoslide->filearea, $fileinfoslide->itemid, $fileinfoslide->filepath, $fileinfoslide->filename);

                if ($fileslide) {
                    $fileslidename = $fileslide->get_filename();
                    $fileurlslide = $urlslide . $fileslidename;
                    $tabstep[$i]['url'] = $fileurlslide;
                } else {
                    $tabstep[$i]['url'] = '';
                }
                $tabstep[$i][$attribute] = (String) $value;
            } else {
                $tabstep[$i][$attribute] = (String) $value;
            }
        }
        $i++;
    }
} else {
    // file doesn't exist - do something
    $contenuxml = '';
    $title = get_string('title', 'richmedia');
    $presentername = '';
    $presentertitle = '';
    $fontcolor = 'FFFFFF';
    $font = 'Arial';
    $tabstep = array();
    $tabstep[0]['id'] = 0;
    $tabstep[0]['label'] = get_string('slidetitle', 'richmedia');
    $tabstep[0]['comment'] = '';
    $tabstep[0]['framein'] = '00:00';
    $tabstep[0]['slide'] = 'Diapositive1.JPG';
    $tabstep[0]['question'] = '';
    $defaultview = 1;
    $autoplay = 1;
}

//AVAILABLE FILES
$available = richmedia_get_richmedia_available_pictures($module->id);

$questions = array();
if (!empty($richmedia->quizid)) {
    $quizActivity = $DB->get_record('symquiz', array('id' => $richmedia->quizid));
    require_once $CFG->dirroot . '/mod/symquiz/quiz.php';
    $cmQuiz = get_coursemodule_from_instance('symquiz', $richmedia->quizid);
    $quiz = symquiz\quiz\Quiz::getQuiz($quizActivity, $cmQuiz->id);
    $quizQuestions = $quiz->getQuestions();
    foreach ($quizQuestions as $quizQuestion) {
        $questions[$quizQuestion->getId()] = strip_tags($quizQuestion->getText());
    }
}

$urlsubmit = $CFG->wwwroot . '/mod/richmedia/xmleditor_save.php';
$urlLocation = $CFG->wwwroot . '/course/modedit.php?update=' . $update;
$urlView = $CFG->wwwroot . '/mod/richmedia/view.php?id=' . $update;
$defaultview = (string) $defaultview;

$PAGE->requires->js_init_call(
        'M.mod_richmedia_xmleditor.init', array(
    $available,
    $tabstep,
    $fileurl,
    $richmedia->referencesvideo,
    $title,
    $presentername,
    $presentertitle,
    $context->id,
    $update,
    $fontcolor,
    $font,
    $urlslide,
    $defaultview,
    $autoplay,
    $urlsubmit,
    $urlLocation,
    $urlView,
    $questions
        )
);

$PAGE->requires->strings_for_js(array(
    'down',
    'wait',
    'currentsave',
    'saveandreturn',
    'view',
    'savedone',
    'information',
    'test',
    'filenotavailable',
    'up',
    'delete',
    'addline',
    'cancel',
    'slidetitle',
    'save',
    'title',
    'video',
    'slidecomment',
    'presentation',
    'tile',
    'actions',
    'newline',
    'confirmdeleteline',
    'warning',
    'gettime',
    'slide',
    'delete',
    'up',
    'defaultview',
    'samesteps'
        ), 'mod_richmedia');

echo $OUTPUT->header();
echo $OUTPUT->heading($streditxml);

echo html_writer::tag('div', '', array('id' => 'tab', 'style' => 'margin:auto;'));
echo $OUTPUT->footer();
