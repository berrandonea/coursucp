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
require_once($CFG->dirroot.'/lib/coursecatlib.php'); // LAURENT
require_once($CFG->libdir .'/filelib.php');
//require_once('bddenp15.php');
redirect_if_major_upgrade_required();

//BRICE
$openblock = optional_param('open', 'lastnews', PARAM_ALPHA);
if ($openblock == 'transfer') {
	header('Location: index.php?open=mycourses');
}
$collab = optional_param('collab', '', PARAM_TEXT);
$perso = optional_param('perso', 0, PARAM_INT);
//FIN

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

//BRICE : L'utilisateur est-il un enseignant ? Est-il enregistré comme utilisateur authentifié ? ----------------------------------------------------

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

// Si c'est bien un enseignant, il peut créer un espace collaboratif
if ($isteacher && $collab) {
    $collabspace = create_collab($collab);
    if ($collabspace) {
		mailspacecreation('collab', $collabspace);
        header("Location: course/view.php?id=$collabspace->id");
    }
}

// Sinon, il peut créer un espace personnel
if (!$isteacher && $perso) {
	$persospace = create_perso();
    if ($persospace) {
        mailspacecreation('perso', $persospace);
        header("Location: course/view.php?id=$persospace->id");
    }
}

//echo "<p style='color:red;font-weight:bold'>NOUS RENCONTRONS ACTUELLEMENT DES DIFFICULTES SUR CETTE PLATEFORME. CE SERA CORRIGE DANS QUELQUES HEURES. MERCI DE VOTRE PATIENCE.</p>";


//FIN ---------------------------------------------------------

echo '<meta name="description" content="Plateforme pédagogique Moodle Université de Cergy-Pontoise CoursUCP cours" />';

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_pagelayout('frontpage');
$editing = $PAGE->user_is_editing();
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$courserenderer = $PAGE->get_renderer('core', 'course');
echo $OUTPUT->header();

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

foreach (explode(',', $frontpagelayout) as $v) {
    switch ($v) {
        // Display the main part of the front page.
        case FRONTPAGENEWS:
            if ($SITE->newsitems) {
                // Print forums only when needed.
                require_once($CFG->dirroot .'/mod/forum/lib.php');

                if (! $newsforum = forum_get_course_forum($SITE->id, 'news')) {
                    print_error('cannotfindorcreateforum', 'forum');
                }

                // Fetch news forum context for proper filtering to happen.
                $newsforumcm = get_coursemodule_from_instance('forum', $newsforum->id, $SITE->id, false, MUST_EXIST);
                $newsforumcontext = context_module::instance($newsforumcm->id, MUST_EXIST);

                $forumname = format_string($newsforum->name, true, array('context' => $newsforumcontext));
                echo html_writer::tag('a',
                    get_string('skipa', 'access', core_text::strtolower(strip_tags($forumname))),
                    array('href' => '#skipsitenews', 'class' => 'skip-block'));

                // Wraps site news forum in div container.
                echo html_writer::start_tag('div', array('id' => 'site-news-forum'));

                if (isloggedin()) {
                    $SESSION->fromdiscussion = $CFG->wwwroot;
                    $subtext = '';
                    if (\mod_forum\subscriptions::is_subscribed($USER->id, $newsforum)) {
                        if (!\mod_forum\subscriptions::is_forcesubscribed($newsforum)) {
                            $subtext = get_string('unsubscribe', 'forum');
                        }
                    } else {
                        $subtext = get_string('subscribe', 'forum');
                    }
                    echo $OUTPUT->heading($forumname);
                    $suburl = new moodle_url('/mod/forum/subscribe.php', array('id' => $newsforum->id, 'sesskey' => sesskey()));
                    echo html_writer::tag('div', html_writer::link($suburl, $subtext), array('class' => 'subscribelink'));
                } else {
                    echo $OUTPUT->heading($forumname);
                }

                forum_print_latest_discussions($SITE, $newsforum, $SITE->newsitems, 'plain', 'p.modified DESC');

                // End site news forum div container.
                echo html_writer::end_tag('div');

                echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipsitenews'));
            }
        break;

        case FRONTPAGEENROLLEDCOURSELIST:
            $mycourseshtml = $courserenderer->frontpage_my_courses();
            if (!empty($mycourseshtml)) {
                echo html_writer::tag('a',
                    get_string('skipa', 'access', core_text::strtolower(get_string('mycourses'))),
                    array('href' => '#skipmycourses', 'class' => 'skip-block'));

                // Wrap frontpage course list in div container.
                echo html_writer::start_tag('div', array('id' => 'frontpage-course-list'));

                echo $OUTPUT->heading(get_string('mycourses'));
                echo $mycourseshtml;

                // End frontpage course list div container.
                echo html_writer::end_tag('div');

                echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipmycourses'));
                break;
            }
            // No "break" here. If there are no enrolled courses - continue to 'Available courses'.

        case FRONTPAGEALLCOURSELIST:
            $availablecourseshtml = $courserenderer->frontpage_available_courses();
            if (!empty($availablecourseshtml)) {
                echo html_writer::tag('a',
                    get_string('skipa', 'access', core_text::strtolower(get_string('availablecourses'))),
                    array('href' => '#skipavailablecourses', 'class' => 'skip-block'));

                // Wrap frontpage course list in div container.
                echo html_writer::start_tag('div', array('id' => 'frontpage-course-list'));

                echo $OUTPUT->heading(get_string('availablecourses'));
                echo $availablecourseshtml;

                // End frontpage course list div container.
                echo html_writer::end_tag('div');

                echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipavailablecourses'));
            }
        break;

        case FRONTPAGECATEGORYNAMES:
            echo html_writer::tag('a',
                get_string('skipa', 'access', core_text::strtolower(get_string('categories'))),
                array('href' => '#skipcategories', 'class' => 'skip-block'));

            // Wrap frontpage category names in div container.
            echo html_writer::start_tag('div', array('id' => 'frontpage-category-names'));

            echo $OUTPUT->heading(get_string('categories'));
            echo $courserenderer->frontpage_categories_list();

            // End frontpage category names div container.
            echo html_writer::end_tag('div');

            echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipcategories'));
        break;

        case FRONTPAGECATEGORYCOMBO:
            echo html_writer::tag('a',
                get_string('skipa', 'access', core_text::strtolower(get_string('courses'))),
                array('href' => '#skipcourses', 'class' => 'skip-block'));

            // Wrap frontpage category combo in div container.
            echo html_writer::start_tag('div', array('id' => 'frontpage-category-combo'));

            echo $OUTPUT->heading(get_string('courses'));
            echo $courserenderer->frontpage_combo_list();

            // End frontpage category combo div container.
            echo html_writer::end_tag('div');

            echo html_writer::tag('span', '', array('class' => 'skip-block-to', 'id' => 'skipcourses'));
        break;

        case FRONTPAGECOURSESEARCH:
            echo $OUTPUT->box($courserenderer->course_search_form('', 'short'), 'mdl-align');
        break;

    }
    echo '<br />';
}
/* Commenté par BRICE : pas de bouton Ajouter un cours
if ($editing && has_capability('moodle/course:create', context_system::instance())) {
    echo $courserenderer->add_new_course_button();
} */

/************************************************* DEBUT CONTENT ***************************************************************/
//BRICE
/*******************************************************************************************************************************/

echo "<script type='text/javascript' src='$CFG->wwwroot/lib/js/foToolTip.js'></script>";
echo "<script type='text/javascript' src='$CFG->wwwroot/lib/js/spin.js'></script>";
?>
<script type="text/javascript">
function affiche( scriptName, args ){
            //alert("Test");
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
        } else {//Message affich� pendant le chargement
            document.getElementById(args).innerHTML = "Merci de patienter...";
        }
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

<script type="text/javascript">
function flipflop(id) {
	if (document.getElementById(id).style.display == "none") {
		document.getElementById(id).style.display = "block";
	} else {
		document.getElementById(id).style.display = "none";
	}
}
</script>

<!--<p style='font-weight:bold;text-align:center;color:red'>
//A partir du lundi 29/08/2016, ce site sera visible dans MonUCP (onglet Pédagogie). La plateforme 2015-2016 restera disponible à l'adresse <a href='https://enp15.u-cergy.fr'>https://enp15.u-cergy.fr</a>.<br>
//Il se peut que les deux plateformes soient inaccessibles pendant quelques minutes au moment de la bascule, qui aura lieu au cours de la journée de lundi.
//</p>-->

<!-- BLOCK INFO COURSUCP -->
<div class="block">
    <div onclick="flipflop('header');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">
        Actualités CoursUCP
        <img src ="Images/sort.png" style="float: right "  height="25" width="25">
    </div>
    <div id = "header"
         class = "content"
         style='width:100%;display:<?php if(isset($_POST['envoidemande'])||($openblock != 'lastnews')) echo "none"; else echo "block"; ?>'
    >
    <?php

    echo "Bonjour,<br><br>La plateforme CoursUCP 2017-2018 est disponible. Vous pouvez désormais :<br><ul><li>Créer vos nouveaux cours</li><li>Copier vos cours de l'année 2016-2017</li></ul>";
    echo "<p style='font-weight:bold;color:blue'>Vos cours de l’année universitaire 2016-2017 sont toujours accessibles dans le bloc « Mes anciens cours (2016-2017) ».</p>";
    echo "<br>L'équipe du SEFIAP<br>";

