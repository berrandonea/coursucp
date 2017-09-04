<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/gradelib.php');

/**
 * Default form for editing course section
 *
 * Course format plugins may specify different editing form to use
 */



class editsp_form extends moodleform {

    function definition() {
        global  $DB;
        //print_object($customdata);
        $mform  = $this->_form;
        $course = $this->_customdata['course'];
        //echo $course;
        $req = 'SELECT DISTINCT mdl_user.id, lastname, firstname, username, department
        FROM mdl_user, mdl_role_assignments, mdl_context, mdl_course
        WHERE mdl_user.id = mdl_role_assignments.userid
        AND mdl_role_assignments.contextid = mdl_context.id
        AND mdl_role_assignments.roleid = 5 
        AND mdl_context.instanceid = mdl_course.id
        AND mdl_course.id='. $course->id . ' LIMIT 0 , 30';
        $users = $DB->get_records_sql($req,null);
        $usercnt = count($users);
        
        
        $i = 0;
        $groups = $DB->get_records('groups',array('courseid'=>$course->id));
        $group = array();
        foreach ($groups as $value) {
            $group[$i] = $value->name;
                    
             $i++;       //= array_push($group,$value->name);
        }
        
        $mform->addElement('header', 'generalhdr', get_string('general'));

                 
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('text', 'name', 'name', array('size' => '30', 'maxlength' => '255'));
        $mform->addGroup($elementgroup, 'name_group', "Titre", ' ', false);

        $mform->addGroupRule('name_group', array('name' => array(array(get_string('maximumchars', '', 255), 'maxlength', 255))));
        
        $mform->setDefault('usedefaultname', true);
        $mform->setType('name', PARAM_TEXT);
        $mform->disabledIf('name','usedefaultname','checked');
        $mform->addRule('name_group', null, 'required', null, 'client');
        /// Prepare course and the editor

        $mform->addElement('editor', 'description', "Problème à résoudre", null, 'dsqhgqfhqfqdhqdfhdqqdfhq');
        $mform->addHelpButton('summary_editor', 'summary');
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->setDefault('description', 'fdqhfqhqdqhdfhq');


        /*$mform->addElement('textarea', 'texteexpositif', 'Texte expositif','wrap="virtual" rows="5" cols="100"');
        $mform->setType('texteexpositif', PARAM_RAW);
        $mform->addRule('texteexpositif', null, 'required', null, 'client');*/

        $mform->addElement('editor', 'Resultatattendu', 'Résultat attendu','wrap="virtual" rows="5" cols="100"');
        $mform->setType('Resultatattendu', PARAM_RAW);
        $mform->addRule('Resultatattendu', null, 'required', null, 'client');
        
        $name = get_string('allowsubmissionsfromdate', 'assign');
        $options = array('optional'=>true);
        $mform->addElement('date_selector', 'datedu', 'Du');
        
        $name = get_string('duedate', 'assign');
        $mform->addElement('date_selector', 'dateau', 'Au');
               
        $mform->addElement('html','<b>Outils de communication</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="chat" checked="yes">Chat</input>&nbsp;');
        $mform->addElement('html','<img src="../mod/chat/pix/icon.png"></img>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');       
        $mform->addElement('html', '<input type="checkbox" name="forum" checked="yes">Forum</input>&nbsp;<img src="../mod/forum/pix/icon.png"></img>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');        
        $mform->addElement('html', '<input type="checkbox" name="depotetudiant">Dépôt étudiant</input>&nbsp;<img src="../mod/depotetudiant/pix/icon.png"></img>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $mform->addElement('html', '<input type="checkbox" name="etherpadlite">Texte partagé</input>&nbsp;<img src="../mod/etherpadlite/pix/icon.png"></img>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $mform->addElement('html', '<input type="checkbox" name="wiki" checked="yes">Wiki</input>&nbsp;<img src="../mod/wiki/pix/icon.png"></img><br><br>');
        
      
        // Copier un groupement existant
        $coursegroupings = $DB->get_records('groupings', array('courseid' => $course->id));
        $options = array(' - ');
        foreach($coursegroupings as $coursegrouping) {
            $options[$coursegrouping->id] = $coursegrouping->name;
        }
        print_object($options);
        $mform->addElement('select', 'copygrouping', 'Copier un groupement existant', $options);        
/*
        // Nombre d'étudiants par groupe de travail Nombre d'étudiants par groupe de travail
        $eltmgroupnbetu= array();
        $eltmgroupnbetu[] = $mform->createElement('select', 'nbuserpergrp', 'Nombre d\'étudiants par groupe de travail',$options);
        $eltmgroupnbetu[] = $mform->createElement('checkbox', 'usenbetu', '', '');
        $mform->addGroup($eltmgroupnbetu, 'nbuserpergrp', 'Nombre d\'étudiants par groupe de travail', ' ', false);
        $mform->setDefault('usenbetu', false);
        $mform->setType('nbuserpergrp', PARAM_TEXT);
        $mform->disabledIf('nbuserpergrp','usegroupe', 'checked'); 
*/
      
        // Nombre de groupes Nombre de groupes
        $options = array_combine(range(1, $usercnt), range(1, $usercnt));
        $eltmnbgroup= array();
        $eltmnbgroup[] = $mform->createElement('select', 'maxgroupe', 'Nombre de groupes',$options);
        //$eltmnbgroup[] = $mform->createElement('checkbox', 'usegroupe', '', '');
        $mform->addGroup($eltmnbgroup, 'maxgroupe', 'Nombre de groupes', ' ', false);
        $mform->disabledIf('maxgroupe','copygrouping','neq', 0);
        //$mform->setDefault('usegroupe', true);
        //$mform->setType('usegroupe', PARAM_TEXT);
        //$mform->disabledIf('maxgroupe','usenbetu','checked');
         


       ?>


<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>



<style>
.multiselect {
    width:20em;
    height:10em;
    border:solid 1px #c0c0c0;
    overflow:auto;
}
 
.multiselect label {
    display:block;
}
 
.multiselect-on {
    color:#ffffff;
    background-color:#000099;
}

.multiselect #fitem_id_usegroupexml .fitemtitle {
width : 15em;
margin: 5px;
float:left;
text-align:left;
}
</style>

        <?php          
        $mform->addElement('html',"<p style='text-align:center'><input type='submit' value='Enregistrer'></input>&nbsp&nbsp&nbsp&nbsp");
        $mform->addElement('html',"<a href='view.php?id=".$course->id."'><input type='button' value='Annuler'></input></a></p>");
        $mform->closeHeaderBefore('submitbutton');
    }

   

