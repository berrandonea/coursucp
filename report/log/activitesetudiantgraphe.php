<?php
require('../../config.php');
        include ("jpgraph-3.5.0b1/src/jpgraph.php");
        include ("jpgraph-3.5.0b1/src/jpgraph_bar.php");

$id=$_GET["id"]; 
$cours=$_GET["cours"];  
     
//r�cup des acti dans ce cours 
$sqlactivit = "select distinct m.traduction,m.name from mdl_log l , mdl_modules m where m.name = l.module and l.course=$cours";
$resulactivite = $DB->get_recordset_sql($sqlactivit);
//modules
$tableauAnnees = array ();
//count des activit�s
$tableauNombrepub = array();
foreach ($resulactivite as $rs)
{
	
$sqlmodule = "select count(*) as nbracti, module from mdl_log where userid=$id and module = '$rs->name' group by module";
$resultmodule = $DB->get_record_sql($sqlmodule);

if($resultmodule)
{
$elm1 = $rs->traduction;
$elm2 = $resultmodule->nbracti;

$tableauAnnees = array_merge($tableauAnnees, array("$elm1"));
$tableauNombrepub =array_merge($tableauNombrepub,array("$elm2"));
}
else 
{
$elm1 = $rs->traduction;
$elm2 = 0;

$tableauAnnees = array_merge($tableauAnnees, array("$elm1"));
$tableauNombrepub =array_merge($tableauNombrepub,array("$elm2"));
}

}


//}
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
$graph->title->Set("Graphique 'HISTOGRAMME' : Activit�s �tudiant");
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