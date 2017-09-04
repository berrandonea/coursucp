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
 * The main group management user interface.
 *
 * @copyright 2006 The Open University, N.D.Freear AT open.ac.uk, J.White AT open.ac.uk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   core_group
 */
require_once('../config.php');
require_once('lib.php');

$courseid = required_param('id', PARAM_INT);
$fromelp   = optional_param('fromelp', false, PARAM_ALPHANUM); // BRICE : copie de groupes.
$fromvet   = optional_param('fromvet', false, PARAM_ALPHANUM); // BRICE : copie de groupes.
$srccourseid = optional_param('othercourse', 0, PARAM_INT);
$groupeacopier = optional_param('groupeAcopier', 0, PARAM_INT);

$returnurl = $CFG->wwwroot.'/group/index.php?id='.$courseid;

// Get the course information so we can print the header and
// check the course id is valid.

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$url = new moodle_url('/group/copyroup.php', array('id' => $courseid));
if ($userid) {
    $url->param('user', $userid);
}
if ($groupid) {
    $url->param('group', $groupid);
}
$PAGE->set_url($url);

// Make sure that the user has permissions to manage groups.
require_login($course);

$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

// Print the page and form.
$strgroups = get_string('groups');
$strparticipants = get_string('participants');

// Print header.
$PAGE->set_title('Importer des groupes UCP');
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');
echo $OUTPUT->header();
echo $OUTPUT->heading('Importer des groupes UCP');

// BRICE.
$xmldoc = new DOMDocument();
$xmldoc->load('/home/referentiel/dokeos_elp_etu_ens.xml');
$xpathvar = new Domxpath($xmldoc);

// Si le paramètre fromelp est renseigné, on importe les groupes du cours celcat $fromvet-$fromelp.
if ($fromelp) {
    // On cherche la méthode d'inscription manuelle et le contexte de ce cours.
    $sql = "SELECT id FROM mdl_enrol WHERE courseid = $courseid AND enrol = 'manual'";
    $enrolid = $DB->get_record_sql($sql)->id;
    $sql = "SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid = $courseid";
    $coursecontextid = $DB->get_record_sql($sql)->id;

    $query = "//Structure_diplome[@Etape='$fromvet']/Cours[@element_pedagogique='$fromelp']/Group";
    $celcatgroups = $xpathvar->query($query);

    foreach ($celcatgroups as $celcatgroup) {
        $celcatgroupname = $celcatgroup->getAttribute("GroupName");
        $celcatgroupcode = $celcatgroup->getAttribute("GroupCode");

        // Si ce groupe n'existe pas encore dans ce cours.
        $sql = "SELECT COUNT(id) AS groupexists, id FROM mdl_groups WHERE codegroupe = '$celcatgroupcode' AND courseid=$courseid";
        $groupdata = $DB->get_record_sql($sql);
        $groupexists = $groupdata->groupexists;
        if (!$groupexists) {
            // On le crée.
            echo "Copie du groupe $celcatgroupname ($celcatgroupcode)<br>";
            $groupid = $DB->insert_record('groups', array('courseid' => $courseid, 'name' => $celcatgroupname,
                'timecreated' => time(), 'codegroupe' => $celcatgroupcode));
        } else {
            echo "Le groupe $celcatgroupname ($celcatgroupcode) existe déjà dans ce cours.<br>";
            $groupid = $groupdata->id;
        }

        // Pour chaque étudiant du groupe.
        $query = "//Structure_diplome[@Etape='$fromvet']/Cours[@element_pedagogique='$fromelp']"
                . "/Group[@GroupCode='$celcatgroupcode']/Student";
        $celcatstudents = $xpathvar->query($query);

        foreach ($celcatstudents as $celcatstudent) {
            $celcatstudentusername = $celcatstudent->getAttribute('StudentUID');

            // Si cet utilisateur existe dans la base de données.
            unset($userdata);
            $sql = "SELECT id, COUNT(id) AS accountexists FROM mdl_user WHERE username = '$celcatstudentusername'";
            $userdata = $DB->get_record_sql($sql);
            if ($userdata->accountexists > 0) {
                $userid = $userdata->id;

                // Si cet utilisateur n'est pas encore inscrit à ce cours.
                $sql = "SELECT COUNT(ue.id) AS isenroled FROM mdl_enrol e, mdl_user_enrolments ue "
                        . "WHERE ue.userid = $userid AND ue.enrolid = e.id AND e.courseid = $courseid";
                $isenroled = $DB->get_record_sql($sql)->isenroled;
                if ($isenroled == 0) {
                    // Si cet utilisateur n'a pas été désinscrit de ce cours.
                    $sql = "SELECT COUNT(id) AS unenroled FROM mdl_unenroled WHERE userid = $userid AND courseid = $courseid";
                    $unenroled = $DB->get_record_sql($sql)->unenroled;
                    if ($unenroled > 0) {
                        echo "L'utilisateur $userid a déjà été désinscrit du cours $courseid<br>";
                    } else {
                        // On l'y inscrit.
                        $DB->insert_record("user_enrolments",
                                array('enrolid' => $enrolid, 'userid' => $userid,
                                    'timestart' => time(), 'timecreated' => time()));

                        // On lui donne le rôle étudiant.
                        $DB->insert_record("role_assignments",
                                array('roleid' => 5, 'contextid' => $coursecontextid,
                                    'userid' => $userid, 'timemodified' => time()));
                    }
                }

                // On l'inscrit au groupe.
                $DB->insert_record("groups_members",
                        array('groupid' => $groupid, 'userid' => $userid, 'timeadded' => time()));

            }
        }
    }
    echo "<br><br>";

    // Invalidate the course groups cache seeing as we've changed it.
    cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($courseid));
}

