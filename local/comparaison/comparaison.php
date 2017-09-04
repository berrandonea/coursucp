<?php
// $Id: inscriptions_massives.php 356 2010-02-27 13:15:34Z ppollet $
/**
 * A bulk enrolment plugin that allow teachers to massively enrol existing accounts to their courses,
 * with an option of adding every user to a group
 * Version for Moodle 1.9.x courtesy of Patrick POLLET & Valery FREMAUX  France, February 2010
 * Version for Moodle 2.x by pp@patrickpollet.net March 2012
 */

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once ('lib.php');
require_once ('comparaison_form.php');


/// Get params

$id = required_param('id', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    error("Course is misconfigured");
}


/// Security and access check

require_course_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/role:assign', $context);

$sql = "SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid = $course->id";
$contextid = $DB->get_record_sql($sql)->id;

/// Start making page
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/local/comparaison/comparaison.php', array('id'=>$id));

$strinscriptions = get_string('comparaison', 'local_comparaison');

$PAGE->set_title($course->fullname . ': ' . $strinscriptions);
$PAGE->set_heading($course->fullname . ': ' . $strinscriptions);

echo $OUTPUT->header();

$mform = new comparaison_form($CFG->wwwroot . '/local/comparaison/comparaison.php', array (
	'course' => $course,
	'context' => $context
));

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', array('id'=>$id)));
} else
if ($data = $mform->get_data(false)) { // no magic quotes
    echo $OUTPUT->heading($strinscriptions);
    $iid = csv_import_reader::get_new_iid('uploaduser');       
    $content = $mform->get_file_content('attachment');   
    
    //Ouverture de DOKEOS_Etudiants_Inscriptions.xml
    $xmldoc = new DOMDocument();
    $fileopening = $xmldoc->load('/home/referentiel/DOKEOS_Etudiants_Inscriptions.xml');    
    
    if ($fileopening == false) {
        echo "Impossible de lire le fichier source.<br>";
    }   
    $xpathvar = new Domxpath($xmldoc);    
    
    
    //Lecture du fichier XML reçu
    $xmlstudents = array();
    $nbxmlstudents = 0;
    $racine = simplexml_load_string($content);        
    $listgelp = $racine->LIST_G_ELP;    
    foreach($listgelp as $gelp) {
        foreach ($gelp as $elp) {
            $listgcip = $elp->LIST_G_CIP;
            foreach($listgcip as $gcip) {
                foreach($gcip as $cip) {
                    $listgetu = $cip->LIST_G_ETU;
                    foreach($listgetu as $getu) {
                        foreach ($getu as $etu) {
                            $xmlstudents[$nbxmlstudents] = $etu;                            
                            $nbxmlstudents++;                            
                        }
                    }
                }
            }
        }        
    }

    //Recherche des logins pour chaque code-étudiant    
    foreach ($xmlstudents as $xmlstudent) {
        $query = "//Student[@StudentETU='$xmlstudent->COD_ETU']";        
        $querystudents = $xpathvar->query($query);        
        
        foreach($querystudents as $querystudent) {
            $studentuid = $querystudent->getAttribute('StudentUID');            
            
            if ($studentuid) {
                $xmlstudent->username = $studentuid;                
            } else {
                /*$celcat_lastname = $querystudent->getAttribute('StudentName');            
                if ($celcat_lastname) {
                    //Si le login n'est pas renseigné dans DOKEOS_Etudiants_Inscriptions.xml
                    $xmlstudent->celcat_firstname = $querystudent->getAttribute('StudentFirstName');
                    $xmlstudent->celcat_lastname = $celcat_lastname;            
                }*/
                $xmlstudent->username = "-";                                
            }               
        }                
    }
    

    
    //Différences entre les deux listes : 1er tableau
    echo "<h2>Etudiants présents dans le fichier XML mais pas dans le cours</h2>";
    $csv = "Etudiants présents dans le fichier XML mais pas dans le cours£µ£";           
    
    //Ligne des titres de colonnes
    echo "<table border = '1' align = 'center' border-collapse>";
    echo "<tr><td bgcolor='#780D68'><FONT COLOR='white'><h3>Code étudiant</h3></td><td bgcolor='#780D68'><FONT COLOR='white'><h3>Login</h3></td>";
    echo "<td bgcolor='#780D68'><FONT COLOR='white'><h3>Nom</h3></td>";
    echo "<td bgcolor='#780D68'><FONT COLOR='white'><h3>Prénom</h3></td>";
    echo "<td bgcolor='#780D68'><FONT COLOR='white'><h3>Courriel</h3></td>";
    $csv .= "Code étudiant;Login;Nom;Prénom;Courriel;";        
    echo "</tr>";
    $csv .= "£µ£";

    //Etudiants présents seulement dans le fichier XML
    foreach($xmlstudents as $xmlstudent) { 
        
        if ($xmlstudent->username == "-") {
            $sql = "SELECT id, username, firstname, lastname, email FROM mdl_user WHERE firstname = '$xmlstudent->LIB_PR1_IND' AND lastname = '$xmlstudent->LIB_NOM_PAT_IND'";
            //echo "$sql<br>";
            /*$xmlstudentbdddata = $DB->get_record_sql($sql);
            
            
            echo "<tr>";
            echo "<td><b>$xmlstudent->COD_ETU</b></td><td><b> - </b></td>"
                    . "<td><b>$xmlstudent->LIB_NOM_PAT_IND</b></td><td><b> - </b></td>"
                    . "<td><b>$xmlstudent->LIB_PR1_IND</b></td><td><b> - </b></td>"
                    . "<td><b> - </b></td>";        
            $csv .= $xmlstudent->COD_ETU."; - ;".$xmlstudent->LIB_NOM_PAT_IND."; - ;".$xmlstudent->LIB_PR1_IND."; - ; - ;";               
            echo "</tr>";
            $csv .= "£µ£";*/
        } else {
            $sql = "SELECT id, username, firstname, lastname, email FROM mdl_user WHERE username = '$xmlstudent->username'";            
        }
        $xmlstudentbdddata = $DB->get_record_sql($sql);            

        $sql = "SELECT COUNT(id) as isincourse FROM mdl_role_assignments WHERE contextid = $contextid AND userid = $xmlstudentbdddata->id";                 
        $isincourse = $DB->get_record_sql($sql)->isincourse;            

        if ($isincourse == 0) {            
            echo "<tr>";
            echo "<td><b>$xmlstudent->COD_ETU</b></td><td><b>$xmlstudent->username</b></td>"
                    . "<td><b>$xmlstudent->LIB_NOM_PAT_IND</b>"
                    . "<td><b>$xmlstudent->LIB_PR1_IND</b></td>"
                    . "<td><b>$xmlstudentbdddata->email</b></td>";        
            $csv .= $xmlstudent->COD_ETU.";$xmlstudent->username;".$xmlstudent->LIB_NOM_PAT_IND.";$xmlstudentbdddata->lastname;".
                    $xmlstudent->LIB_PR1_IND.";$xmlstudentbdddata->firstname;$xmlstudentbdddata->email;";               
            echo "</tr>";
            $csv .= "£µ£";                
        }                
    }
    
    echo "</table>";
    
    echo '<form enctype="multipart/form-data" action="downloadcsv.php" method="post">
            <fieldset><input name="csv" type="hidden" value="'.$csv.'" />
            <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p></fieldset>
          </form>';
    
    
    echo "<br><br>";
    
    
    //Différences entre les deux listes : 2ème tableau
    echo "<h2>Etudiants présents dans le cours mais pas dans le fichier XML</h2>";
    $csv2 = "Etudiants présents dans le cours mais pas dans le fichier XML£µ£";           
    
    //Ligne des titres de colonnes
    echo "<table border = '1' align = 'center' border-collapse>";
    echo "<tr><td bgcolor='#780D68'><FONT COLOR='white'><h3>Login</h3></td>";
    echo "<td bgcolor='#780D68'><FONT COLOR='white'><h3>Nom</h3></td>";
    echo "<td bgcolor='#780D68'><FONT COLOR='white'><h3>Prénom</h3></td>";
    echo "<td bgcolor='#780D68'><FONT COLOR='white'><h3>Courriel</h3></td>";
    $csv2 .= "Login;Nom;Prénom;Courriel;";        
    echo "</tr>";
    $csv2 .= "£µ£";

    //Etudiants présents seulement dans le cours
    $sql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email FROM mdl_user u, mdl_role_assignments ra "
            . "WHERE ra.userid = u.id AND ra.contextid = $contextid AND roleid = 5";
    $coursestudents = $DB->get_recordset_sql($sql);
    
    $doubts = array();
    $nbdoubts = 0;
    foreach ($coursestudents as $coursestudent) {
        $inxml = 0;
        foreach ($xmlstudents as $xmlstudent) {
            if ($xmlstudent->username == $coursestudent->username) {
                $inxml = 1;
                unset($xmlstudent);
                break;
            }
            if ($xmlstudent->username == "-") {
                if ((strtolower($xmlstudent->LIB_PR1_IND) == strtolower($coursestudent->firstname))
                    &&(strtolower($xmlstudent->LIB_NOM_PAT_IND) == strtolower($coursestudent->lastname))) {                                  
                    $inxml = 1;
                    $doubts[$nbdoubts] = $coursestudent;
                    $nbdoubts++;
                    unset($xmlstudent);
                    break;
                }
            }
        }
        if ($inxml == 0) {
            echo "<tr>";
            echo "<td><b>$coursestudent->username</b></td>"
                    . "<td><b>$coursestudent->lastname</b></td>"
                    . "<td><b>$coursestudent->firstname</b></td>"
                    . "<td><b>$coursestudent->email</b></td>";        
            $csv2 .= "$coursestudent->username;$coursestudent->lastname;$coursestudent->firstname;$coursestudent->email;";               
            echo "</tr>";
            $csv2 .= "£µ£";                
        }
    }
    
    echo "</table>";
    
    echo '<form enctype="multipart/form-data" action="downloadcsv.php" method="post">
            <fieldset><input name="csv" type="hidden" value="'.$csv2.'" />
            <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p></fieldset>
          </form>';
    
    //Etudiants incertains : 3ème tableau
    echo "<h2>Etudiants incertains</h2>";
    $csv3 = "Etudiants incertains£µ£";
    
    echo "Ce nom et ce prénom apparaissent à la fois dans le fichier XML et dans le cours. Mais il manque une information dans CELCAT pour faire le lien entre les deux. Il est très probable qu'il s'agisse bien du même étudiant mais nous ne pouvons pas le garantir à 100%.<br><br>";
    $csv3 .= "Ce nom et ce prénom apparaissent à la fois dans le fichier XML et dans le cours. Mais il manque une information dans CELCAT pour faire le lien entre les deux. Il est très probable qu'il s'agisse bien du même étudiant mais nous ne pouvons pas le garantir à 100%.£µ£";
    
    //Ligne des titres de colonnes
    echo "<table border = '1' align = 'center' border-collapse>";
    echo "<tr><td bgcolor='#780D68'><FONT COLOR='white'><h3>Login</h3></td>";
    echo "<td bgcolor='#780D68'><FONT COLOR='white'><h3>Nom</h3></td>";
    echo "<td bgcolor='#780D68'><FONT COLOR='white'><h3>Prénom</h3></td>";
    echo "<td bgcolor='#780D68'><FONT COLOR='white'><h3>Courriel</h3></td>";
    $csv3 .= "Login;Nom;Prénom;Courriel;";        
    echo "</tr>";
    $csv3 .= "£µ£";

    foreach($doubts as $doubt) {
        echo "<tr>";
        echo "<td><b>$doubt->username</b></td>"
                . "<td><b>$doubt->lastname</b></td>"
                . "<td><b>$doubt->firstname</b></td>"
                . "<td><b>$doubt->email</b></td>";        
        $csv3 .= "$doubt->username;$doubt->lastname;$doubt->firstname;$doubt->email;";               
        echo "</tr>";
        $csv3 .= "£µ£";  
    }
    echo "</table>";
    
    
    echo '<form enctype="multipart/form-data" action="downloadcsv.php" method="post">
            <fieldset><input name="csv" type="hidden" value="'.$csv3.'" />
            <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p></fieldset>
          </form>';
    
    echo $OUTPUT->continue_button(new moodle_url('/enrol/users.php',array('id'=>$id))); // Retour à la liste des utilisateurs du cours
    echo $OUTPUT->footer($course);
    die();
}


echo $OUTPUT->heading_with_help($strinscriptions, 'comparaison', 'local_comparaison','icon',get_string('comparaison', 'local_comparaison'));
echo $OUTPUT->box (get_string('comparaison_info', 'local_comparaison'), 'center');
$mform->display();
echo $OUTPUT->footer($course);




?>

