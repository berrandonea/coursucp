<?php

require('../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

$PAGE->set_url('/enrol/demandes.php');
$PAGE->set_pagelayout('report');
 
$course = $DB->get_record('course', array('id'=>1), '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);


$PAGE->set_title("Demandes d'inscription aux cours");
$PAGE->set_heading("Demandes d'inscription aux cours");

$paramreject = optional_param('reject', 0, PARAM_INT);
$paramenrol = optional_param('enrol', 0, PARAM_INT);
$paramall = optional_param('all', 0, PARAM_INT); //1 : Accepter tous, 2 : Accepter tous si bonne VET, 3 : Refuser tous, 4 : Refuser tous si mauvaise VET

//REJET D'UNE DEMANDE
if ($paramreject) {
    rejectenroldemand($paramreject);    
}

//ACCEPTATION D'UNE DEMANDE
if ($paramenrol) {
    acceptenroldemand($paramenrol);
}


if ($paramall) {
    $sql = "SELECT ae.studentid, ae.courseid, ae.id "
         . "FROM mdl_asked_enrolments ae, mdl_context x, mdl_role_assignments ra "
         . "WHERE ra.userid = $USER->id AND ra.roleid = 3 "
         . "AND ra.contextid = x.id  AND x.contextlevel = 50 AND x.instanceid = ae.courseid "
         . "AND ae.answererid = 0";
    //echo "$sql<br>";

    $askedenrolments = $DB->get_recordset_sql($sql);    
    foreach ($askedenrolments as $askedenrolment) {       
        switch ($paramall) {
            case 1: //Accepter tous
                acceptenroldemand($askedenrolment->id);
                break;

            case 2: //Accepter tous si bonne VET
                $sql = "SELECT COUNT(sv.id) AS goodpromo "
                     . "FROM mdl_course c, mdl_student_vet sv "
                     . "WHERE c.id = $askedenrolment->courseid "
                     . "AND c.category = sv.categoryid "
                     . "AND sv.studentid = $askedenrolment->studentid";
                $goodpromo = $DB->get_record_sql($sql)->goodpromo;
                if ($goodpromo) {
                    acceptenroldemand($askedenrolment->id);
                }
                break;
            
            case 3: //Refuser tous
                rejectenroldemand($askedenrolment->id);
                break;

            case 4: //Refuser tous si mauvaise VET
                $sql = "SELECT COUNT(sv.id) AS goodpromo "
                     . "FROM mdl_course c, mdl_student_vet sv "
                     . "WHERE c.id = $askedenrolment->courseid "
                     . "AND c.category = sv.categoryid "
                     . "AND sv.studentid = $askedenrolment->studentid";
                $goodpromo = $DB->get_record_sql($sql)->goodpromo;
                if (!$goodpromo) {
                    rejectenroldemand($askedenrolment->id);
                }
                break;                
            
            default:
                break;
        }
    }
}




echo $OUTPUT->header();


?>
<h2>Demandes envoyées</h2>
<a href='<?php echo $CFG->wwwroot; ?>/course/index.php'>Ajouter une demande <strong>+</strong></a><br><br>
Vous avez demandé votre inscription au(x) cours suivant(s) :<br><br>
    
<table border-collapse>
    <tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>		    	
    	<td>VET du cours</td>    	
    	<td>Cours</td>
        <td>Demande le</td>
    	<td>Réponse</td>
    	<td>Réponse le</td>
    	<td>Réponse par</td>    	
    </tr>

    <?php
    $sql = "SELECT id, courseid, askedat, answer, answeredat, answererid FROM mdl_asked_enrolments WHERE studentid = $USER->id";        
    $askedenrolments = $DB->get_recordset_sql($sql);    
    foreach ($askedenrolments as $askedenrolment) {            
        echo "<tr align = 'center'>";
        if (((time()- $askedenrolment->answeredat) < 7 * 24 * 3600)|| ($askedenrolment->answererid == 0)) {
            $coursesql = "SELECT a.name, c.fullname, c.idnumber FROM mdl_course c, mdl_course_categories a WHERE c.id = $askedenrolment->courseid AND a.id = c.category";                    
            $askedenrolmentcourse = $DB->get_record_sql($coursesql);

            echo "<td>$askedenrolmentcourse->name</td>";
            echo "<td><a href='$CFG->wwwroot/course/view.php?id=$askedenrolment->courseid'>($askedenrolmentcourse->idnumber) $askedenrolmentcourse->fullname</td>";
            echo "<td>".date("d/m/Y", $askedenrolment->askedat)."</td>";

            if ($askedenrolment->answererid > 0) {
                if ($askedenrolment->answer == 'Oui') {
                    echo "<td style='color:green'>Oui</td>";
                } else {
                    echo "<td style='color:red'>Non</td>";
                }
                echo "<td>".date("d/m/Y", $askedenrolment->answeredat)."</td>";

                $answerersql = "SELECT firstname, lastname FROM mdl_user WHERE id = $askedenrolment->answererid";
                $answerer = $DB->get_record_sql($answerersql);                    
                echo "<td><a href='$CFG->wwwroot/user/view.php?id=$askedenrolment->answererid'>$answerer->firstname $answerer->lastname</a></td>";                    
            } else {
                echo "<td> - </td><td> - </td><td> - </td>";
            }                
        }
        echo "</tr>";
    }
    ?>    
</table>

<?php

//Si l'utilisateur est un enseignant
$sql = "SELECT COUNT(id) AS isteacher FROM mdl_role_assignments WHERE (roleid = 2 OR roleid = 1) AND userid = $USER->id";
$isteacher = $DB->get_record_sql($sql)->isteacher;

if ($isteacher > 0) {
    ?>
    <br><br>
    <h2>Demandes reçues</h2>
    Des étudiants (ou des collègues) vous ont demandé de les inscrire à vos cours :<br><br>
    
    <a href='demandes.php?all=1'><button>Accepter tous</button></a>&nbsp;&nbsp;
    <a href='demandes.php?all=2'><button>Accepter tous si bonne VET</button></a><br><br>
    <a href='demandes.php?all=3'><button>Refuser tous</button></a>&nbsp;&nbsp;
    <a href='demandes.php?all=4'><button>Refuser tous si mauvaise VET</button></a><br><br>
    
<table border-collapse>
    <tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>		    	
    	<td>VET du cours</td>    	
    	<td>Cours</td>
        <td>Demande le</td>
    	<td>Demandeur</td>
    	<td>VET(s) du demandeur</td>
    	<td colspan="2">Réponse</td>    	
    </tr>
    
    <?php
    $sql = "SELECT ae.studentid, ae.courseid, ae.askedat, ae.id "
         . "FROM mdl_asked_enrolments ae, mdl_context x, mdl_role_assignments ra "
         . "WHERE ra.userid = $USER->id AND ra.roleid = 3 "
         . "AND ra.contextid = x.id  AND x.contextlevel = 50 AND x.instanceid = ae.courseid "
         . "AND ae.answererid = 0";
    //echo "$sql<br>";

    $askedenrolments = $DB->get_recordset_sql($sql);    
    foreach ($askedenrolments as $askedenrolment) {
        echo "<tr align = 'center'>";
        $coursesql = "SELECT a.name, a.idnumber, c.fullname, c.idnumber FROM mdl_course c, mdl_course_categories a WHERE c.id = $askedenrolment->courseid AND a.id = c.category";                    
        $askedenrolmentcourse = $DB->get_record_sql($coursesql);

        echo "<td>$askedenrolmentcourse->name</td>";
        echo "<td><a href='$CFG->wwwroot/course/view.php?id=$askedenrolment->courseid'>($askedenrolmentcourse->idnumber) $askedenrolmentcourse->fullname</td>";
        echo "<td>".date("d/m/Y", $askedenrolment->askedat)."</td>";

        $askersql = "SELECT firstname, lastname FROM mdl_user WHERE id = $askedenrolment->studentid";
        $asker = $DB->get_record_sql($askersql);                    
        echo "<td><a href='$CFG->wwwroot/user/view.php?id=$askedenrolment->studentid'>$asker->firstname $asker->lastname</a></td>";                    

        //Le demandeur est-il un enseignant ou un étudiant ?
        $sql = "SELECT COUNT(id) AS isteacher FROM mdl_role_assignments WHERE (roleid = 2 OR roleid = 1) AND userid = $askedenrolment->studentid";
        $askerteacher = $DB->get_record_sql($sql)->isteacher;

        if ($askerteacher) {
            echo "<td style='color:blue'>Enseignant</td>";                
        } else {
            //VET(s) dont cet étudiant fait partie
            echo "<td><ul>";
            $sql = "SELECT a.name, a.idnumber FROM mdl_course_categories a, mdl_student_vet s WHERE s.studentid = $askedenrolment->studentid AND s.categoryid = a.id";                
            $studentvets = $DB->get_recordset_sql($sql);
            foreach ($studentvets as $studentvet) {
                if ($studentvet->idnumber == $askedenrolmentcourse->idnumber) {
                    echo "<li style='color:green'>$studentvet->name</li>";
                } else {
                    echo "<li style='color:red'>$studentvet->name</li>";
                }
            }
            echo "</ul></td>";
        }

        echo "<td><a href='demandes.php?enrol=$askedenrolment->id'>Inscrire</a></td>";
        echo "<td><a href='demandes.php?reject=$askedenrolment->id'>Refuser</a></td>";
        echo "</tr>";
    }
    ?>
    
</table>
    <?php
}

echo $OUTPUT->footer();


function rejectenroldemand($paramreject) {
    global $DB, $USER;
    
    $now = time();
    $sql = "UPDATE mdl_asked_enrolments SET answeredat = $now, answer = 'Non', answererid = $USER->id WHERE id = $paramreject";
    $DB->execute($sql);
}

function acceptenroldemand($paramenrol) {
    global $DB, $USER;
    
    //On vérifie que ce cours appartient bien à cet enseignant
    $sql = "SELECT ae.courseid, x.id as contextid, ae.studentid FROM mdl_asked_enrolments ae, mdl_context x WHERE ae.id = $paramenrol AND x.contextlevel = 50 AND x.instanceid = ae.courseid";
    $acceptedcourse = $DB->get_record_sql($sql);
    
    $sql = "SELECT id FROM mdl_role_assignments WHERE contextid = $acceptedcourse->contextid AND roleid = 3 AND userid = $USER->id";
    $iscourseteacher = $DB->get_record_sql($sql)->id;
    
    if ($iscourseteacher) {
        //Si cet utilisateur n'est pas encore inscrit à ce cours 
        $sql = "SELECT COUNT(ue.id) AS isenroled FROM mdl_enrol e, mdl_user_enrolments ue "
                . "WHERE ue.userid = $acceptedcourse->studentid AND ue.enrolid = e.id AND e.courseid = $acceptedcourse->courseid";                        

        $isenroled = $DB->get_record_sql($sql)->isenroled;
        if ($isenroled == 0) {                                                        
            //on l'y inscrit
            $sql = "SELECT id FROM mdl_enrol WHERE courseid = $acceptedcourse->courseid AND enrol = 'manual'";                            
            $enrolid = $DB->get_record_sql($sql)->id;
            $DB->insert_record("user_enrolments", array('enrolid'=>$enrolid,'userid'=>$acceptedcourse->studentid,'timestart'=>time(),'timecreated'=>time()));
            //Le demandeur est-il un enseignant ou un étudiant ?
            $sql = "SELECT COUNT(id) AS isteacher FROM mdl_role_assignments WHERE (roleid = 2 OR roleid = 1) AND userid = $acceptedcourse->studentid";
            $askerteacher = $DB->get_record_sql($sql)->isteacher;
            //On lui donne le rôle étudiant ou enseignant, selon ce qu'il est.
            if ($askerteacher) {
                $DB->insert_record("role_assignments", array('roleid'=>3,'contextid'=>$acceptedcourse->contextid,'userid'=>$acceptedcourse->studentid,'timemodified'=>time()));
            } else {
                $DB->insert_record("role_assignments", array('roleid'=>5,'contextid'=>$acceptedcourse->contextid,'userid'=>$acceptedcourse->studentid,'timemodified'=>time()));
            }            
        }
                
        //On note que la demande est acceptée
        $now = time();
        $sql = "UPDATE mdl_asked_enrolments SET answeredat = $now, answer = 'Oui', answererid = $USER->id WHERE id = $paramenrol";
        $DB->execute($sql);
    }     
}