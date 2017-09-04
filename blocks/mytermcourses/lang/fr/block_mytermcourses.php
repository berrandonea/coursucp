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
 * Displays the courses within which the user his enrolled, their categories, 
 * and their teachers. Courses open to all users are also displayed.
 * Courses are displayed on two columns, one for each term in the year. 
 * Teachers can move their courses to select the relevant column.
 *
 * @package    block_mytermcourses
 * @author     Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : lang/fr/block_mytermcourses.php
 * French text strings
 * 
 */

$string['pluginname'] = 'Mes cours par semestre';
$string['mytermcourses'] = 'Mes cours par semestre';
$string['mytermcourses:addinstance'] = 'Ajouter un nouveau bloc Mes cours par semestre';
$string['mytermcourses:myaddinstance'] = 'Ajouter un nouveau bloc Mes cours par semestre à mon Tableau de bord';
$string['changetitle'] = 'Titre du bloc';
$string['bgcolor'] = 'Couleur de fond des catégories';
$string['teacher'] = 'Enseignant';
$string['teachers'] = 'Enseignants';
$string['useidnumber'] = 'Trier les cours par idnumber';
$string['commoncategories'] = 'Catégories communes';
$string['notenrolled'] = "Vous n'êtes inscrits à aucun cours";
$string['firstterm'] = 'Premier semestre';
$string['secondterm'] = 'Second semestre';
$string['configidnumber_help'] = 'Pour afficher les cours, les trier par idnumber et pas par sortorder.';
$string['configcommon_help'] = 'Les cours de ces catégories seront affichés pour tous les utilisateurs. Vous voudrez certainement vous assurer que ces cours sont bien ouverts aux utilisateurs non inscrits. Ecrire les id des catégories, avec des symboles ; pour les séparer.';
