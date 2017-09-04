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
 * Balaie toute la liste des utilisateurs inscrits sur la plateforme et passe les noms et prénoms 
 * en lettres minuscules (avec les initiales en majuscules).
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
    $PAGE->set_title("Script de correction de la casse.");
    $PAGE->set_course($SITE);
    $PAGE->set_other_editing_capability('moodle/course:update');
    $PAGE->set_other_editing_capability('moodle/course:manageactivities');
    $PAGE->set_other_editing_capability('moodle/course:activityvisibility');


    // Prevent caching of this page to stop confusion when changing page after making AJAX changes
    $PAGE->set_cacheable(false);

    
    user_accesstime_log();
    
    $hassiteconfig = has_capability('moodle/site:config', context_system::instance());


/// If the site is currently under maintenance, then print a message
    if (!empty($CFG->maintenance_enabled) and !$hassiteconfig) {
        print_maintenance_message();
    }

    if ($hassiteconfig && moodle_needs_upgrading()) {
        redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
    }

    if (get_home_page() != HOMEPAGE_SITE) {
        // Redirect logged-in users to My Moodle overview if required
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_SITE);
        } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && optional_param('redirect', 1, PARAM_BOOL) === 1) {
            redirect($CFG->wwwroot .'/my/');
        } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_USER)) {
            $PAGE->settingsnav->get('usercurrentsettings')->add(get_string('makethismyhome'), new moodle_url('/', array('setdefaulthome'=>true)), navigation_node::TYPE_SETTING);
        }
    }


/*/// If the hub plugin is installed then we let it take over the homepage here
    if (file_exists($CFG->dirroot.'/local/hub/lib.php') and get_config('local_hub', 'hubenabled')) {
        require_once($CFG->dirroot.'/local/hub/lib.php');
        $hub = new local_hub();
        $continue = $hub->display_homepage();
        //display_homepage() return true if the hub home page is not displayed
        //mostly when search form is not displayed for not logged users
        if (empty($continue)) {
            exit;
        }
    }*/

    $PAGE->set_pagetype('site-index');
    $PAGE->set_docs_path('');
    $PAGE->set_pagelayout('frontpage');

    $editing = $PAGE->user_is_editing();
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);

    $courserenderer = $PAGE->get_renderer('core', 'course');
	
    echo $OUTPUT->header();
    
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
            // make sure section with number 1 exists
            course_create_sections_if_missing($SITE, 1);
            // re-request modinfo in case section was created
            $modinfo = get_fast_modinfo($SITE);
        }
        $section = $modinfo->get_section_info(1);
        if (($section && (!empty($modinfo->sections[1]) or !empty($section->summary))) or $editing) {
            echo $OUTPUT->box_start('generalbox sitetopic');

            /// If currently moving a file then show the current clipboard
            if (ismoving($SITE->id)) {
                $stractivityclipboard = strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
                echo '<p><font size="2">';
                echo "$stractivityclipboard&nbsp;&nbsp;(<a href=\"course/mod.php?cancelcopy=true&amp;sesskey=".sesskey()."\">". get_string('cancel') .'</a>)';
                echo '</font></p>';
            }

            $context = context_course::instance(SITEID);
            $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php', $context->id, 'course', 'section', $section->id);
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



/************************************************* DEBUT CONTENT ***************************************************************/
// SEB - fevrier 2014
/*******************************************************************************************************************************/




echo "<script type='text/javascript' src='$CFG->wwwroot/lib/js/foToolTip.js'></script>";
echo "<script type='text/javascript' src='$CFG->wwwroot/lib/js/spin.js'></script>";

?>

<script type="text/javascript">
    function affiche( scriptName, args ){
     
    var xhr_object = null;
     
    if(window.XMLHttpRequest) // Firefox
    xhr_object = new XMLHttpRequest();
    else if(window.ActiveXObject) // Internet Explorer
    xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
    else { // XMLHttpRequest non supporté par le navigateur
    alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
    return xhr_object;
    }
     
    xhr_object.open("POST", scriptName, true);
     
    xhr_object.onreadystatechange = function() {
    if(xhr_object.readyState == 4) {
     
    if(xhr_object.status != 200){//Message si il se preoduit une erreur
    document.getElementById(args).innerHTML ="Erreur code " + xhr_object.status;
    } else {//On met le contenu du fichier externe dans la div "form"
    document.getElementById(args).innerHTML = xhr_object.responseText;

        /*var arr = document.getElementsByTagName('script')
        for (var n = 0; n < arr.length; n++)
        eval(arr[n].innerHTML)//run script inside div*/

    }
    } else {//Message affiché pendant le chargement
    document.getElementById(args).innerHTML = "Patientez .........";
    }



  
     
    //alert(xhr_object.readyState);
    return xhr_object.readyState;
    }
     
    xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
     
    xhr_object.send(args);


     
    }
</script>

<style>
    #maincontent{
        display : none;
    }
</style>





<?php // ON AFFICHE LE BLOC UNIQUEMENT PORU LES PROFS (COURSE CREATOR)
if (has_capability('moodle/course:create', get_context_instance(CONTEXT_SYSTEM))) {  ?>
<!-- BLOCK DEMANDE DE COURS -->

<div class="block">
    <div class="header">
        <div class="title">
            <h2>Rectification de la casse des noms d'utilisateurs</h2>
        </div>
    </div>
    <div class="content">
        <?php
            $sql = "SELECT id, firstname, lastname FROM mdl_user";
            echo "$sql<br>";
            $users = $DB->get_recordset_sql($sql);
            
            foreach ($users as $user) {
                $firstname = ucwords(strtolower($user->firstname));
                $lastname = ucwords(strtolower($user->lastname));
                $sql = 'UPDATE mdl_user SET firstname = "'.$firstname.'", lastname = "'.$lastname.'" WHERE id = '.$user->id;
                echo "$sql<br>";
                $DB->execute($sql);
            }
            echo "Correction terminée<br>";
        ?>
    </div>
</div>

<?php } ?>

<?php

/***************************** FIN CONTENT **************************************************************************************************/
/********************************************************************************************************************************************/
 
//echo $OUTPUT->footer();
