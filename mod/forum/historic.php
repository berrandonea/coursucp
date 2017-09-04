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
 * @package mod-forum
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php'); 

require_once('lib.php'); //Fonctions concernant les forums

//Fonctions concernant les wikis
//require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->dirroot . '/mod/wiki/pagelib.php');

/**
 *
 * Class that models the behavior of wiki's history page
 *
 */
class hpage_wiki_history extends page_wiki {
    /**
     * @var int $paging current page
     */
    private $paging;

    /**
     * @var int @rowsperpage Items per page
     */
    private $rowsperpage = 10;

    /**
     * @var int $allversion if $allversion != 0, all versions will be printed in a signle table
     */
    private $allversion;

    function __construct($wiki, $subwiki, $cm) {
        global $PAGE;
        parent::__construct($wiki, $subwiki, $cm);
        $PAGE->requires->js_init_call('M.mod_wiki.history', null, true);
    }

    /*function print_header() {
        parent::print_header();
        $this->print_pagetitle();
    }*/

    /*function print_pagetitle() {
        global $OUTPUT;
        $html = '';

        $html .= $OUTPUT->container_start('wiki_headingtitle');
        $html .= $OUTPUT->heading_with_help(format_string($this->title), 'history', 'wiki', '', '', 3);
        $html .= $OUTPUT->container_end();
        echo $html;
    }*/

    function print_content() {      

        require_capability('mod/wiki:viewpage', $this->modcontext, NULL, true, 'noviewpagepermission', 'wiki');

        $this->print_history_content();
    }

    function set_url() {
        global $PAGE, $CFG;
        $PAGE->set_url($CFG->wwwroot . '/mod/wiki/history.php', array('pageid' => $this->page->id));
    }

    function set_paging($paging) {
        $this->paging = $paging;
    }

    function set_allversion($allversion) {
        $this->allversion = $allversion;
    }

    protected function create_navbar() {
        global $PAGE;

        parent::create_navbar();
        $PAGE->navbar->add(get_string('history', 'wiki'));
    }

