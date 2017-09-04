<?php
require('../../config.php');
include ("jpgraph-3.5.0b1/src/jpgraph.php");
include ("jpgraph-3.5.0b1/src/jpgraph_bar.php");
//phpinfo(); GD => yes
echo "<p name='cordetd' id='cordetd'>";
echo "<strong><h2 id='autrecours' style='display:none;'>Les coordonnées de l'étudiant :</h2></strong><br>";
$query="select distinct u.id,u.firstname,u.lastname, u.email,u.picture from mdl_user u,mdl_role_assignments r where u.id=r.userid and r.id ='".$_REQUEST["idGenres"]."'";
$coordonnesetd = $DB->get_record_sql($query);
 $studentobject = new stdClass;
  $studentobject->id = $coordonnesetd->id;
 $studentobject->picture = $coordonnesetd->picture;
// print_object($studentobject);
 $pictureheight = 50;
 $userpicture = $OUTPUT->user_picture($studentobject, array('size'=>$pictureheight, 'alttext'=>false, 'link'=>false));
 $picturearray = explode('"', $userpicture);
 //print_object($picturearray);
echo "<strong>Coordonnées étudiant</strong><br>";
echo "<table><tr><td><strong>Nom :</strong></td><td>$coordonnesetd->lastname</td>";
echo "<td><strong>Prénom :</strong></td><td>$coordonnesetd->firstname</td>";
echo "<td><strong>Email :</strong></td><td>$coordonnesetd->email</td></tr>";


   echo "<tr><td><image id='picture$coordonnesetd->id' x='60' y='50' width='50px' height='$pictureheight' src='$picturearray[1]' /></td></tr></table><br>"; 
   echo "<br><strong>Badges de cet étudiant pour ce cours</strong><br>";
   //get context
   $req = "SELECT contextid,userid  FROM mdl_role_assignments where id ='".$_REQUEST["idGenres"]."'";
   $context = $DB->get_record_sql($req);
   //get cours
   $reqcours = "SELECT instanceid FROM `mdl_context` where id ='".$context->contextid."'";
   $cours = $DB->get_record_sql($reqcours);
  $sql= "SELECT distinct b.id,b.name,b.attachment FROM mdl_badge_issued i , mdl_badge b where i.userid='".$coordonnesetd->id."' and b.courseid='".$cours->instanceid."'";
  //echo "$sql";
  $badgesetd = $DB->get_recordset_sql($sql); 
  //print_object($badgesetd);
  $count = 0;
  
    foreach ($badgesetd as $bd)
     {

     	  $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($cours->instanceid);
        $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $bd->id, '/', 'f1', false);
     	echo "<image width='50px' src='$imageurl' />&nbsp;&nbsp;&nbsp;";
     	$count++;
    }
  if($count == 0) 
  {
  	echo "Pas de badge";
  }
    echo "<br>";
   //Calcul temps
 //  $tb= report_stats_time_spent_in_course($cours->instanceid, $coordonnesetd->id, $timefrom, $timeto);
   //echo "$tb";
   
function report_stats_time_spent_in_course($courseid, $userid, $timefrom, $timeto) {
    global $DB;
    
    $inthiscourse = 0;
    $timespent = 0;
    $previoustime = 0;
    $timeout = 15 * 60;
    
    $sql = "SELECT time, course FROM mdl_log WHERE userid = $userid AND time >= $timefrom AND time <= ($timeto + 24 * 3600) ORDER BY time ASC";
    //echo "$sql<br/>";
    $useractions = $DB->get_recordset_sql($sql);
    unset($sql);
    
    foreach($useractions as $useraction) {
        if (($useraction->time - $previoustime) > $timeout) {
            if ($inthiscourse == 1) $timespent += $timeout;
            $inthiscourse = 0;
        }
        
        if ($inthiscourse == 0) {
            if ($useraction->course == $courseid) {
                $inthiscourse = 1;                
            }
        } else {
            if ($useraction->course == $courseid) {
                $timespent += ($useraction->time - $previoustime);                
            } else {
                $inthiscourse = 0;
                $timespent += $timeout;
            }
        }
        
        $previoustime = $useraction->time;
    }
    
    if ($inthiscourse == 1) {
        $timespent += $timeout;
    }
 
    return round($timespent / 60, 0);
}   
 
//ajout graphe  
echo "<br><strong>Activités de cet étudiant pour ce cours</strong><br>";
//echo "salma";
//echo "<img src='grapheme.php?id=$coordonnesetd->id'>";

echo "<img src='activitesetudiantgraphe.php?id=$coordonnesetd->id&amp;cours=$cours->instanceid'>";
echo "<br>";
//add tableau

echo "<table style='text-align:center'><tr>
<td bgcolor='#780D68'><FONT COLOR='black'>Date/heure</font></td>
<td></td>
<td></td>
<td bgcolor='#780D68'><FONT COLOR='black'>Section</font></td>
<td></td>
<td bgcolor='#780D68'><FONT COLOR='black'>Type de module</font></td>
<td bgcolor='#780D68'><FONT COLOR='black'>Module</font></td>
<td bgcolor='#780D68'><FONT COLOR='black'>Action</font></td>
</tr>";

$sqllog = "select time,module,action,cmid from mdl_log where course =$cours->instanceid and userid=$coordonnesetd->id and module !='calendar' and module !='user'";
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
			$log->action = "Création";
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
	//on v�rifie pour cmid page d'accueil ou nom section
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


 echo "</p>";
?>