//    if ($isteacher) {
//	$infocourseid = 912;
//    } else {
//        $infocourseid = 0;
//    }
//    if ($infocourseid) {
//		$posts = $DB->get_records('format_socialwall_posts', array('courseid' => $infocourseid, 'togroupid' => 0, 'private' => 0), 'timecreated');
//		foreach ($posts as $post) {
//			$lasttext = $post->posttext;
//		}
//		echo "$lasttext<br>";
//	}
    ?>
    </div>
</div>

<!-- BLOCK COURS CREES -->
<div class="block">
    <div onclick="flipflop('mescours');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">
        Mes cours 2017-2018
        <img src ="Images/sort.png" style="float: right "  height="25" width="25">
    </div>
    <div id = "mescours"
         class="content"
         style='width:100%;display:<?php if ($openblock == 'mycourses') echo "block"; else echo "none"; ?>'
    >
        <?php


        function get_content() {
            global $CFG, $USER;
            global $DB, $PAGE;

            $out = '';

            $out .= '<p style="font-weight:bold;padding:5px;color:white;background-color:#731472;width:80%">Bibliothèque Universitaire</p><ul>';
            $url = $CFG->wwwroot.'/course/view.php?id=9';
            $out .= '<li class="course"><strong><a href="'.$url.'" title="UBIB11-11BIB">Aide à la recherche documentaire</a></strong><br><br>';

            //Si l'utilisateur est bibliothécaire
            $biblicontextid = 245; //Contexte du cours de la BU
            $bibliochatid = 13; //Chat de la BU
            $sql = "SELECT id FROM mdl_role_assignments WHERE userid = $USER->id AND roleid = 3 AND contextid = $biblicontextid";
            $biblioth = $DB->get_record_sql($sql);
            if (($USER->id == 31983 /*Martine Benkimoun*/)||($USER->id == 2914)||($USER->id == 3203) ||($USER->id == 2157)) {
                $out .= "&nbsp;Vous avez été identifié(e) comme bibliothécaire. <a target='_blanck' style='color:blue;font-weight:bold' href='https://cours.u-cergy.fr/mod/chat/gui_ajax/index.php?id=$bibliochatid'>Cliquer ici</a> pour rejoindre le chat.<br><br>";
            }
            $out .= '&nbsp;<strong>Rédigé par</strong> : l\'équipe de la Bibliothèque Universitaire&nbsp;&nbsp;&nbsp;&nbsp;';
            if ((is_siteadmin())||$biblioth) {
                //Nombre de bibliothécaires connectés
                $sql = "SELECT COUNT(cu.id) AS nbbiblios FROM mdl_chat_users cu, mdl_role_assignments ra WHERE ra.userid = cu.userid AND ra.roleid = 3 AND ra.contextid = $biblicontextid AND cu.chatid = $bibliochatid";
                $presentbiblioths = $DB->get_record_sql($sql);
                if ($presentbiblioths) {
                    if ($presentbiblioths->nbbiblios == 1) {
                        $out .= "<span style='font-weight:bold;color:green'>"
                                . "<a target='_blanck' href='https://cours.u-cergy.fr/mod/chat/gui_ajax/index.php?id=$bibliochatid' style='color:blue;font-weight:bold'>"
                                . "1 bibliothécaire"
                                . "</a>"
                                . " est en ligne pour répondre à vos questions sur la BU."
                                . "</span>";
                    } else if ($presentbiblioths->nbbiblios > 1) {
                        $out .= "<span style='font-weight:bold;color:green'>"
                                . "<a target='_blanck' href='https://cours.u-cergy.fr/mod/chat/gui_ajax/index.php?id=$bibliochatid' style='color:blue;font-weight:bold'>"
                                . "$presentbiblioths->nbbiblios bibliothécaires"
                                . "</a>"
                                . " sont en ligne pour répondre à vos questions sur la BU."
                                . "</span>";
                    }
                }

            }
            $out .= "<br><br></li>";
            $url = $CFG->wwwroot.'/course/view.php?id=838';
            $out .= '<li class="course"><strong><a href="'.$url.'" title="UBIB11-11BIB">Se connecter aux ressources numériques : méthodes et tutoriels</a></strong><br><br>';
            $out .= '&nbsp;<strong>Rédigé par</strong> : l\'équipe de la Bibliothèque Universitaire&nbsp;&nbsp;&nbsp;&nbsp;';
	    $out .= '</li>';
	    $out .= "</ul>";

            if (!isloggedin() or empty($USER->id) or $USER->id == 1) {
                //not logged in or logged in as guest - display nothing?
            } else {

                if ($CFG->version < 2012120300) {
                    //the numsections was moved in 2.4
                    //$courses = enrol_get_my_courses('numsections', 'visible DESC, fullname ASC');
                    $courses = enrol_get_my_courses('summary, summaryformat', 'visible DESC, fullname ASC');
                } else {
                       // $courses = enrol_get_my_courses('', 'visible DESC, fullname ASC');
                    $courses = enrol_get_my_courses('summary, summaryformat', 'visible DESC, fullname ASC');
                }

				//On retire les cours 2016
				foreach ($courses as $course) {
					$prefix = substr($course->idnumber, 0, 6);
					if (($prefix != 'Y2017-')&&($prefix != 'PERSO-')&&($prefix != 'COLLAB')) {
						unset($courses[$course->id]);
					}
				}



                //$out .= '<p style="font-weight:bold;padding:5px;color:white;background-color : #7F7F7F">Vos cours créés</p><ul>';

                if (!$courses) {
                    //$out .= get_string('noenrollments', 'block_course_tree_list');
                    $out .= "";
                } else {
                    $query = 'SELECT * FROM '.$CFG->prefix.'course_categories WHERE id IN (SELECT category FROM mdl_course) ORDER BY idnumber';
                    $totalcat = $DB->count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'course_categories WHERE id IN (SELECT category FROM mdl_course) ORDER BY sortorder');

                    $course_categories = $DB->get_records_sql($query);

                    foreach ($course_categories as $cc) {

                        //add the sub_id element to all objects
                        $cc->sub_ids = array();
                        $cc->open = "";
                        $cc->sub_ids[$cc->id] = $cc->id;

                        if ($cc->parent != 0) {
                            $rec = $cc->parent;
                            $allow_exit = 0;
                            do {
                                $course_categories[$rec]->sub_ids[$cc->id] = $cc->id;

                                if (isset($course_categories[$rec]->parent) && $course_categories[$rec]->parent != 0) {
                                    $rec = $course_categories[$rec]->parent;
                                } else {
                                    $allow_exit = 1;
                                }
                            } while ($allow_exit == 0);
                        }
                    }

                    $last_course_id = 0;
                    $last_course_depth = 0;

                    foreach (array_slice($course_categories, 0, $totalcat) as $cc) {
                        if ($cc->id != $CFG->catbrouillonsid || has_capability('moodle/course:create', get_context_instance(CONTEXT_SYSTEM))) {
                            $displayed = 0;
                            foreach ($courses as $course) {
                                if (array_key_exists($course->category, $cc->sub_ids)) {
                                    if ($displayed == 0) {
                                        $displayed = 1;
                                        $depth = $cc->depth - 1;

                                        if ($last_course_depth >= $cc->depth) {
                                            do {
                                                $last_course_depth -= 1;
                                            } while ($last_course_depth != ($cc->depth - 1));
                                        }

                                        $out .= '<p style="font-weight:bold;padding:5px;color:white;background-color:#731472;width:80%">'.$cc->name.'</p><ul>';
                                        $last_course_depth = $cc->depth;
                                    }
                                }

                                if ($course->category == $cc->id) {
                                        $query = "SELECT u.id, u.firstname as ufirstname, u.lastname as ulastname, u.email, c.fullname
                                        FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c
                                        WHERE u.id = r.userid
                                        AND r.contextid = cx.id
                                        AND cx.instanceid = c.id
                                        AND r.roleid =3
                                        AND cx.contextlevel =50
                                        AND c.id = ".$course->id;

                                        $enseignants = $DB->get_records_sql($query);

                                        $url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
                                        $out .= '<li class="course"><strong><a href="'.$url.'" title="'.$course->shortname.'">'.$course->fullname.'</a></strong>';
                                        $term = $DB->get_field('course', 'term', array('id' => $course->id));
                                        if ($term) {
											$out .= " - SEM. $term";
										}
                                        $out .= '<br/>'.$course->summary.'<br/>';

                                        $countt = 0; $sss = ""; $chainetotale = "";
                                        foreach ($enseignants as $enseignant) {
											$urluser = $CFG->wwwroot.'/user/view.php?id='.$enseignant->id.'&course='.$course->id;
                                            if($countt > 0) $chainetotale .= " - ";
                                            $chainetotale .=  '<a href="'.$urluser.'">'.$enseignant->ufirstname." ".$enseignant->ulastname.'</a>';
                                            $countt++;
                                        }

                                        if ($countt>1) $sss = "s";
                                        $out .= '<strong>Enseignant'.$sss.'</strong> :'.$chainetotale;
                                        $out .= '</li><br/>';
                                }
                            }

                            $out .= "</ul>";
                        }
                    }
                }
            }
            echo $out;
        }

        get_content();
        ?>
    </div>
