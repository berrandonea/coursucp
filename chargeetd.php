<?php
define('CLI_SCRIPT', true);
 include 'config.php';
 
 /* ON CHARGE LE XML */
 $xmldoc = new DOMDocument();
	$fileopening = $xmldoc->load('/home/referentiel/DOKEOS_Etudiants_Inscriptions.xml');
	var_dump($fileopening);
		if ($fileopening == false) {
			echo "Impossible de lire le fichier source.\n";
		}
		$xpathvar = new Domxpath($xmldoc);    
$sql = "select id, username from mdl_user";
$students = $DB->get_recordset_sql($sql);
foreach ($students as $student)
{
	
	/* ON COMMENCE LA LECTURE */
/* 	$etudiant = $xpathvar->query('//Student[@StudentUID="'.$student->username.'"]');
	echo "Test\n";
	echo "$student->username\n";
	foreach ($etudiant as $student) 
	{ */
		
		//recup année univ
		$Studentanneeuniversitaire = $xpathvar->query('//Student[@StudentUID="'.$student->username.'"]/Annee_universitaire'); 
		foreach ($Studentanneeuniversitaire as $studentanneeuniversitaire) 
		{
			$Studentannee = $studentanneeuniversitaire->getAttribute('AnneeUniv');
			if($Studentannee ==2014)
			{
					//Récup libdiplome d'inscription
					$vets = $xpathvar->query('//Student[@StudentUID="'.$student->username.'"]/Annee_universitaire[@AnneeUniv="'.$Studentannee.'"]/Inscriptions');
					foreach($vets as $vet)
					{
						$codevet =addslashes($vet->getAttribute('CodeEtape'));
						$sql ="select id from mdl_course_categories where idnumber = '$codevet'";
						$category = $DB->get_record_sql($sql);
				
					if($category)
					{
						$sql ="select count(id) as nbr from  mdl_student_vet where studentid = '$student->id' and categoryid = '$category->id'";
						$tr = $DB->get_record_sql($sql);
						if($tr->nbr ==0)
						{
						$sql = "INSERT INTO mdl_student_vet (studentid, categoryid) VALUES ('$student->id', '$category->id')";
						echo "$sql\n";
					    $DB->execute($sql);
						}
						}
						
					}	
			}			
		}
	
}


?>
