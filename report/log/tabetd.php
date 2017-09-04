<?php
require('../../config.php');

echo "<table style='text-align:center'><tr>
<td bgcolor='#780D68'><FONT COLOR='white'>Date/heure</font></td>
<td></td>
<td></td>
<td bgcolor='#780D68'><FONT COLOR='white'>Section</font></td>
<td></td>
<td bgcolor='#780D68'><FONT COLOR='white'>Type de module</font></td>
<td bgcolor='#780D68'><FONT COLOR='white'>Module</font></td>
<td bgcolor='#780D68'><FONT COLOR='white'>Action</font></td>
</tr>";

$sqllog = "select time,module,action,cmid from mdl_log where course =2 and userid=12 and module !='calendar' and module !='user'";
$resultlog = $DB->get_recordset_sql($sqllog);
foreach ($resultlog as $log)
{
	//traduction action
	switch($log->action)
	{
		case "view";
			$log->action = "Consultation";
			break;
		case "add";
			$log->action = "CrÃ©ation";
			break;
		case "editsection";
			$log->action ="Editer une section";
			break;
		case "add mod";
			$log->action ="Ajouter un module";
			break;
	}
	
	
	$logtime = $log->time;
	$time = date('d/m/Y', $logtime).' &agrave; '.date('H:i:s', $logtime);
	$n =get_string('pluginname', $log->module);
	//on vérifie pour cmid page d'accueil ou nom section
	if($log->cmid)
	{
		//on test si c'est un module
			$sql ="select section,module from mdl_course_modules where id = '".$log->cmid."'";
			$resultsql = $DB->get_record_sql($sql);
			$sqlsection ="select name from mdl_course_sections where id = $resultsql->section";
			$resultatsection = $DB->get_record_sql($sqlsection);
			if($resultatsection->name)
			{
				//add
				$sqlmodule = "select name from mdl_modules where id = '".$resultsql->module."'";
				$resultmodules = $DB->get_record_sql($sqlmodule);
				//echo "$resultmodules->name<br>";
				$nametable = "mdl_".$resultmodules->name."";
				//echo "$nametable";
				$sqlnamemodule = "select name from $nametable";
				$resultmodulesname = $DB->get_record_sql($sqlnamemodule);
		
				echo "<tr><td>$time</td><td></td><td></td><td>$resultatsection->name</td><td></td><td>$n</td><td>$resultmodulesname->name</td><td>$log->action</td></tr>";
			}
			else 
			{
				//add
				$sqlmodule = "select name from mdl_modules where id = '".$resultsql->module."'";
				$resultmodules = $DB->get_record_sql($sqlmodule);
				//echo "$resultmodules->name<br>";
				$nametable = "mdl_".$resultmodules->name."";
				//echo "$nametable";
				$sqlnamemodule = "select name from $nametable";
				$resultmodulesname = $DB->get_record_sql($sqlnamemodule);
				
				echo "<tr><td>$time</td><td></td><td></td><td>Section</td><td></td><td>$n</td><td>$resultmodulesname->name</td><td>$log->action</td></tr>";
			}
	}
	else 
	{
		//on test si c'est un module
		//add
				
	echo "<tr><td>$time</td><td></td><td></td><td>Page d'accueil</td><td></td><td>Cours</td><td>-</td><td>$log->action</td></tr>";
		
	}
	
	
	
	
}



?>