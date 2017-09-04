<?php
defined('MOODLE_INTERNAL') || die();
class block_copysection extends block_base {

    function init() {
        $this->title = $this->title = "Copie de sections";
    }

    function get_content() {
        global $CFG, $COURSE, $DB, $USER;
        if ($this->content !== NULL) {
            return $this->content;
        }
        $this->content = new stdClass;
        if (empty($this->instance)) {
            return $this->content;
        }
        $blockurl = "$CFG->wwwroot/blocks/copysection";
        $this->content->text = '<p>Voulez-vous copier les titres et les descriptions des sections d\'un autre cours vers celui-ci ?</p>';
        $this->content->text .= "<a style='text-align:center' href='$blockurl/view.php?to=$COURSE->id'><button>Oui</button></a>";
        return $this->content;
    }

    public function instance_allow_multiple() {
          return false;
    }

    function has_config() {
        return false;
    }
}
