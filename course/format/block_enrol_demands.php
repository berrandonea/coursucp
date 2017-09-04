<?php

class block_enrol_demands extends block_base {
    function init() {
        //$this->title = get_string('pluginname', 'block_enrol_demands');
        $this->title = "Demandes d'inscription";

    }
 
    function applicable_formats() {
        return array('site' => true);
    }

    function get_content() {
        global $CFG, $DB, $USER;
        
        if ($this->content !== NULL) {
            return $this->content;
        }
        $this->content = new stdClass;
        if (empty($this->instance)) {
            return $this->content;
        }
        $this->content->text = '';
        
        $teachedcoursesids = $this->get_teached_courses($USER->id, 3);
        if ($teachedcoursesids) {
            $teacheddemands = 0;
            foreach ($teachedcoursesids as $teachedcourseid) {
                $coursenbdemands = $DB->count_records('asked_enrolments', array('courseid' => $teachedcourseid, 'answer' => ''));
                $teacheddemands += $coursenbdemands;
            }
        
            $s = $this->get_plural($teacheddemands);
            $this->content->text .= "<p><a href='$CFG->wwwroot/blocks/enrol_demands/requests.php' style='color:#731472;font-weight:bold'>";
            $this->content->text .= "<img src='$CFG->wwwroot/pix/i/enrolusers.png'>";
            $this->content->text .=  " <span style='color:red;font-weight:bold'>$teacheddemands</span> Reçue$s";
            $this->content->text .= "</a></p>";
			$this->content->text .= "<hr></hr><h4 style='color:#731472;'>Mes demandes</h4><br>";
        }
        
        $nbwantedcourses = $DB->count_records('asked_enrolments', array('studentid' => $USER->id, 'answer' => ''));
		//$nbwantedcoursesyes = $DB->count_records('asked_enrolments', array('studentid' => $USER->id, 'answer' => 'Oui'));
		//$nbwantedcoursesno = $DB->count_records('asked_enrolments', array('studentid' => $USER->id, 'answer' => 'Non'));
		$resnbwantedcoursestraitement = "select count(id) as nbr from mdl_asked_enrolments where studentid=$USER->id and answer in ('Oui', 'Non')";
		$nbwantedcoursestraitement = $DB->get_record_sql($resnbwantedcoursestraitement);
		
        $s = $this->get_plural($nbwantedcourses);
		$this->content->text .= "<p><a href='$CFG->wwwroot/blocks/enrol_demands/requests.php' style='color:#731472;font-weight:bold'>";
        $this->content->text .= "<img src='$CFG->wwwroot/blocks/enrol_demands/pix/hourglass.png' height='20' width='20'>";
        $this->content->text .=  " <span style='color:red;font-weight:bold'>$nbwantedcourses</span> En attente$s";		
		//demandes refusées
        //$this->content->text .= "<p><a href='$CFG->wwwroot/blocks/enrol_demands/requests.php' style='color:#731472;font-weight:bold'>";
        //$this->content->text .= "<img src='$CFG->wwwroot/pix/i/enrolusers.png'>";
        //$this->content->text .=  " <span style='color:red;font-weight:bold'>$nbwantedcoursesno</span> demande$s refusée$s";     
	   //Demandes acceptées
	    //$this->content->text .= "<p><a href='$CFG->wwwroot/blocks/enrol_demands/requests.php' style='color:#731472;font-weight:bold'>";
        //$this->content->text .=  " <span style='color:red;font-weight:bold'>$nbwantedcoursesyes</span> demande$s acceptée$s";
        $this->content->text .= "<p><a href='$CFG->wwwroot/blocks/enrol_demands/requests.php' style='color:#731472;font-weight:bold'>";
		$s = $this->get_plural($nbwantedcoursestraitement->nbr);
		$this->content->text .= "<img src='$CFG->wwwroot/blocks/enrol_demands/pix/file.png' height='20' width='20'>";
	    $this->content->text .=  " <span style='color:red;font-weight:bold'>$nbwantedcoursestraitement->nbr</span> Traitée$s";
       
	   //Btn 
	   $this->content->text .= "<br><center><u><a href= '$CFG->wwwroot/course/index.php'> Ajouter une demande +</a></u></center>";
		
		
		$this->content->text .= "</a></p>";
        
        return $this->content;
    }
    
    function get_plural($nb) {
        if ($nb > 1) {
            $plural = "s";            
        } else {
            $plural = "";
        }
        return $plural;
    }
    
    function get_teached_courses($teacherid, $roleid) {
        global $DB;
        $courseids = array();
        $params = array('roleid' => $roleid, 'userid' => $teacherid);
        $teacherassignments = $DB->get_records('role_assignments', $params);
        foreach($teacherassignments as $teacherassignment) {
            $courseid = $DB->get_field('context', 'instanceid', array('id' => $teacherassignment->contextid));
            $courseids[] = $courseid;
        }
        return $courseids;
    }
}