if ($srccourseid && $groupeacopier) {
    $srccontext = context_course::instance($srccourseid);
    require_capability('moodle/course:managegroups', $srccontext);

    $origingroup = $DB->get_record('groups', array('id' => $groupeacopier, 'courseid' => $srccourseid));
    $copiedgroup = $origingroup;
    if ($copiedgroup) {
        $now = time();
        unset($copiedgroup->id);
        $copiedgroup->courseid = $courseid;
        $copiedgroup->timecreated = $now;
        $copiedgroup->timemodified = $now;
        $newgroupid = $DB->insert_record('groups', $copiedgroup);

        if ($newgroupid) {

            $origingroup = $DB->get_record('groups', array('id' => $groupeacopier, 'courseid' => $srccourseid));
            $listgroup = $DB->get_records('groups_members', array('groupid' => $origingroup->id));

            $listenrolmethods = $DB->get_records('enrol', array('courseid' => $srccourseid));
            $newenrolid = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'))->id;

            foreach ($listgroup as $groupmember) {

                foreach ($listenrolmethods as $enrolmethod) {

                    if ($DB->record_exists('user_enrolments',
                            array('enrolid' => $enrolmethod->id, 'userid' => $groupmember->userid))) {

                        $olduserenrolment = $DB->get_record('user_enrolments',
                            array('enrolid' => $enrolmethod->id, 'userid' => $groupmember->userid));
                        break;
                    }
                }

                $oldroleassignment = $DB->get_record('role_assignments',
                                array('contextid' => $srccontext->id, 'userid' => $groupmember->userid));

                // We can only copy the student if he was enrolled in the other course.
                // If he was not enrolled for some strange reason, we ignore him entirely.
                // If the user had no role in the previous course we also won't copy him.
                if (isset($olduserenrolment) && isset($oldroleassignment)) {

                    // If the user was no longer enrolled in the old course, we do not copy him.
                    if (!$olduserenrolment->timeend) {

                        $newuserenrolment = $olduserenrolment;

                        $newuserenrolment->enrolid = $newenrolid;
                        $newuserenrolment->timestart = $now;
                        $newuserenrolment->modifierid = $USER->id;
                        $newuserenrolment->timecreated = $now;
                        $newuserenrolment->timemodified = $now;

                        $newroleassignment = $oldroleassignment;

                        $newroleassignment->contextid = context_course::instance($courseid)->id;
                        $newroleassignment->timemodified = $now;
                        $newroleassignment->modifierid = $USER->id;

                        $userid = $groupmember->userid;

                        $groupmember->groupid = $newgroupid;
                        $groupmember->timeadded = $now;

                        // All insertions are done at the very end when we are sure there is no problem.

                        if (!$DB->record_exists('user_enrolments',
                            array('enrolid' => $newenrolid, 'userid' => $groupmember->userid))) {
                            $DB->insert_record('user_enrolments', $newuserenrolment);
                        }
                        if (!$DB->record_exists('role_assignments',
                            array('contextid' => context_course::instance($courseid)->id,
                                'userid' => $groupmember->userid))) {
                            $DB->insert_record('role_assignments', $newroleassignment);
                        }
                        $DB->insert_record('groups_members', $groupmember);
                    }
                }
            }

            // Invalidate the course groups cache seeing as we've changed it.
            cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($courseid));

        } else {

            echo "La copie du groupe $copiedgroup->name a échouée.";
        }
        echo "Le groupe $copiedgroup->name a été copié dans ce cours-ci.<br>";
    } else {
        echo 'ERREUR : groupe introuvable.<br>';
    }
}
?>