    /**
     * Prints the history for a given wiki page
     *
     * @global object $CFG
     * @global object $OUTPUT
     * @global object $PAGE
     */
    private function print_history_content() {
        global $CFG, $OUTPUT;

        $pageid = $this->page->id;
        $offset = $this->paging * $this->rowsperpage;
        // vcount is the latest version
        $vcount = wiki_count_wiki_page_versions($pageid) - 1;
        $versions = wiki_get_wiki_page_versions($pageid, 0, $vcount);
        
        
        
        // We don't want version 0 to be displayed
        // version 0 is blank page
        if (end($versions)->version == 0) {
            array_pop($versions);
        }

        $contents = array();

        $version0page = wiki_get_wiki_page_version($this->page->id, 0);
        $creator = wiki_get_user_info($version0page->userid);
        $a = new StdClass;
        $a->date = userdate($this->page->timecreated, get_string('strftimedaydatetime', 'langconfig'));
        $a->username = fullname($creator);
        echo html_writer::tag ('div', get_string('createddate', 'wiki', $a), array('class' => 'wiki_headingtime'));
        if ($vcount > 0) {

            /// If there is only one version, we don't need radios nor forms
            if (count($versions) == 1) {

                $row = array_shift($versions);

                $username = wiki_get_user_info($row->userid);
                $picture = $OUTPUT->user_picture($username);
                $date = userdate($row->timecreated, get_string('strftimedate', 'langconfig'));
                $time = userdate($row->timecreated, get_string('strftimetime', 'langconfig'));
                $versionid = wiki_get_version($row->id);
                $versionlink = new moodle_url('/mod/wiki/viewversion.php', array('pageid' => $pageid, 'versionid' => $versionid->id));
                $userlink = new moodle_url('/user/view.php', array('id' => $username->id, 'course' => $this->cm->course));
                $contents[] = array('', html_writer::link($versionlink->out(false), $row->version), $picture . html_writer::link($userlink->out(false), fullname($username)), $time, $OUTPUT->container($date, 'wiki_histdate'));

                $table = new html_table();
                $table->head = array('', get_string('version'), get_string('user'), get_string('modified'), '');
                $table->data = $contents;
                $table->attributes['class'] = 'mdl-align';

                echo html_writer::table($table);

            } else {

                $checked = $vcount - $offset;
                $rowclass = array();

                foreach ($versions as $version) {
                    $user = wiki_get_user_info($version->userid);
                    $picture = $OUTPUT->user_picture($user, array('popup' => true));
                    $date = userdate($version->timecreated, get_string('strftimedate'));
                    $rowclass[] = 'wiki_histnewdate';
                    $time = userdate($version->timecreated, get_string('strftimetime', 'langconfig'));
                    $versionid = wiki_get_version($version->id);
                    if ($versionid) {
                        $url = new moodle_url('/mod/wiki/viewversion.php', array('pageid' => $pageid, 'versionid' => $versionid->id));
                        $viewlink = html_writer::link($url->out(false), $version->version);
                    } else {
                        $viewlink = $version->version;
                    }
                    $userlink = new moodle_url('/user/view.php', array('id' => $version->userid, 'course' => $this->cm->course));
                    $contents[] = array($this->choose_from_radio(array($version->version  => null), 'compare', 'M.mod_wiki.history()', $checked - 1, true) . $this->choose_from_radio(array($version->version  => null), 'comparewith', 'M.mod_wiki.history()', $checked, true), $viewlink, $picture . html_writer::link($userlink->out(false), fullname($user)), $time, $OUTPUT->container($date, 'wiki_histdate'));
                }

                $table = new html_table();

                $icon = $OUTPUT->help_icon('diff', 'wiki');

                $table->head = array(get_string('diff', 'wiki') . $icon, get_string('version'), get_string('user'), get_string('modified'), '');
                $table->data = $contents;
                $table->attributes['class'] = 'generaltable mdl-align';
                $table->rowclasses = $rowclass;

                // Print the form.
                echo html_writer::start_tag('form', array('action'=>new moodle_url('/mod/wiki/diff.php'), 'method'=>'get', 'id'=>'diff'));
                echo html_writer::tag('div', html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'pageid', 'value'=>$pageid)));
                echo html_writer::table($table);
                echo html_writer::start_tag('div', array('class'=>'mdl-align'));
                echo html_writer::empty_tag('input', array('type'=>'submit', 'class'=>'wiki_form-button', 'value'=>get_string('comparesel', 'wiki')));
                echo html_writer::end_tag('div');
                echo html_writer::end_tag('form');
            }
        } else {
            print_string('nohistory', 'wiki');
        }
        if (!$this->allversion) {
            //$pagingbar = moodle_paging_bar::make($vcount, $this->paging, $this->rowsperpage, $CFG->wwwroot.'/mod/wiki/history.php?pageid='.$pageid.'&amp;');
            // $pagingbar->pagevar = $pagevar;
            
            //echo $OUTPUT->paging_bar($vcount, $this->paging, $this->rowsperpage, $CFG->wwwroot . '/mod/wiki/history.php?pageid=' . $pageid . '&amp;');
            
            //print_paging_bar($vcount, $paging, $rowsperpage,$CFG->wwwroot.'/mod/wiki/history.php?pageid='.$pageid.'&amp;','paging');
        } else {
            $link = new moodle_url('/mod/wiki/history.php', array('pageid' => $pageid));
            $OUTPUT->container(html_writer::link($link->out(false), get_string('viewperpage', 'wiki', $this->rowsperpage)), 'mdl-align');
        }
        if ($vcount > $this->rowsperpage && !$this->allversion) {
            $link = new moodle_url('/mod/wiki/history.php', array('pageid' => $pageid, 'allversion' => 1));
            $OUTPUT->container(html_writer::link($link->out(false), get_string('viewallhistory', 'wiki')), 'mdl-align');
        }
    }

    /**
     * Given an array of values, creates a group of radio buttons to be part of a form
     *
     * @param array  $options  An array of value-label pairs for the radio group (values as keys).
     * @param string $name     Name of the radiogroup (unique in the form).
     * @param string $onclick  Function to be executed when the radios are clicked.
     * @param string $checked  The value that is already checked.
     * @param bool   $return   If true, return the HTML as a string, otherwise print it.
     *
     * @return mixed If $return is false, returns nothing, otherwise returns a string of HTML.
     */
    private function choose_from_radio($options, $name, $onclick = '', $checked = '', $return = false) {

        static $idcounter = 0;

        if (!$name) {
            $name = 'unnamed';
        }

        $output = '<span class="radiogroup ' . $name . "\">\n";

        if (!empty($options)) {
            $currentradio = 0;
            foreach ($options as $value => $label) {
                $htmlid = 'auto-rb' . sprintf('%04d', ++$idcounter);
                $output .= ' <span class="radioelement ' . $name . ' rb' . $currentradio . "\">";
                $output .= '<input name="' . $name . '" id="' . $htmlid . '" type="radio" value="' . $value . '"';
                if ($value == $checked) {
                    $output .= ' checked="checked"';
                }
                if ($onclick) {
                    $output .= ' onclick="' . $onclick . '"';
                }
                if ($label === '') {
                    $output .= ' /> <label for="' . $htmlid . '">' . $value . '</label></span>' . "\n";
                } else {
                    $output .= ' /> <label for="' . $htmlid . '">' . $label . '</label></span>' . "\n";
                }
                $currentradio = ($currentradio + 1) % 2;
            }
        }

        $output .= '</span>' . "\n";

        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
    
    /**
     * Prints the history for a given wiki page
     *
     * @global object $CFG
     * @global object $OUTPUT     
     */
    private function print_historic_content() {
        global $CFG, $OUTPUT;

        echo '1<br/>';
        
        require_capability('mod/wiki:viewpage', $this->modcontext, NULL, true, 'noviewpagepermission', 'wiki');
        
        echo '2<br/>';
        
        $pageid = $this->page->id;
        $offset = $this->paging * $this->rowsperpage;
        // vcount is the latest version
        $vcount = wiki_count_wiki_page_versions($pageid) - 1;
        if ($this->allversion) {
            $versions = wiki_get_wiki_page_versions($pageid, 0, $vcount);
        } else {
            $versions = wiki_get_wiki_page_versions($pageid, $offset, $this->rowsperpage);
        }
        // We don't want version 0 to be displayed
        // version 0 is blank page
        if (end($versions)->version == 0) {
            array_pop($versions);
        }

        $contents = array();

        $version0page = wiki_get_wiki_page_version($this->page->id, 0);
        $creator = wiki_get_user_info($version0page->userid);
        $a = new StdClass;
        $a->date = userdate($this->page->timecreated, get_string('strftimedaydatetime', 'langconfig'));
        $a->username = fullname($creator);
        echo html_writer::tag ('div', get_string('createddate', 'wiki', $a), array('class' => 'wiki_headingtime'));
        if ($vcount > 0) {

            /// If there is only one version, we don't need radios nor forms
            if (count($versions) == 1) {

                $row = array_shift($versions);

                $username = wiki_get_user_info($row->userid);
                $picture = $OUTPUT->user_picture($username);
                $date = userdate($row->timecreated, get_string('strftimedate', 'langconfig'));
                $time = userdate($row->timecreated, get_string('strftimetime', 'langconfig'));
                $versionid = wiki_get_version($row->id);
                $versionlink = new moodle_url('/mod/wiki/viewversion.php', array('pageid' => $pageid, 'versionid' => $versionid->id));
                $userlink = new moodle_url('/user/view.php', array('id' => $username->id, 'course' => $this->cm->course));
                $contents[] = array('', html_writer::link($versionlink->out(false), $row->version), $picture . html_writer::link($userlink->out(false), fullname($username)), $time, $OUTPUT->container($date, 'wiki_histdate'));

                $table = new html_table();
                $table->head = array('', get_string('version'), get_string('user'), get_string('modified'), '');
                $table->data = $contents;
                $table->attributes['class'] = 'mdl-align';

                echo html_writer::table($table);

            } else {

                $checked = $vcount - $offset;
                $rowclass = array();

                foreach ($versions as $version) {
                    $user = wiki_get_user_info($version->userid);
                    $picture = $OUTPUT->user_picture($user, array('popup' => true));
                    $date = userdate($version->timecreated, get_string('strftimedate'));
                    $rowclass[] = 'wiki_histnewdate';
                    $time = userdate($version->timecreated, get_string('strftimetime', 'langconfig'));
                    $versionid = wiki_get_version($version->id);
                    if ($versionid) {
                        $url = new moodle_url('/mod/wiki/viewversion.php', array('pageid' => $pageid, 'versionid' => $versionid->id));
                        $viewlink = html_writer::link($url->out(false), $version->version);
                    } else {
                        $viewlink = $version->version;
                    }
                    $userlink = new moodle_url('/user/view.php', array('id' => $version->userid, 'course' => $this->cm->course));
                    $contents[] = array($this->choose_from_radio(array($version->version  => null), 'compare', 'M.mod_wiki.history()', $checked - 1, true) . $this->choose_from_radio(array($version->version  => null), 'comparewith', 'M.mod_wiki.history()', $checked, true), $viewlink, $picture . html_writer::link($userlink->out(false), fullname($user)), $time, $OUTPUT->container($date, 'wiki_histdate'));
                }

                $table = new html_table();

                $icon = $OUTPUT->help_icon('diff', 'wiki');

                $table->head = array(get_string('diff', 'wiki') . $icon, get_string('version'), get_string('user'), get_string('modified'), '');
                $table->data = $contents;
                $table->attributes['class'] = 'generaltable mdl-align';
                $table->rowclasses = $rowclass;

                // Print the form.
                echo html_writer::start_tag('form', array('action'=>new moodle_url('/mod/wiki/diff.php'), 'method'=>'get', 'id'=>'diff'));
                echo html_writer::tag('div', html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'pageid', 'value'=>$pageid)));
                echo html_writer::table($table);
                echo html_writer::start_tag('div', array('class'=>'mdl-align'));
                echo html_writer::empty_tag('input', array('type'=>'submit', 'class'=>'wiki_form-button', 'value'=>get_string('comparesel', 'wiki')));
                echo html_writer::end_tag('div');
                echo html_writer::end_tag('form');
            }
        } else {
            print_string('nohistory', 'wiki');
        }
        if (!$this->allversion) {
            //$pagingbar = moodle_paging_bar::make($vcount, $this->paging, $this->rowsperpage, $CFG->wwwroot.'/mod/wiki/history.php?pageid='.$pageid.'&amp;');
            // $pagingbar->pagevar = $pagevar;
            echo $OUTPUT->paging_bar($vcount, $this->paging, $this->rowsperpage, $CFG->wwwroot . '/mod/wiki/history.php?pageid=' . $pageid . '&amp;');
            //print_paging_bar($vcount, $paging, $rowsperpage,$CFG->wwwroot.'/mod/wiki/history.php?pageid='.$pageid.'&amp;','paging');
            } else {
            $link = new moodle_url('/mod/wiki/history.php', array('pageid' => $pageid));
            $OUTPUT->container(html_writer::link($link->out(false), get_string('viewperpage', 'wiki', $this->rowsperpage)), 'mdl-align');
        }
        if ($vcount > $this->rowsperpage && !$this->allversion) {
            $link = new moodle_url('/mod/wiki/history.php', array('pageid' => $pageid, 'allversion' => 1));
            $OUTPUT->container(html_writer::link($link->out(false), get_string('viewallhistory', 'wiki')), 'mdl-align');
        }
    }
    
    
    
    
}



