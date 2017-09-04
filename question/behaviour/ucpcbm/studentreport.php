<?php

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');

$id = required_param('id', PARAM_INT);


if (!$cm = get_coursemodule_from_id('quiz', $id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}
if (!$quiz = $DB->get_record('quiz', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}

$url = new moodle_url('/question/behaviour/ucpcbm/studentreport.php', array('id' => $cm->id));
$PAGE->set_url($url);

require_login($course, false, $cm);
$quizcontext = context_module::instance($cm->id);
$PAGE->set_pagelayout('report');
$reporttitle = $quiz->name.' : '.get_string('studentreport', 'qbehaviour_ucpcbm');
$PAGE->set_title($reporttitle);
$PAGE->set_heading($reporttitle);

// Tentatives de cet étudiant sur ce quiz.
$attempts = $DB->get_records('quiz_attempts', array('quiz' => $quiz->id, 'userid' => $USER->id), 'timestart');
$nbattempts = count($attempts);

echo $OUTPUT->header();
echo $OUTPUT->heading($reporttitle, 2);
if(!$nbattempts) {    
    echo "Vous n'avez pas encore passé ce test.<br>";
}
$numattempt = 0;
foreach($attempts as $attempt) {
    $numattempt++;
    $startdate = date('d/m/Y à H\hi', $attempt->timestart);
    if ($numattempt == $nbattempts) {
        $day = date('Y-m-d', $attempt->timestart);
    }    
    $attempttitle = "Tentative $attempt->attempt, le $startdate";
    echo $OUTPUT->heading($attempttitle, 3);
    ?>
    <table>
        <tr>        
            <td style='background-color:#781472;color:white;font-weight:bold'>Objectif</td>
            <td style='background-color:#781472;color:white;font-weight:bold'>Niveau que vous avez estimé</td>
            <td style='background-color:#781472;color:white;font-weight:bold'>Bonnes réponses</td>
            <td style='background-color:#781472;color:white;font-weight:bold'>Niveau évalué par le test</td>            
            <td style='background-color:#781472;color:white;font-weight:bold'>Commentaires</td>
        </tr>
        <?php
        $currentoutcome = 0;
        $quizslots = $DB->get_records('quiz_slots', array('quizid' => $quiz->id), 'slot');
        foreach($quizslots as $quizslot) {
            $slotquestion = $DB->get_record('question', array('id' => $quizslot->questionid));
            $questionattempt = $DB->get_record('question_attempts', array('questionusageid' => $attempt->uniqueid, 'slot' => $quizslot->slot));
            $qas = $DB->get_records('question_attempt_steps', array('questionattemptid' => $questionattempt->id)); // question_attempt_step.

            if (substr($slotquestion->name, 0, 16) == 'Auto-évaluation') {
                if ($currentoutcome) {                    
                    $currentoutcome = endline($outcomenbquestions, $outcomenbanswers, $outcomegoodanswers, $outcomescore, $currentoutcome, $day, $nbattempts, $numattempt, $cm);
                }
                echo '<tr>';
                $outcomename = substr($slotquestion->name, 17);
                $params = array('shortname' => $outcomename);
                $outcome = $DB->get_record('grade_outcomes', $params);
                echo "<td>($outcome->shortname) $outcome->fullname</td>";
                echo '<td style="text-align:center">';
                foreach ($qas as $step) {
                $givenanswer = $DB->get_record('question_attempt_step_data', array('attemptstepid' => $step->id, 'name' => 'answer'));
                if ($givenanswer) {
                        echo $givenanswer->value;
                        echo ' / 10';
                        break;
                    }
                }
                echo '</td>';
                $outcomescore = 0;
                $outcomenbquestions = 0;
                $outcomenbanswers = 0;
                $outcomegoodanswers = 0;
                $currentoutcome = $outcome->id;
            } else {
                $gradesql = "SELECT fraction FROM {question_attempt_steps} WHERE questionattemptid = $questionattempt->id AND fraction IS NOT NULL";
                $questiongrade = $DB->get_record_sql($gradesql);
                $outcomenbquestions++;
                if ($questiongrade) {
                    $outcomenbanswers++;
                    $outcomescore += $questiongrade->fraction;
                    if ($questiongrade->fraction > 0.8) {
                        $outcomegoodanswers++;
                    }
                }                
            }
        }
        if ($currentoutcome) {
            $currentoutcome = endline($outcomenbquestions, $outcomenbanswers, $outcomegoodanswers, $outcomescore, $currentoutcome, $day, $nbattempts, $numattempt, $cm);
        }
    echo '</table>';
    echo '<br><p> </p>';
}

$quizurl = new moodle_url('/mod/quiz/view.php', array('id' => $cm->id));
echo "<p style='text-align:center'><a href='$quizurl'><button>OK</button></a></p>";


echo $OUTPUT->footer();

function endline($outcomenbquestions, $outcomenbanswers, $outcomegoodanswers, $outcomescore, $currentoutcome, $day, $nbattempts, $numattempt, $cm) {
    global $DB;
    echo '<td style="text-align:center">';
    echo "$outcomegoodanswers / $outcomenbquestions";
    echo '</td>';
    echo '<td style="text-align:center">';
    $color = 'black';
    if (($outcomenbquestions == $outcomenbanswers)&&($outcomenbquestions)) {
        $outcomescore += 5 * $outcomenbquestions; // Mauvaise réponse, sûr et certain : -5pts
        $evaluatedlevel = ceil($outcomescore * 10 // Pour obtenir une note sur 10
                / (10 * $outcomenbquestions)); // Ecart entre bonne et mauvaise réponse pour "sûr et certain" : 10
        $color = studentdisplay($evaluatedlevel);
        if ($numattempt == $nbattempts) {
            recordlevel($currentoutcome, $day, $nbattempts, $cm, $evaluatedlevel);
        }
    }
    echo '</td>';    
    $outcomedescription = $DB->get_field('grade_outcomes', 'description', array('id' => $currentoutcome));
    echo "<td style='color:$color'>";
    echo $outcomedescription;
    echo '</td>';
    return 0;
}

function studentdisplay($evaluatedlevel) {
    if ($evaluatedlevel > 7) {
        $color = 'green';
    } else if ($evaluatedlevel > 4) {
        $color = 'orange';
    } else {
        $color = 'red';
    }
    $style = "font-weight:bold;color:$color";
    echo "<span style='$style'>$evaluatedlevel / 10</span>";
    return $color;
}

function recordlevel($currentoutcome, $day, $nbattempts, $cm, $evaluatedlevel) {
    global $DB, $USER;
    
    $contextid = $DB->get_field('context', 'id', array('instanceid' => $cm->course, 'contextlevel' => CONTEXT_COURSE));
    $isstudent = $DB->record_exists('role_assignments', array('roleid' => 5, 'contextid' => $contextid, 'userid' => $USER->id));    
    
    if ($isstudent) {
        $table = 'qbehaviour_ucpcbm_level';
        $params = array('cmid' => $cm->id,
                        'outcomeid' => $currentoutcome, 
                        'studentid' => $USER->id, 
                        'date' => $day);
        $previousrecord = $DB->get_record($table, $params);

        $levelrecord = new stdClass();
        $levelrecord->cmid = $cm->id;
        $levelrecord->outcomeid = $currentoutcome;
        $levelrecord->studentid = $USER->id;
        $levelrecord->nbattempts = $nbattempts;
        $levelrecord->level = $evaluatedlevel;
        $levelrecord->date = $day;

        if ($previousrecord) {
            $levelrecord->id = $previousrecord->id;
            $DB->update_record($table, $levelrecord);            
        } else {
            $levelrecord->id = $DB->insert_record($table, $levelrecord);
        }
    }
}
