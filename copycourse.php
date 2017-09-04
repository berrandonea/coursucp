<?php
require_once('config.php');
$libfilename = 'course/lib.php';
require_once($libfilename);
$srccourseid = required_param('src', PARAM_INT);
$srccourse = $DB->get_record('course', array('id' => $srccourseid));
$srccontext = context_course::instance($srccourseid, MUST_EXIST);
require_login();
require_capability('moodle/course:update', $srccontext);
$destcourse = copycourse($srccourse, $srccontext);
header("Location: course/view.php?id=$destcourse->id");

function copycourse($srccourse, $srccontext) {
	global $DB;

	// If the course has already been duplicated, do nothing.
	$destcourse = $DB->get_record('course', array('idnumber' => "Y2017-$srccourse->idnumber"));
    if ($destcourse) {		
	    return $destcourse;
    }

	// Otherwise, duplicate it.
	$destvet = copycourse_copycategory($srccourse->category);
	$coursedata = copycourse_coursedata($srccourse, $destvet);
	$destcourse = create_course($coursedata);	
	$destcontext = context_course::instance($destcourse->id, MUST_EXIST);	
	copycourse_copyteachers($srccontext, $destcontext);
	copycourse_formatoptions($srccourse, $destcourse);
	copycourse_copysections($srccourse, $destcourse);
	copycourse_copyblocks($srccontext, $destcontext);
	//~ if ($destcourse->format == 'grid') {
		//~ copycourse_sectionpictures($srccourse, $destcourse);
	//~ }
	return $destcourse;
}

function copycourse_sectionpictures($srccourse, $destcourse) {
	global $DB;
	$srcpictures = $DB->get_records('format_grid_icon', array('courseid' => $srccourse->id));
	foreach ($srcpictures as $srcpicture) {
		$destpicture = new stdClass();
		$destpicture->image = $srcpicture->image;
		$destpicture->displayedimageindex = $srcpicture->displayedimageindex;
		$srcsection = $DB->get_record('course_sections', array('id' => $srcpicture->sectionid));
		$destsection = $DB->get_record('course_sections', array('course' => $destcourse->id, 'section' => $srcsection->section));
		$destpicture->sectionid = $destsection->id;
		$destpicture->courseid = $destcourse->id;
		$DB->insert_record('format_grid_icon', $destpicture);
	}
	$srcsummary = $DB->get_record('format_grid_summary', array('courseid' => $srccourse->id));
	$destsummary = new stdClass();
	$destsummary->showsummary = $srcsummary->showsummary;
	$destsummary->courseid = $destcourse->id;
	$DB->insert_record('format_grid_summary', $destsummary);
}

function copycourse_copyblocks($srccontext, $destcontext) {
	global $DB;
	$srcblocks = $DB->get_records('block_instances', array('parentcontextid' => $srccontext->id));
	foreach ($srcblocks as $srcblock) {
		$destblock = $srcblock;
		$destblock->id = 0;
		$destblock->parentcontextid = $destcontext->id;
		$DB->insert_record('block_instances', $destblock);
	}
}

function copycourse_formatoptions($srccourse, $destcourse) {
	global $DB;
	$srcformatoptions = $DB->get_records('course_format_options', array('courseid' => $srccourse->id));
	foreach ($srcformatoptions as $srcformatoption) {
		$optionparams = array('courseid' => $destcourse->id, 
		                      'format' => $srcformatoption->format, 
		                      'sectionid' => $srcformatoption->sectionid,
		                      'name' => $srcformatoption->name);		     
		$alreadydestoption = $DB->get_record('course_format_options', $optionparams);
		if ($alreadydestoption) {
			$DB->set_field('course_format_options', 'value', $srcformatoption->value, $optionparams);
		} else {
			$newdestoption = new stdClass();
			$newdestoption->courseid = $destcourse->id;
			$newdestoption->format = $srcformatoption->format;
			$newdestoption->sectionid = $srcformatoption->sectionid;
			$newdestoption->name = $srcformatoption->name;
			$newdestoption->value = $srcformatoption->value;
			$DB->insert_record('course_format_options', $newdestoption);
		}
	}
}

