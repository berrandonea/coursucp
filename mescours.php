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
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!file_exists('./config.php')) {
    header('Location: install.php');
    die;
}
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

// Prevent caching of this page to stop confusion when changing page after making AJAX changes.
$PAGE->set_cacheable(false);

if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hassiteconfig) {
    print_maintenance_message();
}

if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
}

if (get_home_page() != HOMEPAGE_SITE) {
    // Redirect logged-in users to My Moodle overview if required.
    $redirect = optional_param('redirect', 1, PARAM_BOOL);
    if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
        set_user_preference('user_home_page_preference', HOMEPAGE_SITE);
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && $redirect === 1) {
        redirect($CFG->wwwroot .'/my/');
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_USER)) {
        $frontpagenode = $PAGE->settingsnav->find('frontpage', null);
        if ($frontpagenode) {
            $frontpagenode->add(
                get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        } else {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        }
    }
}

// Trigger event.
course_view(context_course::instance(SITEID));

// If the hub plugin is installed then we let it take over the homepage here.
if (file_exists($CFG->dirroot.'/local/hub/lib.php') and get_config('local_hub', 'hubenabled')) {
    require_once($CFG->dirroot.'/local/hub/lib.php');
    $hub = new local_hub();
    $continue = $hub->display_homepage();
    // Function display_homepage() returns true if the hub home page is not displayed
    // ...mostly when search form is not displayed for not logged users.
    if (empty($continue)) {
        exit;
    }
}

//BRICE : L'utilisateur est-il un enseignant ? Est-il enregistré comme utilisateur authentifié ?-----------------------------------------------------

$context = context_system::instance();
$roles = get_user_roles($context, $USER->id, false);
$isknownasteacher = 0;
$isknownasuser = 0;
$isteacher = 0;

foreach ($roles as $role) {
    $roleid = $role->roleid;
    if ($roleid == 7) {
        $isknownasuser = 1;
    }
    if ($roleid == 2) {
        $isknownasteacher = 1;
        $isteacher = 1;
    }
}

//Si l'utilisateur n'a pas encore le rôle "Utilisateur authentifié", on le lui donne
if (!$isknownasuser) {
    role_assign(7, $USER->id, 1, $component = '', $itemid = 0, $timemodified = '');
}

if (!$isknownasteacher) {
    // mail de l'user
    $mailaverifier = $USER->email;
    $verifrole = explode("@", $mailaverifier);
    if (($verifrole[1] == "u-cergy.fr")||($verifrole[1] == "iufm.u-cergy.fr")) {
        $isteacher = 1;
        role_assign(2, $USER->id, 1, $component = '', $itemid = 0, $timemodified = '');
    }
}

//FIN ---------------------------------------------------------
echo '<meta name="description" content="Plateforme pédagogique Moodle Université de Cergy-Pontoise [CoursUCP] cours" />';

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_pagelayout('frontpage');
$editing = $PAGE->user_is_editing();
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$courserenderer = $PAGE->get_renderer('core', 'course');
echo $OUTPUT->header();
?>
<style  type="text/css">
<?php include('assets/bootstrap/css/bootstrap.min.css'); ?>

<?php include('assets/css/animate.css'); ?>

<?php include('assets/css/style.css'); ?>

</style>
<?php
// Print Section or custom info.
$siteformatoptions = course_get_format($SITE)->get_format_options();
$modinfo = get_fast_modinfo($SITE);
$modnames = get_module_types_names();
$modnamesplural = get_module_types_names(true);
$modnamesused = $modinfo->get_used_module_names();
$mods = $modinfo->get_cms();

if (!empty($CFG->customfrontpageinclude)) {
    include($CFG->customfrontpageinclude);

} else if ($siteformatoptions['numsections'] > 0) {
    if ($editing) {
        // Make sure section with number 1 exists.
        course_create_sections_if_missing($SITE, 1);
        // Re-request modinfo in case section was created.
        $modinfo = get_fast_modinfo($SITE);
    }
    $section = $modinfo->get_section_info(1);
    if (($section && (!empty($modinfo->sections[1]) or !empty($section->summary))) or $editing) {
        echo $OUTPUT->box_start('generalbox sitetopic');

        // If currently moving a file then show the current clipboard.
        if (ismoving($SITE->id)) {
            $stractivityclipboard = strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
            echo '<p><font size="2">';
            echo "$stractivityclipboard&nbsp;&nbsp;(<a href=\"course/mod.php?cancelcopy=true&amp;sesskey=".sesskey()."\">";
            echo get_string('cancel') . '</a>)';
            echo '</font></p>';
        }

        $context = context_course::instance(SITEID);

        // If the section name is set we show it.
        if (!is_null($section->name)) {
            echo $OUTPUT->heading(
                format_string($section->name, true, array('context' => $context)),
                2,
                'sectionname'
            );
        }

        $summarytext = file_rewrite_pluginfile_urls($section->summary,
            'pluginfile.php',
            $context->id,
            'course',
            'section',
            $section->id);
        $summaryformatoptions = new stdClass();
        $summaryformatoptions->noclean = true;
        $summaryformatoptions->overflowdiv = true;

        echo format_text($summarytext, $section->summaryformat, $summaryformatoptions);

        if ($editing && has_capability('moodle/course:update', $context)) {
            $streditsummary = get_string('editsummary');
            echo "<a title=\"$streditsummary\" ".
                 " href=\"course/editsection.php?id=$section->id\"><img src=\"" . $OUTPUT->pix_url('t/edit') . "\" ".
                 " class=\"iconsmall\" alt=\"$streditsummary\" /></a><br /><br />";
        }

        $courserenderer = $PAGE->get_renderer('core', 'course');
        echo $courserenderer->course_section_cm_list($SITE, $section);

        echo $courserenderer->course_section_add_cm_control($SITE, $section->section);
        echo $OUTPUT->box_end();
    }
}
// Include course AJAX.
include_course_ajax($SITE, $modnamesused);

