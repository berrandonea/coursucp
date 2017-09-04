<?php

class block_course_main_menu extends block_list {
    function init() {
        //$this->title = get_string('pluginname', 'block_course_main_menu');
                $this->title = "Cours actuel";

    }
 
    function applicable_formats() {
        return array('site' => true);
    }

    function get_content() {
        global $USER, $CFG, $DB, $OUTPUT, $COURSE;

        //print_object($COURSE);
        
        
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        $course = $this->page->course;
        require_once($CFG->dirroot.'/course/lib.php');
        $context = context_course::instance($course->id);
        $isediting = $this->page->user_is_editing() && has_capability('moodle/course:manageactivities', $context);


     

        echo "<style>


            .block_course_main_menu .unlist{
            list-style-type : square;
            
            }

            .block_course_main_menu .unlist li{
          
  margin-bottom : 5px;


    list-style-type: none; /* on annule la puce par défaut */
    background-image: url('$CFG->wwwroot/theme/image.php/formfactor/core/1395758636/i/navigationitem'); /* on affiche l'image souhaitée */
    background-repeat: no-repeat; /* on annule la répétition par défaut */
    background-position: left center; /* on positionne où l'on veut */
    padding-left: 20px; /* pour éviter la superposition du contenu */

  

            }
        </style>";
       
        
/// extra fast view mode
        $this->content->items[] = "<a href='$CFG->wwwroot/course/view.php?id=".$course->id."'>Accueil du cours</a>";
        $this->content->items[] = "<a href='$CFG->wwwroot/user/index.php?id=".$course->id."'>Participants</a>";
//        //Add Salma onglet Suivi des étudiants visible que pour les enseignants
//        $sqlrole = "select distinct roleid, userid from mdl_role_assignments where userid = $USER->id ";
//        $resultrole = $DB->get_recordset_sql($sqlrole);
//        foreach ($resultrole as $role)
//        {
//        	if($role->roleid ==3)
//        	{
//        		$this->content->items[] = "<a href='$CFG->wwwroot/report/stats/index.php?course=$COURSE->id'>Suivi des étudiants</a>"; 
//        	}
//        }
//        
//        
//       //Lien vers l'historique
//        if ((isloggedin()) and (!isguestuser()) and ($COURSE->category > 0)) {    // If known logged in user
//            //$this->content->items[] = '<img style= "float:left" src="' . $OUTPUT->pix_url('i/mnethost') . '" class="icon" alt="" />'."<a href='$CFG->wwwroot/mod/forum/historic.php?id=$COURSE->id'>Historique</a>";
//			//$this->content->items[] = "<a href='$CFG->wwwroot/report/stats/index.php?course=$COURSE->id'>Suivi des étudiants</a>"; 
//        	$this->content->items[] = "<a href='$CFG->wwwroot/mod/forum/historic.php?id=$COURSE->id'>Historique</a>";
//        }      
//
//        $context = context_course::instance($course->id);
//        if (has_capability('report/stats:view', $context)) {
//        	//$this->content->items[] = "<a href='$CFG->wwwroot/report/stats/index.php?course=$COURSE->id'>Suivie</a>"; 
//            //$this->content->items[] = "<a href='$CFG->wwwroot/report/stats/index.php?course=$COURSE->id&report=13&monthfrom=9&direct=1'>Parcours des étudiants</a>";            
//            //$this->content->items[] = "<a href='$CFG->wwwroot/report/stats/index.php?course=$COURSE->id&report=0&monthfrom=9&direct=1'>Nombre d'actions par étudiant</a>";
//            //$this->content->items[] = "<a href='$CFG->wwwroot/report/stats/index.php?course=$COURSE->id&report=12&monthfrom=9&direct=1'>Temps passé sur ce cours</a>";
//            //$this->content->items[] = "<a href='$CFG->wwwroot/report/stats/index.php?course=$COURSE->id&report=10&monthfrom=9&direct=1'>Actions d'un étudiant</a>";
//        }
        
        if (!$isediting) {
            $modinfo = get_fast_modinfo($course);
            if (!empty($modinfo->sections[0])) {
                $options = array('overflowdiv'=>true);

                 
                
                
                      // SEB - 06/2014 - ON A ENLEVE LA SECTION 0 DE CE BLOC (SECTION QUI SE RETROUVE TOUT EN HAUT DE LA COLONNE PRINCIPALE)
                               
               /* foreach($modinfo->sections[0] as $cmid) {
                    $cm = $modinfo->cms[$cmid];
                    if (!$cm->uservisible) {
                        continue;
                    }

                    $content = $cm->get_formatted_content(array('overflowdiv' => true, 'noclean' => true));
                    $instancename = $cm->get_formatted_name();

                    if (!($url = $cm->get_url())) {
                        $this->content->items[] = $content;
                        $this->content->icons[] = '';
                    } else {
                        $linkcss = $cm->visible ? '' : ' class="dimmed" ';
                        $icon = '<img src="' . $cm->get_icon_url() . '" class="icon" alt="" />';
                                                        $this->content->items[] = '<a title="'.$cm->modplural.'" '.$linkcss.' '.$cm->extra.
                                ' href="' . $url . '">'. $instancename . '</a>';

                    }
                }*/
            }
            return $this->content;
        }

        // Slow & hacky editing mode.
        /** @var core_course_renderer $courserenderer */
        $courserenderer = $this->page->get_renderer('core', 'course');
        $ismoving = ismoving($course->id);
        course_create_sections_if_missing($course, 0);
        $modinfo = get_fast_modinfo($course);
        $section = $modinfo->get_section_info(0);

        if ($ismoving) {
            $strmovehere = get_string('movehere');
            $strmovefull = strip_tags(get_string('movefull', '', "'$USER->activitycopyname'"));
            $strcancel= get_string('cancel');
            $stractivityclipboard = $USER->activitycopyname;
        } else {
            $strmove = get_string('move');
        }
        $editbuttons = '';

        if ($ismoving) {
            $this->content->icons[] = '<img src="'.$OUTPUT->pix_url('t/move') . '" class="iconsmall" alt="" />';
            $this->content->items[] = $USER->activitycopyname.'&nbsp;(<a href="'.$CFG->wwwroot.'/course/mod.php?cancelcopy=true&amp;sesskey='.sesskey().'">'.$strcancel.'</a>)';
        }

        /*if (!empty($modinfo->sections[0])) {
            $options = array('overflowdiv'=>true);
            
            //Lien vers l'historique
            if ((isloggedin()) and (!isguestuser()) and ($COURSE->category > 0)) {    // If known logged in user
                $this->content->items[] = '<img style= "float:left" src="' . $OUTPUT->pix_url('i/mnethost') . '" class="icon" alt="" />'."<a href='$CFG->wwwroot/mod/forum/historic.php?id=$COURSE->id'>HISTORIQUE</a>";
                //$this->content->icons[] = '<img src="' . $OUTPUT->pix_url('i/mnethost') . '" class="icon" alt="" />';            
            }           
                       
            foreach ($modinfo->sections[0] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                if (!$mod->uservisible) {
                    continue;
                }
                if (!$ismoving) {
                    $actions = course_get_cm_edit_actions($mod, -1);

                    // Prepend list of actions with the 'move' action.
                    $actions = array('move' => new action_menu_link_primary(
                        new moodle_url('/course/mod.php', array('sesskey' => sesskey(), 'copy' => $mod->id)),
                        new pix_icon('t/move', $strmove, 'moodle', array('class' => 'iconsmall', 'title' => '')),
                        $strmove
                    )) + $actions;

                    $editbuttons = html_writer::tag('div',
                        $courserenderer->course_section_cm_edit_actions($actions, $mod, array('donotenhance' => true)),
                        array('class' => 'buttons')
                    );

                } else {
                    $editbuttons = '';

                }
                if ($mod->visible || has_capability('moodle/course:viewhiddenactivities', $context)) {
                    if ($ismoving) {
                        if ($mod->id == $USER->activitycopy) {
                            continue;
                        }
                        $this->content->items[] = '<a title="'.$strmovefull.'" href="'.$CFG->wwwroot.'/course/mod.php?moveto='.$mod->id.'&amp;sesskey='.sesskey().'">'.
                            '<img style="height:16px; width:80px; border:0px" src="'.$OUTPUT->pix_url('movehere') . '" alt="'.$strmovehere.'" /></a>';
                        $this->content->icons[] = '';
                    }
                    $content = $mod->get_formatted_content(array('overflowdiv' => true, 'noclean' => true));
                    $instancename = $mod->get_formatted_name();
                    $linkcss = $mod->visible ? '' : ' class="dimmed" ';

                    if (!($url = $mod->get_url())) {
                        $this->content->items[] = $content . $editbuttons;
                        $this->content->icons[] = '';
                    } else {
                        //Accessibility: incidental image - should be empty Alt text
                        $icon = '<img src="' . $mod->get_icon_url() . '" class="icon" alt="" />';
                        $this->content->items[] = '<a title="' . $mod->modfullname . '" ' . $linkcss . ' ' . $mod->extra .
                            ' href="' . $url . '">' . $icon . $instancename . '</a>' . $editbuttons;
                    }
                }
            }
        }*/



        if ($ismoving) {
            $this->content->items[] = '<a title="'.$strmovefull.'" href="'.$CFG->wwwroot.'/course/mod.php?movetosection='.$section->id.'&amp;sesskey='.sesskey().'">'.
                                      '<img style="height:16px; width:80px; border:0px" src="'.$OUTPUT->pix_url('movehere') . '" alt="'.$strmovehere.'" /></a>';
            $this->content->icons[] = '';
        }


        /*$this->content->footer = $courserenderer->course_section_add_cm_control($course,
                0, null, array('inblock' => true));*/

        

        return $this->content;
    }    
}



