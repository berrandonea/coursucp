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

$string['modulenameplural'] = 'Quiz adaptatif';
$string['modulename'] = 'Quiz adaptatif';
$string['modulename_help'] = 'L\'activité Quiz adaptatif permet à l\'enseignant de mesurer efficacement les capacités des étudiants. Un quiz adaptatif se compose de questions tirées de la banque de questions, pour chacune desquelles un niveau de difficulté est défini.  Si l\'étudiant répond correctement à une question, une question plus difficile lui est posée ensuite. S\'il se trompe, c\'est une question plus facile qui suit. Vers la fin du test, la difficulté des questions converge ainsi vers le niveau réel de l\'étudiant. Le quiz s\'arrête quand le niveau de l\'étudiant est déterminé avec la précision requise. 

Les questions utilisées dans un Quiz adaptatif doivent satisfaire deux conditions :

 * les réponses doivent pouvoir être automatiquement évaluées comme correctes ou incorrectes
 * un niveau de difficulté doit être défini pour chacune, en utilisant \'adpq_\' suivi par un nombre entier positif

Les paramètres du quiz adaptatif permettent d\'ajuster : 

 * l\'intervalle des niveaux de difficultés des questions. 1-10, 1-16, et 1-100 sont des exemples d\'intervalles valides.
 * la précision requise pour interrompre le quiz. Une marge d\'erreur de 5% dans la mesure du niveau de l\'étudiant est généralement appropriée.
 * le nombre minimum de questions auxquelles l\'étudiant doit répondre
 * le nombre maximum de questions auxquelles l\'étudiant doit répondre

