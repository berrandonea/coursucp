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
 * Main course enrolment management UI.
 *
 * @package    core_enrol
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');

//BRICE pour inscription de toute une VET
require_once($CFG->dirroot.'/group/lib.php'); 
require_once($CFG->dirroot.'/course/lib.php'); 
require_once($CFG->dirroot.'/lib/coursecatlib.php');
//FIN

$id         = required_param('id', PARAM_INT); // course id
$action     = optional_param('action', '', PARAM_ALPHANUMEXT);
$instanceid = optional_param('instance', 0, PARAM_INT);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);
$confirm2   = optional_param('confirm2', 0, PARAM_BOOL);


$allpromo = 'Y2017-'.optional_param('allpromo', 0, PARAM_ALPHANUMEXT); //BRICE pour inscrire d'un coup la totalité d'une promotion

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

require_login($course);
require_capability('moodle/course:enrolreview', $context);

$canconfig = has_capability('moodle/course:enrolconfig', $context);

$PAGE->set_url('/enrol/vet.php', array('id'=>$course->id));
$PAGE->set_pagelayout('admin');
$PAGE->set_title('Inscription par VET');
$PAGE->set_heading($course->fullname);

$instances = enrol_get_instances($course->id, false);
$plugins   = enrol_get_plugins(false);

//if ($canconfig and $action and confirm_sesskey()) {
//    if (isset($instances[$instanceid]) and isset($plugins[$instances[$instanceid]->enrol])) {
//        if ($action === 'up') {
//            $order = array_keys($instances);
//            $order = array_flip($order);
//            $pos = $order[$instanceid];
//            if ($pos > 0) {
//                $switch = $pos - 1;
//                $resorted = array_values($instances);
//                $temp = $resorted[$pos];
//                $resorted[$pos] = $resorted[$switch];
//                $resorted[$switch] = $temp;
//                // now update db sortorder
//                foreach ($resorted as $sortorder=>$instance) {
//                    if ($instance->sortorder != $sortorder) {
//                        $instance->sortorder = $sortorder;
//                        $DB->update_record('enrol', $instance);
//                    }
//                }
//            }
//            redirect($PAGE->url);
//
//        } else if ($action === 'down') {
//            $order = array_keys($instances);
//            $order = array_flip($order);
//            $pos = $order[$instanceid];
//            if ($pos < count($instances) - 1) {
//                $switch = $pos + 1;
//                $resorted = array_values($instances);
//                $temp = $resorted[$pos];
//                $resorted[$pos] = $resorted[$switch];
//                $resorted[$switch] = $temp;
//                // now update db sortorder
//                foreach ($resorted as $sortorder=>$instance) {
//                    if ($instance->sortorder != $sortorder) {
//                        $instance->sortorder = $sortorder;
//                        $DB->update_record('enrol', $instance);
//                    }
//                }
//            }
//            redirect($PAGE->url);
//
//        } else if ($action === 'delete') {
//            $instance = $instances[$instanceid];
//            $plugin = $plugins[$instance->enrol];
//
//            if ($plugin->can_delete_instance($instance)) {
//                if ($confirm) {
//                    if (enrol_accessing_via_instance($instance)) {
//                        if (!$confirm2) {
//                            $yesurl = new moodle_url('/enrol/instances.php',
//                                                     array('id' => $course->id,
//                                                           'action' => 'delete',
//                                                           'instance' => $instance->id,
//                                                           'confirm' => 1,
//                                                           'confirm2' => 1,
//                                                           'sesskey' => sesskey()));
//                            $displayname = $plugin->get_instance_name($instance);
//                            $message = markdown_to_html(get_string('deleteinstanceconfirmself',
//                                                                   'enrol',
//                                                                   array('name' => $displayname)));
//                            echo $OUTPUT->header();
//                            echo $OUTPUT->confirm($message, $yesurl, $PAGE->url);
//                            echo $OUTPUT->footer();
//                            die();
//                        }
//                    }
//                    $plugin->delete_instance($instance);
//                    redirect($PAGE->url);
//                }
//
//                echo $OUTPUT->header();
//                $yesurl = new moodle_url('/enrol/instances.php',
//                                         array('id' => $course->id,
//                                               'action' => 'delete',
//                                               'instance' => $instance->id,
//                                               'confirm' => 1,
//                                               'sesskey' => sesskey()));
//                $displayname = $plugin->get_instance_name($instance);
//                $users = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
//                if ($users) {
//                    $message = markdown_to_html(get_string('deleteinstanceconfirm', 'enrol',
//                                                           array('name' => $displayname,
//                                                                 'users' => $users)));
//                } else {
//                    $message = markdown_to_html(get_string('deleteinstancenousersconfirm', 'enrol',
//                                                           array('name' => $displayname)));
//                }
//                echo $OUTPUT->confirm($message, $yesurl, $PAGE->url);
//                echo $OUTPUT->footer();
//                die();
//            }
//
//        } else if ($action === 'disable') {
//            $instance = $instances[$instanceid];
//            $plugin = $plugins[$instance->enrol];
//            if ($plugin->can_hide_show_instance($instance)) {
//                if ($instance->status != ENROL_INSTANCE_DISABLED) {
//                    if (enrol_accessing_via_instance($instance)) {
//                        if (!$confirm2) {
//                            $yesurl = new moodle_url('/enrol/instances.php',
//                                                     array('id' => $course->id,
//                                                           'action' => 'disable',
//                                                           'instance' => $instance->id,
//                                                           'confirm2' => 1,
//                                                           'sesskey' => sesskey()));
//                            $displayname = $plugin->get_instance_name($instance);
//                            $message = markdown_to_html(get_string('disableinstanceconfirmself',
//                                                        'enrol',
//                                                        array('name' => $displayname)));
//                            echo $OUTPUT->header();
//                            echo $OUTPUT->confirm($message, $yesurl, $PAGE->url);
//                            echo $OUTPUT->footer();
//                            die();
//                        }
//                    }
//                    $plugin->update_status($instance, ENROL_INSTANCE_DISABLED);
//                    redirect($PAGE->url);
//                }
//            }
//
//        } else if ($action === 'enable') {
//            $instance = $instances[$instanceid];
//            $plugin = $plugins[$instance->enrol];
//            if ($plugin->can_hide_show_instance($instance)) {
//                if ($instance->status != ENROL_INSTANCE_ENABLED) {
//                    $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
//                    redirect($PAGE->url);
//                }
//            }
//        }
//    }
//}

