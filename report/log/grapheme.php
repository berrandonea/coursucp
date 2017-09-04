<?php
require('../../config.php');
        include ("jpgraph-3.5.0b1/src/jpgraph.php");
        include ("jpgraph-3.5.0b1/src/jpgraph_bar.php");

//recup userid
$id=$_GET["id"];
//$query="select distinct u.id,u.firstname,u.lastname, u.email,u.picture from mdl_user u,mdl_role_assignments r where u.id=r.userid and r.id ='".$id."'";
//echo "$query";
//$coordonnesetd = $DB->get_record_sql($query);



$sqlminmax = "SELECT min(timecreated) as mindate, max(timecreated) as maxdate FROM mdl_log_session where userid = $id";
echo "$coordonnesetd->id";
$resultminmax = $DB->get_record_sql($sqlminmax);

 $sql = <<<EOF
SELECT 
timecreated, diff 
FROM `mdl_log_session` where userid=$id and timecreated between $resultminmax->mindate and $resultminmax->maxdate
order by timecreated
EOF;

$res = $DB->get_recordset_sql($sql);
$tableauAnnees = array ();
$tableauNombrepub = array();
foreach ($res as $row)
{
	//$tableauAnnees[] = $row['ANNEE'];
	//$tableauAnnees[] = $row->$datesession;
	//$tableauNombrepub[] = $row->$diff;
//$tableauAnnees[] = 'Année ' . $row['ANNEE'];
//$tableauNombrepub[] = $row['NBR_pub'];
    //$tableauNombrepub[] = $row['NBR_pub'];
    //convertir 
    $convert = date('d/m', $row->timecreated);
    $onvertminute = $row->diff /60;
    $tableauAnnees = array_merge($tableauAnnees, array("$convert"));
    $tableauNombrepub =array_merge($tableauNombrepub,array("$onvertminute"));
}

// *******************
// Création du graphique
// *******************
// Construction du conteneur
// Spécification largeur et hauteur
$graph = new Graph(600,450);
// Réprésentation linéaire
$graph->SetScale("textlin");
// Ajouter une ombre au conteneur
$graph->SetShadow();
// Fixer les marges
$graph->img->SetMargin(40,30,25,40);
// Création du graphique histogramme
$bplot = new BarPlot($tableauNombrepub);
// Spécification des couleurs des barres
//$bplot->SetFillColor(array('red', 'green', 'blue'));

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
$bplot->SetFillColor("#731472");
$bplot->value->show();
// Le titre
$graph->title->Set("Graphique 'HISTOGRAMME' : Nombre de connexion en minutes par jour");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
// Titre pour l'axe horizontal(axe x) et vertical (axe y)
//$graph->xaxis->title->Set("Années");
//$graph->yaxis->title->Set("Nombre de connexion par minutes");
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
// Légende pour l'axe horizontal
$graph->xaxis->SetTickLabels($tableauAnnees);
// Afficher le graphique
$graph->Stroke();       
?>