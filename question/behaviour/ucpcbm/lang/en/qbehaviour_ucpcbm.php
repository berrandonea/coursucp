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
 * Strings for component 'qbehaviour_ucpcbm', language 'en'.
 *
 * @package    qbehaviour
 * @subpackage ucpcbm
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['autoevalquestiontext'] = 'Please estimate your level regarding this outcome by giving a number between 0 and 10.';
$string['studentreport'] = "Student's report";
$string['teacherreport'] = "Teacher's report";

$string['accuracy'] = 'Accuracy';
$string['accuracyandbonus'] = 'Accuracy + Bonus';
$string['assumingcertainty'] = 'You did not select a certainty. Assuming: {$a}.';
$string['averagecbmmark'] = 'Average CBM mark';
$string['basemark'] = 'Base mark {$a}';
$string['breakdownbycertainty'] = 'Break-down by certainty';
$string['cbmbonus'] = 'CBM bonus';
$string['cbmmark'] = 'CBM mark {$a}';
$string['cbmgradeexplanation'] = 'For CBM, the grade above is shown relative to the maximum for all correct at C=1.';
$string['cbmgrades'] = 'CBM grades';
$string['cbmgrades_help'] = 'With Certainty Based Marking (CBM) getting every question correct with C=1 (low certainty) gives a grade of 100%. Grades may be as high as 300% if every question is correct with C=3 (high certainty). Misconceptions (confident wrong responses) lower grades much more than wrong responses that are acknowledged to be uncertain. This may even lead to negative overall grades.

**Accuracy** is the % correct ignoring certainty but weighted for the maximum mark of each question. Successfully distinguishing more and less reliable responses gives a better grade than selecting the same certainty for each question. This is reflected in the **CBM Bonus**. **Accuracy** + **CBM Bonus** is a better measure of knowledge than **Accuracy**. Misconceptions can lead to a negative bonus, a warning to look carefully at what is and is not known.';
$string['cbmgrades_link'] = 'qbehaviour/ucpcbm/certaintygrade';
$string['certainty'] = 'Certainty';
$string['certainty_help'] = 'Certainty-based marking requires you to indicate how reliable you think your answer is. The available levels are:

Certainty level     |    No idea    |    Unsure   | Quite sure | Safe and sound |
------------------- | ------------- | ----------- | ---------- | -------------- |
Mark if correct     |      -1       |      1      |     3      |        5       | 
Mark if wrong       |      -1       |     -3      |    -4      |       -5       |';
$string['certainty_link'] = 'qbehaviour/ucpcbm/certainty';
$string['certainty-1'] = 'No Idea';
$string['certainty1'] = 'Unsure';
$string['certainty2'] = 'Quite sure';
$string['certainty3'] = 'Safe and sound';
$string['certaintyshort-1'] = 'No Idea';
$string['certaintyshort1'] = 'Unsure';
$string['certaintyshort2'] = 'Quite sure';
$string['certaintyshort3'] = 'Safe and sound';
$string['dontknow'] = 'No idea';
$string['foransweredquestions'] = 'Results for just the {$a} answered questions';
$string['forentirequiz'] = 'Results for the whole quiz ({$a} questions)';
$string['judgementok'] = 'OK';
$string['judgementsummary'] = 'Responses: {$a->responses}. Accuracy: {$a->fraction}. (Optimal range {$a->idealrangelow} to {$a->idealrangehigh}). You were {$a->judgement} using this certainty level.';
$string['howcertainareyou'] = 'Certainty{$a->help}: {$a->choices}';
$string['noquestions'] = 'No responses';
$string['overconfident'] = 'over-confident';
$string['pluginname'] = 'With UCP certainty indication';
$string['slightlyoverconfident'] = 'a bit over-confident';
$string['slightlyunderconfident'] = 'a bit under-confident';
$string['underconfident'] = 'under-confident';
$string['weightx'] = 'Weight {$a}';

$string['certainty_level'] = 'Certainty level';
$string['certainty_explain'] = "The more certain you declare you are of your answer, the more points you earn if it's correct and the more points you lose if it's wrong.";
$string['mark_if_correct'] = 'Points if correct';
$string['mark_if_wrong'] = 'Points if wong';
$string['mark_values'] = '-1,1,3,5.-1,-3,-4,-5';