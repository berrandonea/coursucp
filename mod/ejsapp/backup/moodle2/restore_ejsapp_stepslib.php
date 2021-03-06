<?php

// This file is part of the Moodle module "EJSApp"
//
// EJSApp is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// EJSApp is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License is available on <http://www.gnu.org/licenses/>
//
// EJSApp has been developed by:
//  - Luis de la Torre: ldelatorre@dia.uned.es
//	- Ruben Heradio: rheradio@issi.uned.es
//
//  at the Computer Science and Automatic Control, Spanish Open University
//  (UNED), Madrid, Spain

/**
 * Tasks file to perform the EJSApp backup
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one EJSApp activity
 */
class restore_ejsapp_activity_structure_step extends restore_activity_structure_step
{
    /**
     * Define structure
     */
    protected function define_structure()
    {
        $paths = array();
        $paths[] = new restore_path_element('ejsapp', '/activity/ejsapp');
        $paths[] = new restore_path_element('ejsapp_personal_vars', '/activity/ejsapp/ejsapp_personal_vars/personal_vars');

        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo){
            $paths[] = new restore_path_element('ejsapp_log', '/activity/ejsapp/ejsapp_log');
            $paths[] = new restore_path_element('ejsappbooking_usersaccess', '/activity/ejsapp/ejsappbooking_usersaccesses/ejsappbooking_usersaccess');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process table ejsapp
     * @param stdClass $data
     */
    protected function process_ejsapp($data)
    {
        global $DB, $CFG;

        $data = (object)$data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();

        // insert the ejsapp record
        $newitemid = $DB->insert_record('ejsapp', $data);

        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);

        // copy files
        if (!empty($data->class_file)) { //JAR applet
            $ext = '.jar';
            if (pathinfo($data->applet_name, PATHINFO_EXTENSION) == 'jar') $ext = '';
            $name = $data->applet_name . $ext;
            $sql = "select * from {files} where component = 'mod_ejsapp' and filearea = 'jarfiles' and itemid = {$data->id} and filename = '{$name}'";
        } else { //Zip file with Javascript
            $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $data->applet_name);
            $without_Simulation = substr($withoutExt, 0, strrpos($withoutExt, '_Simulation'));
            $sql = "select * from {files} where component = 'mod_ejsapp' and filearea = 'jarfiles' and itemid = {$data->id} and filename like '%$without_Simulation%'";
        }
        $file_record = $DB->get_record_sql($sql);
        if ($file_record) {
            $fs = get_file_storage();
            $fileinfo = array(
                'contextid' => $file_record->contextid, // ID of context
                'component' => 'mod_ejsapp',            // usually = table name
                'filearea' => 'jarfiles',               // usually = table name
                'itemid' => $file_record->itemid,       // usually = ID of row in table
                'filepath' => '/',                      // any path beginning and ending in /
                'filename' => $file_record->filename);  // any filename
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'],
                $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'],
                $fileinfo['filename']);
            if ($file) {
                // create directories
                $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/';
                if (!file_exists($path)) mkdir($path, 0700);
                $path .= $data->course . '/';
                if (!file_exists($path)) mkdir($path, 0700);
                $path .= $newitemid;
                if (!file_exists($path)) mkdir($path, 0700);

                $codebase = '/mod/ejsapp/jarfiles/' . $data->course . '/' . $newitemid . '/';

                // copy file .jar or .zip file
                $folderpath = $CFG->dirroot . $codebase;
                $filepath = $folderpath . $file_record->filename;
                $file->copy_content_to($filepath);
                if (empty($data->class_file)) { //Zip file with Javascript
                    modifications_for_javascript($filepath, $data, $folderpath, $codebase);
                    unlink($filepath);
                }

                // codebase
                $codebase_complete = '';
                preg_match('/http:\/\/.+?\/(.+)/', $CFG->wwwroot, $match_result);
                if (!empty($match_result) and $match_result[1]) {
                    $codebase_complete .= '/' . $match_result[1];
                }
                $codebase_complete .= $codebase;

                // <update ejsapp table>
                $codebase = '/mod/ejsapp/jarfiles/' . $data->course . '/' . $newitemid . '/';
                $record = new stdClass();
                $record->id = $newitemid;
                $record->codebase = $codebase_complete;
                $DB->update_record('ejsapp', $record);
            } //if ($file)
        } //if ($file_record)

        // mapping old_ejsapp_id->new_old_ejsapp_id for xml state_files
        // (see after_execute)
        $this->set_mapping('ejsapp', $oldid, $newitemid, true);
    }//process_ejsapp

    /**
     * Process table ejsapp_personal_vars
     * @param stdClass $data
     */
    protected function process_ejsapp_personal_vars($data)
    {
        global $DB;

        $data = (object)$data;

        $data->ejsappid = $this->get_new_parentid('ejsapp');

        // insert the ejsapp record
        $DB->insert_record('ejsapp_personal_vars', $data);
    }//process_ejsapp_personal_vars

    /**
     * Process table ejsapp_log
     * @param stdClass $data
     */
    protected function process_ejsapp_log($data)
    {
        global $DB;

        $data = (object)$data;

        $data->ejsappid = $this->get_new_parentid('ejsapp_log');

        // insert the ejsapp record
        $DB->insert_record('ejsapp_log', $data);
    }//process_ejsapp_log

    /**
     * Process table block_remlab_manager_exptsyst2pract
     * @param stdClass $data
     */
    /*protected function process_remlab_manager_exptsyst2pract($data)
    {
        global $DB;

        $data = (object)$data;

        $data->ejsappid = $this->get_new_parentid('ejsapp');
        $data->practiceid = $DB->get_field('block_remlab_manager_exp2prc', 'practiceid', array('ejsappid'=>$data->ejsappid));
        $data->practiceintro = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid'=>$data->ejsappid));

        // insert the ejsapp record
        $is_exptsyst2pract_restored = $DB->get_record(block_'remlab_manager_exptsyst2pract',array('ejsappid'=>$data->ejsappid));
        $is_exptsyst2pract_restored = !empty($is_exptsyst2pract_restored);
        if (!$is_exptsyst2pract_restored) {
            $DB->insert_record('remlab_manager_exptsyst2pract', $data);
        }
    }//process_remlab_manager_exptsyst2pract

    /**
     * Process table ejsappbooking
     * @param stdClass $data
     */
    protected function process_ejsappbooking($data)
    {
        global $DB;

        $data = (object)$data;

        $data->course = $this->get_courseid();

        // insert the ejsapp record
        $is_ejsappbooking_restored = $DB->get_records('ejsappbooking',array('course'=>$data->course));
        $is_ejsappbooking_restored = !empty($is_ejsappbooking_restored);
        if (!$is_ejsappbooking_restored) {
            $DB->insert_record('ejsappbooking', $data);
        }
    }//process_ejsappbooking

    /**
     * Process table process_ejsappbooking_usersaccess
     * @param stdClass $data
     */
    protected function process_ejsappbooking_usersaccess($data)
    {
        global $DB;

        $data = (object)$data;

        $data->ejsappid = $this->get_new_parentid('ejsapp');
        $data->bookingid = $this->get_mappingid('ejsappbooking', $data->bookingid);
        $data->userid = $this->get_mappingid('user', $data->userid);


        // insert the ejsapp record
        $DB->insert_record('ejsappbooking_usersaccess', $data);
    }//process_ejsappbooking_usersaccess

    /**
     * Process table process_ejsappbooking_remlab_access
     * @param stdClass $data
     */
    protected function process_ejsappbooking_remlab_access($data)
    {
        global $DB;

        $data = (object)$data;

        // There is no necessity of mapping for "practiceid" nor "username"
        $data->ejsappid = $this->get_new_parentid('ejsapp');

        // insert the ejsapp record
        $DB->insert_record('ejsappbooking_remlab_access', $data);
    }//process_ejsappbooking_remlab_access

    /**
     * Extract jarfiles to the ejsapp jar folder
     */
    protected function after_execute()
    {

        // Add ejsapp related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_ejsapp', 'jarfiles', 'ejsapp');
        $this->add_related_files('mod_ejsapp', 'xmlfiles', 'ejsapp');
        $this->add_related_files('mod_ejsapp', 'cntfiles', 'ejsapp');
        $this->add_related_files('mod_ejsapp', 'recfiles', 'ejsapp');

    } //after_execute

} //class