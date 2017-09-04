<?php

defined('MOODLE_INTERNAL') || die;


//
require_once(dirname(__FILE__).'/lib.php');


/*
 function autoattendmod_get_namepattern($courseid)
 function autoattendmod_disp_feedback($courseid)
 function autoattendmod_is_email_enable($courseid)
 function autoattendmod_is_email_allreports($courseid)
 function autoattendmod_is_email_key($courseid)
 function autoattendmod_is_email_user($courseid)

 function autoattendmod_get_receivemail_users($context)
 function autoattendmod_send_email_teachers($courseid, $subject, $posttext, $posthtml, $attachdata, $attachname)
 function autoattendmod_send_email_user($courseid, $user, $subject, $posttext, $posthtml, $attachdata, $attachname)
*/


//
function  autoattendmod_get_namepattern($courseid)
{
	global $DB;

	if ($courseid==0) return 'fullname';

	$pattern = $DB->get_field('autoattendmod', 'namepattern', array('course'=>$courseid));
	if (!$pattern) $pattern = 'fullname';

	return $pattern;
}


//
function  autoattendmod_disp_feedback($courseid)
{
	global $DB;

	if ($courseid==0) return true;

	$disp = $DB->get_field('autoattendmod', 'feedback', array('course'=>$courseid));
	return $disp;
}


//
function  autoattendmod_is_email_enable($courseid)
{
	global $DB;

	if ($courseid==0) return false;

	$enable = $DB->get_field('autoattendmod', 'emailenable', array('course'=>$courseid));
	return $enable;
}


//
function  autoattendmod_is_email_allreports($courseid)
{
	global $DB;

	if ($courseid==0) return false;

	$allrep = $DB->get_field('autoattendmod', 'allreports', array('course'=>$courseid));
	return $allrep;
}


//
function  autoattendmod_is_email_key($courseid)
{
	global $DB;

	if ($courseid==0) return false;

	$enable = $DB->get_field('autoattendmod', 'emailkey', array('course'=>$courseid));
	return $enable;
}


//
function  autoattendmod_is_email_user($courseid)
{
	global $DB;

	if ($courseid==0) return false;

	$enable = $DB->get_field('autoattendmod', 'emailuser', array('course'=>$courseid));
	return $enable;
}



////////////////////////////////////////////////////////////////////////////////////////
//
// E-Mail
//

// メール受信可能なユーザ
function  autoattendmod_get_receivemail_users($context)
{
	$ret = get_users_by_capability($context, 'mod/autoattendmod:receivemail', '', 'lastname', '', '', false, '', false);
	return $ret;
}


//
function  autoattendmod_send_email_teachers($courseid, $subject, $posttext, $posthtml, $attachdata, $attachname)
{
	global $CFG;
	require_once($CFG->dirroot.'/blocks/autoattend/jbxl/jbxl_moodle_tools.php');

	$ccontext = context_course::instance($courseid);
	$teachers = autoattendmod_get_receivemail_users($ccontext);

	if ($teachers) {
		foreach ($teachers as $teacher) {
			if (jbxl_is_teacher($teacher->id, $ccontext, false)) {
				$htmlmail = '';
				if ($teacher->mailformat==1) {
					$htmlmail = $posthtml;
				}
				email_to_user($teacher, 'AutoAttendanceModule', $subject, $posttext, $htmlmail, $attachdata, $attachname);
			}
		}
	}
}


//
function  autoattendmod_send_email_user($courseid, $user, $subject, $posttext, $posthtml, $attachdata, $attachname)
{
	email_to_user($user, 'AutoAttendanceModule', $subject, $posttext, $posthtml, $attachdata, $attachname);
}



////////////////////////////////////////////////////////////////////////////////////////
//
//
//

function  autoattendmod_get_event($cm, $action, $params='', $info='')
{
	global $CFG;
	require_once($CFG->dirroot.'/blocks/autoattend/jbxl/jbxl_tools.php');
	require_once($CFG->dirroot.'/blocks/autoattend/jbxl/jbxl_moodle_tools.php');

	$ver = jbxl_get_moodle_version();

	$event = null;
	if (!is_array($params)) $params = array();

	if (floatval($ver)>=2.7) {
		$params = array(
			'context' => context_module::instance($cm->id),
			'other' => array('params' => $params, 'info'=> $info),
		);
		$event = \mod_autoattendmod\event\view_log::create($params);
	}

	// for Legacy add_to_log()		
	else {
		$file = 'view.php';
		$param_str = jbxl_get_url_params_str($params);
		//
		$event = new stdClass();
		$event->courseid= $cm->course;
		$event->name 	= 'autoattendmod'; 
		$event->action 	= $action;
		$event->url	 = $file.$param_str;
		$event->info   	= $info;
	}
	
	return $event;
}


