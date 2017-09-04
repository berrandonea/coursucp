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
 * Manage files in folder in private area.
 *
 * @package   core_user
 * @category  files
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once("$CFG->dirroot/user/files_form.php");
require_once("$CFG->dirroot/repository/lib.php");

require_login();
if (isguestuser()) {
    die();
}

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (empty($returnurl)) {
    $returnurl = new moodle_url('/user/files.php');
}

$context = context_user::instance($USER->id);
require_capability('moodle/user:manageownfiles', $context);

$title = get_string('privatefiles');
$struser = get_string('user');

$PAGE->set_url('/user/files.php');
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading(fullname($USER));
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('user-files');

$maxbytes = $CFG->userquota;
$maxareabytes = $CFG->userquota;
if (has_capability('moodle/user:ignoreuserquota', $context)) {
    $maxbytes = USER_CAN_IGNORE_FILE_SIZE_LIMITS;
    $maxareabytes = FILE_AREA_MAX_BYTES_UNLIMITED;
}

$data = new stdClass();
$data->returnurl = $returnurl;
$options = array('subdirs' => 1, 'maxbytes' => $maxbytes, 'maxfiles' => -1, 'accepted_types' => '*',
        'areamaxbytes' => $maxareabytes);
file_prepare_standard_filemanager($data, 'files', $options, $context, 'user', 'private', 0);

// Attempt to generate an inbound message address to support e-mail to private files.
$generator = new \core\message\inbound\address_manager();
$generator->set_handler('\core\message\inbound\private_files_handler');
$generator->set_data(-1);
$data->emaillink = $generator->generate($USER->id);

$mform = new user_files_form(null, array('data' => $data, 'options' => $options));

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($formdata = $mform->get_data()) {
    $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $context, 'user', 'private', 0);
    redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');