This description and the testing process in this activity are based on <a href="http://www.rasch.org/memo69.pdf">Computer-Adaptive Testing: A Methodology Whose Time Has Come</a> by John Michael Linacre, Ph.D. MESA Psychometric Laboratory - University of Chicago. MESA Memorandum No. 69.';
$string['pluginadministration'] = 'Quiz adaptatif';
$string['pluginname'] = 'Quiz adaptatifs';
$string['nonewmodules'] = 'Aucune instance de Quiz adaptatif trouvée';
$string['adaptivequizname'] = 'Titre';
$string['adaptivequizname_help'] = 'Donnez un titre à ce Quiz adaptatif';
$string['adaptivequiz:addinstance'] = 'Ajouter un nouveau Quiz adaptatif';
$string['adaptivequiz:viewreport'] = 'Voir les rapports des Quiz adaptatifs';
$string['adaptivequiz:reviewattempts'] = 'Consulter les copies des étudiants';
$string['adaptivequiz:attempt'] = 'Tentative';
$string['attemptsallowed'] = 'Tentatives autorisées';
$string['attemptsallowed_help'] = 'Combien de fois un étudiant peut-il tenter cette activité ?';
$string['requirepassword'] = 'Mot de passe requis';
$string['requirepassword_help'] = 'Les étudiants doivent saisir un mot de passe avant de commencer leur tentative.';
$string['browsersecurity'] = 'Sécurité du navigateur';
$string['browsersecurity_help'] = 'Si "Pop-up plein écran avec sécurité javascript" est sélectionné, le quiz ne commencera que si le navigateur web de l\'étudiant supporte javascript (ce qui est normalement toujours le cas), le quiz apparait dans une fenêtre pop-up plein-écran qui cache toutes les autres fenêtres, n\'a pas de boutons de navigation et empêche les étudiants d\'utiliser le copier/coller';
$string['minimumquestions'] = 'Nombre minimal de questions';
$string['minimumquestions_help'] = 'Le nombre minimal de questions auxquelles l\'étudiant doit répondre';
$string['maximumquestions'] = 'Nombre maximal de questions';
$string['maximumquestions_help'] = 'Le nombre maximal de questions auxquelles l\'étudiant doit répondre';
$string['startinglevel'] = 'Niveau de difficulté de la première question';
$string['startinglevel_help'] = 'Quand un étudiant commence une tentative, une question de ce niveau est tirée au sort.';
$string['lowestlevel'] = 'Niveau de difficulté minimal';
$string['lowestlevel_help'] = 'Le niveau de difficulté le plus bas à utiliser. Aucune question plus facile ne sera posée aux étudiants.';
$string['highestlevel'] = 'Niveau de difficulté maximal';
$string['highestlevel_help'] = 'Le niveau de difficulté le plus haut à utiliser. Aucune question plus difficile ne sera posée aux étudiants.';
$string['questionpool'] = 'Pool de questions';
$string['questionpool_help'] = 'Quelle catégorie de questions faut-il utiliser pendant une tentative ?';
$string['formelementempty'] = 'Choisissez un nombre entier positif compris entre 1 et 999';
$string['formelementnumeric'] = 'Choisissez un nombre entre 1 et 999';
$string['formelementnegative'] = 'Choisissez un nombre positif entre 1 et 999';
$string['formminquestgreaterthan'] = 'Le nombre minimal de questions doit être inférieur au nombre maximal';
$string['formlowlevelgreaterthan'] = 'Le niveau de difficulté minimal doit être inférieur au niveau maximal';
$string['formstartleveloutofbounds'] = 'Le niveau de difficulté de la première question doit être compris entre le niveau minimal et le niveau maximal.';
$string['standarderror'] = 'Marge d\'erreur acceptée pour interrompre le quiz';
$string['standarderror_help'] = 'Quand le niveau de l\'étudiant est déterminé avec une incertitude inférieure à valeur, le quiz s\'arrête. Valeur par défaut : 5%';
$string['formelementdecimal'] = 'Choisissez un nombre décimal. 5 chiffres maximum après la virgule.';
$string['attemptfeedback'] = 'Feedback sur la tentative';
$string['attemptfeedback_help'] = 'Le feedback sur la tentative est présenté à l\'étudiant une fois sa tentative terminée.';
$string['formquestionpool'] = 'Choisissez au moins une catégorie de questions';
$string['submitanswer'] = 'Envoyer la réponse';
$string['startattemptbtn'] = 'Commencer la tentative';
$string['viewreportbtn'] = 'Voir le rapport';
$string['errorfetchingquest'] = 'Aucune question trouvée pour le niveau {$a->level}';
$string['leveloutofbounds'] = 'Le niveau requis {$a->level} est hors limite pour cette tentative';
$string['errorattemptstate'] = 'Il y a eu une erreur. L\'état de cette tentative ne peut pas être déterminé.';
$string['maxquestattempted'] = 'Nombre maximal de questions tentées';
$string['notyourattempt'] = 'Ceci n\'est pas votre tentative.';
$string['noattemptsallowed'] = 'Vous n\'avez plus de tentatives autorisées pour cette activité.';
$string['completeattempterror'] = 'Erreur lors de l\'enregistrement de la tentative';
$string['updateattempterror'] = 'Error lors de la mise à jour de la tentative';
$string['numofattemptshdr'] = 'Nombre de tentatives';
$string['standarderrorhdr'] = 'Erreur standard';
$string['errorlastattpquest'] = 'Erreur lors de l\'analyse de votre réponse à la dernière question';
$string['errornumattpzero'] = 'Erreur : le nombre de questions tentées est évalué à 0 alors que l\'étudiant a répondu à la question précédente';
$string['errorsumrightwrong'] = 'La somme des nombres de réponses correctes et incorrectes n\'est pas égale au nombre total de questions tentées';
$string['calcerrorwithinlimits'] = 'L\'incertitude de {$a->calerror} sur le niveau de l\'étudiant est dans les limites imposées par l\'activité {$a->definederror}';
$string['missingtagprefix'] = 'Le préfixe est manquant';
$string['recentactquestionsattempted'] = 'Questions tentées: {$a}';
$string['recentattemptstate'] = 'Etat de la tentative:';
$string['recentinprogress'] = 'En cours';
$string['recentcomplete'] = 'Terminée';
$string['functiondisabledbysecuremode'] = 'Cette fonctionnalité est actuellement désactivée.';
$string['enterrequiredpassword'] = 'Saisissez le mot de passe requis.';
$string['requirepasswordmessage'] = 'Pour tenter ce quiz, vous devez connaître son mot de passe.';
$string['wrongpassword'] = 'Mot de passe incorrect';
$string['noattemptrecords'] = 'Aucune tentative enregistrée pour cet étudiant';
$string['attemptstate'] = 'Etat de la tentative';
$string['attemptstopcriteria'] = 'Critère pour arrêter la tentative';
$string['questionsattempted'] = 'Nombre de questions tentées';
$string['attemptfinishedtimestamp'] = 'Tentative terminée à';
$string['backtomainreport'] = 'Back to main reports';
$string['reviewattempt'] = 'Récapitulatif de la tentative';
$string['indvuserreport'] = 'Rapport individuel des tentatives pour {$a}';
$string['activityreports'] = 'Rapport sur les tentatives';
$string['stopingconditionshdr'] = 'Conditions d\'arrêt';
$string['backtoviewattemptreport'] = 'Retour au rapport sur la tentative';
$string['backtoviewreport'] = 'Retour aux rapports principaux';
$string['reviewattemptreport'] = 'Récapitulatif de la tentative de {$a->fullname} envoyée le {$a->finished}';
$string['deleteattemp'] = 'Suppression de la tentative';
$string['confirmdeleteattempt'] = 'Confirmation de la suppression de la tentative de {$a->name} envoyée le {$a->timecompleted}';
$string['attemptdeleted'] = 'La tentative de {$a->name} envoyée le {$a->timecompleted} a été supprimée.';
$string['errordeletingattempt'] = 'L\'enregistrement de cette tentative n\'a pas été trouvé.';
$string['closeattempt'] = 'Fermer la tentative';
$string['confirmcloseattempt'] = 'Êtes-vous certains de vouloir fermer et finaliser cette tentative de {$a->name}?';
$string['confirmcloseattemptstats'] = 'Cette tentative a commencé le {$a->started}. Sa dernière mise à jour date de {$a->modified}.';
$string['confirmcloseattemptscore'] = '{$a->num_questions} réponses. Le score actuel est de {$a->measure} {$a->standarderror}.';
$string['attemptclosedstatus'] = 'Fermée manuellement par {$a->current_user_name} (user-id: {$a->current_user_id}) le {$a->now}.';
$string['attemptclosed'] = 'La tentative a été fermée manuellement.';
$string['errorclosingattempt'] = 'L\'enregistrement de cette tentative n\'a pas été trouvé.';
$string['errorclosingattempt_alreadycomplete'] = 'Cette tentative est déjà terminée, elle ne peut pas être fermée manuellement.';
$string['formstderror'] = 'La valeur doit être comprise entre 0 et 50%';
$string['backtoviewattemptreport'] = 'Retour au récapitulatif de la tentative';
$string['backtoviewreport'] = 'Retour aux rapports principaux';
$string['reviewattemptreport'] = 'Récapitulatif de la tentative de {$a->fullname} envoyée le {$a->finished}';
$string['score'] = 'Score';
$string['bestscore'] = 'Meilleur score';
$string['bestscorestderror'] = 'Erreur standard';
$string['attempt_summary'] = 'Résumé de la tentative';
$string['scoring_table'] = 'Tableau des scores';
$string['attempt_questiondetails'] = 'Détails des questions';
$string['attemptstarttime'] = 'Début de la tentative';
$string['attempttotaltime'] = 'Durée totale (hh:mm:ss)';
$string['attempt_user'] = 'Utilisateur';
$string['attempt_state'] = 'Etat de la tentative';
$string['attemptquestion_num'] = '#';
$string['attemptquestion_level'] = 'Niveau des questions';
$string['attemptquestion_rightwrong'] = 'Vrai/Faux';
$string['attemptquestion_ability'] = 'Mesure du niveau';
$string['attemptquestion_error'] = 'Erreur standard (&plusmn;&nbsp;x%)';
$string['attemptquestion_difficulty'] = 'Difficulté de la question (logits)';
$string['attemptquestion_diffsum'] = 'Somme des difficultés';
$string['attemptquestion_abilitylogits'] = 'Niveau mesuré (logits)';
$string['attemptquestion_stderr'] = 'Erreur standard (&plusmn;&nbsp;logits)';
$string['graphlegend_target'] = 'Niveau visé';
$string['graphlegend_error'] = 'Erreur standard';
$string['unknownuser'] = 'Utilisateur inconnu';
$string['answerdistgraph_title'] = 'Distribution des réponses de {$a->firstname} {$a->lastname}';
$string['answerdistgraph_questiondifficulty'] = 'Niveau de la question';
$string['answerdistgraph_numrightwrong'] = 'Incorrectes (-)  / Correctes (+)';
$string['numright'] = 'Nb. correctes';
$string['numwrong'] = 'Nb. incorrectes';
$string['questionnumber'] = 'Question #';
$string['na'] = '?';
$string['downloadcsv'] = 'Télécharger au format CSV';

