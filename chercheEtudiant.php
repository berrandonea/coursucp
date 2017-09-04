<?php

    $dom = new DomDocument('1.0', 'UTF-8');
	$dom->load('/home/referentiel/dokeos_elp_etu_ens.xml');
	
  	$racine = $dom->documentElement;
  	$listeDiplomes = $dom->getElementsByTagName("Structure_diplome");
  	foreach($listeDiplomes as $Diplomes){
  		$listeCours = $Diplomes->getElementsByTagName("Cours");
  		foreach($listeCours as $Cours){
  			$listeGroupes = $Cours->getElementsByTagName("Group");
  			foreach ($listeGroupes as $Groupes){
  				$listeEtudiants = $Groupes->getElementsByTagName("Student");
  				foreach($listeEtudiants as $Etudiants){
  					if($Etudiants->getAttribute("StudentUID") == 'chevallm'){
  						echo "[".$Diplomes->getAttribute("Etape")."] : ";
  						echo utf8_decode($Diplomes->getAttribute("libelle_long_version_etape"). "<br />");
  						echo utf8_decode("--- (".$Cours->getAttribute("element_pedagogique").") : ");
  						echo utf8_decode($Cours->getAttribute("libelle_long_element_pedagogique")."<br />");
  						echo utf8_decode("********* ".$Groupes->getAttribute("GroupName")."<br />");
  						echo utf8_decode("[".$Etudiants->getAttribute("StudentUID")."] ".$Etudiants->getAttribute("StudentNameFirstName")."<br />");
  					}
  				}
  				
  			}   			
  		} 
  	}    	  
?>