function copycourse_copysections($srccourse, $destcourse) {
	global $DB;
	$srcsections = $DB->get_records('course_sections', array('course' => $srccourse->id), 'section');
	foreach ($srcsections as $srcsection) {
		course_create_sections_if_missing($destcourse, $srcsection->section);
		$destsection = $DB->get_record('course_sections', array('course' => $destcourse->id, 'section' => $srcsection->section), '*', MUST_EXIST);
		$destsection->name = $srcsection->name;
		$destsection->summary = $srcsection->summary;
		$destsection->summaryformat = $srcsection->summaryformat;
		$destsection->visible = $srcsection->visible;
		if ($srcsection->availability) {
			$destsection->availability = $srcsection->availability;
		}
		$destsection->spid = $srcsection->spid;
		$DB->update_record('course_sections', $destsection);
		$sectioncmids = explode(',', $srcsection->sequence);
		$newsequence = '';
		foreach ($sectioncmids as $sectioncmid) {
			if (!$sectioncmid) {
				continue;
			}
			var_dump($sectioncmid); echo '<br>';
			$srccm = $DB->get_record('course_modules', array('id' => $sectioncmid));
			if ($srccm) {
			    $newcm = copypaste_module($srccm, $destsection->id);
			} else {
				exit;
			}
			$newsequence .= "$newcm->id,";
		}
		$DB->set_field('course_sections', 'sequence', $newsequence, array('id' => $destsection->id));
	}
}

function copycourse_coursedata($srccourse, $destvet) {
    $coursedata = new stdClass;
    $coursedata->fullname = $srccourse->fullname;
    $coursedata->category = $destvet->id;
    $coursedata->shortname = "Y2017-$srccourse->shortname";
    $coursedata->idnumber = "Y2017-$srccourse->idnumber";
    $coursedata->format = $srccourse->format;    
    return $coursedata;
}

//~ function copycourse_courserecord($srccourse, $destvet) {
	//~ global $DB;
	//~ $destcourse = new stdClass();
	//~ $destcourse->category = $destvet->id;
	//~ $destcourse->fullname = $srccourse->fullname;
	//~ $destcourse->shortname = "Y2017-$srccourse->shortname";
	//~ $destcourse->idnumber = "Y2017-$srccourse->idnumber";
	//~ $fields = array('summary', 'summaryformat', 'format', 'showgrades', 'newsitems', 'startdate', 'marker', 'maxbytes',
	                //~ 'legacyfiles', 'showreports', 'visible', 'visibleold', 'groupmode', 'groupmodeforce', 
	                //~ 'defaultgroupingid', 'lang', 'calendartype', 'theme', 'timecreated', 'timemodified', 'requested',
	                //~ 'enablecompletion', 'completionnotify');
	//~ foreach ($fields as $field) {
		//~ $destcourse->$field = $srccourse->$field;
	//~ }
	//~ $destcourse->cacherev = $now;
    //~ $destcourse->transfered = '2016';
    //~ $destcourse->id = $DB->insert_record('course', $destcourse);
    //~ return $destcourse;
//~ }

function copycourse_copycategory($categoryid) {
	global $DB;
	$srccategory = $DB->get_record('course_categories', array('id' => $categoryid), '*', MUST_EXIST);
	if ($srccategory->idnumber == 'Y2016') {
		$destcategory = $DB->get_record('course_categories', array('idnumber' => 'Y2017'));
		return $destcategory;
	}	
	$destcategory = $DB->get_record('course_categories', array('idnumber' => "Y2017-$srccategory->idnumber"));	
	if ($destcategory) {		
		return $destcategory;
	}
	$destparent = copycourse_copycategory($srccategory->parent);
	$destcategory = new stdClass();
	$destcategory->name = $srccategory->name;
	$destcategory->idnumber = "Y2017-$srccategory->idnumber";
	$destcategory->description = $srccategory->description;
	$destcategory->parent = $destparent->id;
	$destcategory->visible = 1;
	$destcategory->visibleold = 1;
	$destcategory->timemodified = time();
	$destcategory->depth = $srccategory->depth;
	$destcategory->id = $DB->insert_record('course_categories', $destcategory);
	return $destcategory;
}

