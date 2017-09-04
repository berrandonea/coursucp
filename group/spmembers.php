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
 * Add/remove members from group.
 *
 * @copyright 2006 The Open University and others, N.D.Freear AT open.ac.uk, J.White AT open.ac.uk and others
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   core_group
 */

ob_start();

require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/filelib.php');

$spid = required_param('spid', PARAM_INT);
$courseid = required_param('id', PARAM_INT);
$paramgroupid = optional_param('groupid',0,PARAM_INT);
$action   = groups_param_action();

$course = $DB->get_record('course', array('id'=>$courseid));
$PAGE->set_pagelayout('admin');

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

// Print the page and form
$strgroups = get_string('groups');
$strparticipants = get_string('participants');
$stradduserstogroup = get_string('adduserstogroup', 'group');
$strusergroupmembership = get_string('usergroupmembership', 'group');
$PAGE->requires->js('/group/clientlib.js');

//Situation problème concernée, groupement associé et nombre de groupes 
$sp = $DB->get_record('situation_problemes', array('id'=>$spid), '*', MUST_EXIST);
$sql = "select * from mdl_groupings where spid=$spid";
$spgrouping = $DB->get_record_sql($sql);
$sql = "SELECT COUNT(groupid) AS nbspgroups FROM mdl_groupings_groups WHERE groupingid = $spgrouping->id";
$nbspgroups = $DB->get_record_sql($sql)->nbspgroups;


switch ($action) {

    case false: //OK, display form.
        break;

    case 'showcreateorphangroupform':
        //BRICE Création d'un groupe supplémentaire
        $nbspgroups++;
        $now = time();
        
        $groupdata = new stdClass();
        $groupdata->courseid = $courseid;
        $groupdata->name = "$sp->name-Gr $nbspgroups";
        $groupdata->description = "Groupe de situation problème";
        $groupdata->descriptionformat = 1;
        $groupdata->timecreated = $now;
        $groupdata->timemodified = $now;    
        $groupid = groups_create_group($groupdata);

        //On ajoute le groupe au groupement
        $groupinggroup = new stdClass();
        $groupinggroup->groupingid = $spgrouping->id;
        $groupinggroup->groupid = $groupid;
        $groupinggroup->timeadded = $now;
        $groupinggroup->id = $DB->insert_record("groupings_groups", $groupinggroup);   
        //FIN
        
        //BRICE redirect(new moodle_url('/group/group.php', array('courseid'=>$courseid,'spid'=>$spid)));
        break;

   
    case 'showgroupsettingsform':
        redirect(new moodle_url('/group/group.php', array('courseid'=>$courseid, 'id'=>$paramgroupid,'spid'=>$spid)));
        break;
    
    case 'cancel':
        redirect(new moodle_url('/course/view.php', array('id'=>$courseid)));
        break;
    
    case 'cleanmembersgroup':
        // effacer tous les membres du group
        $DB->delete_records('groups_members',array('groupid'=>$paramgroupid));
        redirect(new moodle_url('/group/spmembers.php', array('id'=>$courseid,'spid'=>$spid)));
        break;
    
    case 'cleanallmembersgroup':
        // effacer tous les membres du group
        $sql = "select * from mdl_sp_groups where spid=$spid";
        $spgroups = $DB->get_recordset_sql($sql);
        foreach($spgroups as $spgroup){
            $DB->delete_records('groups_members',array('groupid'=>$spgroup->groupid));
        }
        redirect(new moodle_url('/group/spmembers.php', array('id'=>$courseid,'spid'=>$spid)));
        break;

    default: //ERROR.
        print_error('unknowaction', '', $returnurl);
        break;
}

$PAGE->set_title("$course->shortname: $strgroups");
$PAGE->set_heading("$course->fullname - Groupes pour $sp->name");
echo $OUTPUT->header();
//echo  $OUTPUT->heading('Modifier les groupes pour la situation problème'.": $sp->name", 3);

//echo "<form id='cleanallmembersgroup' method='post' action=' $CFG->wwwroot./group/spmembers.php?id= .$courseid.'&spid='.$spid>";
echo "<center><a href='$CFG->wwwroot/group/cleanallmembers.php?courseid=$courseid&spid=$spid'>"
        . "<input type='submit' style='width:200px' value='Vider tous les groupes' />"
   . "</a></center><br>";
//echo "</form>";

//Groupes du groupement
$sql = "SELECT groupid FROM mdl_groupings_groups WHERE groupingid = $spgrouping->id";
$spgroupinggroups = $DB->get_recordset_sql($sql);

