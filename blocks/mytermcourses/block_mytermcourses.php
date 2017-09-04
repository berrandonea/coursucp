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
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * Displays the courses within which the user his enrolled, their categories,
 * and their teachers. Courses open to all users are also displayed.
 * Courses are displayed on two columns, one for each term in the year.
 * Teachers can move their courses to select the relevant column.
 *
 * @package    block_mytermcourses
 * @author     Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : block_mytermcourses.php
 * Block class definition
 */

class block_mytermcourses extends block_base {
    public function init() {
        $this->title = get_string('mytermcourses', 'block_mytermcourses');
    }

    public function get_content() {
        global $CFG, $DB, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;
	$this->content->text = '';

        // Style for category titles.
        $bgcolor = $PAGE->theme->settings->currentcolor;
        $style = "font-weight:bold;padding:5px;color:white;background-color:$bgcolor;width:90%";

        // Common categories.
        if ($this->config->common) {
            $commoncategoriesid = explode(';', $this->config->common);
            $commoncategories = $this->sortcategories($commoncategoriesid);
            foreach ($commoncategories as $commoncategory) {
                $commoncourses = $DB->get_records('course', array('category' => $commoncategory->id));
                $this->content->text .= "<p style='$style'>$commoncategory->name</p>";
                $this->content->text .= "<table>";
                $commoncolumns = $this->preparecolumns($commoncategory, $commoncourses);
                $this->displaycourses($commoncolumns);
                $this->content->text .= "<tr><td></td><td></td></tr></table>";
            }
        }

        // User's courses.
        $courses = enrol_get_my_courses('summary');
        if (!$courses) {
            $this->content->text .= get_string('notenrolled', 'block_mytermcourses');
        } else {
            $columntitlestyle = "text-align:center;font-weight:bold;font-size:14px";
            $this->content->text .= '<table><tr>';
            $this->content->text .= "<td width='50%' style='$columntitlestyle'>"
                    .get_string('firstterm', 'block_mytermcourses').'</td>';
            $this->content->text .= "<td width='50%' style='$columntitlestyle'>"
                    .get_string('secondterm', 'block_mytermcourses').'</td>';
            $this->content->text .= '</tr></table>';
            ?>
            <?php
            // User's course categories.
            $categoriesid = array();
            foreach ($courses as $course) {
                if (!in_array($course->category, $categoriesid)) {
                    array_push($categoriesid, $course->category);
                }
            }
            $categories = $this->sortcategories($categoriesid);

            // Display categories and courses.
            foreach ($categories as $category) {
                $category = $DB->get_record('course_categories', array('id' => $category->id));
                $this->content->text .= "<p style='$style'>$category->name</p>";
                $this->content->text .= "<table>";
                $columns = $this->preparecolumns($category, $courses);
                $this->displaycourses($columns);
                $this->content->text .= "<tr><td></td><td></td></tr></table>";
            }
        }
        $this->content->footer = '';
        return $this->content;
    }

    public function sortcategories($categoriesid) {
        global $DB;
        if ($this->config->idnumber) {
            $criterium = 'idnumber';
        } else {
            $criterium = 'sortorder';
        }
        $categories = array();
        foreach ($categoriesid as $categoryid) {
            $categoriesorder[$categoryid] = $DB->get_field('course_categories', 'sortorder', array('id' => $categoryid));
        }
        asort($categoriesorder);
        foreach ($categoriesorder as $categoryid => $order) {
            $category = $DB->get_record('course_categories', array('id' => $categoryid));
            array_push($categories, $category);
        }
        return $categories;
    }

    public function preparecolumns($category, $courses) {
        global $DB;
        $columns = array();
        $columns[0] = array();
        $columns[1] = array();
        reset($courses);
        foreach ($courses as $course) {
            if ($course->category == $category->id) {
                $right = $DB->record_exists('block_mytermcourses_col', array('courseid' => $course->id, 'position' => 2));
                if ($right) {
                    array_push($columns[1], $course);
                } else {
                    array_push($columns[0], $course);
                }
            }
        }
        return $columns;
    }

    public function displaycourses($columns) {
        while (isset($columns[0][0]) || isset($columns[1][0])) {
            $this->content->text .= '<tr>';
            foreach ($columns as $colnumber => $column) {
                if ($colnumber % 2 == 0) {
                    $style = "border-right:1px solid #7F7F7F";
                } else {
                    $style = '';
                }
                $this->content->text .= "<td width='50%' style='$style'>";
                if (count($column)) {
                    $course = array_shift($column);
                    $columns[$colnumber] = $column;
                    $this->displaycourse($course, $colnumber);
                }
                $this->content->text .= '</td>';
            }
            $this->content->text .= '</tr>';
        }
    }

    public function displaycourse($course, $colnumber) {
        global $CFG, $DB, $USER;
        $url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
        $this->content->text .= "<a href='$url' style='font-weight:bold' title='$course->shortname'>$course->fullname</a><br/>";

        // Teachers.
        $coursecontextid = $DB->get_field('context', 'id', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $course->id));
        $teacherassignments = $DB->get_records('role_assignments', array('roleid' => 3, 'contextid' => $coursecontextid));
        $first = 1;
        $userteacher = 0;
        foreach ($teacherassignments as $teacherassignment) {
            $teacher = $DB->get_record('user', array('id' => $teacherassignment->userid));
            if ($teacher->id == $USER->id) {
                $userteacher = 1;
            }
            if ($first) {
                $first = 0;
            } else {
                $this->content->text .= ' - ';
            }
            $this->content->text .= "$teacher->firstname $teacher->lastname";
        }
        if ($userteacher) {
            $this->content->text .= '&nbsp;';
            $this->content->text .= "<button onclick='javascript:block_mytermcourses_togglecolumn($course->id, $colnumber)'>";
            if ($colnumber) {
                $this->content->text .= '<<';
            } else {
                $this->content->text .= '>>';
            }
            $this->content->text .= "</button>";
        }
        if ($course->summary) {
            $this->content->text .= "<br><span style='font-size:11'>$course->summary</span>";
        }
    }

    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->changetitle)) {
                $this->title = get_string('mytermcourses', 'block_mytermcourses');
            } else {
                $this->title = $this->config->changetitle;
            }
        }
    }
}
?>

<script type="text/javascript">
var xhr = null;

function getXhr(){
    if(window.XMLHttpRequest)
       xhr = new XMLHttpRequest();
    else if(window.ActiveXObject){
       try {
                xhr = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
    }
    else {
       alert("Your browser doesn't support XMLHTTPRequest...");
       xhr = false;
    }
}

/**
* Move the course from a column to another
*/
function block_mytermcourses_togglecolumn(courseid, colnumber) {
    getXhr();
    xhr.onreadystatechange = function(){
        if(xhr.readyState == 4 && xhr.status == 200) {
            location.reload(true);
        }
    }
    xhr.open("POST","<?php echo "$CFG->wwwroot/blocks/mytermcourses/"?>togglecolumn.php",true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.send("courseid=" + courseid + "&colnumber=" + colnumber);
}
</script>
