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
 * Block to book time slots
 *  
 * @package    block_ucpslotbooking 
 * @author     Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * 
 * File : block_ucpslotbooking.php 
 * Block class definition
 */

class block_ucpslotbooking extends block_base {

    public function init() {
        $this->title = get_string('ucpslotbooking', 'block_ucpslotbooking');
    }

    public function get_content() {
        global $COURSE, $DB, $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        if ($this->config) {
            $this->content->text = "<div style='text-align:justify'>".$this->config->descr."</p><ul>";
        } else {
            $this->content->text = "<div style='text-align:justify'></p><ul>";
        }
        

        $sql = "SELECT * FROM mdl_block_ucpslotbooking_list WHERE blockid = ".$this->instance->id." ORDER BY name";
        $lists = $DB->get_recordset_sql($sql);
        foreach ($lists as $list) {
            $this->update_list($list);
            $this->content->text .= "<li>";
            $url = "$CFG->wwwroot/blocks/ucpslotbooking/slots.php?list=$list->id&courseid=$COURSE->id&blockid=".$this->instance->id;
            $this->content->text .= "<a href='$url'>";
            $this->content->text .= $list->name;
            $this->content->text .= "</a>";
            if (has_capability('block/ucpslotbooking:addinstance', $this->context) && $this->page->user_is_editing()) {
                $this->content->text .= "&nbsp";
                $this->content->text .= $this->pixicon('dellist', $list->id, 't/delete');
                $this->content->text .= $this->pixicon('editslots', $list->id, 't/edit');
                $this->content->text .= $this->pixicon('bookings', $list->id, 'i/export');
            }
            $this->content->text .= "</li>";
        }
        $this->content->text .= "</ul>";

        if (has_capability('block/ucpslotbooking:addinstance', $this->context) && $this->page->user_is_editing()) {
            $params = array('blockid' => $this->instance->id, 'courseid' => $COURSE->id);
            $url = new moodle_url('/blocks/ucpslotbooking/addslotslist.php', $params);
            $this->content->footer = html_writer::link($url,
                    "<p style='text-align:center'><button>".get_string('newlist', 'block_ucpslotbooking')."</button></p>");
        }
	$this->content->text .= '</div>';
        return $this->content;
    }

    public function update_list($list) {
        global $DB;
        if (isset($this->config)) {
            $updatedlist = new stdClass();
            $updatedlist->id = $list->id;
            $updatedlist->blockid = $list->blockid;
            $updatedlist->name = $list->name;
            $updatedlist->justclick = $this->config->justclick['text'];
            $updatedlist->nbshown = $this->config->nbshown;
            $updatedlist->msgbefore = $this->config->msgbefore['text'];
            $updatedlist->msgafter = $this->config->msgafter['text'];
            if ($updatedlist != $list) {
                $DB->update_record('block_ucpslotbooking_list', $updatedlist);
            }
        }
    }

    public function specialization() {
        if (isset($this->config)) {
            if (isset($this->config->changetitle)) {
                $this->title = $this->config->changetitle;
            } else {
                $this->title = get_string('ucpslotbooking', 'block_ucpslotbooking');
            }
        }
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function pixicon($action, $listid, $pix) {
        global $COURSE, $CFG;
        $icon = "&nbsp";
        $url = "$CFG->wwwroot/blocks/ucpslotbooking/$action.php?listid=$listid&courseid=$COURSE->id&blockid=".$this->instance->id;
        $icon .= "<a href='$url'>";
        $title = get_string($action, 'block_ucpslotbooking');
        $icon .= "<img alt='$title' title='$title' src='$CFG->wwwroot/theme/image.php/archaius/core/1452503826/$pix'>";
        $icon .= "</a>";
        return $icon;
    }
}
