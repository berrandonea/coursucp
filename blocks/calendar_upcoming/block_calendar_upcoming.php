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
 * Handles displaying the calendar upcoming events block.
 *
 * @package    block_calendar_upcoming
 * @copyright  2004 Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_calendar_upcoming extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_calendar_upcoming');
    }

    /**
     * Return the content of this block.
     *
     * @return stdClass the content
     */
    public function get_content() {
        global $CFG, $DB, $USER;

        require_once($CFG->dirroot.'/calendar/lib.php');

        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->text = '';

        $filtercourse    = array();
        if (empty($this->instance)) { // Overrides: use no course at all.
            $courseshown = false;
            $this->content->footer = '';

        } else {
            $courseshown = $this->page->course->id;
            $this->content->footer = '<div class="gotocal"><a href="'.$CFG->wwwroot.
                                     '/calendar/view.php?view=upcoming&amp;course='.$courseshown.'">'.
                                      'Plus d\'actualités</a></div>';
            $context = context_course::instance($courseshown);
			$sqlrole = "SELECT count(id) as nbr FROM mdl_role_assignments where userid=$USER->id and roleid=1 and contextid=1";
			$resrole = $DB->get_record_sql($sqlrole);
			$isadmin = is_siteadmin($USER);
			if($isadmin || $resrole->nbr >=1)
			{
				 if (has_any_capability(array('moodle/calendar:manageentries', 'moodle/calendar:manageownentries'), $context)) {
                $this->content->footer .= '<div class="newevent"><a href="'.$CFG->wwwroot.
                                          '/calendar/event.php?action=new&amp;course='.$courseshown.'">'.
                                           'Nouvelle actualité</a></div>';
			}
			$sqlsubscribe = "select count(id) as nbr from mdl_abonnement where userid=$USER->id";
			$ressubscribe = $DB->get_record_sql($sqlsubscribe);
			if($ressubscribe->nbr >=1)
			{
				//$this->content->text .= "<br>Vous êtes déjà abonné(e)<br>";		  
			}	
			else
			{
				$this->content->footer .= "<br><center><a href='$CFG->wwwroot/blocks/calendar_upcoming/abonnement.php'><button type='button' style='float: right;'>S'abonner aux actualités</button></a></center><br><br>";		  
			}
           
            }
            if ($courseshown == SITEID) {
                // Being displayed at site level. This will cause the filter to fall back to auto-detecting
                // the list of courses it will be grabbing events from.
                $filtercourse = calendar_get_default_courses();
            } else {
                // Forcibly filter events to include only those from the particular course we are in.
                $filtercourse = array($courseshown => $this->page->course);
            }
        }

        list($courses, $group, $user) = calendar_set_filters($filtercourse);

        $defaultlookahead = CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD;
        if (isset($CFG->calendar_lookahead)) {
            $defaultlookahead = intval($CFG->calendar_lookahead);
        }
        $lookahead = get_user_preferences('calendar_lookahead', $defaultlookahead);

        $defaultmaxevents = CALENDAR_DEFAULT_UPCOMING_MAXEVENTS;
        if (isset($CFG->calendar_maxevents)) {
            $defaultmaxevents = intval($CFG->calendar_maxevents);
        }
        $maxevents = get_user_preferences('calendar_maxevents', $defaultmaxevents);
        $events = calendar_get_upcoming($courses, $group, $user, $lookahead, $maxevents);
		
        if (!empty($this->instance)) {
            $link = 'view.php?view=day&amp;course='.$courseshown.'&amp;';
            $showcourselink = ($this->page->course->id == SITEID);
           // $this->content->text = calendar_get_block_upcoming($events, $link, $showcourselink);
			$ts =time();
			$sqlevent = "select id,name,timestart,timeduration from mdl_event where eventtype ='site' and timestart >=$ts order by timestart DESC limit 3";
			$resevent = $DB->get_records_sql($sqlevent);
			
			foreach($resevent as $event)
			{
				$this->content->text .= "<a href ='$CFG->wwwroot/calendar/view.php?view=day&course=1&time=$event->timestart#event_$event->id'>$event->name</a> <br>";
				if($event->timeduration ==0)
				{
					$datedebut = date('d/m/Y' , $event->timestart);
					$this->content->text .= "Le : $datedebut";
				}
				else
				{
					$datedebut = date('d/m/Y' , $event->timestart);
					$start =$event->timestart;
					$fin =$event->timeduration;
					$calcul = $start + $fin;
					$datefin = date('d/m/Y' , $calcul);
					$this->content->text .= "Du : $datedebut <br>";
					$this->content->text .= "Jusqu'au : $datefin";
				}
				$this->content->text .= "<hr>";
			}
			//$this->content->text .= "<a href='$CFG->wwwroot/report/log/chiffres.php'><h5 style='color:#569E1B;'>CoursUCP en chiffres</h5></a>";	 
	   }
        if (empty($this->content->text)) {
            $this->content->text = '<div class="post">'. get_string('noupcomingevents', 'calendar').'</div>';
        }
        return $this->content;
    }
    
    //BRICE
    public function specialization() {
        global $COURSE;
        if ($COURSE->id == 1) {
            $this->title = 'Actualités';            
        }
    }
    //FIN
}


