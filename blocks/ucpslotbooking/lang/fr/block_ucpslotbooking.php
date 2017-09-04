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
 * File : lang/fr/block_ucpslotbooking.php
 * French texts
 * 
 */

$string['pluginname'] = 'Réservation de créneaux horaires'; // Nom qui apparaîtra dans la liste des plugins.
$string['ucpslotbooking'] = 'Réservation de créneaux horaires'; // Titre qui apparaîtra en haut du bloc.
$string['ucpslotbooking:addinstance'] = 'Ajouter un nouveau bloc Réservation de créneaux horaires';
$string['ucpslotbooking:myaddinstance'] = 'Ajouter un nouveau bloc Réservation de créneaux horaires à mon Tableau de bord';
$string['ucpslotbooking:addlist'] = 'Ajouter une liste de créneaux horaires à réserver';

$string['fieldexample'] = 'Exemple de champ';
$string['newlist'] = 'Nouvelle série';
$string['changetitle'] = 'Titre personnalisé';
$string['description'] = 'Description';
$string['addlistheader'] = 'Nouvelle série de créneaux horaires';
$string['name'] = 'Nom';
$string['On'] = 'Le';
$string['from'] = 'de';
$string['to'] = 'à';
$string['mybookings'] = 'Je suis inscrit';
$string['notyet'] = 'Nulle-part pour le moment.';
$string['dellist'] = 'Supprimer la liste';
$string['editslots'] = 'Modifier les créneaux horaires';
$string['watchbookings'] = 'Voir les réservations';
$string['adddate'] = 'Ajouter une date';
$string['addtime'] = 'Ajouter un horaire';
$string['newdate'] = 'Nouvelle date';
$string['newstarttime'] = 'Nouvelle heure de début';
$string['newendtime'] = 'Nouvelle heure de fin';
$string['datedeleted'] = 'La date a été supprimée, avec tous ses créneaux horaires et réservations, pour toutes les séries de ce bloc.';
$string['timedeleted'] = 'Le créneau horaire a été supprimé, avec toutes ses réservations, pour toutes les dates et séries de ce bloc.';
$string['delete'] = 'Supprimer';
$string['datealphabetic'] = 'Attention ! Les dates seront affichées dans l\'ordre alphabétique. Assurez-vous que ce soit aussi l\'ordre chronologique.';
$string['timealphabetic'] = 'Attention ! Les horaires seront affichés dans l\'ordre alphabétique. Assurez-vous que ce soit aussi l\'ordre chronologique.';
$string['edit'] = 'Modifier';
$string['cancel'] = 'Annuler';
$string['commentslots'] = 'Commenter les créneaux horaires';
$string['timeformat'] = 'Format : HHhMM';
$string['dateformat'] = 'Vous devez l\'indiquer ici au format AAAA-MM-JJ mais les étudiants la verront bien au format JJ/MM/AAAA.';
$string['browsererror'] = 'Votre navigateur ne supporte pas les objets XMLHTTPRequest. Merci de le mettre à jour.';
$string['freerooms'] = 'places libres';
$string['freeroom'] = 'place libre';
$string['nbooked'] = 'inscrits';
$string['nbooked1'] = 'inscrit(e)';
$string['slotscapacity'] = 'Capacité des créneaux horaires';
$string['slotscomments'] = 'Commentaires sur les créneaux utilisés';
$string['updatelist'] = 'Mettre la liste à jour';
$string['booked'] = 'Vous êtes maintenant inscrit(e) à';
$string['mailsent'] = 'Envoi d\'un courriel de confirmation à';
$string['mailerror'] = 'Le courriel de confirmation n\'a pas pu être envoyé. Mais votre réservation est tout de même enregistrée.';
$string['bookings'] = 'Réservations';
$string['login'] = 'Login';
$string['email'] = 'Courriel';
$string['empty'] = 'Vider ce créneau horaire';
$string['emptyall'] = 'Vider tous les créneaux horaires';
$string['unenrol'] = 'Désinscrire';
$string['back'] = 'Voir le tableau';
$string['firstname'] = 'Prénom';
$string['confirmempty'] = 'Tous les étudiants seront désinscrits de tous ces créneaux horaires. Confirmez vous ?';
$string['csvslot'] = 'Exporter les réservations pour CE créneau horaire vers un fichier CSV.';
$string['csvlist'] = 'Exporter les réservations pour TOUS les créneaux horaires vers un fichier CSV.';
$string['writerights'] = 'Pour que l\'export CSV fonctionne, ce site doit disposer des droits d\'écriture sur le dossier';
$string['confirmdelete'] = 'Tous ces créneaux horaires et réservations seront perdus. Confirmez-vous ?';

$string['justclick'] = 'Pour vous inscrire à un créneau, il suffit de cliquer dessus.';
$string['justclicksettinglabel'] = 'Invitation à cliquer';
$string['nbshown'] = 'Nombre(s) affiché(s) par créneau';
$string['nbbooked'] = 'Combien d\'étudiants ont déjà choisi ce créneau';
$string['nbfree'] = 'Combien d\'étudiants peuvent encore réserver ce créneau';
$string['nbboth'] = 'Les deux';
$string['full'] = 'COMPLET';
$string['msgbefore'] = 'En-tête du message de confirmation';
$string['msgafter'] = 'Message de confirmation';
