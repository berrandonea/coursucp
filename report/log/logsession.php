<?php
//require('../../config.php');
define('CLI_SCRIPT', true);
require('config.php');

//On vérifie les données de la table mdl_session
$sqlsession = "SELECT userid, timecreated,timemodified FROM mdl_sessions";
$resultsession = $DB->get_recordset_sql($sqlsession);
foreach ($resultsession as $session)
     {
     	//On vérifie les enregistrements dans la table mdl_log_session
     	$sqllogsession = "SELECT distinct id,userid ,  datesession ,  timecreated ,  timemodified, diff FROM  mdl_log_session where userid = $session->userid order by id desc limit 1";
     	$reslogsession = $DB->get_record_sql($sqllogsession);
     	//on trouve des enregistrements
     	if($reslogsession)
     	{
     		$datetime = date('m/d/Y', time());
     		echo "$datetime<br>";
     		$datesession = date('m/d/Y', $reslogsession->datesession);
     		echo "$datesession<br>";
     		//on vérifie si datesession est aujourd'hui
     		//if($reslogsession->datesession == time())   
     		if($datetime == $datesession) 
     		{
     			//si datecreated est la meme
     			if($reslogsession->timecreated == $session->timecreated)
     			{
     				//datemodified changed
     				if($reslogsession->timemodified != $session->timemodified)
     				{
     					$diff1=$reslogsession->timemodified - $reslogsession->timecreated;
     					echo "$diff1<br>";
     					$diffsession = $session->timemodified - $session->timecreated;
     					echo "$diffsession<br>";
     					echo "$reslogsession->diff<br>";
     					if($reslogsession->diff == $diff1)
     					{
     						echo "cas c le diff exacte";
     						//on supprime
		     				/*echo "datemodified changed<br>";
		     				$sqldelete ="delete from mdl_log_session where timecreated = $reslogsession->timecreated and timemodified = $reslogsession->timemodified and userid =$reslogsession->userid";
		     				echo "$sqldelete";
		     				$DB->execute($sqldelete);
		     			    $diff = $session->timemodified - $session->timecreated;
		     				$insertlogsession ="INSERT INTO mdl_log_session(userid, datesession, timecreated, timemodified, diff) VALUES ($session->userid, unix_timestamp(NOW()), $session->timecreated, $session->timemodified, $diff)";
				      		$DB->execute($insertlogsession);*/
     						$reqsession = "SELECT userid, timecreated,timemodified FROM mdl_sessions where userid =$reslogsession->userid";
     						$resultreqsession = $DB->get_record_sql($reqsession);
     						
     						//update time created
		     				$updatetimecreated ="update mdl_log_session set timecreated = $resultreqsession->timecreated where id =$reslogsession->id";
		     				$DB->execute($updatetimecreated);
		     				
		     				//update timemodified
		     				$updatetimemodified ="update mdl_log_session set timemodified = $resultreqsession->timemodified where id =$reslogsession->id";
		     				$DB->execute($updatetimemodified);
		     				
		     				//update diff
		     				$newdiff = $resultreqsession->timemodified - $resultreqsession->timecreated;
		     				$updatediff ="update mdl_log_session set diff = $newdiff where id =$reslogsession->id";
		     				$DB->execute($updatediff);
		     				
     					}
     					else 
     					{
     						echo "cas c le diff pas exacte";
     						$reqsession = "SELECT userid, timecreated,timemodified FROM mdl_sessions where userid =$reslogsession->userid";
     						$resultreqsession = $DB->get_record_sql($reqsession);
     						
     						//update time created
		     				$updatetimecreated ="update mdl_log_session set timecreated = $resultreqsession->timecreated where id =$reslogsession->id";
		     				$DB->execute($updatetimecreated);
		     				
		     				//update timemodified
		     				$updatetimemodified ="update mdl_log_session set timemodified = $resultreqsession->timemodified where id =$reslogsession->id";
		     				$DB->execute($updatetimemodified);
		     				
		     				//update diff
		     				$totaldiff = $diff1 + $reslogsession->diff;
		     				$updatediff ="update mdl_log_session set diff = $totaldiff where id =$reslogsession->id";
		     				$DB->execute($updatediff);
     						
     					}
     				
     				}
     			}// if($reslogsession->timecreated == $session->timecreated)
     			//time created changed on fait la somme des diff 
     			else 
     			{
     				echo "ce cas";
     				//enregistrement d'aujourd'hui
     				$sqlnewtimecreated ="select id,timecreated , timemodified, diff, datesession from mdl_log_session where userid=$session->userid order by id desc limit 1";
     				$resultnewtimecreated = $DB->get_record_sql($sqlnewtimecreated);
     				echo "$sqlnewtimecreated<br>";
     				echo "$resultnewtimecreated->timecreated<br>";
     				echo "$resultnewtimecreated->timemodified<br>";
     				echo "$resultnewtimecreated->diff<br>";
     				//on récupère enregis dans la table session
     				$sqlsessiontimecreated = "SELECT distinct userid ,id,  timecreated ,  timemodified FROM  mdl_sessions where userid = $reslogsession->userid";
     				$ressessionnewtimecreated = $DB->get_record_sql($sqlsessiontimecreated);
     				echo "$sqlsessiontimecreated<br>";
     				echo "$ressessionnewtimecreated->timecreated<br>";
     				echo "$ressessionnewtimecreated->timemodified<br>";
     				$diffnew = $ressessionnewtimecreated->timemodified - $ressessionnewtimecreated->timecreated;
     				echo "$diffnew<br>";
     				$difftotal = $diffnew + $resultnewtimecreated->diff;
     				echo "$difftotal<br>";
     				
     				//update time created
     				$updatetimecreated ="update mdl_log_session set timecreated = $ressessionnewtimecreated->timecreated where id =$resultnewtimecreated->id";
     				$DB->execute($updatetimecreated);
     			    echo "$updatetimecreated<br/>";
     				
     				//updatetimemodified
     				$updatenewtimecreated = "update mdl_log_session set timemodified = $ressessionnewtimecreated->timemodified where id =$resultnewtimecreated->id ";
        			$DB->execute($updatenewtimecreated);
        		    echo "$updatenewtimecreated<br/>";
        			
        			//update diff
        			$updatediff = "update mdl_log_session set diff = $difftotal  where id =$resultnewtimecreated->id";
        			$DB->execute($updatediff);
        			echo "$updatediff<br/>";
        
     			}
     			
     		//if($datetime == $datesession)
     		//enregister le meme userid dans la bdd avc une nouvelle datesession
     	}
     		else 
     		{
     			//On ajoute userid
     		  $diff = $session->timemodified - $session->timecreated;
		   	  $insertlogsession ="INSERT INTO mdl_log_session(userid, datesession, timecreated, timemodified, diff) VALUES ($session->userid, unix_timestamp(NOW()), $session->timecreated, $session->timemodified, $diff)";
		      $DB->execute($insertlogsession);
		      echo "new enregis";
     		}		
     	}//if($reslogsession)
     	//userid n'existe pas => nouveau enregistrement
     	else 
     	{
     		//On ajoute userid
     		  $diff = $session->timemodified - $session->timecreated;
		   	  $insertlogsession ="INSERT INTO mdl_log_session(userid, datesession, timecreated, timemodified, diff) VALUES ($session->userid, unix_timestamp(NOW()), $session->timecreated, $session->timemodified, $diff)";
		      $DB->execute($insertlogsession);
     	}
     }



?>
