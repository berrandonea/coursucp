<?php
$string['pluginname'] = 'Comparaison de listes de participants';

$string['comparaison'] = 'Comparaison de listes de participants';
$string['comparaison_info'] = <<<EOS
<p>
ATTENTION : la comparaison peut facilement prendre 30 secondes, voire plus, si le fichier est gros.
</p>
EOS;
$string['enroll'] = 'Comparer';
$string['mailreport'] = 'M\'envoyer un rapport par mail';
$string['creategroups'] = 'Créer le(s) groupe(s) si nécessaire';
$string['creategroupings'] = 'Créer le(s) groupement(s) si nécessaire';
$string['firstcolumn'] = 'La première colonne contient';
$string['roleassign'] = 'Inscrire comme';
$string['idnumber'] = 'Numéro d\'étudiant';
$string['username'] = 'Login';
$string['mail_enrolment_subject'] = 'Inscriptions massives sur {$a}';
$string['mail_enrolment']='
Bonjour,
Vous venez d\'inscrire la liste d\'utilisateurs suivants à votre cours \'{$a->course}\'.
Voici un rapport des opérations :
{$a->report}
Cordialement.
';
$string['email_sent'] = 'email envoyé à {$a}';
$string['im:user_unknown'] = '{$a}  inconnu - ligne ignorée';
$string['im:already_in'] = '{$a} DÉJA inscrit ';
$string['im:enrolled_ok'] = '{$a} inscrit ';
$string['im:error_in'] = 'erreur en inscrivant {$a}';
$string['im:error_addg'] = 'erreur en ajoutant le groupe {$a->groupe}  au cours {$a->courseid} ';
$string['im:error_g_unknown'] = 'erreur groupe {$a} inconnu';
$string['im:error_add_grp'] = 'erreur en ajoutant le groupement {$a->groupe} au cours {$a->courseid}';
$string['im:error_add_g_grp'] = 'erreur en ajoutant le groupe {$a->groupe} au groupement {$a->groupe}';
$string['im:and_added_g'] = ' et ajouté au groupe Moodle {$a}';
$string['im:error_adding_u_g'] = 'impossible d\'ajouter au groupe  {$a}';
$string['im:already_in_g'] = ' DEJA dans le groupe {$a}';
$string['im:stats_i'] = '{$a} inscrits';
$string['im:stats_g'] = '{$a->nb} groupe(s) créé(s) : {$a->what}';
$string['im:stats_grp'] = '{$a->nb} groupement(s) créé(s) : {$a->what}';
$string['im:err_opening_file'] = 'ERREUR en ouvrant le fichier {$a}';

$string['comparaison_help']= <<<EOS
        Glissez votre fichier XML dans le cadre. Il doit être au format attendu. Cliquez sur "Comparer". 
        Quelques dizaines de secondes plus tard, vous verrez deux tableaux. Le premier tableau indique les étudiants 
        présents dans le fichier XML mais pas dans le cours. Ceux qui ont des - sur leur ligne ne sont même pas 
        inscrits à la plateforme (ou alors, comme utilisateurs hors-UCP). Le deuxième tableau montre les étudiants 
        présents dans le cours mais pas dans le fichier XML. Chacun de ces tableau peut être exporté dans un fichier CSV.
EOS;

?>
