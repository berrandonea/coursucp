<?php
defined('MOODLE_INTERNAL') || die();
class block_ucp_chiffres extends block_base {
 
    function init() {
        $this->title = get_string('pluginname', 'block_ucp_chiffres');
    }
    function get_content() {
        global $CFG, $DB, $OUTPUT, $PAGE, $USER;
        if ($this->content !== null) {
            return $this->content;
        }
        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }
        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        
        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }
        $this->content->text = '';
       
		
		if(isloggedin()) {
			//~ $userpicture = new user_picture($DB->get_record('user', array('id' => $USER->id)));
			//~ $url = $userpicture->get_url($PAGE, $OUTPUT);

			$sqlenline = "select count(id) as nbr from mdl_sessions";
			$resenligne = $DB->get_record_sql($sqlenline);
			$this->content->text  .= "<strong> $resenligne->nbr</strong> connectés<br>";

/*			$sqlactif ="SELECT COUNT(DISTINCT l.userid) as nbr "
                                . "FROM mdl_log l, mdl_role_assignments ra, mdl_user u "
                                . "WHERE l.time > (UNIX_TIMESTAMP(NOW()) - 3600 * 24 * 25) AND l.course > 1 "
                                . "AND l.userid = ra.userid AND ra.roleid = 5 "
                                . "AND l.userid = u.id AND u.idnumber > 0";
			$resactif = $DB->get_record_sql($sqlactif);
			$this->content->text .= "<strong>$resactif->nbr</strong> étudiants actifs<br>";*/

			$sqlcourse = "SELECT COUNT( id ) AS nbr FROM mdl_course WHERE `idnumber` LIKE 'Y2017-%'";
			$rescourse = $DB->get_record_sql($sqlcourse);
			$this->content->text .= "<strong> $rescourse->nbr</strong> cours<br>";

			$sql = "SELECT COUNT(DISTINCT ra.userid) AS nbdistinctteachers 
			        FROM mdl_role_assignments ra, mdl_user u, mdl_context ctx, mdl_course c 
			        WHERE ra.roleid = 3 AND ctx.id = ra.contextid AND c.id = ctx.instanceid AND c.idnumber LIKE 'Y2017-%' AND ra.userid = u.id AND u.email LIKE '%@u-cergy.fr'";
			$nbdistinctteachers = $DB->get_record_sql($sql);
			$this->content->text .= "<strong> $nbdistinctteachers->nbdistinctteachers</strong> enseignants<br>";

/*			$sql = "SELECT number FROM mdl_chiffres WHERE name = 'nbviews'";
			$nbviews = $DB->get_record_sql($sql);
			$this->content->text .= "<strong> $nbviews->number</strong> consultations <hr>";*/

			$verifrole = explode("@", $USER->email);
			if (($verifrole[1] == "u-cergy.fr")) {
				$this->content->text .= "<a href = '$CFG->wwwroot/report/log/chiffres.php'><h5 style='color:#569E1B;'>Plus de chiffres ...</h5></a>";
			}
            if (! empty($this->config->text)) {
                $this->content->text .= $this->config->text;
            }
            return $this->content;
	    }
    }

    public function applicable_formats() {
        return array('all' => true,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true,
                     'course-view-social' => false,
                     'mod' => true,
					 'my' => true,
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return false;
    }

    function has_config() {return true;}

    public function cron() {
            mtrace( "Hey, my cron script is running" );
            return true;
    }

}
