<?php
require('../../config.php');
        include ("jpgraph-3.5.0b1/src/jpgraph.php");
        include ("jpgraph-3.5.0b1/src/jpgraph_bar.php");

        
$sql = <<<EOF
	select diff, datesession from mdl_log_session where userid=12
EOF;
$resultatcnx = $DB->get_recordset_sql($sql); 

$sqlminmax = "SELECT min(datesession) as mindate, max(datesession) as maxdate FROM mdl_log_session where userid = 12";
$resultminmax = $DB->get_recordset_sql($sqlminmax);
//tableau présentatif des barres histogramme

foreach ($resultminmax as $resminmax)
{
	
	//récupérer toutes les connexions dans datesession précises
	$sqlcnx = "SELECT diff FROM mdl_log_session WHERE datesession BETWEEN $resminmax->mindate  AND $resminmax->maxdate where userid =12 ";
	$resultcnx = $DB->get_recordset_sql($sqlcnx);
	$datay = array (15=>3258);
	foreach ($resultcnx as $cnx)
	{
		
		if($cnx)
	{
		$newelm =$cnx->diff;
		$ne = $cnx->datesession;
		$datay = array_merge($datay, array("$ne"=>"$newelm"));
	}
	/*else 
	{
		$newelm =0;
		$ne = $resultcnx->datesession;
		$datay = array_merge($datay, array("$ne"=>0));
	}*/
	}
	
}
/* $datay = array (15=>3258);
foreach ($resultatcnx as $row)
{
	$newelm =$row->diff;
	$ne = $row->datesession;
	//array_push($datay, $newelm);
	//$datay[$ne]=$newelm;
	$datay = array_merge($datay, array("$ne"=>"$newelm"));
} */       
        
       $graph = new Graph(600,400,"auto");
       $graph->SetScale('textint');
       $graph->SetShadow();
        
       $bplot = new BarPlot($datay);
       $bplot->SetFillColor("blue");
       $graph->Add($bplot);
        
      $bplot->SetShadow();
       $bplot->value->SetFormat('%d');
       $bplot->value->Show();
        
        $graph->Stroke();
        

/*$sql = <<<EOF
	select diff AS NBR_VENTES , datesession AS ANNEE from mdl_log_session where userid=12
EOF;
$resultatcnx = $DB->get_recordset_sql($sql); 

$tableauAnnees= array();
$tableauNombreVentes =array();

foreach ($resultatcnx as $row)
{
	//$newelm =$row->diff;
	//$ne = $row->datesession;
	$tableauAnnees[] = $row->datesession;
	$tableauNombreVentes[] = $row->diff;
	//array_push($tableauNombreVentes, $newelm);
	//$tableauNombreVentes[]= $newelm;
	//array_push($tableauNombreVentes, $newelm);
	//array_push($tableauAnnees, $ne);
	//$tableauAnnees[]= $ne;
	//$datay[$ne]=$newelm;
}   
*/
/*while ($row = mysql_fetch_array($mysqlQuery,  MYSQL_ASSOC)) {
	$tableauAnnees[] = 'Année ' . $row['ANNEE'];
	$tableauNombreVentes[] = $row['NBR_VENTES'];
}*/

/*
printf('<pre>%s</pre>', print_r($tableauAnnees,1));
printf('<pre>%s</pre>', print_r($tableauNombreVentes,1));
*/

// *******************
// Création du graphique
// *******************


// Construction du conteneur
// Spécification largeur et hauteur
/*$graph = new Graph(400,250);

// Réprésentation linéaire
$graph->SetScale("textlin");

// Ajouter une ombre au conteneur
$graph->SetShadow();

// Fixer les marges
$graph->img->SetMargin(40,30,25,40);

// Création du graphique histogramme
$bplot = new BarPlot($tableauNombreVentes);

// Spécification des couleurs des barres
$bplot->SetFillColor(array('red', 'green', 'blue'));
// Une ombre pour chaque barre
$bplot->SetShadow();

// Afficher les valeurs pour chaque barre
$bplot->value->Show();
// Fixer l'aspect de la police
$bplot->value->SetFont(FF_ARIAL,FS_NORMAL,9);
// Modifier le rendu de chaque valeur
$bplot->value->SetFormat('%d ventes');

// Ajouter les barres au conteneur
$graph->Add($bplot);

// Le titre
$graph->title->Set("Graphique 'HISTOGRAMME' : ventes par années");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Titre pour l'axe horizontal(axe x) et vertical (axe y)
$graph->xaxis->title->Set("Années");
$graph->yaxis->title->Set("Nombre de ventes");

$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Légende pour l'axe horizontal
$graph->xaxis->SetTickLabels($tableauAnnees);

// Afficher le graphique
$graph->Stroke();*/

?>