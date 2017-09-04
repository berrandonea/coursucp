<?php
defined('MOODLE_INTERNAL') || die();
class block_ucp_profil extends block_base {

    function init() {
        //$this->title = get_string('pluginname', 'block_ucp_profil');
		$this->title = "Mon espace";
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
        $sqlunread ="SELECT COUNT(DISTINCT lmidraft.id) AS nbr
                              FROM mdl_local_mail_index lmidraft, mdl_local_mail_index lmicourse, mdl_course c
                              WHERE lmidraft.userid =$USER->id AND lmidraft.type ='inbox' AND lmidraft.unread =1 
                              AND lmicourse.type ='course' AND lmidraft.messageid = lmicourse.messageid AND c.id = lmicourse.item";
        $nbunreads = $DB->get_record_sql($sqlunread)->nbr;
        
        $this->content->text = '';
        $this->content->text .= "<table>";
        $this->content->text .= "<tr>";
		$this->content->text .= "<td><a href='$CFG->wwwroot/user/profile.php'><center><img src ='$CFG->wwwroot/blocks/ucp_profil/pix/ProfilPom.png' height='40' width='40'/><br><strong>Profil</strong></center></a><br></td>";
		$this->content->text .= "<td><a href ='$CFG->wwwroot/user/preferences.php'><center><img src ='$CFG->wwwroot/blocks/ucp_profil/pix/PrefPom2.png' height='40' width='40'/><br><strong>Préférences</strong></center></a><br></td></tr>";
		$this->content->text .= "<tr>";
		$this->content->text .= "<td style='background-color:#f5f5f5;'><a href ='$CFG->wwwroot/user/files.php'><center><img src ='$CFG->wwwroot/blocks/ucp_profil/pix/FilePom.png' height='40' width='40'/><br><strong>Fichiers</strong></center></a><br></td>";
		$this->content->text .= "<td style='background-color:#f5f5f5;'><a href ='$CFG->wwwroot/message/index.php'><center><img src ='$CFG->wwwroot/blocks/ucp_profil/pix/MessagePomm.png' height='40' width='40'/><br><strong>Messages&nbsp;($nbunreads)</strong></center></a><br></td></tr>";
		$this->content->text .= "<tr>";
		$this->content->text .= "<td><a href ='$CFG->wwwroot/grade/report/overview/index.php'><center><img src ='$CFG->wwwroot/blocks/ucp_profil/pix/NotesPom.png' height='40' width='40'/><br><strong>Notes</strong></center></a></td>";
		$this->content->text .= "<td><a href = '$CFG->wwwroot/badges/mybadges.php'><center><img src ='$CFG->wwwroot/blocks/ucp_profil/pix/badgePom.png' height='40' width='40'/><br><strong>Badges</strong></center></a></td>";
		$this->content->text .= "</tr>";
		$this->content->text .= "</table>";
        return $this->content;
    }
    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }
    public function instance_allow_multiple() {
          return false;
    }
    function has_config() {return true;}
    public function cron() {
        mtrace( "Hey, my cron script is running" );
        // do something
    	return true;
    }

}
