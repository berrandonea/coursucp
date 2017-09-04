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




$PAGE->set_title("Offre pédagogique");
$PAGE->set_heading("Offre pédagogique");
$renderer = $PAGE->get_renderer('core_course', 'management');
$renderer->enhance_management_interface();

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
<form method="post" action="offrepedago.php">
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
    //On cherche la composante et ses niveaux dans la BDD
    $sql = "SELECT id, name FROM mdl_cat_demandecours WHERE code = '$ufr'";
    $ufrdata = $DB->get_record_sql($sql);
    echo "<h1>$ufrdata->name</h1>";
    
    $sql = "SELECT id, name, code FROM mdl_cat_demandecours WHERE parent_id = $ufrdata->id ORDER BY code";
    $levels = $DB->get_recordset_sql($sql);
    foreach ($levels as $level) {
        echo "<br>";
        echo "<br><p id='monbeaup' style='font-weight:bold;padding:5px;color:white;background-color : #11238F'>(".$level->code.") ".$level->name."</p>";
                
        //Recherche des VETs
        $sql = "SELECT id, name, code FROM mdl_cat_demandecours WHERE parent_id = $level->id ORDER BY code";
        $levelvets = $DB->get_recordset_sql($sql);
        foreach ($levelvets as $levelvet) {
            echo "<br><p id='monbeaup' style='font-weight:bold;padding:5px;color:white;background-color : #780D68'>$levelvet->name</p>";
            echo "<ul>";
            //Recherche des ELP
            $sql = "SELECT id, name, code FROM mdl_cat_demandecours WHERE parent_id = $levelvet->id ORDER BY code";
            $elps = $DB->get_recordset_sql($sql);
            foreach($elps as $elp) {
                echo "<li>($elp->code) $elp->name</li>";
            }
            echo "</ul>";            
        }
    }
}

echo $renderer->management_form_start();
echo $renderer->management_form_end();

echo $renderer->footer();
?>