</div>


<!-- BLOC ANCIENNES PLATEFORMES -->
    <div class="block">
        <div onclick="flipflop('heade');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">Mes anciens cours (2016-2017)<img src ="Images/sort.png" style="float: right "  height="25" width="25"></div>
    		<div id ="heade" class="content" style='width:100%;display:none'>
    		<?php
    		$courses = enrol_get_my_courses('summary, summaryformat', 'visible DESC, fullname ASC');
    		//On filtre les cours 2016
			foreach ($courses as $course) {
				$prefix = substr($course->idnumber, 0, 6);
				if ($prefix == 'Y2017-') {
					unset($courses[$course->id]);
				}
				if ($prefix == 'Y2018-') {
					unset($courses[$course->id]);
				}
				if ($prefix == 'Y2019-') {
					unset($courses[$course->id]);
				}
				if ($prefix == 'COLLAB') {
					unset($courses[$course->id]);
				}
				if ($prefix == 'PERSO-') {
					unset($courses[$course->id]);
				}
			}

    		if (!$courses) {
                echo "Vous n'étiez inscrit à aucun cours en 2016-2017";
            } else {
                $query = 'SELECT * FROM '.$CFG->prefix.'course_categories WHERE id IN (SELECT category FROM mdl_course) ORDER BY idnumber';
                $totalcat = $DB->count_records_sql('SELECT COUNT(*) FROM '.$CFG->prefix.'course_categories WHERE id IN (SELECT category FROM mdl_course) ORDER BY sortorder');

                $course_categories = $DB->get_records_sql($query);

                foreach ($course_categories as $cc) {
                    //add the sub_id element to all objects
                    $cc->sub_ids = array();
                    $cc->open = "";
                    $cc->sub_ids[$cc->id] = $cc->id;

                    if ($cc->parent != 0) {
                        $rec = $cc->parent;
                        $allow_exit = 0;
                        do {
                            $course_categories[$rec]->sub_ids[$cc->id] = $cc->id;
                            if (isset($course_categories[$rec]->parent) && $course_categories[$rec]->parent != 0) {
                                $rec = $course_categories[$rec]->parent;
                            } else {
                                $allow_exit = 1;
                            }
                        } while ($allow_exit == 0);
                    }
                }

                $last_course_id = 0;
                $last_course_depth = 0;

				$out = '';
                foreach (array_slice($course_categories, 0, $totalcat) as $cc) {
                    if ($cc->id != $CFG->catbrouillonsid || has_capability('moodle/course:create', get_context_instance(CONTEXT_SYSTEM))) {
                        $displayed = 0;

                        foreach ($courses as $course) {
                            if (array_key_exists($course->category, $cc->sub_ids)) {
                                if ($displayed == 0) {
                                    $displayed = 1;
                                    $depth = $cc->depth - 1;

                                    if ($last_course_depth >= $cc->depth) {
                                        do {
                                            $last_course_depth -= 1;
                                        } while ($last_course_depth != ($cc->depth - 1));
                                    }
                                    $out .= '<p style="font-weight:bold;padding:5px;color:white;background-color:#AAAAAA;width:80%">'.$cc->name.'</p><ul>';
                                    $last_course_depth = $cc->depth;
                                }
                            }

                            if ($course->category == $cc->id) {
                                $query = "SELECT u.id, u.firstname as ufirstname, u.lastname as ulastname, u.email, c.fullname
                                          FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c
                                          WHERE u.id = r.userid
                                          AND r.contextid = cx.id
                                          AND cx.instanceid = c.id
                                          AND r.roleid =3
                                          AND cx.contextlevel =50
                                          AND c.id = ".$course->id;

                                $enseignants = $DB->get_records_sql($query);

                                $url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
                                $out .= '<li class="course"><strong><a href="'.$url.'" title="'.$course->shortname.'">'.$course->fullname.'</a></strong>';
                                $out .= '&nbsp;&nbsp;&nbsp;';
								// Si l'utilisateur est enseignant dans ce cours, il peut le copier.
								$cantransfer = false;
								foreach ($enseignants as $enseignant) {
									if ($enseignant->id == $USER->id) {
										$cantransfer = true;
									}
								}
								reset($enseignants);
								if ($cantransfer) {
									// Mais peut-être que ce cours est déjà copié																		
									$newcourse = $DB->get_record('course', array('idnumber' => "Y2017-$course->idnumber"));
									if ($course->category == $CFG->catbrouillonsid) {
									} else if ($course->idnumber == '') {
										$out .= "<span style='color:red'>Ce cours ne peut pas être copié car il n'a pas été créé normalement et n'a pas de numéro d'identification.</span>";
									} else if ($newcourse) {									
										$out .= "<img src='$CFG->wwwroot/yes.png'> Déjà copié";
									} else {
										$out .= "<a href= 'copycourse.php?src=$course->id' style='color:#731472'>";
                                        $out .= "<img src='$CFG->wwwroot/pix/i/restore.png' alt='icone restauration'>";
                                        $out .= "&nbsp;";
                                        $out .= "Copier";
                                        $out .= "</a>";
									}                                    
							    }

                                $out .= '<br/>'.$course->summary.'<br/>';

                                $countt = 0; $sss = ""; $chainetotale = "";
                                foreach ($enseignants as $enseignant) {
									$urluser = $CFG->wwwroot.'/user/view.php?id='.$enseignant->id.'&course='.$course->id;
                                    if($countt > 0) {
										$chainetotale .= " - ";
									}
                                    $chainetotale .=  '<a href="'.$urluser.'">'.$enseignant->ufirstname." ".$enseignant->ulastname.'</a>';
                                    $countt++;
                                }

                                if ($countt>1) $sss = "s";
                                $out .= '<strong>Enseignant'.$sss.'</strong> :'.$chainetotale;
                                $out .= '</li><br/>';
                            }
                        }
                        $out .= "</ul>";
                    }
                }
            }
            
            if (isset($out)) {
				echo $out;
			}
    		
    		?>
        </div>
    </div>
    

<!-- BLOC DEMANDES D'INSCRIPTION AUX COURS (BRICE) -->
<!--<div class="block">
    <div style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">Mes demandes d'inscription</div>
    <div class="content" style="width:100%">
        <?php //BRICE
        //L'utilisateur a-t-il envoyé ou reçu des demandes d'inscription
        $sql = "SELECT COUNT(id) AS nb FROM mdl_asked_enrolments WHERE studentid = $USER->id AND answererid = 0";
        $nbsentasks = $DB->get_record_sql($sql)->nb;
        if ($nbsentasks > 1) {
            $s = "s";
            $article = "des";
        } else {
            $s = "";
            $article = "un";
        }
        echo "Vous avez envoyé <a href='$CFG->wwwroot/enrol/demandes.php' style='color:#731472;font-weight:bold'><span style='color:brown;font-weight:bold'>$nbsentasks</span> demande$s d'inscription</a> à $article cours. ";
        echo "&nbsp;&nbsp;<a href='$CFG->wwwroot/course/index.php' style='color:#731472;font-weight:bold'>Ajouter une demande +</a><br><br>";

        if ($isteacher) {
            $sql = "SELECT COUNT(ae.id) AS nb "
                 . "FROM mdl_asked_enrolments ae, mdl_context x, mdl_role_assignments ra "
                 . "WHERE ra.userid = $USER->id AND ra.roleid = 3 "
                 . "AND ra.contextid = x.id  AND x.contextlevel = 50 AND x.instanceid = ae.courseid "
                 . "AND ae.answererid = 0";

            $nbreceivedasks = $DB->get_record_sql($sql)->nb;
            if ($nbreceivedasks > 1) {
                $s = "s";
                $article = "des";
            } else {
                $s = "";
                $article = "un";
            }
            echo "<a href='$CFG->wwwroot/enrol/demandes.php' style='color:#731472;font-weight:bold'><span style='color:red;font-weight:bold'>$nbreceivedasks</span> demande$s d'inscription</a> reçue$s.<br><br>";
            //echo "Vous avez reçu <a href='$CFG->wwwroot/enrol/demandes.php' style='color:#731472;font-weight:bold'><span style='color:red;font-weight:bold'>$nbreceivedasks</span> demande$s d'inscription</a> à $article cours.<br><br>";
        }
        ?>
   </div>
</div>-->

