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
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 * 
 * Block to book time slots
 *  
 * @package    block_ucpslotbooking 
 * @author     Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * 
 * File : version.php
 * Version number
 * 
 */

$plugin->version = 2016092200;
// Mettre la date du jour suivie de deux autres chiffres (au cas où on essaie plusieurs versions dans la même journée).

$plugin->requires = 2015050500;
// Date de publication de la première version de Moodle sur laquelle on est sûr que le plugin fonctionne.
// 2015111000 pour Moodle 3.0.

$plugin->component = 'block_ucpslotbooking'; // Nom officiel du plugin.

/*
 * Si ce plugin dépend d'autres plugins : $plugin->dependencies = array('mod_quiz' => 2015111000);
 */

/*
 * $plugin->cron = 3600; Intervale de temps minimal (en secondes) entre deux appels automatiques
 * à la méthode cron() de la classe du plugin.
 *
 * ATTENTION : si on change cette valeur, il faut impérativement modifier le numéro de version et mettre à jour le plugin.
 * Sinon, le changement sera ignoré.
 */
