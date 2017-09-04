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
 * Strings
 *
 * @package    engagementindicator_login
 * @copyright  2012 NetSpot Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['dayslate'] = 'Jours de retard';
$string['dayslate_help'] = 'Nombre de jours de retard avec lesquels le devoir a été rendu.';
$string['localrisk'] = 'Risque local';
$string['localrisk_help'] = 'Risque de décrochage d\'après ce devoir uniquement, 
sur 100. Le risque local est multiplié par le poids du devoir dans le calcul du risque global de décrochage.';
$string['logic'] = 'Logique';
$string['logic_help'] = 'Ce champ donne un aperçu de la logique utilisée pour calculer le risque local de décrochage.';
$string['pluginname'] = 'Remise de devoirs (ou quiz, etc.)';
$string['overduegracedays'] = 'Délai de grâce en jours';
$string['overduemaximumdays'] = 'Nombre maximum de jours de retard';
$string['overduesubmittedweighting'] = 'Poids d\'un devoir rendu en retard';
$string['overduenotsubmittedweighting'] = 'Poids d\'un devoir non rendu';
$string['override'] = 'Dérogation';
$string['override_help'] = 'Pour certaines activités (ex : les quiz), on peut définir des dates butoir différentes selon les utilisateurs ou les groupes. 
Ce champ indique que cet utilisateur a bénéficié d\'une dérogation de ce type.';
$string['riskcontribution'] = 'Contribution au risque de décrochage';
$string['riskcontribution_help'] = 'Importance de cette évaluation particulère. Ceci est formé en multipliant le risque local avec l\'évaluation
Pondération. Les contributions de risque de chaque évaluation sont additionnées ensemble pour
former le risque global de l\'indicateur.';
$string['status'] = 'Rendu ?';
$string['status_help'] = 'Indique si l\'utilisateur a rendu sa copie ou non.';
$string['weighting'] = 'Pondération';
$string['weighting_help'] = 'Cette figure montre la note maximale de cette évaluation en pourcentage de la note maximale totale pour toutes les évaluations suivies par l\'indicateur d\'évaluation.
La pondération locale sera multiplié par ce pour former la contribution des risques.';
