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

// Standard plugin strings.
$string['pluginname'] = 'Connexions';

// Settings.
$string['eloginspastweek'] = 'Nombre de connexions attendues par semaine';
$string['eloginsperweek'] = 'Nombre de connexions par semaine';
$string['eavgsessionlength'] = 'Durée moyenne attendue des sessions (secondes)';
$string['etimesincelast'] = 'Heure prévue depuis la dernière connexion (secondes)';
$string['sessionlength'] = 'Durée de la session (secondes)';

// Other.
$string['localrisk'] = 'Risque local';
$string['localrisk_help'] = 'Le pourcentage de risque de cette connexion sur
100. Le risque local est multipliée par la pondération de connexion pour former la
contribution des risques.';
$string['logic'] = 'Logique';
$string['logic_help'] = 'Ce champ fournit un aperçu de la logique utilisée pour
arriver à la valeur locale des risques.';
$string['maxrisktitle'] = 'Jamais connecté(e)';
$string['reasonavgsessionlen'] = 'Risque de 0% pour la longueur moyenne d\'une session inférieur à {$a} secondes. 100% pour la longueur de session de 0.';
$string['reasonloginspastweek'] = 'Risque de 0 % pour plus de {$a} connexions par semaine. 100% pour 0 connexion pendant la semaine dernière.';
$string['reasonloginsperweek'] = 'Risque de 0% pour la connexion au cours > = {$a} fois par semaine. Risque de 100 % pour 0 connexion pendant une semaine.';
$string['reasonnologin'] = 'Cet utilisateur n\'a jamais été connecté au cours, donc il a 100% de risque.';
$string['reasontimesincelogin'] = 'Risque de 0 % pour la dernière connexion au cours. Mise à l\'échelle du risque 100% maximum après {$a} jours.';
$string['riskcontribution'] = 'Contribution des risques';
$string['riskcontribution_help'] = 'Importance de cette connexion particulière. Ceci est formé en multipliant le risque local avec le login
pondération.  Les contributions de risque de chaque connexion sont additionnées ensemble pour
former le risque global pour l\'indicateur.';
$string['weighting'] = 'Pondération';
$string['weighting_help'] = 'Ce chiffre indique la quantité de cet ouvrage
contribue vers le risque global pour l\'indicateur de connexion.
Le risque local sera multiplié par ce pour former le risque de
contribution.';
