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
 * Wikipedia block is a Moodle block to search in the Wikipedia.
 * 
 * @package    block
 * @subpackage wikipedia
 * @copyright  2006 Aggelos Panagiotakis
 * @copyright  2006 David Horat - http://www.nordakademie.de
 * @copyright  2013 Ralf Krause - http://www.moodletreff.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_wikipedia extends block_base {
	
    // Initialize the block
    function init() {
        $this->title = get_string('pluginname', 'block_wikipedia');
    }
	
    // Makes the content accesible for Moodle
    function get_content() {
        global $CFG, $OUTPUT;;
        
        require_once($CFG->libdir.'/filelib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }
		
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
		
		$lang = substr(current_language(), 0, 2);;
		
        // Select the logo to show
		if (file_exists($CFG->dirroot.'/blocks/wikipedia/img/wikipedia-'.$lang.'.gif')) 
			$logofile = $CFG->wwwroot.'/blocks/wikipedia/img/wikipedia-'.$lang.'.gif';
		else
			$logofile = $CFG->wwwroot.'/blocks/wikipedia/img/wikipedia.gif';
            
        $wikilogo = '<img src="'.$logofile.'" alt="Wikipedia" style="width: 122px; height: 36px; margin-bottom: 3px;" />';
		
		$form = '<form action="https://www.wikipedia.org/search-redirect.php" id="searchform" target=_"blank">';
        $form .= '<div>';
        $form .= '<input type="text" name="search" accesskey="f" value="" size="30" style="height:16px; width:90%;" />';
        $form .= '<select id="language" name="language">';
                
        $searchlang = array('ar','bg','ca','cs','da','de','en','el','es','eo','eu','fa','fr','ko','hi','hr','id','it','he','lt','hu','ms','nl','ja','no','pl','pt','ro','ru','sk','sl','sr','fi','sv','tr','uk','vi','vo','zh');
        $form .= $this->language_options($searchlang);
        
        $form .= '</select>';
        $form .= '<input type="submit" name="go" class="searchButton" id="searchButton" value="'.get_string('search', 'block_wikipedia').'" />';
        $form .= '</div>';
        $form .= '</form>';

        $this->content->text = $wikilogo.$form;
       
        return $this->content;
    }

    /**
     * This method returns an html option tag with the corresponding language
     * value for each of the values in the array passed by parameter. 
     * In case that the default searching language is the same as the
     * current searching language that we are processing, then the output tag
     * will have the selected attribute with value "selected".
     * 
     * @param array $searchlang the languages
     * @return string
     */
    private function language_options($searchlang) {
        include('languagemenu.php');
        $defaultsearchlang = substr(current_language(), 0, 2);
        $output = '';
        
        foreach($searchlang as $value) {
            $output .= '<option value="'.$value.'" lang="'.$value
                .'" xml:lang="'.$value;        
            if($value == $defaultsearchlang) {
                $output .= '" selected="selected">';
            } else {
                $output .= '">';
            }                    
            $output .= $menustring[$value];;  
            $output .= '</option>';
        }
        
        return $output;        
    }

}

?>
