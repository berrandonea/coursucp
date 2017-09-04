<script type='text/javascript'>
function flipflop(id) {
    if (document.getElementById(id).style.display == 'none') document.getElementById(id).style.display = 'block';
    else document.getElementById(id).style.display = 'none';
}
</script>

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
 * Displays different views of the logs.
 *
 * @package    report_log
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/report/log/locallib.php');
require_once($CFG->libdir.'/adminlib.php');

$id          = optional_param('id', 0, PARAM_INT);// Course ID
$host_course = optional_param('host_course', '', PARAM_PATH);// Course ID

$thisyear = 'Y2017';

if (empty($host_course)) {
    $hostid = $CFG->mnet_localhost_id;
    if (empty($id)) {
        $site = get_site();
        $id = $site->id;
    }
} else {
    list($hostid, $id) = explode('/', $host_course);
}

$PAGE->set_url('/report/log/chiffres.php');
$PAGE->set_pagelayout('report');
$course = $DB->get_record('course', array('id'=>1), '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);

//$sql = "SELECT COUNT(id) AS isteacher FROM mdl_role_assignments WHERE (roleid = 2 OR roleid = 1) AND userid = $USER->id";
//$isteacher = $DB->get_record_sql($sql)->isteacher;
$isteacher = $DB->record_exists('role_assignments', array('roleid' => 2, 'userid' => $USER->id));

if (!$isteacher) {
    if (strpos($USER->email, '@u-cergy.fr') || strpos($USER->email, '@iufm.u-cergy.fr')) {
	$isteacher = 1;
    }
}

if (!$isteacher) {
    require_capability('report/log:view', $context);
}

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_pagelayout('frontpage');
$editing = $PAGE->user_is_editing();
$PAGE->set_title("La plateforme en chiffres");
$PAGE->set_heading("La plateforme en chiffres");
$courserenderer = $PAGE->get_renderer('core', 'course');
echo $OUTPUT->header();
echo $OUTPUT->heading('La plateforme en chiffres :');

/*$composantes = array(
    array("id" => "3" ,"idcat" => "1", "code" => "1", "name" => "  UFR DROIT", "nbknowncourses" => 0),
    array("id" => "8" ,"idcat" => "5222", "code" => "2", "name" => "  UFR ECONOMIE ET GESTION", "nbknowncourses" => 0),
    array("id" => "9" ,"idcat" => "6874", "code" => "3", "name" => "  UFR LANGUES ETUDES INTERNATIONALES", "nbknowncourses" => 0),
    array("id" => "10" ,"idcat" => "9033", "code" => "4", "name" => "  UFR LETTRES ET SCIENCES HUMAINES", "nbknowncourses" => 0),
    array("id" => "11" ,"idcat" => "11443", "code" => "5", "name" => "  UFR SCIENCES ET TECHNIQUES", "nbknowncourses" => 0),
    array("id" => "13" ,"idcat" => "14963", "code" => "7", "name" => "  INSTITUT UNIVERSITAIRE DE TECHNOLOGIE", "nbknowncourses" => 0),
    array("id" => "16" ,"idcat" => "16356", "code" => "A", "name" => "  ECOLE SUPERIEURE DU PROFESSORAT ET DE L'EDUCATION", "nbknowncourses" => 0),
    array("id" => "17" ,"idcat" => "20080", "code" => "B", "name" => "  SCIENCES-PO ST-GERMAIN-EN-LAYE", "nbknowncourses" => 0),
    //array("code" => "C", "name" => "  SCUIO-IP", "nbknowncourses" => 0)
);*/
$composantes = $DB->get_records('chiffres_ufr');
?>

<div onclick="flipflop('heade');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#731472;border-radius:5px 5px 0 0">
    Nombre de promotions, de cours et d'étudiants déclarés dans Apogée
</div>
<div id ="heade" class="content" style='width:100%;display:none'>
<br>
<p>
    Les cours déclarés dans Apogée apparaissent automatiquement dans l'outil "Demande de création de cours", en bas de la page d'accueil.
    Les étudiants déclarés dans Apogée sont automatiquement inscrits à la plateforme pédagogique (mais pas forcément à des cours).
</p>

<?php
$totalknowncourses = 0;
$totalknownstudents = 0;
$totalnbrvets =0;

//Promotions déclarées
echo "<table width='1200'>";
echo "<tr><td width='700' bgcolor='#731472'><FONT COLOR='#731472'>";
$csv = "Nombre de promotions déclarées £µ£";
echo "<table><tr>";
echo "<td bgcolor='#731472'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#731472'><FONT COLOR='#731472'><h3>Promotions déclarées</h3></td></tr>";
$csv .= "Composante;Promotions déclarées£µ£";

foreach ($composantes as $composante) {
    
    echo "<tr>";
    $sql = "SELECT COUNT(DISTINCT vets.id) AS nbrvets
            FROM `mdl_cat_demandecours` niveau, mdl_cat_demandecours vets
            WHERE niveau.id = vets.parent_id AND niveau.parent_id = '".$composante->catdemandeid."'";
    $nbrvets = $DB->get_record_sql($sql)->nbrvets;
    $DB->set_field('chiffres_ufr', 'nbvets', $nbrvets, array('id' => $composante->id));
    $totalnbrvets += $nbrvets;
    echo "<td>".$composante->name."</td>";
    echo "<td style='text-align:center'>$nbrvets</td>";
    echo "</tr>";
    $csv .= $composante->name.";$nbrvets;£µ£";
}

echo "<tr style ='font-weight:bold'><td>TOTAL</td><td style='text-align:center'>$totalnbrvets</td></tr>";
$csv .= "Total;$totalnbrvets;£µ£";
echo "</table>";

if (isset($csv)) {

    $tabcsv = explode("£µ£", $csv);
    $charbefore = '"';
    $charafter = "'";
    $csvto = $csv;
    $csv = str_replace($charbefore,$charafter,$csvto);
    echo '<form enctype="multipart/form-data" action="../exportlist/downloadcsv.php" method="post">
          <input name="csv" type="hidden" value="'.$csv.'" />
          <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
          </form>';
}

echo "</td>";
echo "<td width='100'></td><td width='400'><img src ='graphe/graphe_promotion_declaree.php' height='500' width='500'></td></tr></table>";

//Étudiants déclarés

$csv = "Nombre des étudiants déclarés £µ£";
echo "<table width='1200'>";
echo "<tr><td width='700' bgcolor='#780D68'><FONT COLOR='#731472'>";
echo "<table><tr>";
echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Étudiants déclarés</h3></td></tr>";
$csv .= "Composante;Étudiants déclarés£µ£";

foreach ($composantes as $composante) {

    echo "<tr>";
    $nbknownstudentsincomposante = $DB->count_records('ufr_student', array('ufrcode' => "$composante->code", 'student' => 1));
    $DB->set_field('chiffres_ufr', 'nbstudents', $nbknownstudentsincomposante, array('id' => $composante->id));
    $totalknownstudents += $nbknownstudentsincomposante;
    echo "<td>".$composante->name."</td>";
    echo "<td style='text-align:center'>$nbknownstudentsincomposante</td>";
    echo "</tr>";
    $csv .= "".$composante->name.";$nbknownstudentsincomposante;£µ£";
}

echo "<tr style ='font-weight:bold'><td>TOTAL</td><td style='text-align:center'>$totalknownstudents</td></tr>";
$csv .= "Total;$totalknownstudents;£µ£";
echo "</table>";

if (isset($csv)) {

    $tabcsv = explode("£µ£", $csv);
    $charbefore = '"';
    $charafter = "'";
    $csvto = $csv;
    $csv = str_replace($charbefore,$charafter,$csvto);
    echo '<form enctype="multipart/form-data" action="../exportlist/downloadcsv.php" method="post">
          <input name="csv" type="hidden" value="'.$csv.'" />
          <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
          </form>';
}
echo "</td>";
echo "<td width='100'></td><td width='400'><img src ='graphe/graphe_etudiants_declares.php' height='500' width='500'></td></tr></table>";

//Cours déclarés

$csv = "Nombre des cours déclarés £µ£";
echo "<table width='1200'>";
echo "<tr><td width='700' bgcolor='#780D68'><FONT COLOR='#731472'>";
echo "<table><tr>";
echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Cours déclarés</h3></td></tr>";
$csv .= "Composante;Cours déclarés£µ£";

foreach ($composantes as $composante) {

    $truncatedcode = substr($composante->code, 6);

    echo "<tr>";
    echo "<td>".$composante->name."</td>";
    $sql = "SELECT COUNT(id) AS nbknowncourses FROM `mdl_cat_demandecours` WHERE code LIKE '".$truncatedcode."%-%'";
    $sql .= " AND (name LIKE '%[UE]' OR name LIKE '%[EC]')"; //On compte uniquement les UE et les EC
    $nbknowncourses = $DB->get_record_sql($sql)->nbknowncourses;
    $DB->set_field('chiffres_ufr', 'nbcourses', $nbknowncourses, array('id' => $composante->id));
    $totalknowncourses += $nbknowncourses;
    echo "<td style='text-align:center'>$nbknowncourses</td>";
    echo "</tr>";
    $csv .= "".$composante->name.";$nbknowncourses;£µ£";
}

echo "<tr style ='font-weight:bold'><td>TOTAL</td><td style='text-align:center'>$totalknowncourses</td></tr>";
$csv .= "Total;$totalknowncourses;£µ£";
echo "</table>";
if (isset($csv)) {
    
    $tabcsv = explode("£µ£", $csv);
    $charbefore = '"';
    $charafter = "'";
    $csvto = $csv;
    $csv = str_replace($charbefore,$charafter,$csvto);
    echo '<form enctype="multipart/form-data" action="../exportlist/downloadcsv.php" method="post">
          <input name="csv" type="hidden" value="'.$csv.'" />
          <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
          </form>';
}
echo "</td>";
echo "<td width='100'></td>
      <td width='400'><img src ='graphe/graphe_cours_declares.php' height='500' width='500'></td>
      </tr></table>";
?>
</div>
<br>


<div onclick="flipflop('header');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#731472;border-radius:5px 5px 0 0">
    Nombre de cours disponibles à la création
</div>
<div id ="header" class="content" style='width:100%;display:none'>
    <br>
    <p>Pour qu'un cours apparaisse automatiquement dans la rubrique "Cours disponibles à la création", la composante doit avoir saisi les emplois du temps dans Celcat et fait la "mise en groupes" (Cours[ELP]-Enseignant(s)-Etudiants).</p>
    <p>Voici, pour chaque composante, combien de cours sont disponibles à la création (y compris ceux qui sont déjà créés) :</p>
<?php
$totalavailcourses = 0;
$totalpromotions = 0;
$composantes = $DB->get_records('chiffres_ufr');

//Cours disponibles / Cours déclarés

$csv = "Nombre de Cours disponibles / Cours déclarés £µ£";
echo "<table width='1200'><tr><td width='700' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Cours disponibles / Cours déclarés</h3>";
echo "<table><tr>";
echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Cours disponibles</h3></td></tr>";
$csv .= "Composante;Cours disponibles£µ£";
foreach ($composantes as $composante) {
    echo "<tr>";
    $sql = "SELECT avail_courses FROM mdl_ufr WHERE code = '$composante->code'";
    $nbavailcourses = $DB->get_record_sql($sql)->avail_courses;
    $DB->set_field('chiffres_ufr', 'nbavailablecourses', $nbavailcourses, array('id' => $composante->id));
    $totalavailcourses += $nbavailcourses;
    $nbknowncourses = $composante->nbcourses;
    if ($nbknowncourses) {
        $availability = round(100 * $nbavailcourses / $nbknowncourses, 1);
    } else {
        $availability = 0;
    }
    echo "<td>".$composante->name."</td>";
    echo "<td style='text-align:center'>$nbavailcourses / $nbknowncourses ($availability%)</td>";
    echo "</tr>";
    $csv .= $composante->name.";$nbavailcourses / $nbknowncourses ($availability%);£µ£";
}
$totalavailability = round($totalavailcourses * 100 / $totalknowncourses, 1);
echo "<tr style ='font-weight:bold'><td>TOTAL</td><td style='text-align:center'>$totalavailcourses / $totalknowncourses ($totalavailability%)</td></tr>";
$csv .= "Total;$totalavailcourses / $totalknowncourses ($totalavailability%);£µ£";
echo "</table>";
if (isset($csv)) {

    $tabcsv = explode("£µ£", $csv);
    $charbefore = '"';
    $charafter = "'";
    $csvto = $csv;
    $csv = str_replace($charbefore,$charafter,$csvto);
    echo '<form enctype="multipart/form-data" action="../exportlist/downloadcsv.php" method="post">
          <input name="csv" type="hidden" value="'.$csv.'" />
          <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
          </form>';
    }
echo "</td>";
echo "<td width='100'></td>";
echo "<td width='400'><img src ='graphe/graphe_cours_dispo_declar.php' height='500' width='500'></td>";
echo "</tr></table>";

//Promotions concernées / Promotions déclarées
$csv = "Nombre de Promotions concernées / Promotions déclarées £µ£";
echo "<table width='1200'><tr><td width='700' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Promotions concernées / Promotions déclarées</h3>";
echo "<table><tr>";
echo "<td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Promotions concernées</h3></td></tr>";
$csv .= "Composante;Promotions concernées£µ£";
foreach ($composantes as $composante) {

    echo "<tr>";
    echo "<td>".$composante->name."</td>";
    $sql = "SELECT promotions FROM mdl_ufr WHERE code = '$composante->code'";
    $nbrpromotions = $DB->get_record_sql($sql)->promotions;
    $DB->set_field('chiffres_ufr', 'nbavailablevets', $nbrpromotions, array('id' => $composante->id));
    $totalpromotions += $nbrpromotions;
//    $sql = "SELECT COUNT(DISTINCT vets.id) AS nbrvets
//	    FROM `mdl_cat_demandecours` niveau, mdl_cat_demandecours vets
//	    WHERE niveau.id = vets.parent_id AND niveau.parent_id = '".$composante["idcat"]."'";
//    $nbrvets = $DB->get_record_sql($sql)->nbrvets;
    $nbrvets = $composante->nbvets;
    if ($nbrvets) {
        $promotionspercent = round(100 * $nbrpromotions / $nbrvets, 1);
    } else {
        $promotionspercent = 0;
    }
    echo "<td style='text-align:center'>$nbrpromotions / $nbrvets ($promotionspercent%)</td>";
    echo "</tr>";
    $csv .= "".$composante->name.";$nbrpromotions / $nbrvets ($promotionspercent%);£µ£";
}
$totalnombrevets = round($totalpromotions *100 / $totalnbrvets, 1);
echo "<tr style ='font-weight:bold'><td>TOTAL</td><td style='text-align:center'>$totalpromotions / $totalnbrvets ($totalnombrevets%)</td></tr>";
$csv .= "Total;$totalpromotions / $totalnbrvets ($totalnombrevets%);£µ£";
echo "</table>";
if (isset($csv)) {
    $tabcsv = explode("£µ£", $csv);
    $charbefore = '"';
    $charafter = "'";
    $csvto = $csv;
    $csv = str_replace($charbefore,$charafter,$csvto);
    echo '<form enctype="multipart/form-data" action="../exportlist/downloadcsv.php" method="post">
          <input name="csv" type="hidden" value="'.$csv.'" />
          <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
          </form>';
    }
echo "</td>";
echo "<td width='100'></td>";
echo "<td width='400'><img src ='graphe/graphe_prmotion_dispo_dec.php' height='500' width='500'></td>";
echo "</tr></table>";
?>
</div>
<br>

<div onclick="flipflop('headers');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#731472;border-radius:5px 5px 0 0">
    Nombre de cours créés, d'étudiants inscrits à ces cours et d'étudiants réellement actifs
</div>
<div id ="headers" class="content" style='width:100%;display:none'>
<br>

<?php
$nbstudents = 0;
$totalactivestudents = 0;
$promotions = 0;
$sql = "SELECT COUNT(id) AS nbcourses FROM mdl_course WHERE category <> 122 AND category <> 243 AND category <> $CFG->catbrouillonsid";
$nbcourses = $DB->get_record_sql($sql)->nbcourses;
$totalcourses = $nbcourses;

//Cours créés sur la plateforme

$csv = "Cours créés sur la plateforme en 2017-2018 £µ£";
echo "<table width='1200'><tr><td width='700' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Cours créés sur la plateforme en 2017-2018</h3>";
echo "<table>";
echo "<tr><td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Cours créés sur la plateforme</h3></td></tr>";
$csv .= "Composante;Cours créés sur la plateforme£µ£";

foreach ($composantes as $composante) {

    echo "<tr>";
    $sql = "SELECT COUNT(id) AS nbcourses FROM mdl_course WHERE shortname LIKE '".$composante->code."%-%'";
    $nbcoursesincomposante = $DB->get_record_sql($sql)->nbcourses;
    $DB->set_field('chiffres_ufr', 'nbcreatedcourses', $nbcoursesincomposante, array('id' => $composante->id));
    //$sql = "SELECT COUNT(id) AS nbknowncourses FROM `mdl_cat_demandecours` WHERE code LIKE '".$composante["code"]."%-%'";
    $nbknowncourses = $composante->nbcourses;
    echo "<td>".$composante->name."</td>";
    if ($nbknowncourses) {
        $coursepercent = round($nbcoursesincomposante * 100 /$nbknowncourses, 1);
    } else {
        $coursepercent = 0;
    }
    echo "<td style='text-align:center'>$nbcoursesincomposante / $nbknowncourses ($coursepercent%)</td>";
    $nbcourses -= $nbcoursesincomposante;
    echo "</tr>";
    $csv .= $composante->name.";$nbcoursesincomposante / $nbknowncourses ($coursepercent%);£µ£";
}

$sql = "SELECT COUNT(id) AS nbsercentrcourses
        FROM mdl_course
        WHERE idnumber LIKE 'Y2017-8S%'";
$nbsercentrcourses = $DB->get_record_sql($sql)->nbsercentrcourses;
$nbcourses -= $nbsercentrcourses;
echo "<tr><td>Services centraux</td><td style='text-align:center'>$nbsercentrcourses</td></tr>";
$csv .= "Services centraux;$nbsercentrcourses;£µ£";
$totalallcourses = $totalknowncourses + $nbsercentrcourses;
$totalcourses -= $nbcourses;
$percentcreatedcourses = round($totalcourses * 100 / $totalallcourses, 1);
echo "<tr><td>TOTAL</td><td style='text-align:center'>$totalcourses / $totalallcourses ($percentcreatedcourses%)</td></tr>";
$csv .= "TOTAL;$totalcourses / $totalallcourses ($percentcreatedcourses%);£µ£";
echo "</table>";

if (isset($csv)) {

    $tabcsv = explode("£µ£", $csv);
    $charbefore = '"';
    $charafter = "'";
    $csvto = $csv;
    $csv = str_replace($charbefore,$charafter,$csvto);
    echo '<form enctype="multipart/form-data" action="../exportlist/downloadcsv.php" method="post">
          <input name="csv" type="hidden" value="'.$csv.'" />
          <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
          </form>';
}
echo "<td width='100'></td><td width='400'><img src ='graphe/graphe_cours_plateforme.php' height='500' width='500'></td></tr>";
echo "</table>";

//Etudiants inscrits

$csv = "Étudiants inscrits £µ£";
echo "<table width='1200'><tr><td width='700' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Étudiants inscrits</h3>";
echo "<table>";
echo "<tr><td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Étudiants inscrits</h3></td></tr>";
$csv .= "Composante;Étudiants inscrits£µ£";

$now = time();
$onemonthago = $now - 30 * 24 * 3600;

foreach ($composantes as $composante) {

    echo "<tr>";
    echo "<td>".$composante->name."</td>";

    //Nombre d'étudiants actifs pour la composante

    $sql = "SELECT COUNT(DISTINCT ul.userid) AS nbactivestudentsincomposante "
            . "FROM mdl_user_lastaccess ul, mdl_ufr_student u "
            . "WHERE ul.courseid > 1 AND u.userid = ul.userid AND u.student = 1 "
            . "AND u.ufrcode = '$composante->code' AND ul.timeaccess > $onemonthago";
    $nbactivestudentsincomposante = $DB->get_record_sql($sql)->nbactivestudentsincomposante;
    $DB->set_field('chiffres_ufr', 'nbactivestudents', $nbactivestudentsincomposante, array('id' => $composante->id));
    //$totalactivestudents += $nbactivestudentsincomposante;
    $sql = "SELECT COUNT(DISTINCT ra.userid) AS nbstudentsincomposante "
            . "FROM mdl_role_assignments ra, mdl_context x, mdl_course c, mdl_user u "
            . "WHERE c.shortname LIKE '$composante->code%' AND x.contextlevel = 50 AND x.instanceid = c.id "
            . "AND ra.contextid = x.id AND ra.roleid = 5 AND u.id = ra.userid AND u.idnumber > 0";
    $nbstudentsincomposante = $DB->get_record_sql($sql)->nbstudentsincomposante;
    $DB->set_field('chiffres_ufr', 'nbenroledstudents', $nbstudentsincomposante, array('id' => $composante->id));
    if ($nbstudentsincomposante < $nbactivestudentsincomposante) {

        $nbstudentsincomposante = $nbactivestudentsincomposante;
    }
    //$sql = "SELECT COUNT(userid) AS nb FROM mdl_ufr_student WHERE ufrcode = '".$composante["code"]."' AND student = 1";
    $nbknownstudentsincomposante = $composante->nbstudents;
    if ($nbstudentsincomposante > $nbknownstudentsincomposante) {

        $nbstudentsincomposante = $nbknownstudentsincomposante;
    }

    $nbstudents += $nbstudentsincomposante;

    if ($nbknownstudentsincomposante) {

        $studentpercent = round($nbstudentsincomposante * 100 /$nbknownstudentsincomposante, 1);
        $activepercent = round($nbactivestudentsincomposante * 100 /$nbknownstudentsincomposante, 1);
    } else {

        $studentpercent = 0;
        $activepercent = 0;
    }
    echo "<td style='text-align:center'>$nbstudentsincomposante / $nbknownstudentsincomposante ($studentpercent%)</td>";
	echo "</tr>";
	$csv .= $composante->name.";$nbstudentsincomposante / $nbknownstudentsincomposante ($studentpercent%);£µ£";
}
if ($totalknownstudents) {

    $percentstudents = round($nbstudents * 100 / $totalknownstudents, 1);
} else {

	$percentstudents = 0;
}

echo "<tr><td>TOTAL</td><td style='text-align:center'>$nbstudents / $totalknownstudents ($percentstudents%)</td></tr>";
$csv .= "TOTAL;$nbstudents / $totalknownstudents ($percentstudents%);£µ£";
echo "</table>";
if (isset($csv)) {

    $tabcsv = explode("£µ£", $csv);
    $charbefore = '"';
    $charafter = "'";
    $csvto = $csv;
    $csv = str_replace($charbefore,$charafter,$csvto);
    echo '<form enctype="multipart/form-data" action="../exportlist/downloadcsv.php" method="post">
          <input name="csv" type="hidden" value="'.$csv.'" />
          <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
          </form>';
}
echo "<td width='100'></td><td width='400'><img src ='graphe/graphe_etudiants_inscrits.php' height='500' width='500'></td></tr>";
echo "</table>";

// Etudiants actifs.
$csv = "Étudiants actifs £µ£";
echo "<table width='1200'><tr><td width='700' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Étudiants actifs</h3>";
echo "<table>";
echo "<tr><td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Étudiants actifs</h3></td></tr>";
$csv .= "Composante;Étudiants actifs£µ£";
$composantes = $DB->get_records('chiffres_ufr');
foreach ($composantes as $composante) {

    echo "<tr>";
    echo "<td>".$composante->name."</td>";
    $nbactivestudentsincomposante = $composante->nbactivestudents;
    $totalactivestudents += $nbactivestudentsincomposante;
    $nbstudentsincomposante = $composante->nbenroledstudents;
    if ($nbstudentsincomposante < $nbactivestudentsincomposante) {

        $nbstudentsincomposante = $nbactivestudentsincomposante;
    }
    $nbstudents += $nbstudentsincomposante;

    //Nombre d'étudiants inscrits à la plateforme pour cette composante
    $nbknownstudentsincomposante = $composante->nbstudents;
    if ($nbstudentsincomposante > $nbknownstudentsincomposante) {

        $nbstudentsincomposante = $nbknownstudentsincomposante;
    }
    if ($nbknownstudentsincomposante) {

        $studentpercent = round($nbstudentsincomposante * 100 /$nbknownstudentsincomposante, 1);
        $activepercent = round($nbactivestudentsincomposante * 100 /$nbknownstudentsincomposante, 1);
    } else {

        $studentpercent = 0;
        $activepercent = 0;
    }
    echo "<td style='text-align:center'>$nbactivestudentsincomposante / $nbknownstudentsincomposante ($activepercent%)</td>";
    $csv .= $composante->name.";$nbactivestudentsincomposante / $nbknownstudentsincomposante ($activepercent%);£µ£";
}
if ($totalknownstudents) {

    $percentactivestudents = round($totalactivestudents * 100 / $totalknownstudents, 1);
} else {

	$percentactivestudents = 0;
}

echo "<tr><td>TOTAL</td><td style='text-align:center'>$totalactivestudents / $totalknownstudents ($percentactivestudents%)</td></tr>";
$csv .= "Total;$totalactivestudents / $totalknownstudents ($percentactivestudents%);£µ£";
echo "</table>";
if (isset($csv)) {

    $tabcsv = explode("£µ£", $csv);
    $charbefore = '"';
    $charafter = "'";
    $csvto = $csv;
    $csv = str_replace($charbefore,$charafter,$csvto);
    echo '<form enctype="multipart/form-data" action="../exportlist/downloadcsv.php" method="post">
          <input name="csv" type="hidden" value="'.$csv.'" />
          <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
          </form>';
}

echo "<td width='100'></td><td width='400'><img src ='graphe/graphe_etudiants_actifs.php' height='500' width='500'></td></tr>";
echo "</table>";
echo "<p>Sont considérés comme actifs les étudiants qui, au cours des 30 derniers jours, se sont connectés à au moins un cours (pas juste à la plateforme).</p>";

//Promotions concernées

$csv = "Promotions concernées £µ£";
echo "<table width='1200'><tr><td width='700' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Promotions concernées</h3>";
echo "<table>";
echo "<tr><td bgcolor='#780D68'><FONT COLOR='#731472'><h3>Composante</h3></td>";
echo "<td style='text-align:center' bgcolor='#780D68'><FONT COLOR='#731472'><h3>Promotions concernées</h3></td></tr>";
$csv .= "Composante;Promotions concernées£µ£";
foreach ($composantes as $composante) {
    echo "<tr>";
    echo "<td>$composante->name</td>";
    $sql= "SELECT count(distinct course.category) as promotions
  	   FROM mdl_course course, mdl_course_categories niveaux, mdl_course_categories vets
	   WHERE niveaux.parent = '$composante->categoryid'
	   AND vets.parent = niveaux.id
	   AND vets.id = course.category";
    $nbpromotions = $DB->get_record_sql($sql)->promotions;
    $DB->set_field('chiffres_ufr', 'nbcreatedvets', $nbpromotions, array('id' => $composante->id));
//    $sql = "SELECT COUNT(DISTINCT vets.id) AS nbrvets
//	    FROM `mdl_cat_demandecours` niveau, mdl_cat_demandecours vets
//	    WHERE niveau.id = vets.parent_id AND niveau.parent_id = '".$composante["idcat"]."'";
//    $nbrvets = $DB->get_record_sql($sql)->nbrvets;
    $nbrvets = $composante->nbvets;
    if ($nbrvets) {
        $promotionspercent = round($nbpromotions * 100 /$nbrvets, 1);
    } else {
        $promotionspercent = 0;
    }
    echo "<td style='text-align:center'>$nbpromotions / $nbrvets ($promotionspercent%)</td>";
    $csv .= $composante->name.";$nbpromotions / $nbrvets ($promotionspercent%);£µ£";
}
$sql= "SELECT COUNT(DISTINCT category) AS total FROM mdl_course";
$totalpromotion = $DB->get_record_sql($sql)->total;
$percentpromotionss = round($totalpromotion * 100 / $totalnbrvets, 1);
echo "<tr><td>TOTAL</td><td style='text-align:center'>$totalpromotion / $totalnbrvets ($percentpromotionss%)</td></tr>";
$csv .= "Total;$totalpromotion / $totalnbrvets ($percentpromotionss%);£µ£";
echo "</table>";
if (isset($csv)) {
    $tabcsv = explode("£µ£", $csv);
    $charbefore = '"';
    $charafter = "'";
    $csvto = $csv;
    $csv = str_replace($charbefore,$charafter,$csvto);
    echo '<form enctype="multipart/form-data" action="../exportlist/downloadcsv.php" method="post">
          <input name="csv" type="hidden" value="'.$csv.'" />
          <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
          </form>';
}
echo "<td width='100'></td><td width='400'><img src ='graphe/graphe_promotions_concernees.php' height='500' width='500'></td></tr>";
echo "</table>";
?>
</div><br>


<!--<div onclick="flipflop('headerss');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#731472;border-radius:5px 5px 0 0">
    Activités utilisées
</div>
<div id ="headerss" class="content" style='width:100%;display:none'>
    <br>
    <p>Certaines activités sont créées automatiquement avec chaque nouveau cours. Celles qui ne servent pas ne sont pas comptabilisées ici.</p>
    <center><img src ='graphe/graphe_activities.php' height='700' width='700'/></center>
</div><br>-->

<div onclick="flipflop('hders');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#731472;border-radius:5px 5px 0 0">
    Divers
</div>

<div id ="hders" class="content" style='width:100%;display:none'>
    <br>
<?php

$timestatbeginning = 1498860000;

$sql = "SELECT COUNT(DISTINCT userid) AS nbdistinctteachers FROM mdl_role_assignments WHERE roleid = 3 "
        . "AND timemodified > $timestatbeginning";
$nbdistinctteachers = $DB->get_record_sql($sql)->nbdistinctteachers;
echo "<strong>".format_number($nbdistinctteachers)."</strong> enseignants distincts ont créé des cours sur la plateforme.<br><br>";
$sql = "SELECT COUNT(DISTINCT userid) AS nbusers FROM mdl_role_assignments WHERE roleid > 2 "
        . "AND roleid < 6 AND contextid <> 48617";
$nbusers = $DB->get_record_sql($sql)->nbusers;
$sql = "SELECT COUNT(DISTINCT userid) AS nbusers FROM mdl_role_assignments WHERE roleid = 3";
$nbteachers = $DB->get_record_sql($sql)->nbusers;
$nbstudents = $nbusers - $nbteachers;

$sql = "SELECT COUNT(id) AS nbusers FROM mdl_user";
$nbusers = $DB->get_record_sql($sql)->nbusers;
$sql = "SELECT COUNT(id) AS nbusers FROM mdl_user "
                    . "WHERE (email LIKE '%@u-cergy.fr' OR email LIKE '%@iufm.u-cergy.fr' OR email LIKE '%@sciencespo-saintgermainenlaye.fr')";
$nbteachers = $DB->get_record_sql($sql)->nbusers;
$nbstudents = $nbusers - $nbteachers;

//echo "<strong>$nbusers</strong> utilisateurs inscrits à la plateforme, dont <strong>$nbstudents</strong> étudiants.<br><br>";

$sql = "SELECT number FROM mdl_chiffres WHERE name = 'weekconnections'";
$weekconnections = $DB->get_record_sql($sql)->number;
echo "<strong>".format_number($weekconnections)."</strong> connexions depuis une semaine.<br><br>";
echo "<center><img src ='graphe/graphe_cnx_sem.php'></center><br>";
$sql = "SELECT number FROM mdl_chiffres WHERE name = 'nbgrades'";
$nbgrades = $DB->get_record_sql($sql)->number;
echo "<strong>".format_number($nbgrades)."</strong> notes attribuées depuis la création de la plateforme.<br><br>";

$sql = "SELECT COUNT(id) AS nbfiles FROM mdl_files WHERE timecreated > 1498860000";
$nbfiles = $DB->get_record_sql($sql)->nbfiles;
echo "<strong>".format_number($nbfiles)."</strong> fichiers déposés.<br><br>";

$sql = "SELECT number FROM mdl_chiffres WHERE name = 'nbviews'";
$nbviews = $DB->get_record_sql($sql)->number;
echo "<strong>".format_number($nbviews)."</strong> consultations de cours ou documents depuis le 01/07/2017.<br><br>";

$sql = "SELECT number FROM mdl_chiffres WHERE name = 'nblogs'";
$nblogs = $DB->get_record_sql($sql)->number;
echo "<strong>".format_number($nblogs)."</strong> actions réalisées sur la plateforme "
        . "(consultation d'un document, envoi d'un message, remise d'un devoir, etc.) depuis le 01/07/2017.<br><br>";
?>
</div>
<br>

<?php
echo $OUTPUT->footer();

//Groupe les chiffres d'un grand nombre par 3
function format_number($nb) {
    $nblength = strlen($nb);
    $nbgroups = ceil($nblength / 3);
    $modulo = $nblength % 3;
    $result = substr($nb, 0, $modulo);
    $nbremains = substr($nb, $modulo);
    for ($i = 1; $i < $nbgroups; $i++) {
        $result .= " ".substr($nbremains, 0, 3);
        $nbremains = substr($nbremains, 3);
    }
    if (!$modulo) {
        $result .= " ".$nbremains;
    }
    return $result;
}
