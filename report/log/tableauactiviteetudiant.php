<?php
require('../../config.php');

echo "<table style='text-align:center'><tr>
<td bgcolor='#780D68'><FONT COLOR='white'>Date/heure</font></td>
<td></td>
<td></td>
<td bgcolor='#780D68'><FONT COLOR='white'>Section</font></td>
<td bgcolor='#780D68'><FONT COLOR='white'>Type de module</font></td>
<td bgcolor='#780D68'><FONT COLOR='white'>Module</font></td>
<td bgcolor='#780D68'><FONT COLOR='white'>Action</font></td>
</tr>";

$sqllog = "select time,module,action,cmid from mdl_log where course =2 and userid=12";
$resultlog = $DB->get_recordset_sql($sqllog);
foreach ($resultlog as $log)
{
	//echo "$log->cmid";
	$logtime = $log->time;
	$time = date('d/m/Y', $logtime).' &agrave; '.date('H:i:s', $logtime);
	//mdl_course_module
	$sql ="select section,module from mdl_course_modules where id = '".$log->cmid."'";
	$resultsql = $DB->get_record_sql($sql);
	//traduction $log->time
	if($log->cmid)
	{
	//mdl_course_section
	$sqlsection ="select name from mdl_course_sections where id = $resultsql->section";
	//echo "$sqlsection<br>";
	$resultatsection = $DB->get_record_sql($sqlsection);
	//add
		if($resultsql->module)
		{
				//mdl_modules
		$sqlmodule = "select name from mdl_modules where id = '".$resultsql->module."'";
		$resultmodules = $DB->get_record_sql($sqlmodule);
		//echo "$resultmodules->name<br>";
		$nametable = "mdl_".$resultmodules->name."";
		//echo "$nametable";
		$sqlnamemodule = "select name from $nametable";
		$resultmodulesname = $DB->get_record_sql($sqlnamemodule);
		//echo "$sqlnamemodule<br>";
		$n =get_string('pluginname', $log->module);
		//echo "$n";
		echo "<tr><td>$time</td><td></td><td></td><td>$resultatsection->name</td><td>$n</td><td>$resultmodulesname->name</td><td>$log->action</td></tr>";
		}
		else 
		{
		$n =get_string('pluginname', $log->module);
		echo "<tr><td>$time</td><td></td><td></td><td>$resultatsection->name</td><td>$n</td><td></td><td>$log->action</td></tr>";}
	
	}
	else 
	{
		
	if($resultsql->module)
		{
				//mdl_modules
		$sqlmodule = "select name from mdl_modules where id = '".$resultsql->module."'";
		$resultmodules = $DB->get_record_sql($sqlmodule);
		//echo "$resultmodules->name<br>";
		$nametable = "mdl_".$resultmodules->name."";
		//echo "$nametable";
		$sqlnamemodule = "select name from $nametable";
		$resultmodulesname = $DB->get_record_sql($sqlnamemodule);
		//echo "$sqlnamemodule<br>";
		$n =get_string('pluginname', $log->module);
		//echo "$n";
		echo "<tr><td>$time</td><td></td><td></td><td>Page d'accueil</td><td>$n</td><td>$resultmodulesname->name</td><td>$log->action</td></tr>";
		
		}
		else 
		{
			//$n =get_string('pluginname', $log->module);
			//echo "$n<br>";
		echo "<tr><td>$time</td><td></td><td></td><td>Page d'accueil</td><td>$log->module</td><td>-</td><td>$log->action</td></tr>";
		}
	}
	

//echo "<tr><td>$time</td><td></td><td>$log->module</td><td></td><td>$log->action</td></tr>";
	
}





echo "</table>";

?>