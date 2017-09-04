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
 * @package    mod
 * @subpackage engagement
 * @copyright  NetSpot Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['engagement:addinstance'] = 'Ajouter une instance du module Engagement'; // This should never appear. This mod is a container for sub-plugins.
$string['cachettl'] = 'Durée de vie du cache';
$string['cachingdisabled'] = 'Désactivation du cache';
$string['configcachettl'] = 'Ce réglage spécifie la durée de vie des données d\'engagement mises en cache. S\'il est utilisé, les risques affichés dans le bloc ne tiendront pas forcément compte des derniers événements, mais seront au contraire 
calculés en fonction des données présentes dans le cache. Les nouveaux devoirs rendus, connexions, etc., ne seront pas détectés avant l\'expiration du cache. Ce réglage est important pour des raisons de performance, pour ne pas envoyer trop de 
requêtes à la base de données sur des plateformes très fréquentées.';
$string['incorrectlyconfigured'] = 'Ce plugin n\'a pas été configuré correctement. Il n\'est pas prévu de l\'ajouter à un cours de cette manière. Merci de contacter votre administrateur système pour qu\'il cache le plugin mod_engagement.';
$string['modulename'] = 'Engagement';
$string['modulenameplural'] = 'Engagements';
$string['pluginname'] = 'Engagement';
$string['roles_desc'] = 'Analyser l\'engagement des utilisateurs ayant le rôle : ';
$string['riskscore'] = 'Risque';
