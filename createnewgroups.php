<title>
Création des nouveaux groupes (apparus dans le fichier XML et pas encore dans la BDD) - Moodle
</title>
<?php
	//Commenté par BRICE echo "<script type='text/javascript' src='https://enp.u-cergy.fr/lib/js/foToolTip.js'></script>";
?>
<style>

.dejacree {
    color : grey;
}

.dejacree a {
    color : grey;
}

 .dejacree a:hover {
    color : black;
 }

</style>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

redirect_if_major_upgrade_required();

$urlparams = array();
if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && optional_param('redirect', 1, PARAM_BOOL) === 0) {
    $urlparams['redirect'] = 0;
}
$PAGE->set_url('/', $urlparams);
$PAGE->set_course($SITE);
$PAGE->set_other_editing_capability('moodle/course:update');
$PAGE->set_other_editing_capability('moodle/course:manageactivities');
$PAGE->set_other_editing_capability('moodle/course:activityvisibility');

// Prevent caching of this page to stop confusion when changing page after making AJAX changes
$PAGE->set_cacheable(false);

echo date("d/m/Y H:i:s", time())." Mise à jour des groupes.\n";


$availinufr = array('1'=>0, '2'=>0, '3'=>0, '4'=>0, '5'=>0, '7'=>0, '9'=>0, 'A'=>0, 'B'=>0, 'C'=>0);
$nbvetsinufr = array('1'=>0, '2'=>0, '3'=>0, '4'=>0, '5'=>0, '7'=>0, 'A'=>0, 'B'=>0, 'C'=>0);

/* ON CHARGE LE XML */

$xmldoc = new DOMDocument();
$fileopening = $xmldoc->load('/home/referentiel/dokeos_elp_etu_ens.xml');
if ($fileopening == false) {
    echo "Impossible de lire le fichier source.\n";
}
$xpathvar = new Domxpath($xmldoc);

/* ON COMMENCE LA LECTURE */

//$queryResult = $xpathvar->query('//Structure_diplome[Cours/Groupe/Enseignant[@UID="'.$USER->username.'"]]');

