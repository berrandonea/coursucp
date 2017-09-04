<title>
CORRECTION DES QUESTIONS D'AUTO-EVALUATION
</title>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');

/************************************************* DEBUT CONTENT ***************************************************************/
// Sert à remplacer des questions comme "Estimez, par un nombre entre 0 et 10, votre maîtrise de ce pré-requis : Math1-derivée."
// par "Estimez, par un nombre entre 0 et 10, votre maîtrise de ce pré-requis : Calculer une dérivée." dans le quiz Mathématiques
// du cours Passeport pour la fac.
/*******************************************************************************************************************************/

$sql = "SELECT * FROM `mdl_question` WHERE `questiontext` LIKE 'Estimez, par un nombre entre 0 et 10, votre maîtrise de ce pré-requis : Math%'";
$autoevalquestions = $DB->get_recordset_sql($sql);
foreach($autoevalquestions as $autoevalquestion) {
    $questionparts = explode(" : ", $autoevalquestion->questiontext);
    echo "$questionparts[1]\n";
    $shortname = substr($questionparts[1], 0, -1);
    echo "$shortname\n";
    $outcome = $DB->get_record('grade_outcomes', array('shortname' => $shortname));
    if ($outcome) {
        echo "$outcome->fullname\n";
        $newquestion = $questionparts[0].' : '.$outcome->fullname;
        echo "$newquestion\n";
        $DB->set_debug(true);
        $DB->set_field('question', 'questiontext', $newquestion, array('id' => $autoevalquestion->id));
        $DB->set_debug(false);
    }    
}
$autoevalquestions->close();

/***************************** FIN CONTENT **************************************************************************************************/
/********************************************************************************************************************************************/

?>