foreach($spgroupinggroups as $spgroupinggroup){
    
    $sql = "select * from mdl_groups where id = $spgroupinggroup->groupid";
    $group = $DB->get_record_sql($sql);

    $groupid = $group->id;

    $groupmembersselector = new group_members_selector('removeselect', array('groupid' => $groupid, 'courseid' => $course->id));
    $potentialmembersselector = new group_non_members_selector('addselect', array('groupid' => $groupid, 'courseid' => $course->id));

    if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
        $userstoadd = $potentialmembersselector->get_selected_users();
        if (!empty($userstoadd)) {
            foreach ($userstoadd as $user) {
                if (!groups_add_member($paramgroupid, $user->id)) {
                    print_error('erroraddremoveuser', 'group', $returnurl);
                }
                $groupmembersselector->invalidate_selected_users();
                $potentialmembersselector->invalidate_selected_users();
            }
        }
    }


    if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
        $userstoremove = $groupmembersselector->get_selected_users();
        if (!empty($userstoremove)) {
            foreach ($userstoremove as $user) {
                if (!groups_remove_member_allowed($groupid, $user->id)) {
                    print_error('errorremovenotpermitted', 'group', $returnurl,
                            $user->fullname);
                }
                if (!groups_remove_member($groupid, $user->id)) {
                    print_error('erroraddremoveuser', 'group', $returnurl);
                }
                else {
                    //redirect("$CFG->wwwroot/group/spmembers.php?id=$courseid&spid=$spid",'',0);
                    header("Location: $CFG->wwwroot/group/spmembers.php?id=$courseid&spid=$spid");
                }
                $groupmembersselector->invalidate_selected_users();
                $potentialmembersselector->invalidate_selected_users();
            }
        }
    }

    $groupname = format_string($group->name);




    // Store the rows we want to display in the group info.
    $groupinforow = array();

    // Check if there is a picture to display.
    if (!empty($group->picture)) {
        $picturecell = new html_table_cell();
        $picturecell->attributes['class'] = 'left side picture';
        $picturecell->text = print_group_picture($group, $course->id, true, true, false);
        $groupinforow[] = $picturecell;
    }
    
    /* BRICE
    // Check if there is a description to display.
    $group->description = file_rewrite_pluginfile_urls($group->description, 'pluginfile.php', $context->id, 'group', 'description', $group->id);
    if (!empty($group->description)) {
        if (!isset($group->descriptionformat)) {
            $group->descriptionformat = FORMAT_MOODLE;
        }

        $options = new stdClass;
        $options->overflowdiv = true;

        $contentcell = new html_table_cell();
        $contentcell->attributes['class'] = 'content';
        $contentcell->text = format_text($group->description, $group->descriptionformat, $options);
        $groupinforow[] = $contentcell;
    } */

    // Check if we have something to show.
    if (!empty($groupinforow)) {
        $groupinfotable = new html_table();
        $groupinfotable->attributes['class'] = 'groupinfobox';
        $groupinfotable->data[] = new html_table_row($groupinforow);
        echo html_writer::table($groupinfotable);
    }

    /// Print the editing form
    ?>


    <div id="addmembersform">
        <form id="assignform" method="post" action="<?php echo $CFG->wwwroot; ?>/group/spmembers.php?id=<?php echo $courseid; ?>&spid=<?php echo $spid; ?>&groupid=<?php echo $spgroupinggroup->groupid; ?>">
        <div>
        <input type="hidden" name="sesskey" value="<?php p(sesskey()); ?>" />

        <table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
        <tr>
          <td id='existingcell'>
              <p>
                <label for="removeselect"><?php print_string('groupmembers', 'group'); echo ' '.$group->name; ?></label>
              </p>
              <?php $groupmembersselector->display(); ?>
              </td>
          <td id='buttonscell'>
            <p class="arrow_button">
                <input name="add" id="add" type="submit" style="width:200px" value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>" title="<?php print_string('add'); ?>" /><br />
                <input name="remove" id="remove" type="submit" style="width:200px" value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php print_string('remove'); ?>" />
            </p>
            <p></p>
            <p></p>
            <p></p>
            
            <!--BRICE <p>
                <input name="act_showgroupsettingsform" id="showeditgroupsettingsform" type="submit" style="width:200px" value="Réglages" />
            </p>-->
            
            <!--<p>
                <input name="act_deletegroup" id="deletegroup" type="submit" style="width:200px"  value="Supprimer le groupe" />
            </p>-->

            <p>
                <input type="submit" name="act_showcreateorphangroupform" id="showcreateorphangroupform" style="width:200px" value="<?php echo "Créer un autre groupe"; ?>" />
            </p>
            <p>
                <input name="act_cleanmembersgroup" id="cleanmembersgroup" type="submit" style="width:200px" value="Vider le groupe" />
            </p>
          </td>
          <td id='potentialcell'>
              <p>
                <label for="addselect"><?php print_string('potentialmembs', 'group'); ?></label>
              </p>

              <?php $potentialmembersselector->display(false, $spid); ?>
          </td>
          <!--<td>
            <p><--?php echo($strusergroupmembership) ?></p>
            <div id="group-usersummary"></div>
          </td>-->
        </tr>
        <tr><td colspan="3" id='backcell'>
            <input type="submit" name="act_cancel" value="Retour au cours" />
        </td></tr>
        </table>
        </div>
        </form>
    </div>

    <?php
}
    //outputs the JS array used to display the other groups users are in
    $potentialmembersselector->print_user_summaries($course->id);

    //this must be after calling display() on the selectors so their setup JS executes first
    $PAGE->requires->js_init_call('init_add_remove_members_page', null, false, $potentialmembersselector->get_js_module());

    echo $OUTPUT->footer();
    
    ob_end_flush();
    
    
    
    function groups_param_action($prefix = 'act_') {
    $action = false;
//($_SERVER['QUERY_STRING'] && preg_match("/$prefix(.+?)=(.+)/", $_SERVER['QUERY_STRING'], $matches)) { //b_(.*?)[&;]{0,1}/

    if ($_POST) {
        $form_vars = $_POST;
    }
    elseif ($_GET) {
        $form_vars = $_GET;
    }
    if ($form_vars) {
        foreach ($form_vars as $key => $value) {
            if (preg_match("/$prefix(.+)/", $key, $matches)) {
                $action = $matches[1];
                break;
            }
        }
    }
    if ($action && !preg_match('/^\w+$/', $action)) {
        $action = false;
        print_error('unknowaction');
    }
    ///if (debugging()) echo 'Debug: '.$action;
    return $action;
}


