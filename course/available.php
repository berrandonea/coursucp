<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Gestion des demandes de création de cours et de webconférence
 *
 * @package    core_course
 * @copyright  2015 Brice Errandonea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/course/lib.php');

require_login();

if (!coursecat::has_capability_on_any(array('moodle/category:manage'))) {
    // The user isn't able to manage any categories. Lets redirect them to the relevant course/index.php page.
    /* BRICE $url = new moodle_url('/course/index.php');
    if ($categoryid) {
        $url->param('categoryid', $categoryid);
    } */
    $url = "http://sefiap.u-cergy.fr";
    redirect($url);
}




$PAGE->set_title("Cours disponibles à la création");
$PAGE->set_heading("Cours disponibles à la création");
$renderer = $PAGE->get_renderer('core_course', 'management');
$renderer->enhance_management_interface();

/*$displaycategorylisting = ($viewmode === 'default' || $viewmode === 'combined' || $viewmode === 'categories');
$displaycourselisting = ($viewmode === 'default' || $viewmode === 'combined' || $viewmode === 'courses');
$displaycoursedetail = (isset($courseid));*/

echo $renderer->header();




$url = new moodle_url('/course/demandes.php');
$systemcontext = $context = context_system::instance();
$course = null;
$courseid = null;
$category = coursecat::get_default();
$categoryid = $category->id;
$context = context_coursecat::instance($category->id);
$url->param('categoryid', $category->id);

$pageheading = format_string($SITE->fullname, true, array('context' => $systemcontext));

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$composantes = array(   "0" => "Choisissez une composante",
                        "1" => "UFR DROIT", 
                        "2" => "UFR ECONOMIE ET GESTION",
                        "3" => "UFR LANGUES ETUDES INTERNATIONALES",
                        "4" => "UFR LETTRES ET SCIENCES HUMAINES",
                        "5" => "UFR SCIENCES ET TECHNIQUES",
                        "7" => "INSTITUT UNIVERSITAIRE DE TECHNOLOGIE",
                        "A" => "ECOLE SUPERIEURE DU PROFESSORAT ET DE L'EDUCATION",
                        "B" => "SCIENCES-PO ST-GERMAIN-EN-LAYE"
                );


$ufr = 0;
$ufr = optional_param('ufr', 0, PARAM_ALPHANUM);


?>

<!-- Raccourcis -->
<a href='demandes.php'>Retour aux demandes de création de cours</a>
<br><br>
<form method="post" action="available.php">
    <select name='ufr'  id='ufr'>
        <?php
        foreach ($composantes as $key => $composante) {
            if ($key == $ufr) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            echo "<option value='$key' $selected>$composante</option>";
        }
        ?>        
    </select>    
    <input type="submit" value="Chercher" />
</form>
<br>
<?php 