if (isloggedin() and !isguestuser() and isset($CFG->frontpageloggedin)) {
    $frontpagelayout = $CFG->frontpageloggedin;
} else {
    $frontpagelayout = $CFG->frontpage;
}

echo "<h1>Mes cours</h1>";
?>
<style>
.service {

    height: 250px;

}

.c { position:absolute;bottom:0;width:390px; }

#maincontent{
	display : none;
}
</style>
<script type="text/javascript">
			function flipflop(id)
			{
				if (document.getElementById(id).style.display == "none")
						document.getElementById(id).style.display = "block";
				else	document.getElementById(id).style.display = "none";
			}
</script>
<?php
$sqlcategories = "SELECT distinct(s.category) as nbr ,cc.name, cc.idnumber FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
where r.userid=$USER->id 
and r.contextid>1
and c.contextlevel = 50
and c.id =r.contextid 
and c.instanceid =s.id
and cc.id =s.category 
AND (cc.idnumber LIKE 'Y2017-%' OR cc.idnumber LIKE 'COLLAB%' OR cc.idnumber LIKE 'PERSO%')
";
$rescategories =$DB->get_records_sql($sqlcategories);

foreach ($rescategories as $category)
{
	//echo "$category->nbr $category->name <br>";
	$sqlparent ="select id,name,parent from mdl_course_categories where id=$category->nbr ";
	$resparent =$DB->get_record_sql($sqlparent);
	if($resparent->parent == 0)
	{
					if($resparent->id ==4)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#2da532;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-biblio.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#2da532;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparent->id ==5)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7f0102;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-droit.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#7f0102;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparent->id ==6)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#0051a1;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-economie-gestion.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#0051a1;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparent->id ==7)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#2da532;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-etudest-interna.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#2da532;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparent->id ==8)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#c97300;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-lettres-sc-humaines.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#c97300;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparent->id ==9)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#0051a1;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-sciences-techniques.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#0051a1;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparent->id ==11)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#515a68;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-iut.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#515a68;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparent->id ==13)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#1E8BC3;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-espe.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#1E8BC3;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparent->id ==14)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#729484;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-sciencespo.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#729484;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					else
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#731472;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-divers.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#731472;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
	}
	else
	{
			$sqlparents ="select id, parent,name from mdl_course_categories where id=$resparent->parent ";
			$resparents =$DB->get_record_sql($sqlparent);
			if($resparents->parent == 0)
			{
				if($resparents->id ==4)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#2da532;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-biblio.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#2da532;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparents->id ==5)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7f0102;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-droit.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#7f0102;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparents->id ==6)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#0051a1;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-economie-gestion.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#0051a1;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparents->id ==7)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#2da532;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-etudest-interna.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#2da532;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparents->id ==8)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#c97300;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
			
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-lettres-sc-humaines.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#c97300;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparents->id ==9)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#0051a1;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
	
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-sciences-techniques.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#0051a1;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparents->id ==11)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#515a68;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-iut.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#515a68;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparents->id ==13)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#1E8BC3;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-espe.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#1E8BC3;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparents->id ==14)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#729484;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
					
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-sciencespo.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#729484;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					else
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#731472;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-divers.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#731472;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
			}
			else
			{
				$sqlparentss ="select id,parent,name from mdl_course_categories where id =$resparents->parent ";
				$resparentss =$DB->get_record_sql($sqlparentss);
				if($resparentss->parent ==0)
				{
					if($resparentss->id ==4)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#2da532;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-biblio.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#2da532;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentss->id ==5)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7f0102;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-droit.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#7f0102;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentss->id ==6)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#0051a1;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-economie-gestion.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#0051a1;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentss->id ==7)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#2da532;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-etudest-interna.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#2da532;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentss->id ==8)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#c97300;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-lettres-sc-humaines.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#c97300;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentss->id ==9)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#0051a1;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
					
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-sciences-techniques.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#0051a1;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentss->id ==11)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#515a68;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-iut.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#515a68;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentss->id ==13)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#1E8BC3;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-espe.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#1E8BC3;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentss->id ==14)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#729484;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
					
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-sciencespo.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#729484;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					else
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#731472;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-divers.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#731472;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
				}
				else
				{
					$sqlparentsse ="select id,parent,name from mdl_course_categories where id =$resparentss->parent ";
					$resparentsse =$DB->get_record_sql($sqlparentsse);
					if($resparentsse->id ==4)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#2da532;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-biblio.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#2da532;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentsse->id ==5)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7f0102;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-droit.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#7f0102;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentsse->id ==6)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#0051a1;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-economie-gestion.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#0051a1;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentsse->id ==7)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#2da532;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";

						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-etudest-interna.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#2da532;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentsse->id ==8)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#c97300;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-lettres-sc-humaines.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#c97300;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentsse->id ==9)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#0051a1;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-sciences-techniques.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#0051a1;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentsse->id ==11)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#515a68;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
					
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-iut.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#515a68;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentsse->id ==13)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#1E8BC3;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-espe.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#1E8BC3;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					elseif ($resparentsse->id ==14)
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#729484;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-sciencespo.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#729484;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
					else
					{
						echo "<div onclick='flipflop('$category->nbr');' style='text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#731472;border-radius:5px 5px 0 0'>$category->name<img src= 'assets/img/sort.png' width='25' height='25' align='right' /></div>";
						
						$sqlcourse = "SELECT distinct(s.id) as nbr , s.fullname as name
						FROM mdl_role_assignments r, mdl_context c, mdl_course s, mdl_course_categories cc 
						where r.userid=$USER->id
						and r.contextid>1
						and c.contextlevel =50
						and c.id =r.contextid 
						and c.instanceid =s.id
						and cc.id =s.category
						and s.category =$category->nbr";
						$rescourse = $DB->get_records_sql($sqlcourse);
						echo "&nbsp;&nbsp;&nbsp;<center><div class='services-container'> <div class='container'><div class='row'>";
						foreach($rescourse as $course)
						{
							$sqlcontext ="select id from mdl_context where contextlevel=50 and instanceid=$course->nbr";
							$rescontext = $DB->get_record_sql($sqlcontext);
							
							$sqlens = "SELECT userid FROM mdl_role_assignments WHERE contextid =$rescontext->id AND roleid =3";
							$resens = $DB->get_records_sql($sqlens);
							
							$sqlcount = "select count(id) as nbr from mdl_role_assignments where contextid =$rescontext->id AND roleid =3";
							$rescount = $DB->get_record_sql($sqlcount);
							
							echo "<div class='col-sm-3'><div class='service wow fadeInUp'>";
							echo "<div class='service-icon'>
							<i><center><img src= 'assets/img/cap-divers.png' width='40' height='40' /></center></i></div>";							
		                    echo "<center><h3>$course->name</h3></center>";	                    
							if($rescount->nbr ==1)
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignant : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
								echo "</ul></p>";
							}
							else
							{
								echo "<p style ='vertical-align: top; text-align: left;'><strong>Enseignants : </strong>";
								echo "<ul style ='vertical-align: top; text-align: left;'>";
								foreach($resens as $user)
								{
									$sqluser = "select firstname, lastname, email  from mdl_user where id =$user->userid";
									$resuser = $DB->get_record_sql($sqluser);
									echo "<a href='$CFG->wwwroot/user/view.php?id=$user->userid&course=$course->nbr'><li>$resuser->firstname $resuser->lastname</li></a>";
								}
									echo "</ul></p>";
							}
							
							echo "<div class='c'>
							 <a class='big-link-1' style='background-color:#731472;' href='$CFG->wwwroot/course/view.php?id=$course->nbr'>Accéder</a>
							</div>";
							
							echo "</div></div>";
						}
						echo "</div></div></div></center><br>";
					}
				}
			}
	}
	
}

echo $OUTPUT->footer();
?>
  <script src="assets/js/jquery-1.11.1.min.js"></script>
        <script src="assets/bootstrap/js/bootstrap.min.js"></script>
        <script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
        <script src="assets/js/jquery.backstretch.min.js"></script>
        <script src="assets/js/wow.min.js"></script>
        <script src="assets/js/retina-1.1.0.min.js"></script>
        <script src="assets/js/jquery.magnific-popup.min.js"></script>
        <script src="assets/flexslider/jquery.flexslider-min.js"></script>
        <script src="assets/js/jflickrfeed.min.js"></script>
        <script src="assets/js/masonry.pkgd.min.js"></script>
        <script src="http://maps.google.com/maps/api/js?sensor=true"></script>
        <script src="assets/js/jquery.ui.map.min.js"></script>
        <script src="assets/js/scripts.js"></script>