//--------------------"MAIN"--------------------------




$id = required_param('id', PARAM_INT);                  // id du cours actuel

$search = trim(optional_param('search', '', PARAM_NOTAGS));  // search string
$page = optional_param('page', 0, PARAM_INT);   // which page to show
$perpage = optional_param('perpage', 10, PARAM_INT);   // how many per page
$showform = optional_param('showform', 0, PARAM_INT);   // Just show the form

$user    = trim(optional_param('user', '', PARAM_NOTAGS));    // auteur à rechercher

$tools = trim(optional_param('tools', 0, PARAM_ALPHA));      // outil(s) dans le(s)quel(s) faire la recherche
//$tools = $_GET['tools'];

/*
$userid  = trim(optional_param('userid', 0, PARAM_INT));      // UserID to search for
$forumid = trim(optional_param('forumid', 0, PARAM_INT));      // ForumID to search for
$subject = trim(optional_param('subject', '', PARAM_NOTAGS)); // Subject
$phrase  = trim(optional_param('phrase', '', PARAM_NOTAGS));  // Phrase
*/

$words   = trim(optional_param('words', '', PARAM_NOTAGS));   // contenu à rechercher

/*
$fullwords = trim(optional_param('fullwords', '', PARAM_NOTAGS)); // Whole words
$notwords = trim(optional_param('notwords', '', PARAM_NOTAGS));   // Words we don't want*/

//Recherche à partir de
$timefromrestrict = optional_param('timefromrestrict', 0, PARAM_INT); 
$fromday = optional_param('fromday', 0, PARAM_INT);      
$frommonth = optional_param('frommonth', 0, PARAM_INT);    
$fromyear = optional_param('fromyear', 0, PARAM_INT);      
$fromhour = optional_param('fromhour', 0, PARAM_INT);      
$fromminute = optional_param('fromminute', 0, PARAM_INT);      
if ($timefromrestrict) {
    $datefrom = make_timestamp($fromyear, $frommonth, $fromday, $fromhour, $fromminute);
} else {
    $datefrom = optional_param('datefrom', 0, PARAM_INT);      // Starting date
}

//Recherche jusqu'à
$timetorestrict = optional_param('timetorestrict', 0, PARAM_INT); // Use ending date
$today = optional_param('today', 0, PARAM_INT);      // Ending date
$tomonth = optional_param('tomonth', 0, PARAM_INT);      // Ending date
$toyear = optional_param('toyear', 0, PARAM_INT);      // Ending date
$tohour = optional_param('tohour', 0, PARAM_INT);      // Ending date
$tominute = optional_param('tominute', 0, PARAM_INT);      // Ending date
if ($timetorestrict) {
    $dateto = make_timestamp($toyear, $tomonth, $today, $tohour, $tominute);
} else {
    $dateto = optional_param('dateto', 0, PARAM_INT);      // Ending date
}


$PAGE->set_pagelayout('standard');
$PAGE->set_url($FULLME); //TODO: this is very sloppy --skodak

if (empty($search)) {   // Check the other parameters instead
    if (!empty($words)) {
        $search .= ' '.$words;
    }
    if (!empty($user)) {
        $search .= ' '.forum_clean_search_terms($user, 'user:');
    }
    if (!empty($datefrom)) {
        $search .= ' datefrom:'.$datefrom;
    }
    if (!empty($dateto)) {
        $search .= ' dateto:'.$dateto;
    }
    $individualparams = true;
} else {
    $individualparams = false;
}
if ($search) {
    $search = forum_clean_search_terms($search);
}


if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

require_course_login($course);

add_to_log($course->id, "course", "historique", "historic.php?id=$course->id&amp;search=".urlencode($search), $search);

$strforums = get_string("modulenameplural", "forum");
$strsearch = "Historique - Recherche";
$strsearchresults = get_string("searchresults", "forum");
$strpage = get_string("page");

if (!$search || $showform) {
    $PAGE->navbar->add($strforums, new moodle_url('/mod/forum/index.php', array('id'=>$course->id)));
    $PAGE->navbar->add(get_string('advancedsearch', 'forum'));

    $PAGE->set_title("$strsearch");
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    
    historic_print_big_search_form($course);

    echo $OUTPUT->footer();
    exit;
}

/// We need to do a search now and print results

$searchterms = str_replace('forumid:', 'instance:', $search);
$searchterms = explode(' ', $searchterms);

$PAGE->navbar->add($strsearch, new moodle_url('/mod/forum/historic.php', array('id'=>$course->id)));
$PAGE->navbar->add($strsearchresults);

$posts = forum_search_posts($searchterms, $course->id, $page*$perpage, $perpage, $totalcount);


/*if (!$posts) {
    $PAGE->set_title($strsearchresults);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strforums, 2);
    echo $OUTPUT->heading($strsearchresults, 3);
    echo $OUTPUT->heading(get_string("noposts", "forum"), 4);

    if (!$individualparams) {
        $words = $search;
    }

    historic_print_big_search_form($course);

    echo $OUTPUT->footer();
    exit;
}*/

//including this here to prevent it being included if there are no search results
require_once($CFG->dirroot.'/rating/lib.php');

//set up the ratings information that will be the same for all posts
$ratingoptions = new stdClass();
$ratingoptions->component = 'mod_forum';
$ratingoptions->ratingarea = 'post';
$ratingoptions->userid = $USER->id;
$ratingoptions->returnurl = $PAGE->url->out(false);
$rm = new rating_manager();

