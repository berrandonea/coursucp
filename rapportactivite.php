<title>
Rapport d'activite
</title>

<?php
//define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

$context = context_course::instance(1);

require_capability('report/log:view', $context);

    
    
$times = array(1404165600,1406844000,1409522400,1412114400,1414796400,1417388400,1420066800,1422745200);
$composantes = array('1', '2', '3', '4', '5', '6', '7', '9', 'A', 'B');

$nom['1'] = "UFR Droit";
$nom['2'] = "UFR Eco-Gestion";
$nom['3'] = "UFR LEI";
$nom['4'] = "UFR LSH";
$nom['5'] = "UFR ST";
$nom['6'] = "IPAG";
$nom['7'] = "IUT";
$nom['9'] = "Inst. Education";
$nom['A'] = "ESPE";
$nom['B'] = "Sciences Po";


foreach($times as $time) {
    $nbcourses[$time] = 0;
    $nbstudents[$time] = 0;
}

echo "<h1>Plateforme pedagogique</h1>";
echo "<h2>Nombre de cours crees dans chaque composante avant le debut de chaque mois</h2>";

echo "<table style='text-align:center'>";
echo "<tr><td></td><td>Juillet</td><td>Aout</td><td>Septembre</td><td>Octobre</td><td>Novembre</td><td>Decembre</td>"
    . "<td>Janvier</td><td>Fevrier</td></tr>";

foreach ($composantes as $composante) {
    echo "<tr>";
    echo "<td>".$nom[$composante]."</td>";
    
    foreach($times as $time) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_course WHERE shortname LIKE '$composante%' AND timecreated < $time";
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
        $nbcourses[$time] += $nb;
    }
    
    echo "</tr>";
}



echo "<tr>";
echo "<td>SEFIAP</td>";
foreach($times as $time) {
    $sql = "SELECT COUNT(id) AS nb FROM mdl_course "
            . "WHERE (category = 93 OR category = 241 OR category = 242 OR category = 244 OR category = 245 OR category = 333) "
            . "AND timecreated < $time";
    $nb = $DB->get_record_sql($sql)->nb;
    echo "<td>$nb</td>";
    $nbcourses[$time] += $nb;
}
echo "</tr>";

echo "<tr>";
echo "<td>Autres</td>";
foreach($times as $time) {
    $sql = "SELECT COUNT(id) AS nbtotal FROM mdl_course "
            . "WHERE (category <> 122 AND category <> 243 AND category <> $CFG->catbrouillonsid) "
            . "AND timecreated < $time";
    $nbtotal[$time] = $DB->get_record_sql($sql)->nbtotal;
    echo "<td>".($nbtotal[$time] - $nbcourses[$time])."</td>";
}
echo "</tr>";

echo "<tr style='font-weight:bold'>";
echo "<td>TOTAL</td>";
foreach ($times as $time) {
    echo "<td>".$nbtotal[$time]."</td>";
}
echo "</tr>";


$sql = "SELECT COUNT(id) AS nbcourses FROM mdl_course WHERE category <> 122 AND category <> 243 AND category <> $CFG->catbrouillonsid";

echo "</table>";

echo "<br><br>";

echo "<h2>Nombre d'etudiants inscrits a des cours de la plateforme avant le debut de chaque mois</h2>";

echo "<table style='text-align:center'>";
echo "<tr><td></td><td>Octobre</td><td>Novembre</td><td>Decembre</td>"
    . "<td>Janvier</td><td>Fevrier</td></tr>";

foreach ($composantes as $composante) {
    echo "<tr>";
    echo "<td>".$nom[$composante]."</td>";
    
    foreach($times as $time) {
        if ($time > 1409522400) {
            $sql = "SELECT COUNT(DISTINCT ra.userid) AS nb "
                . "FROM mdl_role_assignments ra, mdl_context x, mdl_course c "
                . "WHERE c.shortname LIKE '$composante%' AND x.contextlevel = 50 AND x.instanceid = c.id "
                . "AND ra.contextid = x.id AND ra.roleid = 5 "
                . "AND ra.timemodified < $time";
            $nb = $DB->get_record_sql($sql)->nb;
            echo "<td>$nb</td>";
            $nbstudents[$time] += $nb;
        }
    }
    echo "<tr>";
}
echo "<tr style='font-weight:bold'>";
echo "<td>TOTAL</td>";
foreach ($times as $time) {
    if ($time > 1409522400) {
        echo "<td>".$nbstudents[$time]."</td>";
    }
}
echo "</tr>";
echo "</table>";

echo "<br><br>";

echo "<h2>Activites utilisees avant le debut de chaque mois</h2>";
echo "<table style='text-align:center'>";
echo "<tr><td></td><td>Octobre</td><td>Novembre</td><td>Decembre</td>"
    . "<td>Janvier</td><td>Fevrier</td></tr>";

echo "<tr>";
echo "<td>Ressources</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_resource WHERE timemodified < $time";
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Devoirs</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_assign WHERE timemodified < $time";
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Forums</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(DISTINCT forum) AS nb FROM `mdl_forum_discussions` WHERE timemodified < $time";
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Salons de tchat</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(DISTINCT chatid) AS nb FROM `mdl_chat_messages` WHERE timestamp < $time";        
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Pages web</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_page WHERE timemodified < $time";        
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Quiz</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(DISTINCT quiz) AS nb "
                . "FROM `mdl_quiz_attempts` "
                . "WHERE timestart < $time";        
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>SCORM</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_scorm WHERE timemodified < $time";        
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Feedbacks</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_feedback WHERE timemodified < $time";        
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Lecons</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_lesson WHERE timemodified < $time";        
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";


echo "<tr>";
echo "<td>Visioconferences</td>";
$dates = array("2014-10", "2014-11", "2014-12", "2015-01", "2015-02");
foreach($dates as $date) {    
    $sql = "SELECT COUNT(id) AS nb FROM mdl_bigbluebuttonbn WHERE sefiaptime < '$date-01 00:00:00'";        
    $nb = $DB->get_record_sql($sql)->nb;
    echo "<td>$nb</td>";    
}
echo "</tr>";
echo "<tr>";
echo "<td>Ateliers</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_workshop WHERE timemodified < $time";        
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Wikis</td>";
foreach($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(DISTINCT s.wikiid) AS nb "
                . "FROM `mdl_wiki_subwikis` s, mdl_wiki_pages p "
                . "WHERE p.timecreated < $time AND s.id = p.subwikiid";        
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "</table>";

echo "<br><br>";

echo "<h2>Divers</h2>";

echo "<table style='text-align:center'>";
echo "<tr><td></td><td>Octobre</td><td>Novembre</td><td>Decembre</td><td>Janvier</td><td>Fevrier</td></tr>";
echo "<tr>";
echo "<td>Enseignants ayant des cours sur la plateforme</td>";
foreach ($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(DISTINCT userid) AS nb FROM mdl_role_assignments WHERE roleid = 3 AND timemodified < $time";
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Copies virtuelles notees</td>";
foreach ($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_grade_grades WHERE timecreated > 0 AND timecreated < $time";    
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "<tr>";
echo "<td>Fichiers deposes</td>";
foreach ($times as $time) {
    if ($time > 1409522400) {
        $sql = "SELECT COUNT(id) AS nb FROM mdl_files WHERE timecreated > 0 AND timecreated < $time";    
        $nb = $DB->get_record_sql($sql)->nb;
        echo "<td>$nb</td>";
    }
}
echo "</tr>";
echo "</table>";

echo "<br><br>";

?>
