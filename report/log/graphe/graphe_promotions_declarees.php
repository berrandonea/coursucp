<?php // content="text/plain; charset=utf-8"

require_once('../../../config.php');
        include("../jpgraph/src/jpgraph.php");
        include ("../jpgraph/src/jpgraph_bar.php");
       include ("../jpgraph/src/jpgraph_line.php");
	   include ("../jpgraph/src/jpgraph_pie.php");
        include ("../jpgraph/src/jpgraph_error.php");
global $DB, $OUTPUT, $PAGE, $USER;
$tableauAnnees = array();
$tableauNombreVentes = array();
//$data =array();

/*$composantes = array(
    array("id" => "3" ,"idcat" => "1", "code" => "1", "name" => "  UFR DROIT", "nbknowncourses" => 0),
    array("id" => "8" ,"idcat" => "5222", "code" => "2", "name" => "  UFR ECONOMIE ET GESTION", "nbknowncourses" => 0),
    array("id" => "9" ,"idcat" => "6874", "code" => "3", "name" => "  UFR LANGUES ETUDES INTERNATIONALES", "nbknowncourses" => 0),
    array("id" => "10" ,"idcat" => "9033", "code" => "4", "name" => "  UFR LETTRES ET SCIENCES HUMAINES", "nbknowncourses" => 0),
    array("id" => "11" ,"idcat" => "11443", "code" => "5", "name" => "  UFR SCIENCES ET TECHNIQUES", "nbknowncourses" => 0),
    array("id" => "13" ,"idcat" => "14963", "code" => "7", "name" => "  INSTITUT UNIVERSITAIRE DE TECHNOLOGIE", "nbknowncourses" => 0),
    array("id" => "16" ,"idcat" => "16356", "code" => "A", "name" => "  ECOLE SUPERIEURE DU PROFESSORAT ET DE L'EDUCATION", "nbknowncourses" => 0),
    array("id" => "17" ,"idcat" => "20080", "code" => "B", "name" => "  SCIENCES-PO ST-GERMAIN-EN-LAYE", "nbknowncourses" => 0),
);*/
$composantes = $DB->get_records('chiffres_ufr');
foreach ($composantes as $composante) {
//	$sql = "SELECT count(distinct vets.id) as nbrvets FROM `mdl_cat_demandecours` niveau, mdl_cat_demandecours vets where niveau.id = vets.parent_id and niveau.parent_id = '".$composante["idcat"]."'";
//	$nbrvets = $DB->get_record_sql($sql);
	//$composantes[$numcomposante]["nbrvets"] = $nbrvets;
	//$totalnbrvets += $nbrvets;
 //echo $composante['name']; echo"<br>";
 //echo $nbrvets;
// $vet =$nbrvets->nbrvets;
$vet = $composante->nbvets;
 $composante = $composante['name'];
 //echo "$vet <br>";
 $tableauNombreVentes  =array_merge($tableauNombreVentes ,array("$vet"));
 $tableauAnnees  =array_merge($tableauAnnees ,array("$composante"));
}

// A new pie graph
$graph = new PieGraph(400,400,'auto');

// Don't display the border
$graph->SetFrame(false);

// Uncomment this line to add a drop shadow to the border
// $graph->SetShadow();

// Setup title
$graph->title->Set("Demandes d'inscription");
//$graph->title->SetFont(FF_ARIAL,FS_BOLD,15);
$graph->title->SetMargin(8); // Add a little bit more margin from the top

// Create the pie plot
$p1 = new PiePlotC($tableauNombreVentes);

// Set size of pie
$p1->SetSize(0.35);

// Label font and color setup
//$p1->value->SetFont(FF_ARIAL,FS_BOLD,12);
$p1->value->SetColor('white');

$p1->value->Show();

// Setup the title on the center circle
$p1->midtitle->Set("Demandes \nInscription");
//$p1->midtitle->SetFont(FF_VERDANA,FS_NORMAL,11);

// Set color for mid circle
$p1->SetMidColor('yellow');

// Use percentage values in the legends values (This is also the default)
$p1->SetLabelType(PIE_VALUE_PER);

// The label array values may have printf() formatting in them. The argument to the
// form,at string will be the value of the slice (either the percetage or absolute
// depending on what was specified in the SetLabelType() above.
$lbl = array("Non\ntraitées\n%.1f%%","Traitées\nacceptées\n%.1f%%",
         "Traitées\nrefusées\n%.1f%%");
$p1->SetLabels($tableauAnnees);

// Uncomment this line to remove the borders around the slices
// $p1->ShowBorder(false);

// Add drop shadow to slices
$p1->SetShadow();

// Explode all slices 15 pixels
$p1->ExplodeAll(15);

// Add plot to pie graph
$graph->Add($p1);

// .. and send the image on it's marry way to the browser
$graph->Stroke();

?>