//~ function copycourse_copyvet($vetid) {
	//~ global $DB;	
	//~ $srcvet = $DB->get_record('course_categories', array('id' => $vetid));
	//~ $destvet = $DB->get_record('course_categories', array('id' => "Y2017-$srcvet->idnumber"));
	//~ if ($destvet) {
		//~ return $destvet;
	//~ }
	//~ $now = time();	
	//~ $srclevel = $DB->get_record('course_categories', array('id' => $srcvet->parent));
	//~ $destlevel = $DB->get_record('course_categories', array('id' => "Y2017-$srclevel->idnumber"));
	//~ if (!$destlevel) {		
		//~ $srcufr = $DB->get_record('course_categories', array('id' => $srclevel->parent));
		//~ $DB->set_debug(true);
		//~ $destufr = $DB->get_record('course_categories', array('id' => "Y2017-$srcufr->idnumber"));
		//~ $DB->set_debug(false);
		//~ exit;
		//~ $destlevel = new stdClass();
		//~ $destlevel->name = $srclevel->name;
		//~ $destlevel->idnumber = "Y2017-$srclevel->idnumber";
		//~ $destlevel->description = $srclevel->description;
		//~ $destlevel->descriptionformat = $srclevel->descriptionformat;
		//~ $destlevel->parent = $destufr->id;
		//~ $destlevel->visible = 1;
		//~ $destlevel->visibleold = 1;
		//~ $destlevel->timemodified = $now;
		//~ $destlevel->depth = 3;
	    //~ $destlevel->id = $DB->insert_record('course_categories', $destlevel);
		//~ $DB->set_field('course_categories', 'path', "$destufr->path/$destlevel->id", array('id' => $destlevel->id));
	//~ }
	//~ $destvet = new stdClass();
	//~ $destvet->name = $srcvet->name;
	//~ $destvet->idnumber = "Y2017-$srcvet->idnumber";
	//~ $destvet->description = $srcvet->description;
	//~ $destvet->descriptionformat = $srcvet->descriptionformat;
	//~ $destvet->parent = $destlevel->id;
	//~ $destvet->visible = 1;
	//~ $destvet->visibleold = 1;
	//~ $destvet->timemodified = $now;
	//~ $destvet->depth = 4;
	//~ $destvet->id = $DB->insert_record('course_categories', $destvet);
	//~ $destvet->path = "$deslevel->path/destvet->id";
	//~ $DB->set_field('course_categories', 'path', $destvet->path, array('id' => $destvet->id));
	//~ return $destvet;
//~ }

//~ function copycourse_copycontext($srccontext, $destcourse) {
	//~ global $DB;
	//~ $destcontext = new stdClass();
	//~ $destcontext->contextlevel = CONTEXT_COURSE;
	//~ $destcontext->instanceid = $destcourse->id;
	//~ $depth = $srcontext->depth;
	//~ $destcontext->id = $DB->insert_record('context', $destcontext);
	//~ $destvetcontext = $DB->get_record('context', array('contextlevel' => CONTEXT_CATEGORY, 'instanceid' => $destcourse->category));
	//~ $DB->set_field('context', 'path', "$destvetcontext->path/$destcontext->id", array('id' => $destcontext->id));	
//~ }

