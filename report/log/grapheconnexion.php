<?php
require('../../config.php');
include ("jpgraph-3.5.0b1/src/jpgraph.php");
include ("jpgraph-3.5.0b1/src/jpgraph_bar.php");

//$tableauAnnees = array();
//$tableauNombreVentes = array();

$sql = <<<EOF
	select diff, datesession from mdl_log_session where userid=12
EOF;
$resultatcnx = $DB->get_recordset_sql($sql); 
foreach ($resultatcnx as $row)
{
	//$tableauAnnees[] = 'Année ' . $row['ANNEE'];
	$tableauAnnees = $row->datesession;
	echo "$tableauAnnees<br>";
	//$tableauNombreVentes[] = $row['NBR_VENTES'];
	$tableauNombreVentes = $row->diff;
	echo "$tableauNombreVentes<br>";
}

$graph = new Graph(400,250);
echo "lkdjf<br>";
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
$graph->Stroke();


?>