<?php // ON AFFICHE LE BLOC UNIQUEMENT POUR LES PROFS
if ($isteacher) {
?>
        

    <!-- BLOC COURS DISPOS -->
    <div class="block">
    <div onclick="flipflop('createcourse');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">
        Créer un cours ou un espace collaboratif
        <img src ="Images/sort.png" style="float: right "  height="25" width="25">
    </div>
    <div id = "createcourse" 
        class="content"       
        style='width:100%;display:<?php if ($openblock == 'createcourse') echo "block"; else echo "none"; ?>'
    >
	<h3>Cours disponibles à la création</h3>
		<p>Ces cours sont correctement renseignés dans Apogée et Celcat :</p>
        <div id="emoticone2" style='min-height : 200px;display:block;'>
            <script> affiche('createcoursedispo2017.php','emoticone2');</script>
            <script>
            var opts = {
              lines: 13, // The number of lines to draw
              length: 15, // The length of each line
              width: 10, // The line thickness
              radius: 25, // The radius of the inner circle
              corners: 1, // Corner roundness (0..1)
              rotate: 0, // The rotation offset
              direction: 1, // 1: clockwise, -1: counterclockwise
              color: '#731472', // #rgb or #rrggbb or array of colors
              speed: 1, // Rounds per second
              trail: 60, // Afterglow percentage
              shadow: false, // Whether to render a shadow
              hwaccel: false, // Whether to use hardware acceleration
              className: 'spinner', // The CSS class to assign to the spinner
              zIndex: 2e9, // The z-index (defaults to 2000000000)
              top: 'auto', // Top position relative to parent in px
              left: 'auto' // Left position relative to parent in px
            };
            var target = document.getElementById('emoticone2');
            var spinner = new Spinner(opts).spin(target);

            function ayaya (){
                alert("coucou");
            }
            </script>
        </div>
<!--
     </div>
    </div>
-->

    <!-- BLOC DEMANDE DE COURS -->
<!--
    <div class="block">
    <div onclick="flipflop('demandecourse');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">
        Demander la création d'un cours
        <img src ="Images/sort.png" style="float: right "  height="25" width="25">
    </div>
    <div id="demandecourse"
         class="content"
         style='width:100%;display:<?php if(isset($_POST['envoidemande'])) echo "block"; else echo "none"; ?>'
         >
-->
<h3>Créer un espace collaboratif</h3>
<p style='text-align:center'>
<form method="post" name="askcollabspace" id="askcollabspace" action="index.php" style="text-align:center"> 	
	Titre : <input type='text' name='collab' max-length='70' size='40' value='Espace collaboratif de <?php echo "$USER->firstname $USER->lastname"; ?>'> 
	<input type='submit' value='Créer'>
</form>
</p>
<h3>Créer manuellement un cours</h3>
<p>Si le cours que vous souhaitez créer n'apparaît pas dans la liste ci-dessus, vous pouvez demander sa création à l'aide de ce formulaire :</p>
    <?php
    if (isset($_POST['genre'])) {
        $ufrnum = $_POST['genre'];
        $ufrnum = substr($ufrnum, -1);
    }
    $codesareok = 1;
    //S'il faut créer une nouvelle VET
    if (isset($_POST['vet'])) {
        if ($_POST['vet'] == "888888") { //Autre
            //On lit le compteur 'nextvet' puis on l'incrémente
            $nextvet = system("cat vetcompteur");
            $command = "echo '".($nextvet+1)."' > vetcompteur";
            system($command);
            $codevet = $ufrnum."2017"."V".$nextvet;

            if ((!isset($_POST['titreautrevet']))||($_POST['titreautrevet'] == "")) {
                if (isset($_POST['envoidemande'])) {
                    echo "<fieldset style='padding : 10px; width: 98%;font-weight : bold; background-color:red; color:white;''>
                        Erreur : vous n'avez pas indiqué le titre de la VET.
                      </fieldset>";
                }
                $codesareok = 0;
            }
        } else {
            $codevet = $_POST['vet'];
            unset($_POST['titreautrevet']);
        }
    }

    if (isset($_POST['elp'])) {
        if (($_POST['elp'] == "999999")||($_POST['elp'] == "-1")) { //Autres
            //On lit le compteur 'nextelp' puis on l'incrémente
            echo "<span style='display:none'>";
            $nextelp = system("cat elpcompteur");
            echo "</span>";
            $command = "echo '".($nextelp+1)."' > elpcompteur";
            system($command);

            $codesvetelp = $codevet."-".$ufrnum."2017"."E".$nextelp;
            if ((!isset($_POST['titreautrecours']))||($_POST['titreautrecours'] == '')) {
                if (isset($_POST['envoidemande'])) {
                    echo "<fieldset style='padding : 10px; width: 98%;font-weight : bold; background-color:red; color:white;''>
                        Erreur : vous n'avez pas indiqué le titre du cours.
                      </fieldset>";
                }
                $codesareok = 0;
            }
        } else {
            $codesvetelp = $_POST['elp'];
            unset($_POST['titreautrecours']);
        }
    }

    if(isset($_POST['envoidemande'])&&($codesareok == 1)){
        //Si le cours existe déjà
        $sql = "SELECT id, fullname, idnumber FROM mdl_course WHERE idnumber = 'Y2017-$codesvetelp' OR idnumber LIKE 'Y2017-$codesvetelp+%' ORDER BY idnumber";
        $previouscourses = $DB->get_recordset_sql($sql);

        if (($previouscourses->valid())&&(!isset($_POST['lastusedindex']))) {
            echo "<h3 style='color:red'>Le cours $codesvetelp existe déjà : </h3>";
            echo "<span style='font-weight:bold'>1ère possibilité :</span> Cliquez sur le cours existant pour savoir à quel collègue il appartient et lui adresser éventuellement une demande d'inscription.<br><br>";
            echo "<ul>";
            foreach($previouscourses as $previouscourse) {
                echo "<li><a href='$CFG->wwwroot/course/view.php?id=$previouscourse->id' style='color:#731472' target='_blanck'>$previouscourse->fullname</a></li>";
            }
            echo "</ul><br>";
            $lastusedindexarray = explode('+', $previouscourse->idnumber);
            if (isset($lastusedindexarray[1])) {
                $lastusedindex = $lastusedindexarray[1];
            } else {
                $lastusedindex = 0;
            }
            ?>
        <span style='font-weight:bold'>2nde possibilité :</span> Vous pouvez créer un autre cours. Merci d'ajouter un suffixe à son titre pour le distinguer de celui qui existe déjà. A défaut, votre nom sera utilisé comme suffixe.<br><br>
            <form method='post' action='index.php'>
                Suffixe : <input type='text' name='suffixe'/><br>
                <input type='hidden' name='genre' value='<?php echo $_POST['genre']; ?>'/>
                <input type='hidden' name='vet' value='<?php echo $_POST['vet']; ?>'/>
                <input type='hidden' name='elp' value='<?php echo $_POST['elp']; ?>'/>
                <input type='hidden' name='titreautrevet' value="<?php echo $_POST['titreautrevet']; ?>"/>
                <input type='hidden' name='titreautrecours' value="<?php echo $_POST['titreautrecours']; ?>"/>
                <input type='hidden' name='envoidemande' value='<?php echo $_POST['envoidemande']; ?>'/>
                <input type='hidden' name='titrelongcache' value="<?php echo $_POST['titrelongcache']; ?>"/>
                <input type='hidden' name='niveau' value='<?php echo $_POST['niveau']; ?>'/>
                <input type='hidden' name='brouillonid' value='<?php echo $_POST['brouillonid']; ?>'/>
                <input type='hidden' name='descdemande' value="<?php echo $_POST['descdemande']; ?>"/>
                <input type='hidden' name='format' value="<?php echo $_POST['format']; ?>"/>
                <input type='hidden' name='lastusedindex' value='<?php echo $lastusedindex; ?>'/>
                Attention, il n'y aura pas d'inscription automatique d'étudiants dans ce cours supplémentaire. A vous de les inscrire ou de leur donner une clé pour s'inscrire eux-mêmes.<br><br>
                <input type='submit' name='confirm' value='Créer un autre cours' />
                &nbsp; &nbsp;
                <a href='index.php' style='color:#731472'>Annuler</a>
            </form>
            <?php
            echo "";
        } else {
            $originalcodesvetelp = $codesvetelp;
            if (isset($_POST['lastusedindex'])) {
                $codesvetelp .= "+".($_POST['lastusedindex'] + 1);
            }

            // Plusieurs destinataires
            $to  = $USER->email.',cosette.abi-dib@u-cergy.fr,hoang-yen.tran-kiem@u-cergy.fr,julia.kopcinska@u-cergy.fr,caroline.pers@u-cergy.fr'; // notez la virgule

            // Sujet
            $subject = 'Creation de cours - Plateforme Pedagogique';

            // Message
            $message = "
                <html>
                <head>
                   <title>Creation de cours - Plateforme Pédagogique</title>
                </head>
                <body>
                  <p>Un cours vient d'être créé sur la Plateforme pédagogique 2017-2018 par ".$USER->firstname." ".$USER->lastname." (".$USER->email.") </p>
                ".$_POST['titrelongcache']."<br/><br/>
                Code du cours :
                UFR :  ".$_POST['genre']."<br/>
                CURSUS :  ".$_POST['niveau']."<br/>
                VET-ELP : $codesvetelp<br/>";
            if (isset($_POST['titreautrecours'])) {
                $message .= "Autre titre du cours (optionnel - si non trouvé dans la liste) : ".$_POST['titreautrecours']."<br/>";
            }
            if (isset($_POST['titreautrevet'])) {
                $message .= "Autre nom de VET (optionnel - si non trouvé dans la liste) : ".$_POST['titreautrevet']."<br/>";
            }
            $message .= "<br/>";

            //~ if ($_POST['brouillonid']) {
                //~ $message .= "Brouillon utilisé : ".$CFG->wwwroot."/course/view.php?id=".$_POST['brouillonid']."<br>";
            //~ } else {
                //~ $message .= "Format de cours souhaité : ".$_POST['format']."<br/>";
            //~ }

            if (isset($_POST['descdemande'])) {
                $message .= "Données supplémentaires fournies par l'enseignant : ".$_POST['descdemande'];
            }
            $message .= "<br><h3>CoursUCP, votre plateforme pédagogique.</h3>Ceci est un message automatique. Merci de ne pas y répondre. ";
	    $message .= "Pour toute demande ou information, nous vous invitons à <a href='https://monucp.u-cergy.fr/uPortal/f/u312l1s6/p/Assistance.u312l1n252/max/render.uP?pCp'>Effectuer une demande</a> dans la catégorie <strong>SEFIAP -> Applications pédagogiques</strong>.";
            $message .= "</body></html>";
            // En-t�tes additionnels
            $from = "noreply@cours.u-cergy.fr";
            /* $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Reply-To: '.$from.'\n';
            $headers .= 'From: "[CoursUCP]"<'.$from.'>'."\n";
            $headers .= 'Delivered-to: '.$to."\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
            $headers .= 'Bcc: salma.el-mrabah@u-cergy.fr,brice.errandonea@u-cergy.fr,noa.randriamalaka@u-cergy.fr' . "\r\n";
 */
   $headers = 'From: noreply@cours.u-cergy.fr' . "\r\n" .'MIME-Version: 1.0' . "\r\n".
     'Reply-To: noreply@cours.u-cergy.fr' . "\r\n" .'Content-type: text/html; charset=utf-8' . "\r\n".
     'X-Mailer: PHP/' . phpversion();
            // Envoi

            mail($to, $subject, $message, $headers);

            $askedcourse = new stdClass();
            $askedcourse->askerid = $USER->id;
            //$askedcourse->askedat = 'NOW()';
            $askedcourse->ufr = $_POST['genre'];
            $askedcourse->level = $_POST['niveau'];
            $askedcourse->vetnum = $codevet;
            if (isset($_POST['titreautrevet'])) {
                $askedcourse->vettitle = addslashes($_POST['titreautrevet']);                
		    } else {
				$vet = $DB->get_record('cat_demandecours', array('code' => $codevet));
				$askedcourse->vettitle = addslashes($vet->name);
			}
		    $askedcourse->elpnum = $codesvetelp;
		    //~ if (!isset($_POST['suffixe'])) {
				//~ $_POST['suffixe'] = '';
			//~ }
            if ($_POST['suffixe']) {
                $askedcourse->suffixe = $_POST['suffixe'];
            } else if (isset($_POST['confirm'])) {
                global $USER;
                $askedcourse->suffixe = "$USER->firstname $USER->lastname";
            }
            if (isset($_POST['titreautrecours'])) {
                $askedcourse->elptitle = addslashes($_POST['titreautrecours']);
            } else {
                $sql = "SELECT name FROM mdl_cat_demandecours WHERE code = '$originalcodesvetelp'";
                $elptitle = stripslashes($DB->get_record_sql($sql)->name);
                if ($askedcourse->suffixe) {
                    $elptitle .= " - ".stripslashes($askedcourse->suffixe);
                }
                $askedcourse->elptitle = addslashes($elptitle);
            }
            $askedcourse->format = 'topics';//$_POST['format'];
            //~ if (!isset($_POST['descdemande'])) {
				//~ $_POST['descdemande'] = '';
			//~ }
            $askedcourse->description = addslashes(str_replace("\r\n", " ", $_POST['descdemande']));
            $askedcourse->brouillonid = 0; //$_POST['brouillonid'];
            $askedcourse->answer = 'Oui';
            $askedcourseid = $DB->insert_record('asked_courses', $askedcourse);

            $sql = "UPDATE mdl_asked_courses SET askedat = NOW() WHERE id = $askedcourseid";
            $DB->execute($sql);

            createvetcourse($askedcourseid);
        }
    } else {
    ?>

    <script type='text/javascript'>
            var xhr = null;
            nomcomplet = "Titre du cours";

            function getXhr(){
                if(window.XMLHttpRequest) // Firefox et autres
                   xhr = new XMLHttpRequest();
                else if(window.ActiveXObject){ // Internet Explorer
                   try {
                            xhr = new ActiveXObject("Msxml2.XMLHTTP");
                        } catch (e) {
                            xhr = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                }
                else { // XMLHttpRequest non support� par le navigateur
                   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
                   xhr = false;
                }
            }

            /**
            * M�thode qui sera appel�e sur le click du bouton
            */
            function go(){
                getXhr();
                // On d�fini ce qu'on va faire quand on aura la r�ponse
                xhr.onreadystatechange = function(){
                    // On ne fait quelque chose que si on a tout re�u et que le serveur est ok
                    if(xhr.readyState == 4 && xhr.status == 200){
                        leselect = xhr.responseText;
                        // On se sert de innerHTML pour rajouter les options a la liste
                        document.getElementById('niveau').innerHTML = leselect;
                        document.getElementById('niveau').disabled=false;
                    }
                }

                // Ici on va voir comment faire du post
                xhr.open("POST","getvet.php",true);
                // ne pas oublier �a pour le post
                xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                // ne pas oublier de poster les arguments
                // ici, l'id de l'auteur
                sel = document.getElementById('genre');
                idgenre = sel.options[sel.selectedIndex].value;
                xhr.send("idGenre="+idgenre);
            }

            function goesp(){
                getXhr();
                // On d�fini ce qu'on va faire quand on aura la r�ponse
                xhr.onreadystatechange = function(){
                    // On ne fait quelque chose que si on a tout re�u et que le serveur est ok
                    if(xhr.readyState == 4 && xhr.status == 200){
                        leselect = xhr.responseText;
                        // On se sert de innerHTML pour rajouter les options a la liste
                        document.getElementById('vet').innerHTML = leselect;
                          document.getElementById('vet').disabled=false;
                    }
                }

                // Ici on va voir comment faire du post
                xhr.open("POST","getelp.php",true);
                // ne pas oublier �a pour le post
                xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                // ne pas oublier de poster les arguments
                // ici, l'id de l'auteur
                sel = document.getElementById('niveau');                
                autrevet = document.getElementById('autrevet');
                autredisplay = autrevet.style.display;
                idesp = sel.options[sel.selectedIndex].value;
                if (autredisplay == 'block') {
					if (idesp == -1) {
						buttondisplay = 'none';
					} else {
						buttondisplay = 'block';
					}
					validbutton = document.getElementById('envoidemande');
					validbutton.style.display = buttondisplay;
				}                
                xhr.send("idEsp="+idesp);
            }

            function goesp2(){
                getXhr();
                // On d�fini ce qu'on va faire quand on aura la r�ponse
                xhr.onreadystatechange = function(){
                    // On ne fait quelque chose que si on a tout re�u et que le serveur est ok
                    if(xhr.readyState == 4 && xhr.status == 200){
                        leselect = xhr.responseText;
                        // On se sert de innerHTML pour rajouter les options a la liste
                        document.getElementById('elp').innerHTML = leselect;
                        document.getElementById('elp').disabled=false;
                    }
                }

                // Ici on va voir comment faire du post
                xhr.open("POST","getfinal.php",true);
                // ne pas oublier �a pour le post
                xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                // ne pas oublier de poster les arguments
                // ici, l'id de l'auteur
                sel = document.getElementById('vet');
                idesp = sel.options[sel.selectedIndex].value;
                xhr.send("idEsp="+idesp);
            }

            function affichebuttonsub(){
                document.getElementById("autrecours").style.display="block";
                document.getElementById("autrevet").style.display="block";
                document.getElementById('envoidemande').style.display="block";
                document.getElementById('descdemande').style.display="block";
                document.getElementById('pdescdemande').style.display="block";
            }

            function recupertitre() {
                titrelong = document.getElementById('genre').options[document.getElementById('genre').selectedIndex].text+" / "+
                document.getElementById('niveau').options[document.getElementById('niveau').selectedIndex].text+" / "+
                    document.getElementById('vet').options[document.getElementById('vet').selectedIndex].text+" / "+
                    document.getElementById('elp').options[document.getElementById('elp').selectedIndex].text;
                document.getElementById('titrelongcache').value = titrelong;
            }
    </script>

    <style>
    label {
        min-width : 120px;
        font-weight:bold;
        display:block;
        float:left;
    }
    select:disabled {
        background-color : #b5b5b5;
    }
    </style>


    <form method="post" name="subaskcours" id="subaskcours" action="#subaskcours">
	<input type='hidden' name='open' value='createcourse'>
    <table>
    <tr><td width="340px">
    <label>Composante</label></td><td>
    <select name='genre' id='genre' onchange='go()' style='width:300px'>
        <option value='-1'>Choisissez votre composante</option>
        <?php
        $query = "SELECT * FROM mdl_cat_demandecours WHERE parent_id is null AND code NOT LIKE 'Y2017-%' ORDER BY code ASC";
        $enseignants = $DB->get_recordset_sql($query);
        foreach ($enseignants as $row) {
            echo "<option value='".$row->code."'>".$row->name."</option>";
        }
        ?>
    </select>
    </td></tr>
    <tr><td width="340px">
    <label>Niveau</label></td><td>
    <select name='niveau'  id='niveau' onchange='goesp()' style='width:300px' disabled>
        <option value='-1'>Choisissez votre niveau</option>
    </select>
    </td></tr>
    <tr><td width="340px">
    <label>VET (Etape d'un diplôme)</label></td><td>
    <select name='vet'  id='vet' onchange='goesp2()' style='width:300px' disabled>
        <option value='-1'>Choisissez votre VET</option>
    </select>
    <tr><td width="340px">
    <label>ELP</label></td><td>
    <select name='elp' id="elp" onclick='affichebuttonsub()' style='width:300px' disabled>
        <option value='-1'>Choisissez votre ELP</option>
    </select>
    </td></tr>
    </table>
    
    <p id="autrecours" style="display:none;"><strong>Si vous n'avez pas trouvé le cours que vous souhaitez créer...<br/>
    Merci de renseigner le titre du cours désiré : </strong><input id="titreautrecours" name="titreautrecours" type="text" size="60" />
    <p id="autrevet" style="display:none;"><strong>Si vous n'avez pas trouvé la VET non plus...<br/>
    Merci de renseigner le nom de la VET désirée : </strong><input id="titreautrevet" name="titreautrevet" type="text" size="60" />
<!--
    <p id="pdescdemande" style="display:none" name="pdescdemande"><strong>Description de votre cours</strong></p>
    <textarea id="descdemande" name="descdemande" style="display:none;width:500px;height:120px;"></textarea>

    <table>
    <tr>
    	<td> </td><td> </td>
    </tr>
    <tr>
    	<td> </td><td> </td>
    </tr>
    <tr>
        <td width="340px">
                <label>Format de cours </label>
            </td>
            <td>
                <select name='format'  id='format' style='width:300px'>
                    <option value='Aucun format choisi'>Choisissez votre format</option>
                    <option value='Une section par chapitre'>Une section par chapitre</option>
                    <option value='Une section par semaine'>Une section par semaine</option>
                    <option value='Je ne sais pas'>Je ne sais pas</option>
                </select>
            </td>
		</tr>
		<tr>
                    <td>&nbsp; ou alors &nbsp;</td><td></td>
		</tr>
		<tr>
            <td width="340px">
                <label>Utilisez un de vos brouillons </label>
            </td>
            <td>
                <select name='brouillonid'  id='brouillon' style='width:300px'>
                    <option value='0'>Aucun brouillon - Créer un cours vierge.</option>
                    <?php
                        $sql = "SELECT c.id, c.fullname "
                                . "FROM mdl_course c, mdl_context x, mdl_role_assignments ra "
                                . "WHERE c.category = $CFG->catbrouillonsid "
                                . "AND x.contextlevel = 50 AND x.instanceid = c.id "
                                . "AND ra.contextid = x.id AND ra.roleid = 3 "
                                . "AND ra.userid = $USER->id";
                        $brouillons = $DB->get_recordset_sql($sql);

                        foreach ($brouillons as $brouillon) {
                            echo "<option value='$brouillon->id'>$brouillon->fullname</option>";
                        }
                    ?>
                </select>
            </td>
        </tr>
    </table>
-->
    <input type="hidden" id="titrelongcache" name="titrelongcache" value=""/>
    <input onmouseover="recupertitre()" style="display:none" id="envoidemande" type="submit" name="envoidemande" value="Créer le cours" />
    </p>
        </form>
    <?php } ?>

<!--
<h3>Créer un brouillon</h3>
            <p style = 'font-weight:bold;text-align:center'>
            Un brouillon est un cours que les étudiants ne peuvent pas voir, même si vous les y inscrivez.<br>Vous pouvez créer des brouillons librement mais n'en abusez pas.
            </p>
            <p style = 'font-weight:bold;text-align:center'>
                <a href='course/edit.php?category=<?php echo $CFG->catbrouillonsid; ?>&returnto=category'>
                    <button>Créer un brouillon de cours</button>
                </a>
            </p>
-->
        </div>
    </div>

<?php
} else {
?>

    <!-- BLOC DES COURS DE LA VET DE CET ETUDIANT -->
  <!--  <div class="block">
        <div onclick="flipflop('vetcourses');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">Autres cours de ma promotion<img src ="Images/sort.png" style="float: right "  height="25" width="25"></div>
	    <div id="vetcourses" class="content" style='width:100%;display:none'>
	        <div id="interessants" style='min-height : 200px;display:block;text-align:justify'>
    		Les cours ci-dessous concernent la promotion - ou VET - dont vous faites partie. Vous n'êtes pas inscrit(e) à ces cours. C'est peut-être normal (vous n'avez peut-être pas ces options). Mais si vous pensez que vous devriez être inscrit(e) à l'un de ces cours, cliquez dessus.<br><br>
    		<?php
		//add salma
    		//On cherche la (ou les) VET de l'étudiant
                $studentvets = $DB->get_records('student_vet', array('studentid' => $USER->id));

    		//On cherche les cours de la (ou des) VET à laquelle il est inscrit.
                foreach ($studentvets as $studentvet) {
                   $sql = "select distinct c.fullname, c.id from mdl_course c , mdl_course_categories m where c.category = $studentvet->categoryid and c.fullname not in (
                                SELECT distinct c.fullname
                                FROM mdl_course c, mdl_user u, mdl_user_enrolments ue,  mdl_enrol e, mdl_course_categories m
                                WHERE c.id = e.courseid and e.id = ue.enrolid and u.id = ue.userid and c.category = $studentvet->categoryid and ue.userid=$USER->id AND m.idnumber LIKE 'Y2017-%'
                            )";
                    $autrescours = $DB->get_recordset_sql($sql);
                    $displayvet = 1;

                    foreach ($autrescours as $ac) {
                            $out ="";
                             //$url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
                   //  $out .= '<li class="course"><strong><a href="'.$url.'" title="'.$course->shortname.'">'.$course->fullname.'</a></strong><br/>'.$course->summary.'<br/>';
                            if ($displayvet) {
                                $vetname = $DB->get_field('course_categories', 'name', array('id' => $studentvet->categoryid));
                                echo "<p style='font-weight:bold;padding:5px;color:white;background-color:#AAAAAA;width:80%'>VET $vetname</p>";
                                echo "<ul style='color:#888888'>";
                                $displayvet = 0;
                            }
                            echo "<li class='course' style= 'color =black'><strong><a href='enrol/index.php?id=$ac->id'>$ac->fullname</a></strong></li>";

                            //enseignant des cours
                            $query = "SELECT u.firstname as ufirstname, u.lastname as ulastname, u.email, c.fullname
                                      FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c
                                      WHERE u.id = r.userid
                                      AND r.contextid = cx.id
                                        AND cx.instanceid = c.id
                                        AND r.roleid =3
                                        AND cx.contextlevel =50
                                        AND c.id = ".$ac->id;

                             $enseignantscours = $DB->get_recordset_sql($query);
                              $countt = 0; $sss = ""; $chainetotale = "";
                              //echo "$enseignantscours";
                             foreach ($enseignantscours as $en)
                             {
                                    //echo "$chainetotale";
                                     if($countt > 0) $chainetotale .= " - ";
                                       $chainetotale .=  $en->ufirstname." ".$en->ulastname;
                           $countt++;

                             }
                       if ($countt>1) {
                            $sss = "s";
                            $out .= '<strong>Enseignant'.$sss.'</strong> : '.$chainetotale;
                            echo "$out<br>";
                            //$out2 .= '</li>';
                            echo "</li><br>";
                       } else {
                            $out .= '<strong>Enseignant</strong> : '.$chainetotale;
                            echo "$out<br>";
                           //$out2 .= '</li>';
                            echo "</li><br>";
                       }
                    }
                    echo "</ul>";
                }
    		?>
    		</div>
	    </div>
    </div> -->

    <!-- BLOC POUR LES ETUDIANTS -->
    <div class="block">
        <div onclick="flipflop('whattodo');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">Je ne vois pas mon cours. Que faire ?<img src ="Images/sort.png" style="float: right "  height="25" width="25"></div>
	    <div id="whattodo" class="content" style='width:100%;display:none'>
			Les cours ci-dessous concernent la promotion - ou VET - dont vous faites partie. Vous n'êtes pas inscrit(e) à ces cours. C'est peut-être normal (vous n'avez peut-être pas ces options). Mais si vous pensez que vous devriez être inscrit(e) à l'un de ces cours, cliquez dessus.<br><br>
	        <?php
		//add salma
    		//On cherche la (ou les) VET de l'étudiant
                $studentvets = $DB->get_records('student_vet', array('studentid' => $USER->id));

    		//On cherche les cours de la (ou des) VET à laquelle il est inscrit.
                foreach ($studentvets as $studentvet) {
                   $sql = "SELECT distinct c.fullname, c.id, m.idnumber
                           FROM mdl_course c , mdl_course_categories m
                           WHERE c.category = $studentvet->categoryid
                           AND c.category = m.id
                           AND c.id NOT IN (
                                SELECT DISTINCT e.courseid
                                FROM mdl_user_enrolments ue, mdl_enrol e
                                WHERE e.id = ue.enrolid and ue.userid = $USER->id
                            )
                            AND m.idnumber LIKE 'Y2017-%'";
                    $autrescours = $DB->get_recordset_sql($sql);
                    $displayvet = 1;

                    foreach ($autrescours as $ac) {
                            $out ="";
                             //$url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
                   //  $out .= '<li class="course"><strong><a href="'.$url.'" title="'.$course->shortname.'">'.$course->fullname.'</a></strong><br/>'.$course->summary.'<br/>';
                            if ($displayvet) {
                                $vetname = $DB->get_field('course_categories', 'name', array('id' => $studentvet->categoryid));
                                echo "<p style='font-weight:bold;padding:5px;color:white;background-color:#AAAAAA;width:80%'>VET $vetname</p>";
                                echo "<ul style='color:#888888'>";
                                $displayvet = 0;
                            }
                            echo "<li class='course' style= 'color =black'><strong><a href='enrol/index.php?id=$ac->id'>$ac->fullname</a></strong></li>";

                            //enseignant des cours
                            $query = "SELECT u.firstname as ufirstname, u.lastname as ulastname, u.email, c.fullname
                                      FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c
                                      WHERE u.id = r.userid
                                      AND r.contextid = cx.id
                                        AND cx.instanceid = c.id
                                        AND r.roleid =3
                                        AND cx.contextlevel =50
                                        AND c.id = ".$ac->id;

                             $enseignantscours = $DB->get_recordset_sql($query);
                              $countt = 0; $sss = ""; $chainetotale = "";
                              //echo "$enseignantscours";
                             foreach ($enseignantscours as $en)
                             {
                                    //echo "$chainetotale";
                                     if($countt > 0) $chainetotale .= " - ";
                                       $chainetotale .=  $en->ufirstname." ".$en->ulastname;
                           $countt++;

                             }
                       if ($countt>1) {
                            $sss = "s";
                            $out .= '<strong>Enseignant'.$sss.'</strong> : '.$chainetotale;
                            echo "$out<br>";
                            //$out2 .= '</li>';
                            echo "</li><br>";
                       } else {
                            $out .= '<strong>Enseignant</strong> : '.$chainetotale;
                            echo "$out<br>";
                           //$out2 .= '</li>';
                            echo "</li><br>";
                       }
                    }
                    echo "</ul>";
                }
    		?>
	        
	        
	        <div id="pouretudiants" style='min-height : 200px;text-align:justify'>
                    <?php
                    echo "Si le cours que vous cherchez n'apparait pas ici non plus, cherchez-le dans la liste complète (Tous les cours par composante) en bas de cette page. Si vous le trouvez, vous pourrez peut-être vous y inscrire, avec ou sans mot de passe.
                          Sinon, vous pourrez déposer une demande d'inscription à ce cours, demande qu'un enseignant acceptera ou rejettera.";
		            echo "<br><br>";		    
                    ?>	            
	        </div>
	    </div>
    </div>

    <!-- BLOC ESPACE PERSONNEL -->
    <div class="block">
        <div onclick="flipflop('personalspace');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">
			Créer un espace personnel
			<img src ="Images/sort.png" style="float: right "  height="25" width="25">
		</div>
	    <div id="personalspace" class="content" style='width:100%;display:none'>
	        <div id="personnel" style='text-align:center'>
				
				<?php
				$persospace = $DB->get_record('course', array('idnumber' => "PERSO-$USER->id"));
				if ($persospace) {
					?>
					Vous avez déjà un espace personnel.<br>
					<a style='font-weight:bold;color:#731472' href='course/view.php?id=<?php echo $persospace->id ?>'>Accès à votre espace personnel</a>
					<?php
				} else {
				    ?>				    
                    <!--<button onclick="alert('Cette fonctionnalité sera prête avant le mois de septembre 2017. Merci de réessayer un peu plus tard.')"> -->
                    <a href='index.php?perso=1'>
                    <button>
                    Créer un espace personnel
                    </button>
                    </a>
                    <!--</button> -->
                    <?php
				} ?>				
				
			</div>
		</div>
	</div>		


<?php
}
//~ if ($isteacher) {
?>
    
    
    <!-- BLOC ANCIENNES PLATEFORMES -->
<!--
    <div class="block">
        <div onclick="flipflop('heade');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">Mes anciens cours (2016-2017)<img src ="Images/sort.png" style="float: right "  height="25" width="25"></div>
    		<div id ="heade" class="content" style='width:100%;display:none'>
    		<?php
    		    //Ajout des cours ENP15
                $sqlcateg = "SELECT DISTINCT categoryname FROM ".$CFG->prefix."oldcourses WHERE username = '$USER->username'";
                $oldcategories = $DB->get_records_sql($sqlcateg);
                if(!$oldcategories) {
                    echo "Vous n'aviez pas de cours sur cette plateforme l'an dernier.";
                }
                foreach ($oldcategories as $oldcategory) {
                    echo "<p style='font-weight:bold;padding:5px;color:white;background-color:#AAAAAA;width:80%'>$oldcategory->categoryname</p>";
                    $params = array('categoryname' => $oldcategory->categoryname, 'username' => $USER->username);
                    $oldcourses = $DB->get_records('oldcourses', $params);
                    echo "<ul>";
                    foreach ($oldcourses as $oldcourse) {
                            if ($oldcourse->courseidnumber == '') {
                                continue;
                            }
                            //Ce cours a-t-il déjà été créé sur ENP16 ?
                            $sql = "SELECT id, transfered FROM mdl_course WHERE idnumber = '$oldcourse->courseidnumber'";
                            $stub = $DB->get_record_sql($sql);
                            if (!is_object($stub)) {
                                $stub = new stdClass();
                                $stub->id = 0;
                            }

                            if ($stub->id == 0) {
                                $stub->id = 1;
                            }

                            $paramid = $stub->id * 100000 + $oldcourse->oldcourseid;
                            echo "<li class='course'><strong>";
                            echo "<a href = 'course/view.php?id=$paramid'>$oldcourse->coursename</a></strong>";
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                            echo "<a href= 'ebauches.php?id=$paramid' style='color:#731472'>";
                            echo "<img src='$CFG->wwwroot/pix/i/restore.png' alt='icone restauration'>";
                            echo "&nbsp;";
                            echo "Dupliquer";
                            echo "</a>";
                            echo "&nbsp;";
                            if (isset($stub->transfered)) {
                                if ($stub->transfered == 2015) {
                                    echo "<img src='$CFG->wwwroot/yes.png'> Déjà transféré";
                                }
                            }
                            echo "</li>";
                        }
                        echo "</ul>";
                }
    		?>
        </div>
    </div>
-->
    
<?php
//~ }
?>

<!-- BLOC ARBORESCENCE -->
<div class="block">
    <div onclick="flipflop('allcourses');" style="text-align:center;width:100%;font-weight:bold;padding:5px;color:white;background-color:#7F7F7F;border-radius:5px 5px 0 0">
        Tous les cours par composante (UFR, Institut, etc.)
        <img src ="Images/sort.png" style="float: right "  height="25" width="25">
    </div>
    <div id = "allcourses" class="content" style='width:100%;display:none'>
        <ul>
            <?php
            $sql = "SELECT id, name FROM mdl_course_categories WHERE parent = 395 AND idnumber NOT LIKE '%-TESTS' ORDER BY sortorder ASC";
            $maincategories = $DB->get_recordset_sql($sql);
            foreach ($maincategories as $maincategory) {
                    echo "<li><a style='color:#731472' href='course/index.php?categoryid=$maincategory->id'>$maincategory->name</a></li>";
            }
            ?>
        </ul>
    </div>
</div>
<?php
echo $OUTPUT->footer();

// LAURENT

function createvetcourse($created) {

    global $DB, $OUTPUT, $USER;

    $sql = "SELECT askerid, ufr, level, vetnum, vettitle, elpnum, elptitle, format, brouillonid FROM mdl_asked_courses WHERE id = $created";
    
    $createdcourse = $DB->get_record_sql($sql);    
    $askerid = $createdcourse->askerid;

    //On vérifie que le demandeur est bien un enseignant
    $sql = "SELECT id as isteacher FROM mdl_role_assignments WHERE userid = $askerid AND roleid = 2";
    $isteacher = $DB->get_record_sql($sql)->isteacher;
    if (!$isteacher) {
        return null;
    }

    //Si le cours existe déjà
    $sql = "SELECT id, COUNT(id) as alreadycreated FROM mdl_course WHERE idnumber = '$createdcourse->elpnum'";
    $result = $DB->get_record_sql($sql);
    $alreadycreated = $result->alreadycreated;
    if ($alreadycreated) {
        echo "<span style='font-weight:bold,text-align:center'>Le cours $createdcourse->elpnum existe déjà.</span><br>";
        $newcourseid = $result->id;
        return null;
    } else {
        //Sinon, on le crée
        //echo "Création du cours $createdcourse->elpnum<br>";
        $vetcategoryid = createvetifnew($createdcourse->ufr, $createdcourse->level, $createdcourse->vetnum, $createdcourse->vettitle);
        //echo "vetcategoryid: $vetcategoryid<br>";
        $coursedata = array();
        $coursedata = new stdClass;
        $coursedata->fullname = $createdcourse->elptitle;
        $coursedata->category = $vetcategoryid;        
        $coursedata->shortname = "Y2017-".$createdcourse->elpnum;
        $coursedata->idnumber = "Y2017-".$createdcourse->elpnum;

        //Si le cours est créé à partir d'un brouillon
        if ($createdcourse->brouillonid) {
                $sql = "UPDATE mdl_course "
                        . "SET fullname = '".addslashes($coursedata->fullname)."', "
                        . "category = $coursedata->category, "
                        . "shortname = '$coursedata->shortname', "
                        . "idnumber = '$coursedata->idnumber' "
                        . "WHERE id = $createdcourse->brouillonid";
                //echo "$sql<br>";
                $DB->execute($sql);
                $newcourseid = $createdcourse->brouillonid;
                //echo "newcourseid : $newcourseid<br>";
        } else {
                //~ if ($createdcourse->format == "Une section par semaine") {
                        //~ $coursedata->format = "weeks";
                //~ } else {
                        //~ $coursedata->format = "topics";
                //~ }

				$coursedata->format = 'topics';

                //echo "Création proprement dite<br>";
                
                $alreadyshortname = $DB->get_record('course', array('shortname' => $coursedata->shortname));
                if ($alreadyshortname) {
					$alreadyidnumber = $alreadyshortname;
				} else {
                    $alreadyidnumber = $DB->get_record('course', array('idnumber' => $coursedata->idnumber));                    
				}
				
				if ($alreadyidnumber) {
					echo "<p style='color:red;font-weight:bold'>Ce cours existe déjà sur la plateforme.</p>";
					echo "<a href='course/view.php?id=$alreadyidnumber->id'><button>Voir ce cours</button></a>";
					//header("Location: course/view.php?id=$alreadyidnumber->id");
					$OUTPUT->footer();
					exit;
			    }
                
                $newcourse = create_course($coursedata);

                $newcoursecontext = context_course::instance($newcourse->id, MUST_EXIST);
                $newcourseid = $newcourse->id;

				//On enregistre qui crée le cours
                $now = time();
                $creator = new stdClass();
                $creator->userid = $askerid;
                $creator->courseid = $newcourseid;
                $creator->timecreated = $now;
                $DB->insert_record('course_creator', $creator);

                //L'utilisateur qui crée ce cours a-t-il déjà des droits d'édition dessus ?
                $caneditnewcourse = has_capability('moodle/course:update', $newcoursecontext);
                if (!$caneditnewcourse) {
					//On inscrit l'enseignant demandeur au cours, comme enseignant (s'il n'a pas déjà des droits d'édition dessus).
                    $sql = "SELECT id FROM mdl_enrol WHERE enrol = 'manual' AND courseid = $newcourseid";
                    $enrolid = $DB->get_record_sql($sql)->id;                    
                    $sql = "INSERT INTO mdl_user_enrolments (enrolid, userid, timestart, modifierid, timecreated, timemodified) VALUES ($enrolid, $askerid, $now, $USER->id, $now, $now)";
                    $DB->execute($sql);
                    $sql = "INSERT INTO mdl_role_assignments (roleid, contextid, userid, timemodified, modifierid) VALUES (3, $newcoursecontext->id, $askerid, $now, $USER->id)";
                    $DB->execute($sql);
				}        
		}
    }
    $sql = "UPDATE mdl_asked_courses SET answererid = $USER->id, answer = 'Oui', answeredat = NOW(), courseid = $newcourseid WHERE id = $created";
    $DB->execute($sql);

    // On redirige l'enseignant vers la page des paramètres du cours.
    redirect(new moodle_url('/course/edit.php', array('id'=>$newcourseid)));
}

// FIN LAURENT

//BRICE
function create_collab($collabtitle) {
    global $DB, $USER;
    $collabid = $DB->get_field('course_categories', 'id', array('idnumber' => 'COLLAB'));
    $coursedata = new stdClass;
    $coursedata->fullname = trim($collabtitle);
    $coursedata->category = $collabid;
    $firstnamefirstletters = strtoupper(substr($USER->firstname, 0, 2));
    $lastnamefirstletter = strtoupper(substr($USER->lastname, 0, 1));
    $firstidnumber = 'COLLAB-'.$firstnamefirstletters.'17'.$lastnamefirstletter;
    $idnumber = choose_idnumber($firstidnumber, 0);
    $coursedata->shortname = $idnumber;
    $coursedata->idnumber = $idnumber;
    $coursedata->format = 'topics';
    $collabspace = create_course($coursedata);
    $enrolmethod = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $collabspace->id));
    $collabcontext = context_course::instance($collabspace->id, MUST_EXIST);
    $enrolment = new stdClass();
    $enrolment->enrolid = $enrolmethod->id;
    $enrolment->userid = $USER->id;
    $enrolment->modifierid = $USER->id;
    $now = time();
    $enrolment->timestart = $now;
    $enrolment->timecreated = $now;
    $enrolment->timemodified = $now;
    $DB->insert_record('user_enrolments', $enrolment);
    $assignment = new stdClass();
    $assignment->roleid = 3;
    $assignment->contextid = $collabcontext->id;
    $assignment->userid = $USER->id;
    $assignment->timemodified = $now;
    $assignment->modifierid = $USER->id;
    $DB->insert_record('role_assignments', $assignment);
    return $collabspace;
}

function create_perso() {
    global $DB, $USER;
    $persoid = $DB->get_field('course_categories', 'id', array('idnumber' => 'PERSO'));
    $coursedata = new stdClass;
    $coursedata->fullname = "Espace personnel de $USER->firstname $USER->lastname";
    $coursedata->category = $persoid;
    $coursedata->shortname = "PERSO-$USER->id";
    $coursedata->idnumber = "PERSO-$USER->id";
    $coursedata->format = 'topics';
    $persospace = create_course($coursedata);
    $enrolmethod = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $persospace->id));
    $persocontext = context_course::instance($persospace->id, MUST_EXIST);
    $enrolment = new stdClass();
    $enrolment->enrolid = $enrolmethod->id;
    $enrolment->userid = $USER->id;
    $enrolment->modifierid = $USER->id;
    $now = time();
    $enrolment->timestart = $now;
    $enrolment->timecreated = $now;
    $enrolment->timemodified = $now;
    $DB->insert_record('user_enrolments', $enrolment);
    $assignment = new stdClass();
    $assignment->roleid = 3;
    $assignment->contextid = $persocontext->id;
    $assignment->userid = $USER->id;
    $assignment->timemodified = $now;
    $assignment->modifierid = $USER->id;
    $DB->insert_record('role_assignments', $assignment);
    return $persospace;
}

