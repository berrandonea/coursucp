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
 * Strings for component 'qbehaviour_ucpcbm', language 'fr', branch 'MOODLE_29_STABLE'
 *
 * @package   qbehaviour_ucpcbm
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['autoevalquestiontext'] = 'Estimez votre degré de maîtrise de ce pré-requis avec un nombre entre 0 et 10.';
$string['studentreport'] = "Rapport pour l'étudiant(e)";
$string['teacherreport'] = "Rapport pour l'enseignant(e)";

$string['accuracy'] = 'Précision';
$string['accuracyandbonus'] = 'Précision + bonus';
$string['assumingcertainty'] = 'Vous n\'avez pas sélectionné de degré de certitude. Supposition : {$a}.';
$string['averagecbmmark'] = 'Évaluation moyenne du degré de certitude';
$string['basemark'] = 'Évaluation de base {$a}';
$string['breakdownbycertainty'] = 'Ventilé par certitude';
$string['cbmbonus'] = 'Bonus degré de certitude';
$string['cbmgradeexplanation'] = 'Dans l\'évaluation avec indication de certitude, la note ci-dessus est affichée relativement au maximum pour toutes les réponses correctes, avec C = 1.';
$string['cbmgrades'] = 'Notes avec degré de certitude';
$string['cbmgrades_help'] = 'Dans l\'évaluation avec indication de certitude, on obtient une note de 100 % lorsque l\'on a répondu correctement à toutes les questions avec C = 1 (certitude faible). Les notes peuvent atteindre jusqu\'à 300 % si l\'on a répondu à correctement à chaque question avec C = 3 (certitude élevée). Les idées fausses (réponses fausses avec taux de certitude élevé) font descendre la note beaucoup plus que les réponses fausses avec une indication de certitude faible. Ceci peut avoir pour conséquence des notes négatives.

**Précision** est le pourcentage de réponses correctes sans tenir compte de l\'indication de certitude, mais pondéré en fonction du maximum de chaque question. La capacité de distinguer entre réponses plus ou moins fiables donne une meilleure note que le choix du même degré de certitude pour chaque réponse.
**Précision** + **Bonus degré de certitude** est une meilleure mesure que **Précision**. Les idées fausses peuvent mener à un bonus négatif, incitant à réfléchir sur ce qui est su et ce qui ne l\'est pas.';
$string['cbmmark'] = 'Évaluation du degré de certitude {$a}';
$string['certainty'] = 'Certitude';
$string['certainty1'] = 'Peu sûr';
$string['certainty-1'] = 'Aucune idée';
$string['certainty2'] = 'Plutôt sûr';
$string['certainty3'] = 'Sûr et certain';
$string['certainty_help'] = 'Plus vous affirmez être certain de votre réponse, plus vous marquez de points si elle est bonne et plus vous en perdez si elle est fausse.

Degré de certitude  |  Aucune idée  |   Peu sûr   | Plutôt sûr | Sûr et certain |
------------------- | ------------- | ----------- | ---------- | -------------- |
Points si correct   |      -1       |      1      |     3      |        5       | 
Points si incorrect |      -1       |     -3      |    -4      |       -5       |';
$string['certaintyshort1'] = 'Peu sûr';
$string['certaintyshort-1'] = 'Aucune idée';
$string['certaintyshort2'] = 'Plutôt sûr';
$string['certaintyshort3'] = 'Sûr et certain';
$string['dontknow'] = 'Aucune idée';
$string['foransweredquestions'] = 'Résultats pour les {$a} questions répondues';
$string['forentirequiz'] = 'Résultats tout le test ({$a} questions)';
$string['howcertainareyou'] = 'Degré de certitude{$a->help} : {$a->choices}';
$string['judgementok'] = 'Ok';
$string['judgementsummary'] = 'Réponses : {$a->responses}. Précision : {$a->fraction}. (Plage optimale {$a->idealrangelow} à {$a->idealrangehigh}). Vous étiez {$a->judgement} en sélectionnant ce degré de certitude.';
$string['noquestions'] = 'Pas de réponse';
$string['overconfident'] = 'trop confiant';
$string['pluginname'] = 'Avec indication de certitude UCP';
$string['slightlyoverconfident'] = 'un peu trop confiant';
$string['slightlyunderconfident'] = 'un peu trop prudent';
$string['underconfident'] = 'trop prudent';
$string['weightx'] = 'Pondération {$a}';

$string['certainty_level'] = 'Degré de certitude';
$string['certainty_explain'] = 'Plus vous affirmez être certain de votre réponse, plus vous marquez de points si elle est bonne et plus vous en perdez si elle est fausse.';
$string['mark_if_correct'] = 'Points si correct';
$string['mark_if_wrong'] = 'Points si incorrect';
$string['mark_values'] = '-1,1,3,5.-1,-3,-4,-5';