$PAGE->set_title($strsearchresults);
$PAGE->set_heading($course->fullname);
//$PAGE->set_button($searchform);
echo $OUTPUT->header();
echo '<div class="reportlink">';
    echo '<a href="historic.php?id='.$course->id;
    if (isset($user)) {
        echo '&amp;user='.urlencode($user);
    }
    if (isset($userid)) {
        echo '&amp;userid='.$userid;
    }
    if (isset($forumid)) {
        echo '&amp;forumid='.$forumid;
    }
    if (isset($subject)) {
        echo '&amp;subject='.urlencode($subject);
    }
    if (isset($phrase)) {
        echo '&amp;phrase='.urlencode($phrase);
    }
    if (isset($words)) {
        echo '&amp;words='.urlencode($words);
    }
    if (isset($fullwords)) {
        echo '&amp;fullwords='.urlencode($fullwords);
    }
    if (isset($notwords)) {
        echo '&amp;notwords='.urlencode($notwords);
    }
    if (isset($dateto)) {
        echo '&amp;dateto='.$dateto;
    }
    if (isset($datefrom)) {
        echo '&amp;datefrom='.$datefrom;
    }
    echo '&amp;showform=1">Autre recherche...</a>';
echo '</div>';


//RECHERCHE DANS LES FORUMS
if (($tools == 'forums')||($tools == 'tous')) {
    $forumsHtml = searchforums($search, $posts);        
    echo $forumsHtml;
    
    $envoieforums = htmlspecialchars($forumsHtml);
    
    echo '<form enctype="multipart/form-data" action="downloadpdf.php" method="post">
            <fieldset><input name="tool" type="hidden" value="'.$envoieforums.'" />
            <input type="submit" value="Exporter PDF"/></fieldset>
          </form>';
    
}

//RECHERCHE DANS LES CHATS
if (($tools == 'chats')||($tools == 'tous')) {
  
    $chatsHtml = searchchats($id, $user, $words, $timefromrestrict, $datefrom, $timetorestrict, $dateto);
    echo $chatsHtml;
    
    $envoiechats = htmlspecialchars($chatsHtml);
    
    echo '<form enctype="multipart/form-data" action="downloadpdf.php" method="post">
            <fieldset><input name="tool" type="hidden" value="'.$envoiechats.'" />
            <input type="submit" value="Exporter PDF"/></fieldset>
          </form>';    
}

//RECHERCHE DANS LES WIKIS
if (($tools == 'wikis')||($tools == 'tous')) searchwikis($id, $user, $words, $timefromrestrict, $datefrom, $timetorestrict, $dateto);





