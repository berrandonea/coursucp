<title>
Cron quotidien - Enregistrement des profs
</title>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');
require_once($CFG->libdir .'/accesslib.php');

$context = get_context_instance(CONTEXT_SYSTEM);

/* ON CHARGE LE XML */

$xmldoc = new DOMDocument();
$xmldoc->load('/home/referentiel/DOKEOS_Enseignants_Affectations.xml');
$xpathvar = new Domxpath($xmldoc);

$querygroupe = $xpathvar->query('//Teacher');
foreach($querygroupe as $result){
    if($teacheruid = $result->getAttribute('StaffUID')){
        //Est-il sursitaire ?
        $affectations = $xpathvar->query('//Teacher[@StaffUID="'.$teacheruid.'"]/Affectations');
        foreach ($affectations as $affectation) {
            $position = $affectation->getAttribute('Position');
            echo "$position\n";
        }

        // ON CHERCHE L'ENSEIGNANT DANS LA BDD
	$query = $DB->get_record('user', array('username'=>$result->getAttribute('StaffUID')));
        if($query) {
            //On suspend les sursitaires
            if ($position == 'Sursitaire') {
//                $DB->set_field('user', 'suspended', 1, array('id' => $query->id));
            }

            //echo $result->getAttribute('uid')."- DEJA EXISTANT<br/>";
            $roles = get_user_roles($context, $query->id, false);
            $role = key($roles);
            if ($role) {
				$roleid = $roles[$role]->roleid;
                if($roleid != 2) {
		            role_assign(2, $query->id, 1, $component = '', $itemid = 0, $timemodified = '');
                }
			}
            
            $newfirstname = addslashes(ucwords(strtolower($result->getAttribute('StaffFirstName'))));
            if($query->firstname != $newfirstname) {
                echo "test<br/>";
                $DB->execute("UPDATE mdl_user SET firstname = '".$newfirstname."' WHERE username ='".$result->getAttribute('StaffUID')."'");
            }
            $newlastname = addslashes(ucwords(strtolower($result->getAttribute('StaffCommonName'))));
            if($query->lastname != $newlastname) {
                //~ $lastname = addslashes(ucwords(strtolower($result->getAttribute('StaffCommonName'))));
                //~ echo "$lastname<br/>";
                $DB->execute("UPDATE mdl_user SET lastname = '".$newlastname."' WHERE username ='".$result->getAttribute('StaffUID')."'");
            }
            if(!$query->email) {
                echo "test<br/>";
                $DB->execute("UPDATE mdl_user SET email = '".ucwords(strtolower($result->getAttribute('StaffEmail')))."' WHERE username ='".$result->getAttribute('StaffUID')."'");
            }
        } else if ($position != 'Sursitaire') { // SINON (PAS DANS LA BDD) ALORS => INSCRIPTION DU COMPTE UTILISATEUR
            $user = new StdClass();
            $user->auth = 'cas';
            $user->confirmed = 1;
            $user->mnethostid = 1;
            $user->email = $result->getAttribute('StaffEmail');
            $user->username = $result->getAttribute('StaffUID');
            $user->password = '';
            $user->lastname = ucwords(strtolower($result->getAttribute('StaffCommonName')));
            $user->firstname = ucwords(strtolower($result->getAttribute('StaffFirstName')));
            $user->timecreated = time();
            $user->timemodified = time();
            $user->lang = 'fr';
            $user->id = $DB->insert_record('user', $user);
            role_assign(2, $user->id, 1, $component = '', $itemid = 0, $timemodified = '');
        }

        $teacher = $DB->get_record('user', array('username' => $result->getAttribute('StaffUID')));

        if ($DB->record_exists('user', array('username' => $result->getAttribute('StaffUID')))) {
            foreach ($affectations as $affectation) {
                $codestructure = $affectation->getAttribute('CodeStructure');
                if (isset($codestructure)) {
                    $ufrcode = substr($codestructure, 0, 1);
                    //$ufrcodeyear = "Y2017-$ufrcode"; 
                    if (!$DB->record_exists('ufr_teacher',
                            array('userid' => $teacher->id, 'ufrcode' => $ufrcode))) {

                        $ufrteacher = array();
                        $ufrteacher['userid'] = $teacher->id;
                        $ufrteacher['ufrcode'] = $ufrcode;
                        $DB->insert_record('ufr_teacher', $ufrteacher);
                        if ($DB->record_exists('ufr_teacher',
                            array('userid' => $teacher->id, 'ufrcode' => '-1'))) {
                            $DB->delete_record('ufr_teacher', array('userid' => $teacher->id, 'ufrcode' => '-1'));
                        }
                    }
                }
            }

            if (!$DB->record_exists('ufr_teacher', array('userid' => $teacher->id))) {
                $ufrteacher = array();
                $ufrteacher['userid'] = $teacher->id;
                $ufrteacher['ufrcode'] = '-1';
                $DB->insert_record('ufr_teacher', $ufrteacher);
            }

            if ($result->getAttribute('LC_CORPS') != null && $result->getAttribute('LC_CORPS') != "") {
                $sqlrecordexistsinfodata = "SELECT * FROM {teacher_type} WHERE "
                        . "userid = ? AND typeteacher LIKE ?";

                if (!$DB->record_exists_sql($sqlrecordexistsinfodata,
                            array($teacher->id, $result->getAttribute('LC_CORPS')))) {

                    $typeprofdata = array();
                    $typeprofdata['userid'] = $teacher->id;
                    $typeprofdata['typeteacher'] = $result->getAttribute('LC_CORPS');
                    $DB->insert_record('teacher_type', $typeprofdata);

                    $nonindique = $DB->get_record_sql($sqlrecordexistsinfodata, array($teacher->id, "Non indiqué"));
                    if ($nonindique) {
                        $DB->delete_records('teacher_type', array('id' => $nonindique->id));
                    }
                }
            }

            if (!$DB->record_exists('teacher_type', array('userid' => $teacher->id))) {
                $typeprofdata = array();
                $typeprofdata['userid'] = $teacher->id;
                $typeprofdata['typeteacher'] = "Non indiqué";
                $DB->insert_record('teacher_type', $typeprofdata);
            }
        }
    }
}

/***************************** FIN CONTENT **************************************************************************************************/
/********************************************************************************************************************************************/
?>
