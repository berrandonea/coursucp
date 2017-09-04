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
 * Question behaviour that is like the deferred feedback model, but with
 * certainty based marking. That is, in addition to the other controls, there are
 * where the student can indicate how certain they are that their answer is right.
 *
 * @package    qbehaviour
 * @subpackage ucpcbm
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../deferredfeedback/behaviour.php');


/**
 * Question behaviour for deferred feedback with certainty based marking.
 *
 * The student enters their response during the attempt, along with a certainty,
 * that is, how sure they are that they are right, and it is saved. Later,
 * when the whole attempt is finished, their answer is graded. Their degree
 * of certainty affects their score.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_ucpcbm extends qbehaviour_deferredfeedback {
    const IS_ARCHETYPAL = true;

    public function get_min_fraction() {
        return question_ucpcbm::adjust_fraction(0, question_ucpcbm::HIGH);
    }

    public function get_max_fraction() {
        return question_ucpcbm::adjust_fraction(1, question_ucpcbm::HIGH);
    }

    public function get_expected_data() {
        if ($this->qa->get_state()->is_active()) {
            return array('certainty' => PARAM_INT);
        }
        return parent::get_expected_data();
    }

    public function get_right_answer_summary() {
        $summary = parent::get_right_answer_summary();
        return question_ucpcbm::summary_with_certainty($summary, question_ucpcbm::HIGH);
    }

    public function get_correct_response() {
        if ($this->qa->get_state()->is_active()) {
            return array('certainty' => question_ucpcbm::HIGH);
        }
        return array();
    }

    protected function get_our_resume_data() {
        $lastcertainty = $this->qa->get_last_behaviour_var('certainty');
        if ($lastcertainty) {
            return array('-certainty' => $lastcertainty);
        } else {
            return array();
        }
    }

    protected function is_same_response(question_attempt_step $pendingstep) {
        return parent::is_same_response($pendingstep) &&
                $this->qa->get_last_behaviour_var('certainty') ==
                        $pendingstep->get_behaviour_var('certainty');
    }

    protected function is_complete_response(question_attempt_step $pendingstep) {
        return parent::is_complete_response($pendingstep) &&
                $pendingstep->has_behaviour_var('certainty');
    }

    public function process_finish(question_attempt_pending_step $pendingstep) {
        $status = parent::process_finish($pendingstep);
        if ($status == question_attempt::KEEP) {
            $fraction = $pendingstep->get_fraction();
            if ($this->qa->get_last_step()->has_behaviour_var('certainty')) {
                $certainty = $this->qa->get_last_step()->get_behaviour_var('certainty');
            } else {
                $certainty = question_ucpcbm::default_certainty();
                $pendingstep->set_behaviour_var('_assumedcertainty', $certainty);
            }
            if (!is_null($fraction)) {
                $pendingstep->set_behaviour_var('_rawfraction', $fraction);
                $pendingstep->set_fraction(question_ucpcbm::adjust_fraction($fraction, $certainty));
            }
            $pendingstep->set_new_response_summary(
                    question_ucpcbm::summary_with_certainty($pendingstep->get_new_response_summary(),
                    $this->qa->get_last_step()->get_behaviour_var('certainty')));
        }
        return $status;
    }

    public function summarise_action(question_attempt_step $step) {
        $summary = parent::summarise_action($step);
        if ($step->has_behaviour_var('certainty')) {
            $summary = question_ucpcbm::summary_with_certainty($summary,
                    $step->get_behaviour_var('certainty'));
        }
        return $summary;
    }
}

/**
 * This helper class contains the constants and methods required for
 * manipulating scores for certainty based marking.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class question_ucpcbm {
    /**#@+ @var integer named constants for the certainty levels. */
    const NOIDEA = -1;
    const LOW = 1;
    const MED = 2;
    const HIGH = 3;    
    /**#@-*/

    /** @var array list of all the certainty levels. */
    public static $certainties = array(self::NOIDEA, self::LOW, self::MED, self::HIGH);
    
    /**#@+ @var array coefficients used to adjust the fraction based on certainty. */
    protected static $rightscore = array(
        self::NOIDEA => -1,
        self::LOW  => 1,
        self::MED  => 3,
        self::HIGH => 5,        
    );
    protected static $wrongscore = array(
        self::NOIDEA => -1,
        self::LOW  =>  -3,
        self::MED  => -4,
        self::HIGH => -5,
    );
    /**#@-*/

    /**#@+ @var array upper and lower limits of the optimal window. */
    protected static $lowlimit = array(
        self::NOIDEA => 0,
        self::LOW  => 0.3,
        self::MED  => 0.666666666666667,
        self::HIGH => 0.8,
    );
    protected static $highlimit = array(
        self::NOIDEA => 0.3,
        self::LOW  => 0.666666666666667,
        self::MED  => 0.8,
        self::HIGH => 1,
    );
    /**#@-*/

    /**
     * @return int the default certaintly level that should be assuemd if
     * the student does not choose one.
     */
    public static function default_certainty() {
        return self::LOW;
    }

    /**
     * Given a fraction, and a certainty, compute the adjusted fraction.
     * @param number $fraction the raw fraction for this question.
     * @param int $certainty one of the certainty level constants.
     * @return number the adjusted fraction taking the certainty into account.
     */
    public static function adjust_fraction($fraction, $certainty) {
//        if ($certainty == -1) {            
//            return 0;
//        }
        if ($fraction <= 0.00000005) {
            return self::$wrongscore[$certainty];
        } else {
            return self::$rightscore[$certainty] * $fraction;
        }
    }

    /**
     * @param int $certainty one of the NOIDEA/LOW/MED/HIGH constants.
     * @return string a textual description of this certainty.
     */
    public static function get_string($certainty) {
        return get_string('certainty' . $certainty, 'qbehaviour_ucpcbm');
    }

    /**
     * @param int $certainty one of the LOW/MED/HIGH constants.
     * @return string a short textual description of this certainty.
     */
    public static function get_short_string($certainty) {
        return get_string('certaintyshort' . $certainty, 'qbehaviour_ucpcbm');
    }

    /**
     * Add information about certainty to a response summary.
     * @param string $summary the response summary.
     * @param int $certainty the level of certainty to add.
     * @return string the summary with information about the certainty added.
     */
    public static function summary_with_certainty($summary, $certainty) {
        if (is_null($certainty)) {
            return $summary;
        }
        return $summary . ' [' . self::get_short_string($certainty) . ']';
    }

    /**
     * @param int $certainty one of the LOW/MED/HIGH constants.
     * @return float the lower limit of the optimal probability range for this certainty.
     */
    public static function optimal_probablility_low($certainty) {
        return self::$lowlimit[$certainty];
    }

    /**
     * @param int $certainty one of the LOW/MED/HIGH constants.
     * @return float the upper limit of the optimal probability range for this certainty.
     */
    public static function optimal_probablility_high($certainty) {
        return self::$highlimit[$certainty];
    }
}