function choose_idnumber($firstidnumber, $i) {
	global $DB;
	$idnumber = $firstidnumber;
	if ($i) {
		$idnumber .= $i;
	}
	$already = $DB->get_record('course', array('idnumber' => $idnumber));
	if ($already) {
		return choose_idnumber($firstidnumber, $i + 1);
	} else {
		return $idnumber;
	}
}

function mailspacecreation($type, $space) {
	global $CFG, $USER;
	if ($type == 'collab') {
		$longtype = 'espace collaboratif';
	} else if ($type == 'perso') {
		$longtype = 'espace personnel';
	} else {
		return null;
	}
	$to      = "$USER->email";
    $subject = "CoursUCP : Création d'un $longtype ";
    $message = "Bonjour, <br><br>
                Vous venez de créer un $longtype intitulé $space->fullname sur la plateforme CoursUCP.<br>
                Vous pouvez y accéder à l'adresse $CFG->wwwroot/course/view.php?id=$space->id.<br><br>
                Bon travail !<br><br>
                CoursUCP, votre plateforme pédagogique<br>";
    $message .= "<br>Ceci est un message automatique. Merci de ne pas y répondre.<br>";
    if ($type == 'collab') {
		$message .= "Pour toute demande ou information, nous vous invitons à <a href='https://monucp.u-cergy.fr/uPortal/f/u312l1s6/p/Assistance.u312l1n252/max/render.uP?pCp'>Effectuer une demande</a> dans la catégorie <strong>SEFIAP -> Applications pédagogiques</strong>.";
	}    
    $headers = 'From: noreply@cours.u-cergy.fr' . "\r\n" .'MIME-Version: 1.0' . "\r\n".
               'Reply-To: noreply@cours.u-cergy.fr' . "\r\n" .'Content-type: text/html; charset=utf-8' . "\r\n".
               'X-Mailer: PHP/' . phpversion();
     mail($to, $subject, $message, $headers);	
}

//FIN BRICE