$vets = $xpathvar->query('//Structure_diplome');
echo "Test\n";
foreach ($vets as $vet) {
    $idvet = $vet->getAttribute('Etape');
    echo "VET : $idvet\n";
    $ufrcode = substr($idvet, 0, 1);
    echo "ufrcode : $ufrcode\n";

    $nbvetsinufr[$ufrcode]++;

    // Pour les cours spéciaux de l'UFR Droit, on n'inscrit personne automatiquement.
    if ($idvet == '1M09A1) {
        continue;
    }

    $vetcourses = $xpathvar->query('//Structure_diplome[@Etape="'.$idvet.'"]/Cours');
    foreach ($vetcourses as $vetcourse) {
        $elementpedagogique = $vetcourse->getAttribute('element_pedagogique');

        $availinufr[$ufrcode]++;

        //Si ce cours est présent dans la base de données (il a été créé)
        $course = $DB->get_record('course', array('shortname' => "$idvet-$elementpedagogique"));

        if ($course) {
            echo "Le cours $idvet-$elementpedagogique existe dans Moodle\n";
            $courseid = $course->id;

            //On cherche le contexte associé à ce cours
            $coursecontextid = $DB->get_field('context', 'id', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $courseid));

            //Pour chaque groupe associé à ce cours dans le fichier xml
            $vetcoursegroups = $xpathvar->query('//Structure_diplome[@Etape="'.$idvet.'"]/Cours[@element_pedagogique="'.$elementpedagogique.'"]/Group');

            foreach ($vetcoursegroups as $vetcoursegroup) {
                $codegroupe = $vetcoursegroup->getAttribute('GroupCode');
                $group = $DB->get_record('groups', array('codegroupe' => $codegroupe, 'courseid' => $courseid));
                if (!$group) { //Si ce groupe n'existe pas encore dans la base de données
                    //On le crée
                    $groupname = $vetcoursegroup->getAttribute('GroupName');
                    $groupid = $DB->insert_record('groups',array('courseid'=>$courseid,'name'=>$groupname,'timecreated'=>time(),'codegroupe'=>$codegroupe));
                } else {
                    $groupid = $group->id;
                }

                $query = '//Structure_diplome[@Etape="'.$idvet.'"]/Cours[@element_pedagogique="'.$elementpedagogique.'"]/Group[@GroupCode="'.$codegroupe.'"]/Student';
                if ($idvet == "3F10A1") echo "$query\n";
                $vetcoursegroupstudents = $xpathvar->query($query);

                foreach ($vetcoursegroupstudents as $vetcoursegroupstudent) {
                    $username = $vetcoursegroupstudent->getAttribute('StudentUID');
                    if ($idvet == "3F10A1") echo "$username\n";
                    //Si cet utilisateur existe dans la base de données
                    unset($userdata);
                    $sql = "SELECT id, COUNT(id) AS accountexists FROM mdl_user WHERE username = '$username'";
                    if ($idvet == "3F10A1") echo "$sql\n";
                    $userdata = $DB->get_record_sql($sql);
                    if ($userdata->accountexists > 0) {
                        $userid = $userdata->id;

                        //Si cet utilisateur n'est pas encore inscrit à ce cours
                        $sql = "SELECT COUNT(ue.id) AS isenroled FROM mdl_enrol e, mdl_user_enrolments ue "
                                . "WHERE ue.userid = $userid AND ue.enrolid = e.id AND e.courseid = $courseid";
                        $isenroled = $DB->get_record_sql($sql)->isenroled;
                        if ($isenroled == 0) {
                            //Si cet utilisateur n'a pas été désinscrit de ce cours
                            $sql = "SELECT COUNT(id) AS unenroled FROM mdl_unenroled WHERE userid = $userid AND courseid = $courseid";
                            if ($idvet == "3F10A1") echo "$sql\n";
                            $unenroled = $DB->get_record_sql($sql)->unenroled;
                            if ($unenroled > 0) {
                                echo "L'utilisateur $userid a déjà été désinscrit du cours $courseid\n";
                            } else {
                                //on l'y inscrit
                                $sql = "SELECT id FROM mdl_enrol WHERE courseid = $courseid AND enrol = 'manual'";
                                if ($idvet == "3F10A1") echo "$sql\n";
                                $enrolid = $DB->get_record_sql($sql)->id;
                                $DB->insert_record("user_enrolments", array('enrolid'=>$enrolid,'userid'=>$userid,'timestart'=>time(),'timecreated'=>time()));

                                //on lui donne le rôle étudiant
                                $DB->insert_record("role_assignments", array('roleid'=>5,'contextid'=>$coursecontextid,'userid'=>$userid,'timemodified'=>time()));

                                //Si cet utilisateur n'est pas encore inscrit dans ce groupe
                                $sql = "SELECT id "
                                        . "FROM mdl_groups_members "
                                        . "WHERE userid = $userid AND groupid = $groupid";

                                $isinthisgroup = $DB->get_record_sql($sql);
                                if (!$isinthisgroup) {
                                    //on l'y inscrit
                                    $DB->insert_record("groups_members", array('groupid'=>$groupid,'userid'=>$userid, 'timeadded'=>time()));
                                    echo "Inscription de l'utilisateur $userid dans le groupe $groupid\n";
                                }
                            }
                        }
                    }
                }

                $vetcoursegroupsteachers = $xpathvar->query('//Structure_diplome[@Etape="'.$idvet.'"]/Cours[@element_pedagogique="'.$elementpedagogique.'"]/Group[@GroupCode="'.$codegroupe.'"]/Teacher');
                foreach ($vetcoursegroupsteachers as $vetcoursegroupsteacher) {
                    $username = $vetcoursegroupsteacher->getAttribute('StaffUID');

                    //Si cet utilisateur existe dans la base de données
                    unset($userdata);
                    $userdata = $DB->get_record('user', array('username' => $username));
//                    $sql = "SELECT id, COUNT(id) AS accountexists FROM mdl_user WHERE username = '$username'";
//                    $userdata = $DB->get_record_sql($sql);
                    if ($userdata) {
                        $userid = $userdata->id;

                        //Si cet utilisateur n'est pas encore inscrit à ce cours
                        $sql = "SELECT COUNT(ue.id) AS isenroled FROM mdl_enrol e, mdl_user_enrolments ue "
                                . "WHERE ue.userid = $userid AND ue.enrolid = e.id AND e.courseid = $courseid";
                        $isenroled = $DB->get_record_sql($sql)->isenroled;
                        if ($isenroled == 0) {
                            //On cherche les autres enseignants de ce cours
                            $courseteachers = $DB->get_records('role_assignments', array('roleid' => 3, 'contextid' => $coursecontextid));

                            //on l'y inscrit
                            $sql = "SELECT id FROM mdl_enrol WHERE courseid = $courseid AND enrol = 'manual'";
                            $enrolid = $DB->get_record_sql($sql)->id;
                            $DB->insert_record("user_enrolments", array('enrolid'=>$enrolid,'userid'=>$userid,'timestart'=>time(),'timecreated'=>time()));

                            //on lui donne le rôle enseignant
                            $DB->insert_record("role_assignments", array('roleid'=>3,'contextid'=>$coursecontextid,'userid'=>$userid,'timemodified'=>time()));

                            //On prévient les autres enseignants du cours de cette inscription.
                            if ($courseteachers) {
                                $coursename = $DB->get_field('course', 'fullname', array('id' => $courseid));
                                $to = '';
                                foreach ($courseteachers as $courseteacher) {
                                    $to .= $courseteacher->email.',';
                                }
                                $to  = substr($to, 0, -1);
                                $subject = "CoursUCP : Ajout d'un(e) enseignant(e) au cours $coursename";
                                $message = "<html>
                                            <head>
                                               <title>$subject</title>
                                            </head>
                                            <body>
                                            <p>
                                               Bonjour $courseteacher->firstname $courseteacher->lastname,<br>
                                               D'après le référentiel de l'Université, $userdata->firstname $userdata->lastname enseigne avec vous dans le cours $course->fullname.<br>
                                               $userdata->firstname $userdata->lastname a donc été automatiquement inscrit(e) au cours $course->fullname de la plateforme CoursUCP, avec le rôle Enseignant.
                                            </p>
                                            </body>";
                                $headers = 'From: noreply@cours.u-cergy.fr' . "\r\n" .'MIME-Version: 1.0' . "\r\n".
                                           'Reply-To: noreply@cours.u-cergy.fr' . "\r\n" .'Content-type: text/html; charset=utf-8' . "\r\n".
                                           'X-Mailer: PHP/' . phpversion();
                                mail($to, $subject, $message, $mailheaders);
                            }
                        }

                        //Si cet utilisateur n'est pas encore inscrit dans ce groupe
                        $sql = "SELECT id "
                                . "FROM mdl_groups_members "
                                . "WHERE userid = $userid AND groupid = $groupid";
                        $isinthisgroup = $DB->get_record_sql($sql);
                        if (!$isinthisgroup) {
                            //on l'y inscrit
                            $DB->insert_record("groups_members", array('groupid'=>$groupid,'userid'=>$userid, 'timeadded'=>time()));
                            echo "Inscription de l'utilisateur $userid dans le groupe $groupid\n";
                        }
                    } else {
                        //if ($groupid == 634) echo "Ce compte n'existe pas\n";
                    }
                }
            }
        }
    }
}

echo "Mise à jour des nombres de cours disponibles à la création.\n";
foreach ($availinufr as $ufrcode =>$nbcourses) {
    $sql = "UPDATE mdl_ufr SET avail_courses = $nbcourses WHERE code = '$ufrcode'";
    echo "$sql\n";
    $DB->execute($sql);
}

foreach ($nbvetsinufr as $ufrcode =>$nbvets) {
    $sql = "UPDATE mdl_ufr SET promotions = $nbvets WHERE code = '$ufrcode'";
    echo "$sql\n";
    $DB->execute($sql);
}

echo date("d/m/Y H:i:s", time())." Mise à jour réussie.\n";


exit;



/***************************** FIN CONTENT **************************************************************************************************/
/********************************************************************************************************************************************/

?>