function copycourse_copyteachers($srccontext, $destcontext) {
	global $DB, $USER;	
	$destenrolmethod = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $destcontext->instanceid));
	$teacherroleassignments = $DB->get_records('role_assignments', array('roleid' => 3, 'contextid' => $srccontext->id));
	$now = time();
	foreach ($teacherroleassignments as $teacherroleassignment) {
		$destassignment = new stdClass();
		$destassignment->roleid = $teacherroleassignment->roleid;
		$destassignment->contextid = $destcontext->id;
		$destassignment->userid = $teacherroleassignment->userid;
		$destassignment->timemodified = $now;
		$destassignment->modifierid = $USER->id;
		$DB->insert_record('role_assignments', $destassignment);
		$destenrolment = new stdClass();
		$destenrolment->enrolid = $destenrolmethod->id;
		$destenrolment->userid = $teacherroleassignment->userid;
		$destenrolment->timestart = $now;
		$destenrolment->timecreated = $now;
		$destenrolment->timemodified = $now;
		$destenrolment->modifierid = $USER->id;		
		$DB->insert_record('user_enrolments', $destenrolment);
	}
}

//~ $srccm = get_coursemodule_from_id('', $srccmid, 0, true, MUST_EXIST);
//~ $srccourse = $DB->get_record('course', array('id' => $srccm->course), '*', MUST_EXIST);
//~ $srccoursesections = get_records('course_sections', array('courseid' => $srccourse->id));



//~ if (!empty($duplicate) and confirm_sesskey()) {
    //~ $cm     = get_coursemodule_from_id('', $duplicate, 0, true, MUST_EXIST);
    //~ $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

    //~ require_login($course, false, $cm);
    //~ $modcontext = context_module::instance($cm->id);
    //~ require_capability('moodle/course:manageactivities', $modcontext);

    //~ // Duplicate the module.
    //~ $newcm = duplicate_course_module($course, $cm);
    //~ redirect(course_get_url($course, $cm->sectionnum, array('sr' => $sectionreturn)));
//~ }

/**
 * Api to duplicate a module.
 *
 * @param object $course course object.
 * @param object $cm course module object to be duplicated.
 * @since Moodle 2.8
 *
 * @throws Exception
 * @throws coding_exception
 * @throws moodle_exception
 * @throws restore_controller_exception
 *
 * @return cm_info|null cminfo object if we sucessfully duplicated the mod and found the new cm.
 */
function duplicate_course_module($course, $cm) {
    global $CFG, $DB, $USER;
    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
    require_once($CFG->libdir . '/filelib.php');

    $a          = new stdClass();
    $a->modtype = get_string('modulename', $cm->modname);
    $a->modname = format_string($cm->name);

    if (!plugin_supports('mod', $cm->modname, FEATURE_BACKUP_MOODLE2)) {
        throw new moodle_exception('duplicatenosupport', 'error', '', $a);
    }

    // Backup the activity.

    $bc = new backup_controller(backup::TYPE_1ACTIVITY, $cm->id, backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id);

    $backupid       = $bc->get_backupid();
    $backupbasepath = $bc->get_plan()->get_basepath();

    $bc->execute_plan();

    $bc->destroy();

    // Restore the backup immediately.

    $rc = new restore_controller($backupid, $course->id,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id, backup::TARGET_CURRENT_ADDING);

    $cmcontext = context_module::instance($cm->id);
    if (!$rc->execute_precheck()) {
        $precheckresults = $rc->get_precheck_results();
        if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
            if (empty($CFG->keeptempdirectoriesonbackup)) {
                fulldelete($backupbasepath);
            }
        }
    }

    $rc->execute_plan();

    // Now a bit hacky part follows - we try to get the cmid of the newly
    // restored copy of the module.
    $newcmid = null;
    $tasks = $rc->get_plan()->get_tasks();
    foreach ($tasks as $task) {
        if (is_subclass_of($task, 'restore_activity_task')) {
            if ($task->get_old_contextid() == $cmcontext->id) {
                $newcmid = $task->get_moduleid();
                break;
            }
        }
    }

    // If we know the cmid of the new course module, let us move it
    // right below the original one. otherwise it will stay at the
    // end of the section.
    if ($newcmid) {
        $info = get_fast_modinfo($course);
        $newcm = $info->get_cm($newcmid);
        $section = $DB->get_record('course_sections', array('id' => $cm->section, 'course' => $cm->course));
        moveto_module($newcm, $section, $cm);
        moveto_module($cm, $section, $newcm);

        // Trigger course module created event. We can trigger the event only if we know the newcmid.
        $event = \core\event\course_module_created::create_from_cm($newcm);
        $event->trigger();
    }
    rebuild_course_cache($cm->course);

    $rc->destroy();

    if (empty($CFG->keeptempdirectoriesonbackup)) {
        fulldelete($backupbasepath);
    }

    return isset($newcm) ? $newcm : null;
}


