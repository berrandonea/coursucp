<title>
Cron quotidien - Enregistrement des étudiants
</title>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

/************************************************* DEBUT CONTENT ***************************************************************/

/*/ Vérification des étudiants sans logins :
$filename = "DOKEOS_Etudiants_Inscriptions.xml";
$command = "grep '<Student' /home/referentiel/$filename | grep -v 'StudentUID' | wc -l";
$nologin = system($command);

if ($nologin) {
    $to = "brice.errandonea@u-cergy.fr";
    $subject = "CoursUCP : Erreur dans le fichier $filename";
    $message = "Bonjour, \n\nUn étudiant n'a pas de login :\n\n";
    $command = "grep '<Student' /home/referentiel/$filename | grep -v 'StudentUID'";
    $studentwithoutlogin = system($command);
    $message .= $studentwithoutlogin;
    $headers = 'From: noreply@cours.u-cergy.fr'."\r\n".'Reply-To: noreply@cours.u-cergy.fr'."\r\n".'X-Mailer: PHP/'.phpversion();
    mail($to, $subject, $message, $headers);
}*/


/* ON CHARGE LE XML */

$nbstudents = 1;
$xmldoc = new DOMDocument();
$xmldoc->load('/home/referentiel/DOKEOS_Etudiants_Inscriptions.xml');
$xpathvar = new Domxpath($xmldoc);
$querystudent = $xpathvar->query('//Student');
$i = 0; //Nombre d'étudiants dans le fichier XML
$j = 0; //Nombre d'étudiants 2016 dans le fichier XML
foreach($querystudent as $result){
    $i++;
    $studentuid = $result->getAttribute('StudentUID');
    $email = $result->getAttribute('StudentEmail');
    $idnumber = $result->getAttribute('StudentETU');
    $lastname = ucwords(strtolower($result->getAttribute('StudentName')));
    $firstname = ucwords(strtolower($result->getAttribute('StudentFirstName')));
    $queryyear = $xpathvar->query('//Student[@StudentUID="'.$studentuid.'"]/Annee_universitaire');

    if($studentuid){
        //On regarde uniquement l'année 2016
        foreach($queryyear as $yearresult) {
            $year = $yearresult->getAttribute('AnneeUniv');

            if ($year == "2016") {
                $j++;
                $query = $DB->get_record('user', array('username'=>$studentuid));
                //Si l'utilisateur est déjà dans la table user
                if($query) {
                    $user = $query;
                    // mise a jour des etudiants qui n'auraient pas de nom, de prénom, d'idnumber ou de mail
                    if(stripslashes($query->firstname) != $firstname) {
                        echo "update firstname\n";
                        $DB->execute("UPDATE mdl_user SET firstname = '".addslashes($firstname)."' WHERE username ='$studentuid'");
                    }

                     if(stripslashes($query->lastname) != $lastname) {
                        echo "update lastname\n";
                        $DB->execute("UPDATE mdl_user SET lastname = '".addslashes($lastname)."' WHERE username ='$studentuid'");

                    }

                    if($query->idnumber != $idnumber) {
                        echo "update idnumber\n";
                        $DB->execute("UPDATE mdl_user SET idnumber = '$idnumber' WHERE username ='$studentuid'");

                    }

                     if($query->email != $email) {
                         echo "update email\n";
                         $DB->execute('UPDATE mdl_user SET email = "'.$email.'" WHERE username ="'.$studentuid.'"');
                    }

                //Sinon
                } else {
                    $user = new StdClass();
                    $user->auth = 'cas';
                    $user->confirmed = 1;
                    $user->mnethostid = 1;
                    $user->email = $email;
                    $user->username = $studentuid;
                    $user->password = '';
                    $user->lastname = $lastname;
                    $user->firstname = $firstname;
                    $user->idnumber = $idnumber;
                    $user->timecreated = time();
                    $user->timemodified = time();
                    $user->lang = 'fr';
                    $user->id = $DB->insert_record('user', $user);
                    echo "Nouvel étudiant: $firstname $lastname ($studentuid, $idnumber)\n";
                    $nbstudents++;
                }

                //Pour chaque inscription de l'utilisateur en 2016
                $queryinscription = $xpathvar->query('//Student[@StudentUID="'.$studentuid.'"]/Annee_universitaire[@AnneeUniv="2016"]/Inscriptions');
                foreach ($queryinscription as $inscriptionresult) {
                    $codeetape = $inscriptionresult->getAttribute('CodeEtape');
                    $ufrcode = substr($codeetape, 0, 1);

                    //$query = $DB->get_record('ufr_student', array('userid'=>$userid, 'ufrcode'));
                    $sql = "SELECT COUNT(id) AS ishere FROM mdl_ufr_student WHERE userid = $user->id AND ufrcode = '$ufrcode'";
                    //echo "$sql\n";
                    $ufrstudent = $DB->get_record_sql($sql)->ishere;

                    //Si cette inscription de l'utilisateur à cette composante n'est pas encore dans mdl_ufr_student, on l'y ajoute
                    if ($ufrstudent == 0) {
                        $sql = "INSERT INTO mdl_ufr_student (userid, ufrcode, student) VALUES ($user->id, '$ufrcode', 1)";
                        echo "$sql\n";
                        $DB->execute($sql);
                    }

                    //idem pour la VET
                    $sql = "SELECT id FROM mdl_course_categories WHERE idnumber = '$codeetape'";
                    $vet = $DB->get_record_sql($sql);
                    if ($vet) {
                        $sql = "SELECT COUNT(id) AS ishere FROM mdl_student_vet WHERE studentid = $user->id AND categoryid = $vet->id";
                        $studentvet = $DB->get_record_sql($sql)->ishere;
                        if ($studentvet == 0) {
                            $sql = "INSERT INTO mdl_student_vet (studentid, categoryid) VALUES ($user->id, $vet->id)";
                            echo "$sql\n";
                            $DB->execute($sql);
                        }
                    }
                }
            }
        }
    }
    //echo "$i étudiants dont $j en 2016\n";
}

echo "Détection des doublons.\n";
//Détection des username doublons
$sql = "SELECT COUNT(username) AS nbusernames FROM mdl_user";
$nbusernames = $DB->get_record_sql($sql)->nbusernames;

$sql = "SELECT COUNT(DISTINCT username) AS nbdistinctusernames FROM mdl_user";
$nbdistinctusernames = $DB->get_record_sql($sql)->nbdistinctusernames;

if ($nbdistinctusernames != $nbusernames) {
    echo ($nbusername - $nbdistinctusernames)." doublons détectés.\n";
    $sql = "SELECT username FROM mdl_user";
    $usernames = $DB->get_recordset_sql($sql);
    $now = time();
    foreach ($usernames as $username) {
        $sql = "SELECT COUNT(id) AS nbtwins WHERE username = '$username->username'";
        $nbtwins = $DB->get_record_sql($sql)->nbtwins;
        if ($nbtwins > 1) {
            echo "$username->username\n";
            $sql = "SELECT id AS userid, username, firstname, lastname, email, idnumber FROM mdl_user WHERE username = '$username->username'";
            $twins = $DB->get_recordset_sql($sql);
            foreach ($twins as $twin) {
                $twin->found_at = $now;
                $sql = "SELECT id FROM mdl_twins WHERE username = '$twin->username' AND idnumber = '$twin->idnumber'";
                $twinisknown = $DB->get_record_sql($sql);
                if (!$twinisknown) {
                    $twin->id = $DB->insert_record('twins', $twin);
                }
            }
        }
    }
}




/***************************** FIN CONTENT **************************************************************************************************/
/********************************************************************************************************************************************/

?>