function searchforums($search, $posts) {
    global $OUTPUT, $DB;
    
    $forumcss = ".forumpost {display: block;position:relative;margin:0 0 1em 0;padding:0;border:1px solid #000;max-width:100%;}
            .forumpost .row {width:100%;position:relative;}
            .forumpost .row .left {float:left;width: 43px;overflow:hidden;}
            .forumpost .row .left .grouppictures a {text-align:center;display:block;margin:6px 2px 0 2px;}
            .forumpost .row .left .grouppicture {width:20px;height:20px;}
            .forumpost .row .topic,
            .forumpost .row .content-mask,
            .forumpost .row .options {margin-left:43px;}
            .forumpost .picture img {margin:4px;}
            .forumpost .options .commands,
            .forumpost .content .attachments,
            .forumpost .options .footer,
            .forumpost .options .link {text-align:right;}
            .forumpost .options .forum-post-rating {float:left;}
            .forumpost .content .posting {overflow:auto;max-width:100%;}
            .forumpost .content .attachedimages img {max-width:100%;}
            .forumpost .post-word-count { font-size: .85em; font-style: italic; }
            .forumpost .shortenedpost .post-word-count { display: inline; padding: 0 .3em; }
            .dir-rtl .forumpost .row .topic,
            .dir-rtl .forumpost .row .content-mask,
            .dir-rtl .forumpost .row .options {margin-right:43px;margin-left:0;}
            .dir-rtl .forumpost .row .left {float:right;}
            .dir-rtl.path-mod-forum .indent {margin-right:30px;margin-left:0;}

            .path-mod-forum .forumolddiscuss,
            #page-mod-forum-search .c0 {text-align:right;}
            .path-mod-forum .indent {margin-left: 30px;}
            .path-mod-forum .forumheaderlist {width: 100%;border-width:1px;border-style:solid;border-collapse:separate;margin-top: 10px;}
            .path-mod-forum .forumheaderlist td {border-width:1px 0px 0px 1px;border-style:solid;}
            .path-mod-forum .forumheaderlist th.header.replies .iconsmall { margin: 0 .3em;}
            .path-mod-forum .forumheaderlist .picture {width: 35px;}
            .path-mod-forum .forumheaderlist .discussion .starter {vertical-align: middle;}
            .path-mod-forum .forumheaderlist .discussion .lastpost {white-space: nowrap;text-align: right;}
            .path-mod-forum .forumheaderlist .replies,
            .path-mod-forum .forumheaderlist .discussion .author {white-space: nowrap;}

            /** Styles for subscribers.php */
            #page-mod-forum-subscribers .subscriberdiv,
            #page-mod-forum-subscribers .subscribertable {width:100%;vertical-align:top;}
            #page-mod-forum-subscribers .subscribertable tr td {vertical-align:top;}
            #page-mod-forum-subscribers .subscribertable tr td.actions {width:16%;padding-top:3em;}
            #page-mod-forum-subscribers .subscribertable tr td.actions .actionbutton {margin:0.3em 0;padding:0.5em 0;width:100%;}
            #page-mod-forum-subscribers .subscribertable tr td.existing,
            #page-mod-forum-subscribers .subscribertable tr td.potential {width:42%;}

            /** Styles for discuss.php **/
            #page-mod-forum-discuss .discussioncontrols {width:100%;margin:5px;}
            #page-mod-forum-discuss .discussioncontrols .discussioncontrol {width:33%;float:left;}
            #page-mod-forum-discuss .discussioncontrol.exporttoportfolio {text-align:left;}
            #page-mod-forum-discuss .discussioncontrol.displaymode {text-align:center;}
            #page-mod-forum-discuss .discussioncontrol.movediscussion {float:right;width:auto;text-align:right;padding-right:10px;}
            #page-mod-forum-discuss .discussioncontrol.movediscussion .movediscussionoption {}

            /** Styles for view.php **/
            #page-mod-forum-view .forumaddnew {margin-bottom: 20px;}
            #page-mod-forum-view .groupmenu {float: left;text-align:left;white-space: nowrap;}
            #page-mod-forum-index .subscription,
            #page-mod-forum-view .subscription {float: right;text-align:right;white-space: nowrap;margin: 5px 0;}

            /** Styles for search.php */
            #page-mod-forum-search .introcontent {padding: 15px;font-weight:bold;}
            #page-mod-forum-index .unread a:first-child,
            #page-mod-forum-view .unread a:first-child {padding-right: 10px;}
            #page-mod-forum-index .unread img,
            #page-mod-forum-view .unread img {margin-left: 5px;}
            #page-mod-forum-view .unread img {margin-left: 5px;}
            .dir-rtl#page-mod-forum-view .unread img {margin-right: 5px; margin-left: 0; }

            /** Unknown Styles ??? */
            #email .unsubscribelink {margin-top:20px;}

            /* Forumpost unread
            -------------------------*/
            #page-mod-forum-view .unread,
            .forumpost.unread .row.header,
            .path-course-view .unread,
            span.unread {
                background-color: #FFD;
            }
            .forumpost.unread .row.header {
                border-bottom: 1px solid #DDD;
            }";
    
    
    
    $forumsHtml = '<style type="text/css">'.$forumcss.'</style><div id="forumresults">'.$OUTPUT->heading("Forums", 1);
           
    //$url = new moodle_url('historic.php', array('search' => $search, 'id' => $course->id, 'perpage' => $perpage));
    
    //added to implement highlighting of search terms found only in HTML markup
    //fiedorow - 9/2/2005
    $strippedsearch = str_replace('user:','',$search);
    $strippedsearch = str_replace('subject:','',$strippedsearch);
    $strippedsearch = str_replace('&quot;','',$strippedsearch);
    $searchterms = explode(' ', $strippedsearch);    // Search for words independently
    foreach ($searchterms as $key => $searchterm) {
        
        if (preg_match('/^\-/',$searchterm)) {
            unset($searchterms[$key]);
        } else {
            $searchterms[$key] = preg_replace('/^\+/','',$searchterm);
        }
    }
    $strippedsearch = implode(' ', $searchterms);    // Rebuild the string

    foreach ($posts as $post) {

        // Replace the simple subject with the three items forum name -> thread name -> subject
        // (if all three are appropriate) each as a link.
        if (! $discussion = $DB->get_record('forum_discussions', array('id' => $post->discussion))) {
            print_error('invaliddiscussionid', 'forum');
        }
        if (! $forum = $DB->get_record('forum', array('id' => "$discussion->forum"))) {
            print_error('invalidforumid', 'forum');
        }

        if (!$cm = get_coursemodule_from_instance('forum', $forum->id)) {
            print_error('invalidcoursemodule');
        }

        $post->subject = highlight($strippedsearch, $post->subject);
        $discussion->name = highlight($strippedsearch, $discussion->name);

        $fullsubject = "<a href=\"view.php?f=$forum->id\">".format_string($forum->name,true)."</a>";
        if ($forum->type != 'single') {
            $fullsubject .= " -> <a href=\"discuss.php?d=$discussion->id\">".format_string($discussion->name,true)."</a>";
            if ($post->parent != 0) {
                $fullsubject .= " -> <a href=\"discuss.php?d=$post->discussion&amp;parent=$post->id\">".format_string($post->subject,true)."</a>";
            }
        }

        $post->subject = $fullsubject;
        $post->subjectnoformat = true;

        //add the ratings information to the post
        //Unfortunately seem to have do this individually as posts may be from different forums
        if ($forum->assessed != RATING_AGGREGATE_NONE) {
            $modcontext = context_module::instance($cm->id);
            $ratingoptions->context = $modcontext;
            $ratingoptions->items = array($post);
            $ratingoptions->aggregate = $forum->assessed;//the aggregation method
            $ratingoptions->scaleid = $forum->scale;
            $ratingoptions->assesstimestart = $forum->assesstimestart;
            $ratingoptions->assesstimefinish = $forum->assesstimefinish;
            $postswithratings = $rm->get_ratings($ratingoptions);

            if ($postswithratings && count($postswithratings)==1) {
                $post = $postswithratings[0];
            }
        }

        // Identify search terms only found in HTML markup, and add a warning about them to
        // the start of the message text. However, do not do the highlighting here. forum_print_post
        // will do it for us later.
        $missing_terms = "";

        $options = new stdClass();
        $options->trusted = $post->messagetrust;
        $post->message = highlight($strippedsearch,
                        format_text($post->message, $post->messageformat, $options, $course->id),
                        0, '<fgw9sdpq4>', '</fgw9sdpq4>');

        foreach ($searchterms as $searchterm) {
            if (preg_match("/$searchterm/i",$post->message) && !preg_match('/<fgw9sdpq4>'.$searchterm.'<\/fgw9sdpq4>/i',$post->message)) {
                $missing_terms .= " $searchterm";
            }
        }

        $post->message = str_replace('<fgw9sdpq4>', '<span class="highlight">', $post->message);
        $post->message = str_replace('</fgw9sdpq4>', '</span>', $post->message);

        if ($missing_terms) {
            $strmissingsearchterms = get_string('missingsearchterms','forum');
            $post->message = '<p class="highlight2">'.$strmissingsearchterms.' '.$missing_terms.'</p>'.$post->message;
        }

        // Prepare a link to the post in context, to be displayed after the forum post.
        $fulllink = "<a href=\"discuss.php?d=$post->discussion#p$post->id\">".get_string("postincontext", "forum")."</a>";       
        
        // Now print the post.
        $forumsHtml .= forum_print_post($post, $discussion, $forum, $cm, $course, false, false, false,
                $fulllink, '', -99, false, null, true);
    }

    $forumsHtml .= '</div>';
    
    return $forumsHtml;
}


