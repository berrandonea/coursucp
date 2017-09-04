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
 * Defines the renderer for the deferred feedback with certainty based marking
 * behaviour.
 *
 * @package    qbehaviour
 * @subpackage ucpcbm
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Renderer for outputting parts of a question belonging to the deferred
 * feedback with certainty based marking behaviour.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_ucpcbm_renderer extends qbehaviour_renderer {
    
//    public function complete_behaviour() {
//        $this->certainties = array(self::NOIDEA, self::LOW, self::MED, self::HIGH);
//        $this->rightscore = array(
//            self::NOIDEA => -1,
//            self::LOW  => 1,
//            self::MED  => 3,
//            self::HIGH => 5,        
//        );
//        $this->wrongscore = array(
//            self::NOIDEA => -1,
//            self::LOW  =>  -3,
//            self::MED  => -4,
//            self::HIGH => -5,
//        );
//    }
//    
    
    protected function certainty_choices($controlname, $selected, $readonly) {
        $attributes = array(
            'type' => 'radio',
            'name' => $controlname,
        );
        if ($readonly) {
            $attributes['disabled'] = 'disabled';
        }

        $choices = '';
        $certainties = question_ucpcbm::$certainties;
//        $standardcertainties = question_ucpcbm::$certainties;               
//        $certainties[0] = -1; // No Idea.
//        foreach ($standardcertainties as $standardcertainty) {
//            $certainties[] = $standardcertainty;
//        }
                
        foreach ($certainties as $certainty) {
            $id = $controlname . $certainty;
            $attributes['id'] = $id;
            $attributes['value'] = $certainty;
            if ($selected == $certainty) {
                $attributes['checked'] = 'checked';
            } else {
                unset($attributes['checked']);
            }
            $choices .= ' ' .
                    html_writer::tag('label', html_writer::empty_tag('input', $attributes) .
                            get_string('certainty' . $certainty, 'qbehaviour_ucpcbm'), array('for' => $id));
        }
        return $choices;
    }

    public function controls(question_attempt $qa, question_display_options $options) {
        $a = new stdClass();
        $a->help = $this->output->help_icon('certainty', 'qbehaviour_ucpcbm');
        $a->choices = $this->certainty_choices($qa->get_behaviour_field_name('certainty'),
                $qa->get_last_behaviour_var('certainty'), $options->readonly);
	// BRICE.
	$question = $qa->get_question();
	if (substr($question->name, 0, 16) == 'Auto-évaluation') {
		return '';
	}
	// FIN.


        return html_writer::tag('div', get_string('howcertainareyou', 'qbehaviour_ucpcbm', $a),
                array('class' => 'certaintychoices'));
    }

    public function feedback(question_attempt $qa, question_display_options $options) {
        if (!$options->feedback) {
            return '';
        }

        if ($qa->get_state() == question_state::$gaveup || $qa->get_state() ==
                question_state::$mangaveup) {
            return '';
        }

        $feedback = '';
        if (!$qa->get_last_behaviour_var('certainty') &&
                $qa->get_last_behaviour_var('_assumedcertainty')) {
            $feedback .= html_writer::tag('p',
                    get_string('assumingcertainty', 'qbehaviour_ucpcbm',
                    question_ucpcbm::get_string($qa->get_last_behaviour_var('_assumedcertainty'))));
        }

        return $feedback;
    }

    public function marked_out_of_max(question_attempt $qa, core_question_renderer $qoutput,
            question_display_options $options) {
        return get_string('weightx', 'qbehaviour_ucpcbm', $qa->format_fraction_as_mark(
                question_ucpcbm::adjust_fraction(1, question_ucpcbm::default_certainty()),
                $options->markdp));
    }

    public function mark_out_of_max(question_attempt $qa, core_question_renderer $qoutput,
            question_display_options $options) {
        return get_string('cbmmark', 'qbehaviour_ucpcbm', $qa->format_mark($options->markdp)) .
                '<br>' . $this->marked_out_of_max($qa, $qoutput, $options);
    }
}
