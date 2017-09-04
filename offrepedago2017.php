
<title>
Cron quotidien - Remplissage de l'outil de demande de création de cours
</title>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');
require_once($CFG->libdir .'/accesslib.php');

/* ON CHARGE LE XML */

$xmldoc = new DOMDocument();
$xmldoc->load('/home/referentiel/dokeos_offre_pedagogique.xml');
$xpathvar = new Domxpath($xmldoc);

//Pour chaque VET
$queryvet = $xpathvar->query('//Etape');
foreach($queryvet as $vet){
    $codevet = $vet->getAttribute('Code_etape');
    $codevetyear = "$codevet";
    $nomvet = $vet->getAttribute('Lib_etape');
    $updatedvetname = "($codevetyear) $nomvet";

    //Mise à jour VET - BASE DE DONNES COURS CREES      !!!!!
    $sql = "SELECT id FROM mdl_course_categories WHERE idnumber = '$codevetyear'";
    $vetresult = $DB->get_record_sql($sql);
    if ($vetresult) {
        $sql = "SELECT name FROM mdl_course_categories WHERE idnumber = '".$codevetyear."'";
        $currentvetname = $DB->get_record_sql($sql)->name;
        if ($currentvetname != $updatedvetname) {
            echo "MISE A JOUR CATEGORIE VET : $currentvetname -> $updatedvetname<br/>\n";
            $sql = "UPDATE mdl_course_categories SET name = \"".$updatedvetname."\" WHERE idnumber = '".$codevetyear."'";
            echo "$sql\n";
            $DB->execute($sql);
        }
    }
    //// fin

    $sql = "SELECT id FROM mdl_cat_demandecours WHERE code = '$codevetyear'";
    $vetresult = $DB->get_record_sql($sql);
    if ($vetresult) {
        $vetid = $vetresult->id;

        //Mise à jour de son intitulé
        $sql = "SELECT name FROM mdl_cat_demandecours WHERE id = $vetid";
        $currentvetname = $DB->get_record_sql($sql)->name;
        if ($currentvetname != $updatedvetname) {
            echo "MàJ VET: $currentvetname -> $updatedvetname\n";
            $sql = "UPDATE mdl_cat_demandecours SET name = \"".$updatedvetname."\" WHERE id = $vetid";
            echo "$sql\n";
            $DB->execute($sql);
        }

    } else {
        //Dans quelle UFR faut-il créer cette VET ?
        unset($ufr);
        $ufr = substr($codevet, 0, 1);
        $ufryear = "$ufr";
        $sql = "SELECT id FROM mdl_cat_demandecours WHERE code = '$ufryear'";
        $ufrresult = $DB->get_record_sql($sql);

        if ($ufrresult) {
            $ufrid = $ufrresult->id;
        } else {
            $sql = "SELECT name FROM mdl_ufr WHERE code = '$ufryear'";
            $ufrshortname = $DB->get_record_sql($sql)->name;
            echo "Nouvelle composante: $ufryear\n";
            $newufr = new stdClass();
            $newufr->name = $ufrshortname;
            $newufr->code = $ufryear;
            $ufrid = $DB->insert_record('cat_demandecours', $newufr);
        }

        //Dans quel niveau faut-il créer cette VET ?
        $testdulp = substr($nomvet, 0, 2);
        $testlicence = substr($nomvet, 0, 7);
        $testmaster = substr($nomvet, 0, 6);
        $vetlastchar = substr($nomvet, -1);

        if ($testdulp == "LP") {
            $levelcode = $ufryear."LP";
            $levelname = "Licence professionnelle";
        } else if ($testdulp == "DU") {
            $levelcode = $ufryear."DU";
            $levelname = "DU ou DUT";
        } else if ($testlicence == "Licence") {
            if ($vetlastchar == "1") {
                $levelcode = $ufryear."L1";
                $levelname = "Licence 1ère année";
            } else if ($vetlastchar == "2") {
                $levelcode = $ufryear."L2";
                $levelname = "Licence 2ème année";
            } else {
                $levelcode = $ufryear."L3";
                $levelname = "Licence 3ème année";
            }
        } else if ($testmaster == "Master") {
            if ($vetlastchar == "1") {
                $levelcode = $ufryear."M1";
                $levelname = "Master 1ère année";
            } else {
                $levelcode = $ufryear."M2";
                $levelname = "Master 2ème année";
            }
        } else {
            $levelcode = $ufryear."A";
            $levelname = "Autre";
        }

        //Si le niveau en question n'existe pas encore dans 'mdl_cat_demandecours', on le crée
        $sql = "SELECT id FROM mdl_cat_demandecours WHERE code = '$levelcode'";
        $levelresult = $DB->get_record_sql($sql);

        if ($levelresult) {
            $levelid = $levelresult->id;
        } else {
            //echo "Nouveau Niveau: $levelcode\n";
            $newlevel = new stdClass();
            $newlevel->name = $levelname;
            $newlevel->code = $levelcode;
            $newlevel->parent_id = $ufrid;
            $levelid = $DB->insert_record('cat_demandecours', $newlevel); 
        }

        //Insertion de la VET dans la table
        echo "Nouvelle VET: $codevet\n";
        $newvet = new stdClass();
        $newvet->name = $updatedvetname;
        $newvet->code = $codevetyear;
        $newvet->parent_id = $levelid;
        $vetid = $DB->insert_record('cat_demandecours', $newvet);
    }

    //Pour chaque ELP de cette VET
    $queryelp = $xpathvar->query('//Etape[@Code_etape="'.$codevet.'"]/Version_etape/ELP');
    foreach($queryelp as $elp){
        $codeelp = $elp->getAttribute('Code_ELP');
        $natureelp = $elp->getAttribute('Nature_ELP');
        $nomelp = /*"(".$codevet."-".$codeelp.") ".*/ $elp->getAttribute('Lib_ELP')." [".$natureelp."]";

        $nomelp = str_replace("?", "", $nomelp);
        $nomelp = str_replace("\"", "'", $nomelp);
        $nomelp = str_replace(" ; ", " : ", $nomelp);
        $nomelp = str_replace(":", ": ", $nomelp);
        $nomelp = str_replace(":  ", ": ", $nomelp);
        /*$nomelp = str_replace(":a", " : a", $nomelp);
        $nomelp = str_replace(":d", " : d", $nomelp);
        $nomelp = str_replace(":m", " : m", $nomelp);
        $nomelp = str_replace(":p", " : p", $nomelp);*/


        $updatedelpname = str_replace(";", ",", $nomelp);
        //echo "codelp: $codeelp\n";
        $sql = "SELECT id FROM mdl_cat_demandecours WHERE code = '$codevetyear-$codeelp'";
        //echo "$sql\n";
        $elpresult = $DB->get_record_sql($sql);
        if ($elpresult) {
            $elpid = $elpresult->id;

            //Mise à jour de son intitulé
            $sql = "SELECT name FROM mdl_cat_demandecours WHERE id = $elpid";
            $currentelpname = $DB->get_record_sql($sql)->name;
            if ($currentelpname != $updatedelpname) {
                echo "$elpid -> MàJ ELP: $currentelpname -> $updatedelpname<br/>\n";
                $sql = "UPDATE mdl_cat_demandecours SET name = \"".addslashes($updatedelpname)."\" WHERE id = $elpid";
                echo "$sql\n";
                $DB->execute($sql);
            }
        } else {
            //echo $sql."<br/>";
            echo "Nouvel ELP: $codevetyear-$codeelp<br/>\n";
            /*$newelp = new stdClass();
            $newelp->name = $updatedelpname;
            $newelp->code = $codevet."-".$codeelp;
            $newelp->parent_id = $vetid;
            $DB->insert_record('cat_demandecours', $newelp);*/
            $sql = "INSERT INTO mdl_cat_demandecours (name, code, parent_id) VALUES (\"".addslashes($updatedelpname)."\", \"$codevetyear-$codeelp\", $vetid)";
            echo "$sql\n";
            $DB->execute($sql);
        }
    }
}


//14463 -> MàJ ELP: (5F39D1-5PLA1ERH) [EC] EC3 Energies renouvelables ; HQE -> (5F39D1-5PLA1ERH) EC3 Energies renouvelables ; HQE [EC]<br/>
//UPDATE mdl_cat_demandecours SET name = "(5F39D1-5PLA1ERH) EC3 Energies renouvelables ; HQE [EC]" WHERE id = 14463


?>



