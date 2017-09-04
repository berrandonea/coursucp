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
 * Depotetudiant module renderer
 *
 * @package    mod
 * @subpackage depotetudiant
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class mod_depotetudiant_renderer extends plugin_renderer_base {

    /**
     * Returns html to display the content of mod_depotetudiant
     * (Description, depotetudiant files and optionally Edit button)
     *
     * @param stdClass $depotetudiant record from 'depotetudiant' table (please note
     *     it may not contain fields 'revision' and 'timemodified')
     * @return string
     */
    public function display_depotetudiant(stdClass $depotetudiant) {
        $output = '';
        $depotetudiantinstances = get_fast_modinfo($depotetudiant->course)->get_instances_of('depotetudiant');
        if (!isset($depotetudiantinstances[$depotetudiant->id]) ||
                !($cm = $depotetudiantinstances[$depotetudiant->id]) ||
                !($context = context_module::instance($cm->id))) {
            // Some error in parameters.
            // Don't throw any errors in renderer, just return empty string.
            // Capability to view module must be checked before calling renderer.
            return $output;
        }

        if (trim($depotetudiant->intro)) {
            if ($depotetudiant->display != DEPOTETUDIANT_DISPLAY_INLINE) {
                $output .= $this->output->box(format_module_intro('depotetudiant', $depotetudiant, $cm->id),
                        'generalbox', 'intro');
            } else if ($cm->showdescription) {
                // for "display inline" do not filter, filters run at display time.
                $output .= format_module_intro('depotetudiant', $depotetudiant, $cm->id, false);
            }
        }

        $depotetudianttree = new depotetudiant_tree($depotetudiant, $cm);
        if ($depotetudiant->display == DEPOTETUDIANT_DISPLAY_INLINE) {
            // Display module name as the name of the root directory.
            $depotetudianttree->dir['dirname'] = $cm->get_formatted_name();
        }
        $output .= $this->output->box($this->render($depotetudianttree),
                'generalbox depotetudianttree');

        // Do not append the edit button on the course page.
        if ($depotetudiant->display != DEPOTETUDIANT_DISPLAY_INLINE && has_capability('mod/depotetudiant:managefiles', $context)) {
            $output .= $this->output->container(
                    $this->output->single_button(new moodle_url('/mod/depotetudiant/edit.php',
                    array('id' => $cm->id)), get_string('edit')),
                    'mdl-align depotetudiant-edit-button');
        }
        return $output;
    }

    public function render_depotetudiant_tree(depotetudiant_tree $tree) {
        static $treecounter = 0;

        $content = '';
        $id = 'depotetudiant_tree'. ($treecounter++);
        $content .= '<div id="'.$id.'" class="filemanager">';
        $content .= $this->htmllize_tree($tree, array('files' => array(), 'subdirs' => array($tree->dir)));
        $content .= '</div>';
        $showexpanded = true;
        if (empty($tree->depotetudiant->showexpanded)) {
            $showexpanded = false;
        }
        $this->page->requires->js_init_call('M.mod_depotetudiant.init_tree', array($id, $showexpanded));
        return $content;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     */
    protected function htmllize_tree($tree, $dir) {
        global $CFG;

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(24), $subdir['dirname'], 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', s($subdir['dirname']), array('class' => 'fp-filename'));
            $filename = html_writer::tag('div', $filename, array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename. $this->htmllize_tree($tree, $subdir));
        }
        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                    $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $filename, false);
            if (file_extension_in_typegroup($filename, 'web_image')) {
                $image = $url->out(false, array('preview' => 'tinyicon', 'oid' => $file->get_timemodified()));
                $image = html_writer::empty_tag('img', array('src' => $image));
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $filename, 'moodle');
            }
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', $filename, array('class' => 'fp-filename'));
            $filename = html_writer::tag('span',
                    html_writer::link($url->out(false, array('forcedownload' => 1)), $filename),
                    array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename);
        }
        $result .= '</ul>';

        return $result;
    }
}

class depotetudiant_tree implements renderable {
    public $context;
    public $depotetudiant;
    public $cm;
    public $dir;

    public function __construct($depotetudiant, $cm) {
        $this->depotetudiant = $depotetudiant;
        $this->cm     = $cm;

        $this->context = context_module::instance($cm->id);
        $fs = get_file_storage();
        $this->dir = $fs->get_area_tree($this->context->id, 'mod_depotetudiant', 'content', 0);
    }
}