function searchchats($id, $user, $words, $timefromrestrict, $datefrom, $timetorestrict, $dateto) {
    
    global $OUTPUT, $DB, $USER, $CFG;
    
    $chatcss = ".path-mod-chat .chat-event .picture,
                .path-mod-chat .chat-message .picture {width:40px;}
                .path-mod-chat .chat-event .text {text-align: left;}
                .path-mod-chat #messages-list,
                .path-mod-chat #users-list {list-style-type:none;padding:0;margin:0}
                .path-mod-chat #chat-header {overflow: hidden;}
                .path-mod-chat #chat-input-area table.generaltable td.cell {padding:1px;}

                /** shrink the text box so the theme link is always accessible */
                @media all and (max-device-width: 320px) {
                    .path-mod-chat #input-message {width: 150px;}
                }
                @media all and (min-device-width: 321px) and (max-device-width: 640px) {
                    .path-mod-chat #input-message {width: 175px;}
                }

                /** styles for view.php **/
                #page-mod-chat-view .chatcurrentusers .chatuserdetails {vertical-align: middle;}

                /** Styles for basic chat **/
                #page-mod-chat-gui_basic #participants ul {margin:0;padding:0;list-style-type:none;}
                #page-mod-chat-gui_basic #participants ul li {list-style-type:none;display:inline;margin-right:10px;}
                #page-mod-chat-gui_basic #participants ul li .userinfo {display:inline;}
                #page-mod-chat-gui_basic #messages {padding:0;margin:0}
                #page-mod-chat-gui_basic #messages dl {padding:0;margin:6px 0}
                #page-mod-chat-gui_basic #messages dt {margin-left:0;margin-right:5px;padding:0;display:inline;}
                #page-mod-chat-gui_basic #messages dd {padding:0;margin:0}

                /** Styles for header **/
                #page-mod-chat-gui_header_js-jsupdate .chat-event,
                #page-mod-chat-gui_header_js-jsupdate .chat-message {width:100%;}

                /** YUI Overrides **/
                .path-mod-chat .yui-layout-unit-top {background: #FFE39D;}
                .path-mod-chat .yui-layout-unit-right {background: #FFD46B;}
                .path-mod-chat .yui-layout-unit-bottom {background: #FFCB44;}
                .path-mod-chat .yui-layout .yui-layout-hd {border:0;}
                .path-mod-chat .yui-layout .yui-layout-unit div.yui-layout-bd {border:0;background: transparent;}
                .path-mod-chat .yui-layout .yui-layout-unit div.yui-layout-unit-right {background: white;}";
    
      
    //echo $OUTPUT->heading("Chats", 1);

    $chatsHtml = '<style type="text/css">'.$chatcss.'</style>'.$OUTPUT->heading("Chats", 1);
    
    
    
    //Recherche tous les chats auxquels cet utilisateur a participé (dans ce cours)   
    $sql = "SELECT distinct c.id, c.name, c.chattime FROM mdl_chat c, mdl_chat_messages m WHERE c.course = $id AND c.id = m.chatid AND m.userid = $USER->id";

    $mychats = $DB->get_records_sql($sql, null);
    unset($sql);
    

    //Pour chacun de ces chats 
    foreach ($mychats as $onechat) {    
      //On récupère l'objet représentant ce chat.
      $onechatobject = get_coursemodule_from_instance("chat", $onechat->id);

      //Titre du chat avec lien
      $chatsHtml .= $OUTPUT->heading('<a href="'.$CFG->wwwroot.'/mod/chat/report.php?id='.$onechatobject->id.'&show_all=1">'.$onechat->name.'</a>', 3);       

      // On regarde si ce chat utilise des groupes.      
      $currentgroup = groups_get_activity_group($onechatobject, true);

      // If the user is allocated to a group, only show messages from people
      // in the same group, or no group
      if ($currentgroup) {
        $groupselect = " AND (groupid = $currentgroup OR groupid = 0)";
      } else {
        $groupselect = "";
      }

      //Auteur à rechercher        
      if(empty($user)) {
        $userselect = "";    
      } else {
        $sql = "SELECT id FROM mdl_user WHERE firstname = '$user' OR lastname = '$user'"  ;
        $authorid = $DB->get_record_sql($sql)->id;
        $userselect = " AND userid = $authorid";
      }

      //Texte à rechercher   
      if(empty($words)) {
        $wordselect = "";    
      } else {
        $wordselect = " AND message LIKE '%$words%'";
      }

      // Intervalle de temps pour les messages
      if ($timefromrestrict == 1) {
          $start = $datefrom;
      } else {
          $start = $onechat->chattime;
      }

      if ($timetorestrict == 1) {
          $end = $dateto;
      } else {
          $end = time();
      } 

      $sql = "SELECT * "
              . "FROM mdl_chat_messages "
              . "WHERE chatid = $onechat->id "
              . "AND timestamp >= $start "
              . "AND timestamp <= $end"
              .$userselect.$wordselect.$groupselect
              ." ORDER BY timestamp ASC";

      
          
      
      
      $messages = $DB->get_records_sql($sql, null);
      unset($sql);        


      

      if (!messages) {
        $chatsHtml .= $OUTPUT->heading(get_string('nomessages', 'chat'));
      } else {
        $chatsHtml .= '<p class="boxaligncenter">'.userdate($start).' --> '. userdate($end).'</p>';
        $chatsHtml .= $OUTPUT->box_start('center');
        $participates = array();

        foreach ($messages as $message) {  // We are walking FORWARDS through messages
            if (!isset($participates[$message->userid])) {
                $participates[$message->userid] = true;
            }
            $formatmessage = historic_chat_format_message($onechatobject, $message, $course->id, $USER);
            if (isset($formatmessage->html)) {
                $chatsHtml .= $formatmessage->html;                
            }
        }

        $chatsHtml .= $OUTPUT->box_end();
      }

    }
    
    return $chatsHtml;
}



function searchwikis($id, $user, $words, $timefromrestrict, $datefrom, $timetorestrict, $dateto) {
    //$id est l'identifiant du cours actuel
    global $OUTPUT, $USER, $DB, $CFG;
    
    echo $OUTPUT->heading("Modifications apportées aux wikis", 1);
    
    
    //Auteur à rechercher  
    if(empty($user)) {
      $userselect = "";    
    } else {
      $userselect = "AND v2.userid = u.id AND (u.firstname = '$user' OR u.lastname = '$user') ";
    }

    //Texte à rechercher   
    if(empty($words)) {
      $wordselect = "";    
    } else {
      $wordselect = "AND v2.content LIKE '%$words%' ";
    }

    // Intervalle de temps pour les messages
    if ($timefromrestrict == 1) {
        $fromselect = "AND v2.timecreated >= '$datefrom' ";
    } else {
        $fromselect = "";
    }

    if ($timetorestrict == 1) {
        $toselect = "AND v2.timecreated <= '$dateto' ";
    } else {
        $toselect = "";
    } 
    
    //Recherche toutes les pages de wiki auxquelles cet utilisateur a participé (dans ce cours)   
    $sql = "SELECT distinct p.id, p.title, w.id AS wid, w.name "
            . "FROM mdl_wiki w, mdl_wiki_subwikis s, mdl_wiki_pages p, mdl_wiki_versions v1, mdl_wiki_versions v2, mdl_user u "
            . "WHERE w.course = $id "
            . "AND w.id = s.wikiid "
            . "AND s.id = p.subwikiid "
            . "AND p.id = v1.pageid "
            . "AND v1.userid = $USER->id "
            . "AND p.id = v2.pageid "
            .$userselect .$wordselect.$fromselect.$toselect;
    
       
    //echo $sql;
    $mypages = $DB->get_records_sql($sql, null);
    unset($sql);
    

    //Pour chacune de ces pages de wiki
    foreach ($mypages as $onepage) {

      //On récupère l'objet représentant le wiki.
      $onepageobject = get_coursemodule_from_instance("wiki", $onepage->wid);

      //Titre du wiki avec lien
      echo $OUTPUT->heading('<a href="'.$CFG->wwwroot.'/mod/wiki/view.php?id='.$onepageobject->id.'&show_all=1">'.$onepage->name.'</a>', 3);       

      if (!$page = wiki_get_page($onepage->id)) {
        print_error('incorrectpageid', 'wiki');
      }

      if (!$subwiki = wiki_get_subwiki($page->subwikiid)) {
        print_error('incorrectsubwikiid', 'wiki');
      }

      if (!$wiki = wiki_get_wiki($subwiki->wikiid)) {
        print_error('incorrectwikiid', 'wiki');
      }

      if (!$cm = get_coursemodule_from_instance('wiki', $wiki->id)) {
        print_error('invalidcoursemodule');
      }

      $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

      require_login($course, true, $cm);
      $context = context_module::instance($cm->id);
      require_capability('mod/wiki:viewpage', $context);
      add_to_log($course->id, 'wiki', 'history', "history.php?pageid=".$onepage->id, $onepage->id, $cm->id);

      //Titre de la page avec lien
      echo $OUTPUT->heading('<a href="'.$CFG->wwwroot.'/mod/wiki/prettyview.php?pageid='.$onepage->id.'&show_all=1">'.$onepage->title.'</a>', 4);       
      
      
      $wikipage = new hpage_wiki_history($wiki, $subwiki, $cm);     
      $wikipage->set_page($page);            
      $wikipage->print_content();  
    }
}

echo $OUTPUT->footer();








/**
 * Affiche le formulaire de filtre
 * @todo Document this function
 */
function historic_print_big_search_form($course) {
    global $CFG, $DB, $words, $subject, $phrase, $user, $userid, $fullwords, $notwords, $datefrom, $dateto, $PAGE, $OUTPUT;    

    echo $OUTPUT->box_start('generalbox boxaligncenter');

    echo html_writer::script('', $CFG->wwwroot.'/mod/forum/forum.js');

    echo '<form id="searchform" action="historic.php" method="get">';
    echo '<table cellpadding="10" class="searchbox" id="form">';

    echo '<tr>';
    echo '<td class="c0">'."Depuis".'</td>';
    echo '<td class="c1">';
    if (empty($datefrom)) {
        $datefromchecked = '';
        $datefrom = make_timestamp(2000, 1, 1, 0, 0, 0);
    }else{
        $datefromchecked = 'checked="checked"';
    }

    echo '<input name="timefromrestrict" type="checkbox" value="1" alt="'."Depuis".'" onclick="return lockoptions(\'searchform\', \'timefromrestrict\', timefromitems)" '.  $datefromchecked . ' /> ';
    $selectors = html_writer::select_time('days', 'fromday', $datefrom)
               . html_writer::select_time('months', 'frommonth', $datefrom)
               . html_writer::select_time('years', 'fromyear', $datefrom)
               . html_writer::select_time('hours', 'fromhour', $datefrom)
               . html_writer::select_time('minutes', 'fromminute', $datefrom);
    echo $selectors;
    echo '<input type="hidden" name="hfromday" value="0" />';
    echo '<input type="hidden" name="hfrommonth" value="0" />';
    echo '<input type="hidden" name="hfromyear" value="0" />';
    echo '<input type="hidden" name="hfromhour" value="0" />';
    echo '<input type="hidden" name="hfromminute" value="0" />';

    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td class="c0">'."Jusqu'à".'</td>';
    echo '<td class="c1">';
    if (empty($dateto)) {
        $datetochecked = '';
        $dateto = time()+3600;
    }else{
        $datetochecked = 'checked="checked"';
    }

    echo '<input name="timetorestrict" type="checkbox" value="1" alt="'."Jusqu'à".'" onclick="return lockoptions(\'searchform\', \'timetorestrict\', timetoitems)" ' .$datetochecked. ' /> ';
    $selectors = html_writer::select_time('days', 'today', $dateto)
               . html_writer::select_time('months', 'tomonth', $dateto)
               . html_writer::select_time('years', 'toyear', $dateto)
               . html_writer::select_time('hours', 'tohour', $dateto)
               . html_writer::select_time('minutes', 'tominute', $dateto);
    echo $selectors;

    echo '<input type="hidden" name="htoday" value="0" />';
    echo '<input type="hidden" name="htomonth" value="0" />';
    echo '<input type="hidden" name="htoyear" value="0" />';
    echo '<input type="hidden" name="htohour" value="0" />';
    echo '<input type="hidden" name="htominute" value="0" />';

    echo '</td>';
    echo '</tr>';
    
    
    echo '<tr>';
    echo '<td class="c0"><label for="words">'."Messages contenant".'</label>';
    echo '<input type="hidden" value="'.$course->id.'" name="id" alt="" /></td>';
    echo '<td class="c1"><input type="text" size="35" name="words" id="words"value="'.s($words, true).'" alt="" /></td>';
    echo '</tr>';
    

    echo '<tr>';
    echo '<td class="c0"><label for="user">'.get_string('searchuser', 'forum').'</label></td>';
    echo '<td class="c1"><input type="text" size="35" name="user" id="user" value="'.s($user, true).'" alt="" /></td>';
    echo '</tr>';
    
    /*
    echo '<tr>';
    echo '<td class="c0"><label for="menuforumid">'.get_string('searchwhichforums', 'forum').'</label></td>';
    echo '<td class="c1">';
    echo html_writer::select("Forums", "Chats", "Wikis", 'forumid', '', array(''=>"Forums, Chats et Wikis"));
    echo '</td>';
    echo '</tr>';*/
    
    echo '<tr>';
    echo '<td>Afficher l\'historique de:</td>';
    echo '<td><select name="tools">'; 
    echo '<option selected>forums</option>';
    echo '<option>chats</option>';
    echo '<option>wikis</option>';
    echo '<option>tous</option>';
    echo '</select></td>';
    echo '</tr>';
       
    echo '<tr>';
    echo '<td class="submit" colspan="2" align="center">';
    echo '<input type="submit" value="Afficher" alt="" /></td>';
    echo '</tr>';
    
    echo '</table>';
    echo '</form>';

    echo html_writer::script(js_writer::function_call('lockoptions_timetoitems'));
    echo html_writer::script(js_writer::function_call('lockoptions_timefromitems'));

    echo $OUTPUT->box_end();
}

/**
 * This function takes each word out of the search string, makes sure they are at least
 * two characters long and returns an array containing every good word.
 *
 * @param string $words String containing space-separated strings to search for
 * @param string $prefix String to prepend to the each token taken out of $words
 * @returns array
 * @todo Take the hardcoded limit out of this function and put it into a user-specified parameter
 */
function forum_clean_search_terms($words, $prefix='') {
    $searchterms = explode(' ', $words);
    foreach ($searchterms as $key => $searchterm) {
        if (strlen($searchterm) < 2) {
            unset($searchterms[$key]);
        } else if ($prefix) {
            $searchterms[$key] = $prefix.$searchterm;
        }
    }
    return trim(implode(' ', $searchterms));
}

/**
 * @todo Document this function
 */
function forum_menu_list($course)  {

    $menu = array();

    $modinfo = get_fast_modinfo($course);

    if (empty($modinfo->instances['forum'])) {
        return $menu;
    }

    foreach ($modinfo->instances['forum'] as $cm) {
        if (!$cm->uservisible) {
            continue;
        }
        $context = context_module::instance($cm->id);
        if (!has_capability('mod/forum:viewdiscussion', $context)) {
            continue;
        }
        $menu[$cm->instance] = format_string($cm->name);
    }

    return $menu;
}

/**
 * @global object
 * @param object $course
 * @param string $search
 * @return string
 */
function historic_search_form($course, $search='') {
    global $CFG, $OUTPUT;

    $output  = '<div class="forumsearch">';
    $output .= '<form action="'.$CFG->wwwroot.'/mod/forum/search.php" style="display:inline">';
    $output .= '<fieldset class="invisiblefieldset">';
    $output .= $OUTPUT->help_icon('search');
    $output .= '<label class="accesshide" for="search" >'.get_string('search', 'forum').'</label>';
    $output .= '<input id="search" name="search" type="text" size="18" value="'.s($search, true).'" alt="search" />';
    $output .= '<label class="accesshide" for="searchforums" >'.get_string('searchforums', 'forum').'</label>';
    $output .= '<input id="searchforums" value="Afficher" type="submit" />';
    $output .= '<input name="id" type="hidden" value="'.$course->id.'" />';
    $output .= '</fieldset>';
    $output .= '</form>';
    $output .= '</div>';

    return $output;
}



/**
 * @global object
 * @global object
 * @param object $message
 * @param int $courseid
 * @param object $sender
 * @param object $currentuser
 * @param string $chat_lastrow
 * @return bool|string Returns HTML or false
 */
function historic_chat_format_message_manually($onechatobject, $message, $courseid, $sender, $currentuser, $chat_lastrow=NULL) {
    global $CFG, $USER, $OUTPUT;

    $output = new stdClass();
    $output->beep = false;       // by default
    $output->refreshusers = false; // by default

    // Use get_user_timezone() to find the correct timezone for displaying this message:
    // It's either the current user's timezone or else decided by some Moodle config setting
    // First, "reset" $USER->timezone (which could have been set by a previous call to here)
    // because otherwise the value for the previous $currentuser will take precedence over $CFG->timezone
    $USER->timezone = 99;
    $tz = get_user_timezone($currentuser->timezone);

    // Before formatting the message time string, set $USER->timezone to the above.
    // This will allow dst_offset_on (called by userdate) to work correctly, otherwise the
    // message times appear off because DST is not taken into account when it should be.
    $USER->timezone = $tz;
    $message->strtime = userdate($message->timestamp, '%a %d %b %Y %H:%M', $tz);

    $message->picture = $OUTPUT->user_picture($sender, array('size'=>false, 'courseid'=>$courseid, 'link'=>false));

    if ($courseid) {
        $message->picture = "<a onclick=\"window.open('$CFG->wwwroot/user/view.php?id=$sender->id&amp;course=$courseid')\" href=\"$CFG->wwwroot/user/view.php?id=$sender->id&amp;course=$courseid\">$message->picture</a>";
    }

    //Calculate the row class
    if ($chat_lastrow !== NULL) {
        $rowclass = ' class="r'.$chat_lastrow.'" ';
    } else {
        $rowclass = '';
    }

    // Start processing the message

    if(!empty($message->system)) {
        // System event
        $output->text = $message->strtime.': '.get_string('message'.$message->message, 'chat', fullname($sender));
        $output->html  = '<table class="chat-event"><tr'.$rowclass.'><td class="picture">'.$message->picture.'</td><td class="text">';
        $output->html .= '<span class="event">'.$output->text.'</span></td></tr></table>';
        $output->basic = '<tr class="r1">
                            <th scope="row" class="cell c1 title"></th>
                            <td class="cell c2 text">' . get_string('message'.$message->message, 'chat', fullname($sender)) . '</td>
                            <td class="cell c3">' . $message->strtime . '</td>
                          </tr>';
        if($message->message == 'exit' or $message->message == 'enter') {
            $output->refreshusers = true; //force user panel refresh ASAP
        }
        return $output;        
        
    }

    // It's not a system event
    $text = trim($message->message);

    /// Parse the text to clean and filter it
    $options = new stdClass();
    $options->para = false;
    $text = format_text($text, FORMAT_MOODLE, $options, $courseid);

    // And now check for special cases
    $patternTo = '#^\s*To\s([^:]+):(.*)#';
    $special = false;

    if (substr($text, 0, 5) == 'beep ') {
        /// It's a beep!
        $special = true;
        $beepwho = trim(substr($text, 5));

        if ($beepwho == 'all') {   // everyone
            $outinfobasic = get_string('messagebeepseveryone', 'chat', fullname($sender));
            $outinfo = $message->strtime . ': ' . $outinfobasic;
            $outmain = '';

            $output->beep = true;  // (eventually this should be set to
                                   //  to a filename uploaded by the user)

        } else if ($beepwho == $currentuser->id) {  // current user
            $outinfobasic = get_string('messagebeepsyou', 'chat', fullname($sender));
            $outinfo = $message->strtime . ': ' . $outinfobasic;
            $outmain = '';
            $output->beep = true;

        } else {  //something is not caught?
            return false;
        }
    } else if (substr($text, 0, 1) == '/') {     /// It's a user command
        $special = true;
        $pattern = '#(^\/)(\w+).*#';
        preg_match($pattern, $text, $matches);
        $command = isset($matches[2]) ? $matches[2] : false;
        // Support some IRC commands.
        switch ($command){
            case 'me':
                $outinfo = $message->strtime;
                $outmain = '*** <b>'.
                        fullname($sender).' '.substr($text, 4).'</b>';
                break;
            default:
                // Error, we set special back to false to use the classic message output.
                $special = false;
                break;
        }
    } else if (preg_match($patternTo, $text)) {
        $special = true;
        $matches = array();
        preg_match($patternTo, $text, $matches);
        if (isset($matches[1]) && isset($matches[2])) {
            $outinfo = $message->strtime;
            $outmain = fullname($sender).' '.get_string('saidto', 'chat').' <i>'.$matches[1].'</i>: '.$matches[2];
        } else {
            // Error, we set special back to false to use the classic message output.
            $special = false;
        }
    }

    if(!$special) {
        $outinfo = $message->strtime.' '.fullname($sender);
        $outmain = $text;
    }

    /// Format the message as a small table

    $output->text  = strip_tags($outinfo.': '.$outmain);

    $output->html  = "<table class=\"chat-message\"><tr$rowclass><td class=\"picture\" valign=\"top\">$message->picture</td><td class=\"text\">";
    $output->html .= "<span class=\"title\">$outinfo</span>";
    if ($outmain) {
        $output->html .= ': '.$outmain.' <br/><a href="'.$CFG->wwwroot.'/mod/chat/report.php?id='.$onechatobject->id.'&start='.($message->timestamp-300).'&end='.($message->timestamp+300).'">Voir le contexte</a>';
        $output->basic = '<tr class="r0">
                            <th scope="row" class="cell c1 title">' . fullname($sender) . '</th>
                            <td class="cell c2 text">' . $outmain . '</td>
                            <td class="cell c3">' . $message->strtime . '</td>                            
                          </tr>';
    } else {
        $output->basic = '<tr class="r1">
                            <th scope="row" class="cell c1 title"></th>
                            <td class="cell c2 text">' . $outinfobasic . '</td>
                            <td class="cell c3">' . $message->strtime . '</td>                            
                          </tr>';
    }
    $output->html .= "</td></tr></table>";
    return $output;
}

/**
 * @global object
 * @param object $message
 * @param int $courseid
 * @param object $currentuser
 * @param string $chat_lastrow
 * @return bool|string Returns HTML or false
 */
function historic_chat_format_message($onechatobject, $message, $courseid, $currentuser, $chat_lastrow=NULL) {
/// Given a message object full of information, this function
/// formats it appropriately into text and html, then
/// returns the formatted data.
    global $DB;

    static $users;     // Cache user lookups

    if (isset($users[$message->userid])) {
        $user = $users[$message->userid];
    } else if ($user = $DB->get_record('user', array('id'=>$message->userid), user_picture::fields())) {
        $users[$message->userid] = $user;
    } else {
        return NULL;
    }    
    
    return historic_chat_format_message_manually($onechatobject, $message, $courseid, $user, $currentuser, $chat_lastrow);
}