echo $OUTPUT->header();
echo $OUTPUT->heading('Inscription par VET');

//echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');
//
//// display strings
//$strup      = get_string('up');
//$strdown    = get_string('down');
//$strdelete  = get_string('delete');
//$strenable  = get_string('enable');
//$strdisable = get_string('disable');
//$strmanage  = get_string('manageinstance', 'enrol');
//
//$table = new html_table();
//$table->head  = array(get_string('name'), get_string('users'), $strup.'/'.$strdown, get_string('edit'));
//$table->align = array('left', 'center', 'center', 'center');
//$table->width = '100%';
//$table->data  = array();
//
//// iterate through enrol plugins and add to the display table
//$updowncount = 1;
//$icount = count($instances);
//$url = new moodle_url('/enrol/instances.php', array('sesskey'=>sesskey(), 'id'=>$course->id));
//foreach ($instances as $instance) {
//    if (!isset($plugins[$instance->enrol])) {
//        continue;
//    }
//    $plugin = $plugins[$instance->enrol];
//
//    $displayname = $plugin->get_instance_name($instance);
//    if (!enrol_is_enabled($instance->enrol) or $instance->status != ENROL_INSTANCE_ENABLED) {
//        $displayname = html_writer::tag('span', $displayname, array('class'=>'dimmed_text'));
//    }
//
//    $users = $DB->count_records('user_enrolments', array('enrolid'=>$instance->id));
//
//    $updown = array();
//    $edit = array();
//
//    if ($canconfig) {
//        // up/down link
//        $updown = '';
//        if ($updowncount > 1) {
//            $aurl = new moodle_url($url, array('action'=>'up', 'instance'=>$instance->id));
//            $updown[] = $OUTPUT->action_icon($aurl, new pix_icon('t/up', $strup, 'core', array('class' => 'iconsmall')));
//        } else {
//            $updown[] = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('spacer'), 'alt'=>'', 'class'=>'iconsmall'));
//        }
//        if ($updowncount < $icount) {
//            $aurl = new moodle_url($url, array('action'=>'down', 'instance'=>$instance->id));
//            $updown[] = $OUTPUT->action_icon($aurl, new pix_icon('t/down', $strdown, 'core', array('class' => 'iconsmall')));
//        } else {
//            $updown[] = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('spacer'), 'alt'=>'', 'class'=>'iconsmall'));
//        }
//        ++$updowncount;
//
//        if ($plugin->can_delete_instance($instance)) {
//            $aurl = new moodle_url($url, array('action'=>'delete', 'instance'=>$instance->id));
//            $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/delete', $strdelete, 'core', array('class' => 'iconsmall')));
//        }
//
//        if (enrol_is_enabled($instance->enrol) && $plugin->can_hide_show_instance($instance)) {
//            if ($instance->status == ENROL_INSTANCE_ENABLED) {
//                $aurl = new moodle_url($url, array('action'=>'disable', 'instance'=>$instance->id));
//                $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/hide', $strdisable, 'core', array('class' => 'iconsmall')));
//            } else if ($instance->status == ENROL_INSTANCE_DISABLED) {
//                $aurl = new moodle_url($url, array('action'=>'enable', 'instance'=>$instance->id));
//                $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/show', $strenable, 'core', array('class' => 'iconsmall')));
//            } else {
//                // plugin specific state - do not mess with it!
//                $edit[] = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/show'), 'alt'=>'', 'class'=>'iconsmall'));
//            }
//
//        }
//    }
//
//    // link to instance management
//    if (enrol_is_enabled($instance->enrol) && $canconfig) {
//        if ($icons = $plugin->get_action_icons($instance)) {
//            $edit = array_merge($edit, $icons);
//        }
//    }
//
//    // Add a row to the table.
//    $table->data[] = array($displayname, $users, implode('', $updown), implode('', $edit));
//
//}
//echo html_writer::table($table);
//
//// access security is in each plugin
//$candidates = array();
//foreach (enrol_get_plugins(true) as $name=>$plugin) {
//    if (!$link = $plugin->get_newinstance_link($course->id)) {
//        continue;
//    }
//    $candidates[$link->out(false)] = get_string('pluginname', 'enrol_'.$name);
//}
//
//if ($candidates) {
//    $select = new url_select($candidates);
//    $select->set_label(get_string('addinstance', 'enrol'));
//    echo $OUTPUT->render($select);
//}
//
//echo $OUTPUT->box_end();
//
////SALMA Inscription massive
//echo '<br><p style="text-align:center"><a href="../local/mass_enroll/mass_enroll.php?id='.$COURSE->id.'"><button>Inscription massive avec un fichier CSV</button></a></p><br>';