$string['grademethod'] = 'Méthode de notation';
$string['gradehighest'] = 'Note la plus haute';
$string['attemptfirst'] = 'Première tentative';
$string['attemptlast'] = 'Dernière tentative';
$string['grademethod_help'] = 'Si plusieurs tentatives sont permises, les méthodes suivantes sont disponibles pour calculer la note finale au quiz:

* Note la plus haute
* Première tentative (toutes les suivantes sont ignorées)
* Dernière tentative (toutes les précédentes sont ignorées)';
$string['resetadaptivequizsall'] = 'Supprimer toutes les tentatives de Quiz adaptatif';
$string['all_attempts_deleted'] = 'Toutes les tentatives de Quiz adaptatif ont été supprimées.';
$string['all_grades_removed'] = 'Toutes les notes de Quiz adaptatif ont été retirées.';

$string['questionanalysisbtn'] = 'Analyser la question';
$string['id'] = 'ID';
$string['name'] = 'Titre';
$string['questions_report'] = 'Rapport sur les question';
$string['question_report'] = 'Rapport sur la question';
$string['times_used_display_name'] = 'Times Used';
$string['percent_correct_display_name'] = '% de bonnes réponses';
$string['discrimination_display_name'] = 'Discrimination';
$string['back_to_all_questions'] = '&laquo; Retour à la liste des questions';
$string['answers_display_name'] = 'Réponses';
$string['answer'] = 'Réponse';
$string['statistic'] = 'Statistiques';
$string['value'] = 'Valeur';
$string['highlevelusers'] = 'Utilisateurs au dessus du niveau de la question';
$string['midlevelusers'] = 'Utilisateurs proches du niveau de la question';
$string['lowlevelusers'] = 'Utilisateurs en dessous du niveau de la question';
$string['user'] = 'Utilisateur';
$string['result'] = 'Résultat';