/**
 * Copy a module to a given section in another course.
 * Code heavily based on function duplicate_module from course/lib.php
 * 
 * @param object $srccm course module object to be copied.
 * @param int $tosectionid id of the course section where the module must be pasted.
 * @since Moodle 2.8
 *
 * @throws Exception
 * @throws coding_exception
 * @throws moodle_exception
 * @throws restore_controller_exception
 *
 * @return cm_info|null cminfo object if we sucessfully duplicated the mod and found the new cm.
 */
function copypaste_module($srccm, $tosectionid) {
    global $CFG, $DB, $USER;
    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
    require_once($CFG->libdir . '/filelib.php');

	$srccm->modname = $DB->get_field('modules', 'name', array('id' => $srccm->module));
	if (!$srccm->modname) {
		print_object($srccm); exit;
	}
	$srccm->name = $DB->get_field($srccm->modname, 'name', array('id' => $srccm->instance));

    $a          = new stdClass();
    $a->modtype = get_string('modulename', $srccm->modname);
    $a->modname = format_string($srccm->name);

    if (!plugin_supports('mod', $srccm->modname, FEATURE_BACKUP_MOODLE2)) {
        throw new moodle_exception('duplicatenosupport', 'error', '', $a);
    }

    // Backup the activity.
    $bc = new backup_controller(backup::TYPE_1ACTIVITY, $srccm->id, backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id);
    $backupid       = $bc->get_backupid();
    $backupbasepath = $bc->get_plan()->get_basepath();
    $bc->execute_plan();
    $bc->destroy();

    // Destination
	$tosection = $DB->get_record('course_sections', array('id' => $tosectionid));	
	$destcourse = get_course($tosection->course);

    // Restore the backup immediately.
    $rc = new restore_controller($backupid, $destcourse->id,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id, backup::TARGET_CURRENT_ADDING);

    $cmcontext = context_module::instance($srccm->id);
    if (!$rc->execute_precheck()) {
        $precheckresults = $rc->get_precheck_results();
        if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
            if (empty($CFG->keeptempdirectoriesonbackup)) {
                fulldelete($backupbasepath);
            }
        }
    }

    $rc->execute_plan();

    // Now a bit hacky part follows - we try to get the cmid of the newly
    // restored copy of the module.
    $newcmid = null;
    $tasks = $rc->get_plan()->get_tasks();
    foreach ($tasks as $task) {
        if (is_subclass_of($task, 'restore_activity_task')) {
            if ($task->get_old_contextid() == $cmcontext->id) {
                $newcmid = $task->get_moduleid();
                break;
            }
        }
    }

    // If we know the cmid of the new course module, let us move it
    // right below the original one. otherwise it will stay at the
    // end of the section.
    if ($newcmid) {
        $info = get_fast_modinfo($destcourse);
        $newcm = $info->get_cm($newcmid);
        //~ $section = $DB->get_record('course_sections', array('id' => $srccm->section, 'course' => $srccm->course));
        //~ moveto_module($newcm, $section, $srccm);
        //~ moveto_module($srccm, $section, $newcm);

        // Trigger course module created event. We can trigger the event only if we know the newcmid.
        $event = \core\event\course_module_created::create_from_cm($newcm);
        $event->trigger();
    }
    rebuild_course_cache($newcm->course);

    $rc->destroy();

    if (empty($CFG->keeptempdirectoriesonbackup)) {
        fulldelete($backupbasepath);
    }
    return isset($newcm) ? $newcm : null;
}