if ($ufr) {
    echo "<h1>".$composantes[$ufr]."</h1>";
    
    /* ON CHARGE LE XML */
    $codecourselp = "";
    $filename = '/home/referentiel/dokeos_elp_etu_ens.xml';    
    if (filesize($filename) > 0) {
        $xmldoc = new DOMDocument();
        $xmldoc->load($filename);
        $xpathvar = new Domxpath($xmldoc);     

        /* ON COMMENCE LA LECTURE */
        $nbvetsinufr = 0;
        $availinufr = 0;
        $createdinufr = 0;
        $vets = $xpathvar->query('//Structure_diplome');
        
        //POUR CHAQUE VET
        foreach ($vets as $vet) {
            $idvet = $vet->getAttribute('Etape');             
            $ufrcode = substr($idvet, 0, 1);
            $nomvet = $vet->getAttribute('libelle_long_version_etape');
            
            if ($ufrcode == $ufr) { //On affiche uniquement les cours disponibles dans la composante demandée                
                $nbvetsinufr++;                
                $showvet = 0;
                
                //echo "<br>VET : $idvet<br>";
                $vetcourses = $xpathvar->query('//Structure_diplome[@Etape="'.$idvet.'"]/Cours');    
                
                //POUR CHAQUE COURS DE LA VET
                foreach ($vetcourses as $vetcourse) {
                    $elementpedagogique = $vetcourse->getAttribute('element_pedagogique');
                    $titrecours = $vetcourse->getAttribute('libelle_long_element_pedagogique');
                    $codecourselp = $idvet."-".$elementpedagogique;
                    $availinufr++;
                    
                    if (!$showvet) {
                        echo "<br><p id='monbeaup".$idvet."' style='font-weight:bold;padding:5px;color:white;background-color : #780D68'>(".$idvet.") ".$nomvet."</p>";
                        echo "<ul>";
                        $showvet = 1;
                    }
                    
                    echo "<li>";
                    echo "<span style='font-size:125%'>($codecourselp) $titrecours</span> ";
                    
                    //Ce cours est-il déjà créé ?
                    $course = $DB->get_record('course', array('shortname' => $codecourselp));
                    
                    if ($course) {
                        echo " &nbsp; <a href='view.php?id=$course->id'>Voir</a> <span style='color:green'>DEJA CREE</span>";
                        $createdinufr++;
                    }
                    
                    //Recherche des enseignants dans le fichier XML
                    echo "<br> Enseignants : ";
                    $teachers = $xpathvar->query('//Structure_diplome[@Etape="'.$idvet.'"]/Cours[@element_pedagogique="'.$elementpedagogique.'"]/Group/Teacher');
                    if (isset($unique_teacher)) {
                        unset($unique_teacher);
                    }
                    $unique_teacher = array();
                    $uniqueteachers = 0;
                    foreach ($teachers as $teacher) {
                        //print_object($teacher);
                        $teacher_username = $teacher->getAttribute('StaffUID');
                        if (!in_array($teacher_username, $unique_teacher)) {
                            $unique_teacher[$uniqueteachers] = $teacher_username;
                            $uniqueteachers++;
                        }                                                
                    }
                    
                    foreach ($unique_teacher as $unique_teacher_username) {
                        //On cherche l'enseignant dans la base de données
                        $sql = "SELECT id, firstname, lastname FROM mdl_user WHERE username = '$unique_teacher_username'";
                        $teacherdata = $DB->get_record_sql($sql);
                        echo "<a href='$CFG->wwwroot/user/profile.php?id=$teacherdata->id'>$teacherdata->firstname $teacherdata->lastname</a> &nbsp; ";                       
                    }
                    
                    
                    echo "<br><br></li>";
                    
                    
                    
                    
                }
                if ($showvet) {
                    echo "</ul>";
                }
            }
        }
        
        echo "<br><p>$availinufr cours disponibles dans cette composante (dont $createdinufr déjà créés), répartis dans $nbvetsinufr VETs.</p>";
        
        
        
        
        
        
        
        /* $ufrResult = $xpathvar->query('//Structure_diplome[@Libelle_composante="$ufr : '.$composantes[$ufr].'"]');

        foreach($ufrResult as $result){

            print_object($result);
            // RECUPERATION CODE VET
            $idvet = $result->getAttribute('Etape'); 
            $nomvet = $result->getAttribute('libelle_long_version_etape');
            //echo "$idvet<br>";

            $showvet = 0;

            
            $querycours = $xpathvar->query('//Structure_diplome[@Etape="'.$idvet.'"]/Cours');



            foreach($querycours as $cours){
                //print_object($cours);        
                $codecourselp = $idvet."-".$cours->getAttribute('element_pedagogique');
                echo "$codecourselp<br>";
                $course = $DB->get_record('course', array('shortname' => $codecourselp));
                $codecourselpseul = $cours->getAttribute('element_pedagogique');

                if(!$course) {
                    // LE SCRIPT JS AFFICHE (DANS LES CAS OU DES COURS SONT TROUVES), LE BLOCK P DE LA VET QUI EST CACHE PAR DEFAUT 
                    echo '<script language="javascript" type="text/javascript">document.getElementById("monbeaup'.$idvet.'").style.display = "block";</script>' ;

                    if ($cours->getAttribute('type_element_pedagogique')!="") {
                       $elpeda = " [".$cours->getAttribute('type_element_pedagogique')."]";
                    } else $elpeda = "";


                    if ($showvet == 0) {
                        $showvet = 1;
                        echo "<p id='monbeaup".$idvet."' style='font-weight:bold;padding:5px;color:white;background-color : #780D68'>(".$idvet.") ".$nomvet."</p>";
                        echo "<ul>";
                    }

                    echo "<li class='dejacree'><a onmouseover=\"FoToolTip.show(this,'Créer ce cours')\" href=\"$CFG->wwwroot/course/edit.php?category=4&cat=".
                            $idvet."&nomvet=$nomvet&codecours=".$idvet."-".$cours->getAttribute('element_pedagogique').
                            "&titrecours=".$cours->getAttribute('libelle_long_element_pedagogique')."".$elpeda."&returnto=category\">".
                            $cours->getAttribute('libelle_long_element_pedagogique')."".$elpeda."</a><br/><i>".$idvet."-".
                            $cours->getAttribute('element_pedagogique')."</i></li>"; 
                }
            }
            if ($showvet == 1) {
                echo "</ul>";
            }

        } */
        
        
    } 

    if($codecourselp == ""){
        echo "<p id='rientrouve'>Aucun cours disponible pour cette composante.</p>";
    }

    
    
    
}





?>



<?php 

echo $renderer->management_form_start();
echo $renderer->management_form_end();

echo $renderer->footer();
?>

