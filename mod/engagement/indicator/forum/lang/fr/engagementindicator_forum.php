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
 * @package    engagementindicator_forum
 * @copyright  2012 NetSpot Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['e_newposts'] = 'Nouveaux posts par semaine';
$string['e_readposts'] = 'Posts lus par semaine';
$string['e_replies'] = 'Réponses par semaine';
$string['e_totalposts'] = 'Nombre total de posts par semaine';
$string['maxrisk'] = 'Risque maximum';
$string['norisk'] = 'Aucun risque';
$string['pluginname'] = 'Participation aux forums';

// Other.
$string['localrisk'] = 'Risque local';
$string['localrisk_help'] = 'Le risque de décrochage d\'après cet élément uniquement, sur 100. Le risque local est multiplié par la pondération pour former la contribution au risque.';
$string['logic'] = 'Logique';
$string['logic_help'] = 'Ce champ donne un aperçu de la logique utilisée pour calculer le risque local de décrochage.';
$string['maxrisktitle'] = 'Aucun consultation ou participation aux forums';
$string['riskcontribution'] = 'Contribution au risque';
$string['riskcontribution_help'] = 'Importance de ce forum particulier. Ceci est formé en multipliant le risque local avec la pondération . Les contributions de risque de chaque article de forum sont additionnés pour former le risque global de l\'indicateur.';
$string['weighting'] = 'Pondération';
$string['weighting_help'] = 'Ce chiffre indique la quantité de cet ouvrage contribue au risque global pour l\'indicateur du Forum.
Le risque local sera multiplié par ce pour former la contribution des risques.';