$mform->display();
echo $OUTPUT->box_end();
//Récap étudiant SALMA
$sqlrole ="SELECT distinct roleid FROM `mdl_role_assignments` where userid = $USER->id";
$resultrole = $DB->get_recordset_sql($sqlrole);
foreach ($resultrole as $role)
{
	//if($role->roleid)
	//echo "$role->roleid<br>";
	if($role->roleid == 5) 
	{
		echo "<h1>Récapitulatif des copies rendues</h1>";
		
	// Tri sur colonne
$tri = "id ASC";
$liste_tri = array('id', 'fullname', 'firstname', 'email');
if(isset($_GET['tri']) && in_array($_GET['tri'], $liste_tri))
{
	switch($_GET['tri'])
	{
		case 'id':
		$tri = ' '.$_GET['tri'].' DESC';
		break;
		case 'fullname':
		$tri = ' '.$_GET['tri'].' ASC';
		break;
		default:
		$tri = ' '.$_GET['tri'].' DESC';
		break;
	}
}
$sql = "SELECT distinct c.fullname,c.id
                                    FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c, mdl_course_categories z
                                    WHERE u.id = r.userid
                                    AND r.contextid = cx.id
                                    AND cx.instanceid = c.id
                                    and r.roleid =5
                                    AND cx.contextlevel =50  
                                    AND c.category = z.id
                                    AND u.id =$USER->id
                                     order by $tri";
		$result = $DB->get_recordset_sql($sql);
		//echo "<table><tr><td><a href ='files.php?tri=fullname'><strong>Intitulé du cours</strong></a></td><td><strong>Activités</strong></td></tr>";
		echo "<table><tr><th>Intitulé du cours</th><th colspan =2>Module</th><th>Etat</th><th>Date butoir</th><th>Travail rendu</th><th>Lien vers l'activité</th></tr>";
		foreach ($result as $cours)
		{
			//Devoir
			$countassign = "select count(distinct id) as nbr from mdl_assign where course =$cours->id";
			$resultcountassign= $DB->get_record_sql($countassign);
			$sommedevoir = $resultcountassign->nbr;
			
			//Atelier
			$countworkshop = "select count(distinct id) as nbr from mdl_workshop where course =$cours->id";
			$resultcountworkshop= $DB->get_record_sql($countworkshop);		
			$sommeatelier = $resultcountworkshop->nbr;
			
			//QCM
			$countquiz = "select count(distinct id) as nbr from mdl_quiz where course =$cours->id";
			$resultcountquiz= $DB->get_record_sql($countquiz);
			$sommequiz = $resultcountquiz->nbr;
			
			//Forum
			$countforum = "select count(distinct id) as nbr from mdl_forum where course =$cours->id";
			$resultcountforum= $DB->get_record_sql($countforum);
			$sommeforum = $resultcountforum->nbr;
			
			//Rowspan
			$somme = $sommedevoir+$sommeatelier+$sommequiz+$sommeforum;
			echo "<tr><td rowspan=$somme><strong>$cours->fullname</strong></td>";
			
			//devoir
			//$sqldevoir ="select distinct module from mdl_log where course = $cours->id and module = 'assign'";
			$sqldevoir = "select  id from mdl_assign where course = $cours->id";
			$resultdevoir = $DB->get_record_sql($sqldevoir);
			$existdevoirs =$DB->record_exists_sql($sqldevoir);
			
			//if($resultdevoir->module)
			if($existdevoirs)
				{
				echo "<TH ROWSPAN=$sommedevoir BGCOLOR=#780D68><font color='white'><strong>Devoir</strong></font></th>";
				$sqlnamedevoir = "SELECT distinct(id) as identifiant, name FROM mdl_assign where course = $cours->id";
				$resultnamedevoir = $DB->get_recordset_sql($sqlnamedevoir);
				foreach ($resultnamedevoir as $devoir)
				{
					echo "<td>$devoir->name</td>";
					//Devoir rendu
					$sqldevoirredu = "SELECT distinct s.id FROM mdl_assign_submission s , mdl_assign a where  s.assignment=$devoir->identifiant and course = $cours->id and s.userid = $USER->id";
					$resultdevoirrendu = $DB->get_recordset_sql($sqldevoirredu);
					$existdevoir =$DB->record_exists_sql($sqldevoirredu);
					if($existdevoir)
					{
						echo " <td><img src = 'images/yes.png' height='25' width='25'></td>";
					}
					else 
					{
						echo "<td><img src = 'images/no.png' height='25' width='25'></td>";
					}
					$sqldatebutoirdevoir = "select duedate from mdl_assign where course = $cours->id and id = $devoir->identifiant";
					$resultdatebutoirdevoir = $DB->get_record_sql($sqldatebutoirdevoir);
					//Convert date
					$datedevoir = date('d/m/Y', $resultdatebutoirdevoir->duedate);
					$heuredevoir = date('H:i:s', $resultdatebutoirdevoir->duedate);
					if($resultdatebutoirdevoir->duedate)
					{
						echo "<td>$datedevoir à $heuredevoir</td> ";
					}
					else
					{
						echo "<td>-</td>";
					}	
					//Download
					$sqllink = "SELECT distinct f.id, f.contextid, f.component, f.itemid, f.filename, f.userid 
								FROM mdl_files f , mdl_assign a , mdl_assignsubmission_file  s
								where f.itemid= s.submission
								and s.assignment = $devoir->identifiant
								and  f.component='assignsubmission_file'
								and a.course = $cours->id
							    and f.filename not like '.%'
							    and f.userid = $USER->id";
					$resultlinkdevoir = $DB->get_recordset_sql($sqllink);
					$existdevoir =$DB->record_exists_sql($sqllink);
						if($existdevoir)
						{
							echo "<td>";
							foreach($resultlinkdevoir as $resultlink)
							{
								//txt
								if( strstr($resultlink->filename, ".txt"))
								 { 
								 	echo "<a href = '$CFG->wwwroot/pluginfile.php/$resultlink->contextid/assignsubmission_file/submission_files/$resultlink->itemid/$resultlink->filename?forcedownload=1'><img src = 'images/txt.png' height='30' width='30'>&nbsp;&nbsp;$resultlink->filename</a><br>";
								 }
								 //pdf
								 elseif(strstr($resultlink->filename, ".pdf"))
								{
								 echo "<a href = '$CFG->wwwroot/pluginfile.php/$resultlink->contextid/assignsubmission_file/submission_files/$resultlink->itemid/$resultlink->filename?forcedownload=1'><img src = 'images/pdf.png' height='30' width='30'>&nbsp;&nbsp;$resultlink->filename</a><br>";
								}
								 //docx
								elseif(strstr($resultlink->filename, ".docx"))
								{
								 echo "<a href = '$CFG->wwwroot/pluginfile.php/$resultlink->contextid/assignsubmission_file/submission_files/$resultlink->itemid/$resultlink->filename?forcedownload=1'><img src = 'images/docx.png' height='30' width='30'>&nbsp;&nbsp;$resultlink->filename</a><br>";
								}
								 //doc
								elseif(strstr($resultlink->filename, ".doc"))
								{
								 echo "<a href = '$CFG->wwwroot/pluginfile.php/$resultlink->contextid/assignsubmission_file/submission_files/$resultlink->itemid/$resultlink->filename?forcedownload=1'><img src = 'images/docx.png' height='30' width='30'>&nbsp;&nbsp;$resultlink->filename</a><br>";
								}
								 //excel
								elseif(strstr($resultlink->filename, ".xlsx"))
								{
								 echo "<a href = '$CFG->wwwroot/pluginfile.php/$resultlink->contextid/assignsubmission_file/submission_files/$resultlink->itemid/$resultlink->filename?forcedownload=1'><img src = 'images/excel.png' height='30' width='30'>&nbsp;&nbsp;$resultlink->filename</a><br>";
								}
								 //pptx
								elseif(strstr($resultlink->filename, ".pptx"))
								{
								 echo "<a href = '$CFG->wwwroot/pluginfile.php/$resultlink->contextid/assignsubmission_file/submission_files/$resultlink->itemid/$resultlink->filename?forcedownload=1'><img src = 'images/ppt.png' height='30' width='30'>&nbsp;&nbsp;$resultlink->filename</a><br>";
								}
								 
								 else 
								 {	
							echo "<a href = '$CFG->wwwroot/pluginfile.php/$resultlink->contextid/assignsubmission_file/submission_files/$resultlink->itemid/$resultlink->filename?forcedownload=1'><img src = 'images/file.png' height='30' width='30'>&nbsp;&nbsp;$resultlink->filename</a><br>";
								 }
								 }
							echo "</td>";
							
						}
						else 
						{
							echo "<td>-</td>";
						}
						//lien vers l'activité 
						$sqllienactivitedevoir = "select distinct c.id from mdl_assign a , mdl_course_modules c where c.instance = $devoir->identifiant and c.module = 1 and c.course = $cours->id";
						$resultlienactivitedevoir = $DB->get_record_sql($sqllienactivitedevoir);
						if($resultlienactivitedevoir->id)
						{
							echo "<td><a href = '$CFG->wwwroot/mod/assign/view.php?id=$resultlienactivitedevoir->id'>Lien activité devoir</a></td>";
						}
						else 
						{
							echo "<td>-</td>";
						}
						
					
					echo "</tr>";
				}
			}
			
			//atelier
			//$sqlatelier ="select distinct module from mdl_log where course = $cours->id and module = 'workshop'";
			$sqlatelier = "SELECT id FROM mdl_workshop where course = $cours->id";
			$resultatelier = $DB->get_record_sql($sqlatelier);
			$existworkshops =$DB->record_exists_sql($sqlatelier);
			//if($resultatelier->module)
			if($existworkshops)
				{
				echo "<TH ROWSPAN=$sommeatelier BGCOLOR=#780D68><font color='white'><strong>Atelier</strong></font></th>";
				$sqlnameatelier = "SELECT distinct(id) as identifiant, name FROM mdl_workshop where course = $cours->id";
				$resultnameatelier = $DB->get_recordset_sql($sqlnameatelier);
				foreach ($resultnameatelier as $atelier)
				{
					echo "<td>$atelier->name</td>";
					//atelier rendu
					$sqlatelierrendu = "select distinct s.id  from mdl_workshop w , mdl_workshop_submissions s where s.workshopid = $atelier->identifiant and w.course = $cours->id and s.authorid = $USER->id";
					$resultdevoirrendu = $DB->get_record_sql($sqlatelierrendu);
					$existatelier =$DB->record_exists_sql($sqlatelierrendu);
					if($existatelier)
					{
						echo " <td><img src = 'images/yes.png' height='25' width='25'></td>";
					}
					else 
					{
						echo "<td><img src = 'images/no.png' height='25' width='25'></td>";
					}
					//Date
					$sqldateatelier = "select submissionend from mdl_workshop where id = $atelier->identifiant and course = $cours->id";
					$resultdateatelier = $DB->get_record_sql($sqldateatelier);
					//Convert date
					$dateatelier = date('d/m/Y', $resultdateatelier->submissionend);
					$heureatelier = date('H:i:s', $resultdateatelier->submissionend);
					if($resultdateatelier->submissionend)
					{
						echo "<td>$dateatelier à $heureatelier</td> ";
					}
					else 
					{
							echo "<td>-</td> ";
					}
					
					//Download
					$sqllinkatelier = "SELECT distinct f.id, f.contextid, f.component, f.itemid, f.filename, f.userid 
								FROM mdl_files f , mdl_workshop w , mdl_workshop_submissions  s
								where f.itemid= s.id
								and s.workshopid = $atelier->identifiant
								and w.course =$cours->id
								and  f.component='mod_workshop'
								and f.filearea = 'submission_attachment'
							    and f.filename not like '.%'
							    and f.userid = $USER->id";
					$resultlinkatelier = $DB->get_recordset_sql($sqllinkatelier);
					$existworkshop =$DB->record_exists_sql($sqllinkatelier);
					if($existworkshop)
					{
						echo "<td>";
						foreach ($resultlinkatelier as $linkworkshop)
						{
							//txt
							if( strstr($linkworkshop->filename, ".txt"))
								 { 
								 	echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkworkshop->contextid/mod_workshop/submission_attachment/$linkworkshop->itemid/$linkworkshop->filename?forcedownload=1'><img src = 'images/txt.png' height='30' width='30'>&nbsp;&nbsp;$linkworkshop->filename</a><br>";
								 }
							//pdf
								 elseif(strstr($linkworkshop->filename, ".pdf"))
								{
							echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkworkshop->contextid/mod_workshop/submission_attachment/$linkworkshop->itemid/$linkworkshop->filename?forcedownload=1'><img src = 'images/pdf.png' height='30' width='30'>&nbsp;&nbsp;$linkworkshop->filename</a><br>";
								}
							//docx
								elseif(strstr($linkworkshop->filename, ".docx"))
								{
							echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkworkshop->contextid/mod_workshop/submission_attachment/$linkworkshop->itemid/$linkworkshop->filename?forcedownload=1'><img src = 'images/docx.png' height='30' width='30'>&nbsp;&nbsp;$linkworkshop->filename</a><br>";
								}
							//doc
								elseif(strstr($linkworkshop->filename, ".doc"))
								{
							echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkworkshop->contextid/mod_workshop/submission_attachment/$linkworkshop->itemid/$linkworkshop->filename?forcedownload=1'><img src = 'images/docx.png' height='30' width='30'>&nbsp;&nbsp;$linkworkshop->filename</a><br>";
								}
							//excel
								elseif(strstr($linkworkshop->filename, ".xlsx"))
								{
							echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkworkshop->contextid/mod_workshop/submission_attachment/$linkworkshop->itemid/$linkworkshop->filename?forcedownload=1'><img src = 'images/excel.png' height='30' width='30'>&nbsp;&nbsp;$linkworkshop->filename</a><br>";
								}
							//pptx
								elseif(strstr($linkworkshop->filename, ".pptx"))
								{
							echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkworkshop->contextid/mod_workshop/submission_attachment/$linkworkshop->itemid/$linkworkshop->filename?forcedownload=1'><img src = 'images/ppt.png' height='30' width='30'>&nbsp;&nbsp;$linkworkshop->filename</a><br>";
								}
								 else 
								 {
							echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkworkshop->contextid/mod_workshop/submission_attachment/$linkworkshop->itemid/$linkworkshop->filename?forcedownload=1'><img src = 'images/file.png' height='30' width='30'>&nbsp;&nbsp;$linkworkshop->filename</a><br>";
								 }
								 
								 }
						echo "</td>";
					}	
					else 
					{
						echo "<td>-</td>";
					}	
					//lien vers l'activité 
					$sqllienactiviteatelier = "SELECT DISTINCT c.id FROM mdl_workshop a, mdl_course_modules c WHERE c.instance = $atelier->identifiant AND c.module =22 AND c.course =$cours->id";
					$resultlienactiviteatelier = $DB->get_record_sql($sqllienactiviteatelier);
					if($resultlienactiviteatelier->id)
					{
						echo "<td><a href = '$CFG->wwwroot/mod/workshop/view.php?id=$resultlienactiviteatelier->id'>Lien activité atelier</a></td>";
					}
					else 
					{
						echo "<td>-</td>";	
					}
								
					echo "</tr>";
				}
			}
			//Quiz
			//$sqlquiz ="select distinct module from mdl_log where course = $cours->id and module = 'quiz'";
			$sqlquiz = "SELECT id  FROM mdl_quiz where course = $cours->id";
			$resultquiz = $DB->get_record_sql($sqlquiz);
			$existquizs =$DB->record_exists_sql($sqlquiz);
			//if($resultquiz->module)
			if($existquizs)
				{
				echo "<TH ROWSPAN=$sommequiz BGCOLOR=#780D68><font color='white'><strong>Quiz</strong></font></th>";
				$sqlnamequiz = "SELECT distinct(id) as identifiant, name FROM mdl_quiz where course = $cours->id";
				$resultnamequiz = $DB->get_recordset_sql($sqlnamequiz);
				foreach ($resultnamequiz as $quiz)
				{
					echo "<td>$quiz->name</td>";
					//Quiz rendu
					$sqlquizrendu = "SELECT distinct a.quiz as count FROM mdl_quiz_attempts a, mdl_quiz q where a.quiz =$quiz->identifiant  and q.course = $cours->id and a.userid = $USER->id and a.state = 'finished'";
					$existquiz =$DB->record_exists_sql($sqlquizrendu);
					if($existquiz)
					{
						echo " <td><img src = 'images/yes.png' height='25' width='25'></td>";
					}
					else 
					{
						echo "<td><img src = 'images/no.png' height='25' width='25'></td>";
					}
					//date
					$sqldatequiz = "select timeclose from mdl_quiz where id = $quiz->identifiant and course = $cours->id";
					$resultdatequiz = $DB->get_record_sql($sqldatequiz);
					//Convert date
					$datequiz = date('d/m/Y', $resultdatequiz->timeclose);
					$heurequiz = date('H:i:s', $resultdatequiz->timeclose);
				if($resultdatequiz->timeclose)
					{
						echo "<td>$datequiz à $heurequiz</td> ";
					}
					else 
					{
							echo "<td>-</td> ";
					}
					
					//Download
					$sqllinkquiz = "select distinct qas.questionattemptid ,qas.userid, qa.questionid, qa.questionusageid, qua.quiz, s.slot,f.itemid, quiz.course,f.filename,f.contextid,qua.uniqueid
								from
								mdl_question_attempt_steps qas , mdl_files f, mdl_question_attempts qa, mdl_quiz_attempts qua, mdl_quiz_slots s, mdl_quiz quiz
								
								where f.itemid = qas.id
								and qas.userid= $USER->id
								and qa.id = qas.questionattemptid
								and qua.uniqueid = qa.questionusageid
								and qua.quiz = $quiz->identifiant
								and s.questionid = qa.questionid
								and  f.component='question'
								and  f.filearea = 'response_attachments'
								 and f.filename not like '.%'
								and quiz.course = $cours->id";
					$resultlinkquiz = $DB->get_recordset_sql($sqllinkquiz);
					$existquiz =$DB->record_exists_sql($sqllinkquiz);
					if($existquiz)
					{
						echo "<td>";
						foreach ($resultlinkquiz as $linkquiz)
						{
							//txt
							if( strstr($linkquiz->filename, ".txt"))
								 { 
								 echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkquiz->contextid/question/response_attachments/$linkquiz->uniqueid/$linkquiz->slot/$linkquiz->itemid/$linkquiz->filename?forcedownload=1'><img src = 'images/txt.png' height='30' width='30'>&nbsp;&nbsp;$linkquiz->filename</a><br>";	
								 }
							 //pdf
							elseif(strstr($linkquiz->filename, ".pdf"))
								{
								echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkquiz->contextid/question/response_attachments/$linkquiz->uniqueid/$linkquiz->slot/$linkquiz->itemid/$linkquiz->filename?forcedownload=1'><img src = 'images/pdf.png' height='30' width='30'>&nbsp;&nbsp;$linkquiz->filename</a><br>";
								}
							//docx
							elseif(strstr($linkquiz->filename, ".docx"))
								{
								echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkquiz->contextid/question/response_attachments/$linkquiz->uniqueid/$linkquiz->slot/$linkquiz->itemid/$linkquiz->filename?forcedownload=1'><img src = 'images/docx.png' height='30' width='30'>&nbsp;&nbsp;$linkquiz->filename</a><br>";
								}
							//doc
							elseif(strstr($linkquiz->filename, ".doc"))
								{
									echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkquiz->contextid/question/response_attachments/$linkquiz->uniqueid/$linkquiz->slot/$linkquiz->itemid/$linkquiz->filename?forcedownload=1'><img src = 'images/docx.png' height='30' width='30'>&nbsp;&nbsp;$linkquiz->filename</a><br>";
								}	
							//excel
							elseif(strstr($linkquiz->filename, ".xlsx"))
								{
								echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkquiz->contextid/question/response_attachments/$linkquiz->uniqueid/$linkquiz->slot/$linkquiz->itemid/$linkquiz->filename?forcedownload=1'><img src = 'images/excel.png' height='30' width='30'>&nbsp;&nbsp;$linkquiz->filename</a><br>";
								}	
							//pptx
							elseif(strstr($linkquiz->filename, ".pptx"))
								{
								echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkquiz->contextid/question/response_attachments/$linkquiz->uniqueid/$linkquiz->slot/$linkquiz->itemid/$linkquiz->filename?forcedownload=1'><img src = 'images/ppt.png' height='30' width='30'>&nbsp;&nbsp;$linkquiz->filename</a><br>";
								}
							else 
							{
							echo "<a href = '$CFG->wwwroot/pluginfile.php/$linkquiz->contextid/question/response_attachments/$linkquiz->uniqueid/$linkquiz->slot/$linkquiz->itemid/$linkquiz->filename?forcedownload=1'><img src = 'images/file.png' height='30' width='30'>&nbsp;&nbsp;$linkquiz->filename</a><br>";
							}
							}
						echo "</td>";
					}
				else 
					{
						echo "<td>-</td>";
					}
				//lien vers l'activité 
					$sqllienactivitequiz = "SELECT DISTINCT c.id FROM mdl_quiz a, mdl_course_modules c WHERE c.instance = $quiz->identifiant AND c.module =16 AND c.course =$cours->id";
					$resultlienactivitequiz = $DB->get_record_sql($sqllienactivitequiz);
					if($resultlienactivitequiz->id)
					{
						echo "<td><a href = '$CFG->wwwroot/mod/quiz/view.php?id=$resultlienactivitequiz->id'>Lien activité test</a></td>";
					}
					else 
					{
						echo "<td>-</td>";	
					}
					echo "</tr>";
				}
				
			//Forum
			//$sqlforum ="select distinct module from mdl_log where course = $cours->id and module = 'forum'";
			$sqlforum = "SELECT id FROM mdl_forum where course = $cours->id";
			$resultforum = $DB->get_record_sql($sqlquiz);
			$existforums =$DB->record_exists_sql($sqlforum);
			//if($resultforum->module)
			if($existforums)
				{
					echo "<TH ROWSPAN=$sommeforum BGCOLOR=#780D68><font color='white'><strong>Forum</strong></font></th>";
					$sqlnameforum = "SELECT distinct(id) as identifiant, name FROM mdl_forum where course = $cours->id";
					$resultnameforum = $DB->get_recordset_sql($sqlnameforum);
					foreach ($resultnameforum as $forum)
					{
						echo "<td>$forum->name</td>";
						//Forum discussion
						$sqlforumdiscussion = "SELECT distinct(d.id) FROM mdl_forum_discussions d , mdl_forum f where f.course = $cours->id and d.forum = $forum->identifiant and d.userid= $USER->id";
						$existforum =$DB->record_exists_sql($sqlforumdiscussion);
						if($existforum)
						{
							echo " <td><img src = 'images/yes.png' height='25' width='25'></td>";
						}
						else 
						{
							echo "<td><img src = 'images/no.png' height='25' width='25'></td>";
						}
						//Date
						$sqldateforum = "select assesstimefinish from mdl_forum where course = $cours->id and id = $forum->identifiant";
						$resultdateforum = $DB->get_record_sql($sqldateforum);
						//Convert date
						$dateforum = date('d/m/Y', $resultdateforum->assesstimefinish);
						$heureforum = date('H:i:s', $resultdateforum->assesstimefinish);
						if($resultdateforum->assesstimefinish)
						{
							echo "<td>$dateforum à $heureforum</td> ";
						}
						else 
						{
							echo "<td>-</td> ";
						}
						//Link
						$sqllinkforum = "SELECT DISTINCT f.id, f.contextid, f.component, f.itemid, f.filename, f.userid
										FROM mdl_files f, mdl_forum m, mdl_forum_discussions d, mdl_forum_posts p
										WHERE d.forum = $forum->identifiant
										AND d.id = p.discussion
										AND p.id = f.itemid
										AND m.course =$cours->id
										AND f.component =  'mod_forum'
										AND f.filename NOT LIKE  '.%'
										AND p.userid =$USER->id";
						$resultlinkforum = $DB->get_recordset_sql($sqllinkforum);
						$existforumdiscussion =$DB->record_exists_sql($sqllinkforum);
						if($existforumdiscussion)
						{
							echo "<td>";
							foreach($resultlinkforum as $linkforum)
							{
								//txt
								if( strstr($linkforum->filename, ".txt"))
								 { 
									echo "<a href='$CFG->wwwroot/pluginfile.php/$linkforum->contextid/mod_forum/attachment/$linkforum->itemid/$linkforum->filename'><img src = 'images/txt.png' height='30' width='30'>&nbsp;&nbsp;$linkforum->filename</a>";
								}
								//pdf
								elseif(strstr($linkforum->filename, ".pdf"))
								{
									echo "<a href='$CFG->wwwroot/pluginfile.php/$linkforum->contextid/mod_forum/attachment/$linkforum->itemid/$linkforum->filename'><img src = 'images/pdf.png' height='30' width='30'>&nbsp;&nbsp;$linkforum->filename</a>";
								}
								//docx
								elseif(strstr($linkforum->filename, ".docx"))
								{
									echo "<a href='$CFG->wwwroot/pluginfile.php/$linkforum->contextid/mod_forum/attachment/$linkforum->itemid/$linkforum->filename'><img src = 'images/docx.png' height='30' width='30'>&nbsp;&nbsp;$linkforum->filename</a>";
								}
								//doc
								elseif(strstr($linkforum->filename, ".doc"))
								{
									echo "<a href='$CFG->wwwroot/pluginfile.php/$linkforum->contextid/mod_forum/attachment/$linkforum->itemid/$linkforum->filename'><img src = 'images/docx.png' height='30' width='30'>&nbsp;&nbsp;$linkforum->filename</a>";
								}
								//excel
								elseif(strstr($linkforum->filename, ".xlsx"))
								{
									echo "<a href='$CFG->wwwroot/pluginfile.php/$linkforum->contextid/mod_forum/attachment/$linkforum->itemid/$linkforum->filename'><img src = 'images/excel.png' height='30' width='30'>&nbsp;&nbsp;$linkforum->filename</a>";
								}
								//pptx
								elseif(strstr($linkforum->filename, ".pptx"))
								{
									echo "<a href='$CFG->wwwroot/pluginfile.php/$linkforum->contextid/mod_forum/attachment/$linkforum->itemid/$linkforum->filename'><img src = 'images/ppt.png' height='30' width='30'>&nbsp;&nbsp;$linkforum->filename</a>";
								}
								else 
								{
								echo "<a href='$CFG->wwwroot/pluginfile.php/$linkforum->contextid/mod_forum/attachment/$linkforum->itemid/$linkforum->filename'><img src = 'images/file.png' height='30' width='30'>&nbsp;&nbsp;$linkforum->filename</a>";
								}
								
								}
							echo "</td>";
						}
						else 
						{
							echo "<td>-</td> ";
						}
						
					//lien vers l'activité 
					$sqllienactiviteforum = "SELECT DISTINCT c.id FROM mdl_forum a, mdl_course_modules c WHERE c.instance = $forum->identifiant AND c.module =9 AND c.course =$cours->id";
					$resultlienactiviteforum = $DB->get_record_sql($sqllienactiviteforum);
					if($resultlienactiviteforum->id)
					{
						echo "<td><a href = '$CFG->wwwroot/mod/forum/view.php?id=$resultlienactiviteforum->id'>Lien activité forum</a></td>";
					}
					else 
					{
						echo "<td>-</td>";	
					}
						echo "</tr>";
					}
					
				}
					
				}
			//}
			//echo "</tr>";
			
		}//foreach cours
		//echo "</tr>";
		echo "</table>";
	/*foreach($result as $cours)
	{
		$countassign = "select count(distinct id) as nbr from mdl_assign where course =$cours->id";
		$resultcountassign= $DB->get_record_sql($countassign);
		
		$countworkshop = "select count(distinct id) as nbr from mdl_workshop where course =$cours->id";
		$resultcountworkshop= $DB->get_record_sql($countworkshop);
		
		$rowassign = $resultcountassign->nbr;
		$rowworkshop = $resultcountworkshop->nbr;
		
		echo "<tr><td rowspan=$row>$cours->fullname</td>";
				
				$moduledevoir = "select distinct name from mdl_assign where course = $cours->id";
				$resultmoduledevoir = $DB->get_recordset_sql($moduledevoir);
				echo "<td>";
				foreach ($resultmoduledevoir as $devoir)
				{
					echo "$devoir->name<br>";
				}
			//echo "</td>";
			
			$moduleatelier = "select distinct name from mdl_workshop where course = $cours->id";
				$resultmoduleatelier = $DB->get_recordset_sql($moduleatelier);
				//echo "<td>";
				foreach ($resultmoduleatelier as $atelier)
				{
					echo "$atelier->name<br>";
				}
			echo "</td>";
			
			
		echo "</tr>";
	}*/
	
		// Tri sur colonne
	
		
	}
}

echo $OUTPUT->footer();