    public function validation($data, $files) {
              
        global $CFG, $COURSE;
        $errors = parent::validation($data, $files);
        $req = 'SELECT DISTINCT mdl_user.id, lastname, firstname, username, department
        FROM mdl_user, mdl_role_assignments, mdl_context, mdl_course
        WHERE mdl_user.id = mdl_role_assignments.userid
        AND mdl_role_assignments.contextid = mdl_context.id
        AND mdl_role_assignments.roleid = 5 
        AND mdl_context.instanceid = mdl_course.id
        AND mdl_course.id='. $COURSE->id . ' LIMIT 0 , 30';
        
        $users = $DB->get_records_sql($req,null);
        $usercnt = count($users);
         
        // Nombre de groupe doit être inférieur au nombre d'utilisateur inscrit
        if(!empty($data['usegroupe'])){
            if ($data['maxgroupe'] > $usercnt || $data['number'] < 1) {
                  $errors['maxgroupe'] = get_string('toomanygroups', 'group', $usercnt);
            }
        }
            
        // Nombre d'utilisateur par groupe doit être inférieur au nombre d'utilisateur inscrit
        if(!empty($data['usenbetu'])){
            if ($data['nbuserpergrp'] > $usercnt || $data['number'] < 1) {
                  $errors['nbuserpergrp'] = get_string('toomanygroups', 'group', $usercnt);
            }
        }
        
        return $errors;
    }

    /**
     * Load in existing data as form defaults
     *
     * @param stdClass|array $default_values object or array of default values
     */
    function set_data($default_values) {
          if (!is_object($default_values)) {
            // we need object for file_prepare_standard_editor
            $default_values = (object)$default_values;
        }
        $editoroptions = $this->_customdata['editoroptions'];
        $default_values = file_prepare_standard_editor($default_values, 'summary', $editoroptions,
                $editoroptions['context'], 'course', 'section', $default_values->id);
        $default_values->usedefaultname = (is_null($default_values->name));
        parent::set_data($default_values);
    }

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * @return object submitted data; NULL if not valid or not submitted or cancelled
     */
    function get_data() {
   
        $data = parent::get_data();
        if ($data !== null) {
            $editoroptions = $this->_customdata['editoroptions'];
            if (!empty($data->usedefaultname)) {
                $data->name = null;
            }
            $data = file_postupdate_standard_editor($data, 'summary', $editoroptions,
                    $editoroptions['context'], 'course', 'section', $data->id);
            $course = $this->_customdata['course'];
            foreach (course_get_format($course)->section_format_options() as $option => $unused) {
                // fix issue with unset checkboxes not being returned at all
                if (!isset($data->$option)) {
                    $data->$option = null;
                }
            }
        }
        return $data;
    }
}