<br><br>

<h3>Copier des groupes d'étudiants depuis un cours déclaré dans CELCAT</h3><br>
<?php
/* On cherche les cours de cet enseignant(e) dans le fichier XML */
$query = '//Structure_diplome[Cours/Group/Teacher[@StaffUID="'.$USER->username.'"]]';
$queryresult = $xpathvar->query($query);

foreach ($queryresult as $result) {
    // RECUPERATION CODE VET.
    $idvet = $result->getAttribute('Etape');
    $nomvet = $result->getAttribute('libelle_long_version_etape');
    $showvet = 0;

    /* SEULEMENT LES ELP AVEC L'UID DE L'USER CONNECTE */
    $query = '//Structure_diplome[@Etape="'.$idvet.'"]/Cours[Group/Teacher[@StaffUID="'.$USER->username.'"]]';
    $querycours = $xpathvar->query($query);

    foreach ($querycours as $cours) {

        $codecourselp = $idvet."-".$cours->getAttribute('element_pedagogique');
        $codecourselpseul = $cours->getAttribute('element_pedagogique');

        // LE SCRIPT JS AFFICHE, DANS LES CAS OU DES COURS SONT TROUVES, LE BLOCK P DE LA VET QUI EST CACHE PAR DEFAUT.
        echo '<script language="javascript" type="text/javascript">document.getElementById("monbeaup'
            .$idvet.'").style.display = "block";</script>';

        if ($cours->getAttribute('type_element_pedagogique') != "") {
             $elpeda = " [".$cours->getAttribute('type_element_pedagogique')."]";
        } else {
            $elpeda = "";
        }


        if ($showvet == 0) {
            $showvet = 1;
            echo "<p id='monbeaup".$idvet."' style='font-weight:bold;padding:5px;color:white;background-color : #780D68'>("
                    .$idvet.") ".$nomvet."</p>";
            echo "<ul>";
        }
        $idelp = $cours->getAttribute('element_pedagogique');
        $libellelongelementpedagogique = $cours->getAttribute('libelle_long_element_pedagogique');

        echo "<li class='dejacree'><a onmouseover=\"FoToolTip.show(this,'Copier les groupes de ce cours')\" "
            . "href='$CFG->wwwroot/group/copygroup.php?id=$courseid&fromvet=$idvet&fromelp=$idelp'>"
            . $libellelongelementpedagogique.$elpeda."</a><br/>"
            . "<i>$idvet-$idelp</i></li>";
    }
    if ($showvet == 1) {
        echo "</ul>";
    }

}
?>

<br><br>

<h3>Copier un groupe d'étudiants depuis un cours déjà créé</h3><br>
<form action='copygroup.php' method='post'>
    <input type='hidden' name='id' value='<?php echo $courseid; ?>'>
    <label><strong>Cours d'origine : </strong></label>
    <select name='othercourse' id='othercourse' onchange='othercourseselected()'>
    <option value='0'>Choisissez un de vos autres cours</option>
    <?php
    $sql = "SELECT c.id, c.shortname, c.fullname "
            . "FROM mdl_course c, mdl_context x, mdl_role_assignments ra "
            . "WHERE (ra.roleid = 3 OR ra.roleid = 4) "
            . "AND ra.userid = $USER->id "
            . "AND ra.contextid = x.id AND x.contextlevel = 50 AND x.instanceid = c.id "
            . "AND c.id <> $courseid";
    echo "$sql<br>";
    $othercourses = $DB->get_recordset_sql($sql);
    foreach ($othercourses as $othercourse) {
        echo "<option value='$othercourse->id'>($othercourse->shortname) $othercourse->fullname</option>";
    }
    ?>
    </select><br><br>
    <div id ="groupselector"></div>
</form>

<?php
echo $OUTPUT->footer();
?>

<script>
function getXhr(){
    if (window.XMLHttpRequest) {
        xhr = new XMLHttpRequest();
    } else if(window.ActiveXObject) {
        try {
            xhr = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
    } else {
        alert("Error : your browser doesn't support XMLHTTPRequest...");
        xhr = false;
    }
}

function othercourseselected() {
    getXhr();
    xhr.onreadystatechange = function() {
        if(xhr.readyState == 4 && xhr.status == 200) {
            response = xhr.responseText;
            document.getElementById('groupselector').innerHTML = response;
        }
    }
    othercourseid = document.getElementById('othercourse').value;
    xhr.open("POST", "importgroupfrom.php", true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    args = "id=" + othercourseid;
    xhr.send(args);
}
</script>
