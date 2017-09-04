<?php

/**
 * Copy the course modedit file to save the instance and redirect to the xmleditor
 * Author:
 * 	Adrien Jamot  (adrien_jamot [at] symetrix [dt] fr)
 * 
 * @package   mod_richmedia
 * @copyright 2011 Symetrix
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

require_once("../../config.php");
require_once("../../course/lib.php");
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/conditionlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot . '/course/modlib.php');

$add    = optional_param('add', '', PARAM_ALPHA);     // module name
$update = optional_param('update', 0, PARAM_INT);
$return = optional_param('return', 0, PARAM_BOOL);    //return to course/view.php if false or mod/modname/view.php if true
$type   = optional_param('type', '', PARAM_ALPHANUM); //TODO: hopefully will be removed in 2.0
$sectionreturn = optional_param('sr', null, PARAM_INT);

$url = new moodle_url('/course/modedit.php');
$url->param('sr', $sectionreturn);
if (!empty($return)) {
    $url->param('return', $return);
}

if (!empty($add)) {
    $section = required_param('section', PARAM_INT);
    $course  = required_param('course', PARAM_INT);

    $url->param('add', $add);
    $url->param('section', $section);
    $url->param('course', $course);
    $PAGE->set_url($url);

    $course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
    require_login($course);

    // There is no page for this in the navigation. The closest we'll have is the course section.
    // If the course section isn't displayed on the navigation this will fall back to the course which
    // will be the closest match we have.
    navigation_node::override_active_url(course_get_url($course, $section));

    list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);

    $cm = null;

    $data = new stdClass();
    $data->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
    $data->visible          = $cw->visible;
    $data->course           = $course->id;
    $data->module           = $module->id;
    $data->modulename       = $module->name;
    $data->groupmode        = $course->groupmode;
    $data->groupingid       = $course->defaultgroupingid;
    $data->groupmembersonly = 0;
    $data->id               = '';
    $data->instance         = '';
    $data->coursemodule     = '';
    $data->add              = $add;
    $data->return           = 0; //must be false if this is an add, go back to course view on cancel
    $data->sr               = $sectionreturn;

    if (plugin_supports('mod', $data->modulename, FEATURE_MOD_INTRO, true)) {
        $draftid_editor = file_get_submitted_draft_itemid('introeditor');
        file_prepare_draft_area($draftid_editor, null, null, null, null, array('subdirs'=>true));
        $data->introeditor = array('text'=>'', 'format'=>FORMAT_HTML, 'itemid'=>$draftid_editor); // TODO: add better default
    }

    if (plugin_supports('mod', $data->modulename, FEATURE_ADVANCED_GRADING, false)
            and has_capability('moodle/grade:managegradingforms', $context)) {
        require_once($CFG->dirroot.'/grade/grading/lib.php');

        $data->_advancedgradingdata['methods'] = grading_manager::available_methods();
        $areas = grading_manager::available_areas('mod_'.$module->name);

        foreach ($areas as $areaname => $areatitle) {
            $data->_advancedgradingdata['areas'][$areaname] = array(
                'title'  => $areatitle,
                'method' => '',
            );
            $formfield = 'advancedgradingmethod_'.$areaname;
            $data->{$formfield} = '';
        }
    }

    if (!empty($type)) { //TODO: hopefully will be removed in 2.0
        $data->type = $type;
    }

    $sectionname = get_section_name($course, $cw);
    $fullmodulename = get_string('modulename', $module->name);

    if ($data->section && $course->format != 'site') {
        $heading = new stdClass();
        $heading->what = $fullmodulename;
        $heading->to   = $sectionname;
        $pageheading = get_string('addinganewto', 'moodle', $heading);
    } else {
        $pageheading = get_string('addinganew', 'moodle', $fullmodulename);
    }
    $navbaraddition = $pageheading;

} else if (!empty($update)) {

    $url->param('update', $update);
    $PAGE->set_url($url);

    // Select the "Edit settings" from navigation.
    navigation_node::override_active_url(new moodle_url('/course/modedit.php', array('update'=>$update, 'return'=>1)));

    // Check the course module exists.
    $cm = get_coursemodule_from_id('', $update, 0, false, MUST_EXIST);

    // Check the course exists.
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

    // require_login
    require_login($course, false, $cm); // needed to setup proper $COURSE

    list($cm, $context, $module, $data, $cw) = can_update_moduleinfo($cm);

    $data->coursemodule       = $cm->id;
    $data->section            = $cw->section;  // The section number itself - relative!!! (section column in course_sections)
    $data->visible            = $cm->visible; //??  $cw->visible ? $cm->visible : 0; // section hiding overrides
    $data->cmidnumber         = $cm->idnumber;          // The cm IDnumber
    $data->groupmode          = groups_get_activity_groupmode($cm); // locked later if forced
    $data->groupingid         = $cm->groupingid;
    $data->course             = $course->id;
    $data->module             = $module->id;
    $data->modulename         = $module->name;
    $data->instance           = $cm->instance;
    $data->return             = $return;
    $data->sr                 = $sectionreturn;
    $data->update             = $update;
    $data->completion         = $cm->completion;
    $data->completionview     = $cm->completionview;
    $data->completionexpected = $cm->completionexpected;
    $data->completionusegrade = is_null($cm->completiongradeitemnumber) ? 0 : 1;
    $data->showdescription    = $cm->showdescription;
    if (!empty($CFG->enableavailability)) {
        $data->availabilityconditionsjson = $cm->availability;
    }

    if (plugin_supports('mod', $data->modulename, FEATURE_MOD_INTRO, true)) {
        $draftid_editor = file_get_submitted_draft_itemid('introeditor');
        $currentintro = file_prepare_draft_area($draftid_editor, $context->id, 'mod_'.$data->modulename, 'intro', 0, array('subdirs'=>true), $data->intro);
        $data->introeditor = array('text'=>$currentintro, 'format'=>$data->introformat, 'itemid'=>$draftid_editor);
    }

    if (plugin_supports('mod', $data->modulename, FEATURE_ADVANCED_GRADING, false)
            and has_capability('moodle/grade:managegradingforms', $context)) {
        require_once($CFG->dirroot.'/grade/grading/lib.php');
        $gradingman = get_grading_manager($context, 'mod_'.$data->modulename);
        $data->_advancedgradingdata['methods'] = $gradingman->get_available_methods();
        $areas = $gradingman->get_available_areas();

        foreach ($areas as $areaname => $areatitle) {
            $gradingman->set_area($areaname);
            $method = $gradingman->get_active_method();
            $data->_advancedgradingdata['areas'][$areaname] = array(
                'title'  => $areatitle,
                'method' => $method,
            );
            $formfield = 'advancedgradingmethod_'.$areaname;
            $data->{$formfield} = $method;
        }
    }

    if ($items = grade_item::fetch_all(array('itemtype'=>'mod', 'itemmodule'=>$data->modulename,
                                             'iteminstance'=>$data->instance, 'courseid'=>$course->id))) {
        // add existing outcomes
        foreach ($items as $item) {
            if (!empty($item->outcomeid)) {
                $data->{'outcome_'.$item->outcomeid} = 1;
            }
        }

        // set category if present
        $gradecat = false;
        foreach ($items as $item) {
            if ($gradecat === false) {
                $gradecat = $item->categoryid;
                continue;
            }
            if ($gradecat != $item->categoryid) {
                //mixed categories
                $gradecat = false;
                break;
            }
        }
        if ($gradecat !== false) {
            // do not set if mixed categories present
            $data->gradecat = $gradecat;
        }
    }

    $sectionname = get_section_name($course, $cw);
    $fullmodulename = get_string('modulename', $module->name);

    if ($data->section && $course->format != 'site') {
        $heading = new stdClass();
        $heading->what = $fullmodulename;
        $heading->in   = $sectionname;
        $pageheading = get_string('updatingain', 'moodle', $heading);
    } else {
        $pageheading = get_string('updatinga', 'moodle', $fullmodulename);
    }
    $navbaraddition = null;

} else {
    require_login();
    print_error('invalidaction');
}

$pagepath = 'mod-' . $module->name . '-';
if (!empty($type)) { //TODO: hopefully will be removed in 2.0
    $pagepath .= $type;
} else {
    $pagepath .= 'mod';
}
$PAGE->set_pagetype($pagepath);
$PAGE->set_pagelayout('admin');

$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
if (file_exists($modmoodleform)) {
    require_once($modmoodleform);
} else {
    print_error('noformdesc');
}

$mformclassname = 'mod_'.$module->name.'_mod_form';
$mform = new $mformclassname($data, $cw->section, $cm, $course);
$mform->set_data($data);

if ($mform->is_cancelled()) {
    if ($return && !empty($cm->id)) {
        redirect("$CFG->wwwroot/mod/$module->name/view.php?id=$cm->id");
    } else {
        redirect(course_get_url($course, $cw->section, array('sr' => $sectionreturn)));
    }
} else if ($fromform = $mform->get_data()) {
    
    $fromform->theme = $_POST['theme'];
    $fromform->html5 = $_POST['html5'];

    if (!empty($fromform->update)) {
        list($cm, $fromform) = update_moduleinfo($cm, $fromform, $course, $mform);
    } else if (!empty($fromform->add)) {
        $fromform = add_moduleinfo($fromform, $course, $mform);
    } else {
        print_error('invaliddata');
    }
    redirect("$CFG->wwwroot/mod/$module->name/xmleditor.php?update=$fromform->coursemodule");
    exit;

}
 else {
    redirect("$CFG->wwwroot/course/modedit.php?add=richmedia&type=&course={$course->id}&section={$section}&return=0");
}
/*else {

    $streditinga = get_string('editinga', 'moodle', $fullmodulename);
    $strmodulenameplural = get_string('modulenameplural', $module->name);

    if (!empty($cm->id)) {
        $context = context_module::instance($cm->id);
    } else {
        $context = context_course::instance($course->id);
    }

    $PAGE->set_heading($course->fullname);
    $PAGE->set_title($streditinga);
    $PAGE->set_cacheable(false);

    if (isset($navbaraddition)) {
        $PAGE->navbar->add($navbaraddition);
    }

    echo $OUTPUT->header();

    if (get_string_manager()->string_exists('modulename_help', $module->name)) {
        echo $OUTPUT->heading_with_help($pageheading, 'modulename', $module->name, 'icon');
    } else {
        echo $OUTPUT->heading_with_help($pageheading, '', $module->name, 'icon');
    }

    $mform->display();

    echo $OUTPUT->footer();
}*/