//BRICE Inscrire toute une promotion d'après son code VET
echo "<h3>Inscrire TOUS les étudiants d'une certaine VET</h3>";

//BRICE Inscription de toute la promotion courante
if ($allpromo) {
    //Promotion à inscrire
    $sql = "SELECT name, id FROM mdl_course_categories WHERE idnumber = '$allpromo'";    
    $enroledpromo = $DB->get_record_sql($sql);
    $newvet = 0;
        
    //Si la promotion demandée n'existe pas encore sur la plateforme, il faut la créer
    if ($enroledpromo) {
        echo "<p style='font-weight:bold;color:green'>La VET ($allpromo) $enroledpromo->name existait déjà sur la plateforme.</p>";
    } else {
        //Recherche de la VET dans le fichier XML
        $xmldoc = new DOMDocument();
        $xmldoc->load('/home/referentiel/dokeos_offre_pedagogique.xml');
        $xpathvar = new Domxpath($xmldoc);            
        $queryvet = $xpathvar->query("//Etape[@Code_etape='$allpromo']");
        
        foreach ($queryvet as $vetdata) {            
            $nomvet = $vetdata->getAttribute('Lib_etape');            
            echo "nomvet : $nomvet<br>";
        }
        
        if ($nomvet) {
            echo "<p style='font-weight:bold;color:red'>La VET ($allpromo) $nomvet n'existe pas encore sur la plateforme.</p>";
                
            //Création de la VET
            echo "Création de la VET ($allpromo) $nomvet ... ";
            $enroledpromo = new stdClass();
            $enroledpromo->name = $nomvet;
            $enroledpromo->id = createvetifnew($allpromo, $nomvet);
            $newvet = 1;
            echo "<span style='font:weight:bold;color:green'>Création réussie.</span><br><br>";

            echo "<span style='font:weight:bold;color:green'>Les étudiants de la nouvelle VET ($allpromo) $nomvet seront inscrits à votre cours d'ici quelques heures puis au fur et à mesure de leur inscription dans CELCAT.</span><br><br>";
        } else {
            echo "<p style='color:red;font-weight:bold'>ERREUR : ce code VET n'existe pas.</p>";
            exit;
        }
    }
    
    
    //Y a-t-il déjà, dans ce cours, un groupe portant l'idnumber de la promotion ?
    $sql = "SELECT id AS groupid FROM mdl_groups WHERE idnumber = '$allpromo' AND courseid = $course->id";    
    $groupid = $DB->get_record_sql($sql)->groupid;        
    //Si non, on le crée
    if (!$groupid) {        
        $newgroupdata = new stdClass();
        $newgroupdata->courseid = $course->id;
        $newgroupdata->idnumber = $allpromo;
        $newgroupdata->name = $enroledpromo->name;        
        $newgroupdata->description = 'Toute la VET';        
        $groupid = groups_create_group($newgroupdata);
        
        //$groupid = $DB->insert_record('groups',array('courseid'=>$course->id,'idnumber'=>"$enroledpromo->idnumber",'name'=>$enroledpromo->name,'description'=>'Tous les étudiants de la VET', 'descriptionformat'=>1, 'timecreated'=>time(),'timemodified'=>time()));
    }
    
    //Pour chaque étudiant de cette promotion
    $sql = "SELECT studentid FROM mdl_student_vet WHERE categoryid = $enroledpromo->id";    
    $vetstudents = $DB->get_recordset_sql($sql);    
    $now = time();
    
    $nbenroledstudents = 0;
    
    
    foreach ($vetstudents as $vetstudent) {
        //S'il n'est pas encore inscrit au cours, on l'y inscrit
        $sql = "SELECT ue.id "
             . "FROM mdl_user_enrolments ue, mdl_enrol e "
             . "WHERE ue.userid = $vetstudent->studentid "
             . "AND ue.enrolid = e.id AND e.roleid = 5 AND e.courseid = $course->id";        
        $ue = $DB->get_record_sql($sql);
        if (!$ue) {
            $sql = "SELECT id FROM mdl_enrol WHERE courseid = $course->id AND enrol = 'manual'";            
            $enrolid = $DB->get_record_sql($sql)->id;            
            $inserted = array('enrolid'=>$enrolid,'userid'=>$vetstudent->studentid,'timestart'=>$now,'modifierid'=>$USER->id,'timecreated'=>$now,'timemodified'=>$now);            
            $ueid = $DB->insert_record('user_enrolments', $inserted);
            //On lui donne le rôle étudiant
            $inserted = array('roleid'=>5,'contextid'=>$context->id,'userid'=>$vetstudent->studentid,'timemodified'=>$now,'modifierid'=>$USER->id);            
            $raid = $DB->insert_record('role_assignments', $inserted);
            $nbenroledstudents++;
        }
        //S'il n'est pas encore inscrit dans le groupe de la VET, on l'y inscrit
        $sql = "SELECT id FROM mdl_groups_members WHERE groupid = $groupid AND userid = $vetstudent->studentid";        
        $gm = $DB->get_record_sql($sql);
        if (!$gm) {
            $inserted = array('groupid'=>$groupid,'userid'=>$vetstudent->studentid,'timeadded'=>$now);            
            $gmid = $DB->insert_record('groups_members', $inserted);
        }
    }
    
    if (!$newvet) {
        echo "<p style='font-weight:bold;color:red'>$nbenroledstudents étudiants inscrits.</p>";
    }
    
    echo "<p style='text-align:center'>--------------------------------</p>";
        
}
//FIN









$sql = "SELECT idnumber FROM mdl_course_categories WHERE id = $COURSE->category";
$currentvetcode = $DB->get_record_sql($sql)->idnumber;


$action = "vet.php?id=$COURSE->id";
echo '<form enctype="multipart/form-data" action="'.$action.'" method="post">            
        Indiquez le code de la VET dont vous souhaitez inscrire les étudiants. La valeur proposée par défaut est le code de la VET à laquelle ce cours est associé.<br><br>
        <p style="text-align:center">    
            <input type="text" name="allpromo" value="'.$currentvetcode.'"/>    <input type="submit" value="Valider"/><br>
        </p>
      </form><br>';

echo "ATTENTION : si la VET que vous demandez n'a pas encore de cours sur la plateforme, ce processus peut prendre quelques minutes !<br>";

echo $OUTPUT->footer();
