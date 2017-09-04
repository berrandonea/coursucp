<title>
Création de cours - Enseignants - Moodle
</title>

<script type="text/javascript" src="$CFG->wwwroot/lib/js/foToolTip.js"></script>

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

    if ($CFG->forcelogin) {
        require_login();
    } else {
        user_accesstime_log();
    }

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

    if (isloggedin()) {
        add_to_log(SITEID, 'course', 'view', 'view.php?id='.SITEID, SITEID);
    }

/// If the hub plugin is installed then we let it take over the homepage here
    if (file_exists($CFG->dirroot.'/local/hub/lib.php') and get_config('local_hub', 'hubenabled')) {
        require_once($CFG->dirroot.'/local/hub/lib.php');
        $hub = new local_hub();
        $continue = $hub->display_homepage();
        //display_homepage() return true if the hub home page is not displayed
        //mostly when search form is not displayed for not logged users
        if (empty($continue)) {
            exit;
        }
    }

    $PAGE->set_pagetype('site-index');
    $PAGE->set_docs_path('');
    $PAGE->set_pagelayout('frontpage');

    $editing = $PAGE->user_is_editing();
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);

    $courserenderer = $PAGE->get_renderer('core', 'course');


/************************************************* DEBUT CONTENT ***************************************************************/
//BRICE
/*******************************************************************************************************************************/

$codecourselp = "";

$filename = '/home/referentiel/dokeos_elp_etu_ens.xml';
/* ON CHARGE LE XML */
if (filesize($filename) > 0) {
    $xmldoc = new DOMDocument();
    $xmldoc->load($filename);
    $xpathvar = new Domxpath($xmldoc);

    /* ON COMMENCE LA LECTURE */
    $queryResult = $xpathvar->query('//Structure_diplome[Cours/Group/Teacher[@StaffUID="'.$USER->username.'"]]');

    foreach($queryResult as $result){        
        // RECUPERATION CODE VET
        $idvet = $result->getAttribute('Etape'); 
        $nomvet = $result->getAttribute('libelle_long_version_etape');
        //echo "$idvet<br>";

        $showvet = 0;

        /* SEULEMENT LES ELP AVEC L'UID DE L'USER CONNECTE */
        $querycours = $xpathvar->query('//Structure_diplome[@Etape="'.$idvet.'"]/Cours[Group/Teacher[@StaffUID="'.$USER->username.'"]]');

        foreach($querycours as $cours){
            $codecourselp = $idvet."-".$cours->getAttribute('element_pedagogique');
            //echo "$codecourselp<br>";
            $course = $DB->get_record('course', array('shortname' => $codecourselp));
            $codecourselpseul = $cours->getAttribute('element_pedagogique');

            if(!$course) {
                // LE SCRIPT JS AFFICHE (DANS LES CAS OU DES COURS SONT TROUVES), LE BLOCK P DE LA VET QUI EST CACHE PAR DEFAUT 
                echo '<script language="javascript" type="text/javascript">document.getElementById("monbeaup'.$idvet.'").style.display = "block";</script>' ;

                if ($cours->getAttribute('type_element_pedagogique')!="") {
                   $elpeda = " [".$cours->getAttribute('type_element_pedagogique')."]";
                } else $elpeda = "";


                if ($showvet == 0) {
                    $showvet = 1;
                    echo "<p id='monbeaup".$idvet."' style='font-weight:bold;padding:5px;color:white;background-color : #780D68'>(".$idvet.") ".$nomvet."</p>";
                    echo "<ul>";
                }

                echo "<li class='dejacree'><a onmouseover=\"FoToolTip.show(this,'Créer ce cours')\" href=\"$CFG->wwwroot/course/edit.php?category=4&cat=".
                        $idvet."&nomvet=$nomvet&codecours=".$idvet."-".$cours->getAttribute('element_pedagogique').
                        "&titrecours=".$cours->getAttribute('libelle_long_element_pedagogique')."".$elpeda."&returnto=category\">".
                        $cours->getAttribute('libelle_long_element_pedagogique')."".$elpeda."</a><br/><i>".$idvet."-".
                        $cours->getAttribute('element_pedagogique')."</i></li>"; 
            } 
        }
        if ($showvet == 1) {
            echo "</ul>";
        }

    }
}

if($codecourselp == ""){
    echo "<p id='rientrouve'>Aucun cours trouvé pour cet utilisateur.</p>";
}

/***************************** FIN CONTENT **************************************************************************************************/
/********************************************************************************************************************************************/

?>


