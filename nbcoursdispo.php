<?php

    $dom = new DomDocument('1.0', 'UTF-8');
	$dom->load('/home/referentiel/dokeos_elp_etu_ens.xml');
	
  	$racine = $dom->documentElement;
  	$listeDiplomes = $dom->getElementsByTagName("Structure_diplome");
  	$nombreDiplomes = 0;
  	$nombreTotalCours = 0;
  	foreach($listeDiplomes as $Diplomes){
  		echo "[".$Diplomes->getAttribute("Etape")."] : ";
  		echo utf8_decode($Diplomes->getAttribute("libelle_long_version_etape"). "<br />");
  		$listeCours = $Diplomes->getElementsByTagName("Cours");
  		$nombreCours = 0;
  		foreach($listeCours as $Cours){
  			echo utf8_decode("--- (".$Cours->getAttribute("element_pedagogique").") : ");
  			echo utf8_decode($Cours->getAttribute("libelle_long_element_pedagogique"));
  			$listeEnseignants = $Cours->getElementsByTagName("Teacher");
  			/*echo " (";
  			foreach ($listeEnseignants as $Enseignants){
  				echo $Enseignants->getAttribute("StaffName"). ", ";
  			}
  			echo ")";*/
  			echo "<br />";
  			$nombreCours = $nombreCours + 1;
  		} 
  		echo "NOMBRE DE COURS = ".$nombreCours."<br />";
  		$nombreTotalCours = $nombreTotalCours + $nombreCours ;
  		$nombreDiplomes = $nombreDiplomes +1;
  	}   	
  	echo "NOMBRE DE DIPLOMES = ".$nombreDiplomes."<br />";
  	echo "NOMBRE TOTAL DE COURS DISPONIBLES = ".$nombreTotalCours;
  	  
?>