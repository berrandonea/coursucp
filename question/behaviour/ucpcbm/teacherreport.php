<style>
    .qbehaviour_ucpcbm_columnheader {
        background-color:#731472;
        color:white;
        font-weight:bold;
        text-align:center;
    }
    .qbehaviour_ucpcbm_cell {
        text-align:center;
    }
</style>



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

$url = new moodle_url('/question/behaviour/ucpcbm/teacherreport.php', array('id' => $cm->id));
$PAGE->set_url($url);

require_login($course, false, $cm);
$quizcontext = context_module::instance($cm->id);
$PAGE->set_pagelayout('report');
$reporttitle = $quiz->name.' : '.get_string('teacherreport', 'qbehaviour_ucpcbm');
$PAGE->set_title($reporttitle);
$PAGE->set_heading($reporttitle);

//Résultats des étudiants sur ce quiz
$table = 'qbehaviour_ucpcbm_level';
$recordedlevels = $DB->get_records($table, array('cmid' => $cm->id));
$days = array();
$outcomeids = array();
foreach ($recordedlevels as $recordedlevel) {
    if (!in_array($recordedlevel->date, $days)) {
        $days[] = $recordedlevel->date;
    }
    if (!in_array($recordedlevel->outcomeid, $outcomeids)) {
        $outcomeids[] = $recordedlevel->outcomeid;
    }
}
sort($days);
sort($outcomeids);
$nboutcomes = count($outcomeids);
$outcomes = array();
foreach($outcomeids as $outcomeid) {
    $outcome = $DB->get_record('grade_outcomes', array('id' => $outcomeid));
    $outcomes[] = $outcome;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($reporttitle, 2);

if(!$days) {
    echo "Aucun n'étudiant n'a encore passé ce test.<br>";
}

foreach ($days as $day) {
    $explodeddate = explode('-', $day);
    $displayeddate = $explodeddate[2].'/'.$explodeddate[1].'/'.$explodeddate[0];    
    $daytitle = "Au soir du $displayeddate";
    echo $OUTPUT->heading($daytitle, 3);
    $select = "cmid = $cm->id AND date <= '$day'";
    $priorrecords = $DB->get_records_select($table, $select);    
    ?>
    <table>
        <tr>
            <th class='qbehaviour_ucpcbm_columnheader'>Nombre total de tentatives</th>
            <th class='qbehaviour_ucpcbm_columnheader'>Nombre d'étudiants distincts</th>
            <th  class='qbehaviour_ucpcbm_columnheader'>Nb. moyen de tentatives/étudiant</th>
        </tr>
        <tr>
            <?php
            
            $priorstudentids = array();
            foreach ($priorrecords as $priorrecord) {
                if (!in_array($priorrecord->studentid, $priorstudentids)) {
                    $priorstudentids[] = $priorrecord->studentid;
                }
            }
            $nbpriorstudents = count($priorstudentids);
            $nbpriorattempts = 0;
            $studentscores = array();
            foreach ($priorstudentids as $priorstudentid) {
                $studentoutcomelevels = array();
                $select = "cmid = $cm->id AND date <= '$day' AND studentid = $priorstudentid";
                $priorstudentresults = $DB->get_records_select($table, $select, null, 'nbattempts');
                foreach ($priorstudentresults as $priorstudentresult) {
                    $studentnbattempts = $priorstudentresult->nbattempts;
                    $studentoutcomelevels[$priorstudentresult->outcomeid] = $priorstudentresult->level;
                }
                $nbpriorattempts += $studentnbattempts;
                $studentscores[$priorstudentid] = $studentoutcomelevels;
            }
            
            echo "<td class='qbehaviour_ucpcbm_cell'>$nbpriorattempts</td>";
            echo "<td class='qbehaviour_ucpcbm_cell'>$nbpriorstudents</td>";
            echo "<td class='qbehaviour_ucpcbm_cell'>".($nbpriorattempts/$nbpriorstudents).'</td>';
            ?>
        </tr>
    </table>
    <p> </p>    
    <table>
        <tr>
            <th class='qbehaviour_ucpcbm_columnheader'>Objectif</th>
            <th class='qbehaviour_ucpcbm_columnheader'>Niveau minimum</th>
            <th class='qbehaviour_ucpcbm_columnheader'>Niveau moyen</th>
            <th class='qbehaviour_ucpcbm_columnheader'>Niveau maximum</th>            
            <th class='qbehaviour_ucpcbm_columnheader'>Etudiants au niveau 4 ou moins</th>
            <th class='qbehaviour_ucpcbm_columnheader'>Etudiants de niveau intermédiaire</th>
            <th class='qbehaviour_ucpcbm_columnheader'>Etudiants au niveau 8 ou plus</th>
        </tr>
        <?php                
        foreach ($outcomes as $outcome) {
            $maxlevel = 0;
            $minlevel = 10;
            $nbstudents = 0;
            $totallevelsum = 0;
            $lowlevelstudents = 0;
            $midlevelstudents = 0;
            $highlevelstudents = 0;
            foreach ($studentscores as $studentscore) {
                $studentlevel = $studentscore[$outcome->id];
                if ($studentlevel > 7) {
                    $highlevelstudents++;
                } else if ($studentlevel > 4) {
                    $midlevelstudents++;
                } else {
                    $lowlevelstudents++;
                }
                if ($studentlevel > $maxlevel) {
                    $maxlevel = $studentlevel;
                }
                if ($studentlevel < $minlevel) {
                    $minlevel = $studentlevel;
                }
                $nbstudents++;
                $totallevelsum += $studentlevel;
            }
            $averagelevel = $totallevelsum / $nbstudents;
            echo '<tr>';
            echo "<td>($outcome->shortname) $outcome->fullname</td>";
            echo "<td class='qbehaviour_ucpcbm_cell'>$minlevel</td>";
            echo "<td class='qbehaviour_ucpcbm_cell'>$averagelevel</td>";
            echo "<td class='qbehaviour_ucpcbm_cell'>$maxlevel</td>";
            echo "<td class='qbehaviour_ucpcbm_cell' style='color:red'>$lowlevelstudents</td>";
            echo "<td class='qbehaviour_ucpcbm_cell' style='color:orange'>$midlevelstudents</td>";
            echo "<td class='qbehaviour_ucpcbm_cell' style='color:green'>$highlevelstudents</td>";
            echo '</tr>';
            reset($studentscores);
        }
        ?>
    </table>
    <br>
    <p> </p>
    <br>
    <?php
    reset($outcomes);
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
            <td  class='qbehaviour_ucpcbm_columnheader'>Objectif</td>
            <td style='background-color:#781472;color:white;font-weight:bold'>Niveau que vous avez estimé</td>
            <td style='background-color:#781472;color:white;font-weight:bold'>Niveau évalué par le test</td>
            <!--<td style='background-color:#781472;color:white;font-weight:bold'>Commentaires</td>-->
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
                    $currentoutcome = endline($outcomenbquestions, $outcomescore, $currentoutcome, $day, $nbattempts, $numattempt, $cm);
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
                        break;
                    }
                }
                echo '</td>';
                $outcomescore = 0;
                $outcomenbquestions = 0;
                $currentoutcome = $outcome->id;
            } else {
                $outcomenbquestions++;
                $gradesql = "SELECT fraction FROM {question_attempt_steps} WHERE questionattemptid = $questionattempt->id AND fraction IS NOT NULL";
                $questiongrade = $DB->get_record_sql($gradesql);                
                $outcomescore += $questiongrade->fraction;
            }
        }
        if ($currentoutcome) {
            $currentoutcome = endline($outcomenbquestions, $outcomescore, $currentoutcome, $day, $nbattempts, $numattempt, $cm);
        }
    echo '</table>';
    echo '<br><p> </p>';
}

$quizurl = new moodle_url('/mod/quiz/view.php', array('id' => $cm->id));
echo "<br><p style='text-align:center'><a href='$quizurl'><button>OK</button></a></p>";


echo $OUTPUT->footer();

function endline($outcomenbquestions, $outcomescore, $currentoutcome, $day, $nbattempts, $numattempt, $cm) {
    echo '<td style="text-align:center">';
    $outcomescore += 5 * $outcomenbquestions; // Mauvaise réponse, sûr et certain : -5pts
    $evaluatedlevel = ceil($outcomescore * 10 // Pour obtenir une note sur 10
            / (10 * $outcomenbquestions)); // Ecart entre bonne et mauvaise réponse pour "sûr et certain" : 10
    studentdisplay($evaluatedlevel);
    if ($numattempt == $nbattempts) {
        recordlevel($currentoutcome, $day, $nbattempts, $cm, $evaluatedlevel);
    }
    return 0;
}

function studentdisplay($evaluatedlevel) {
    if ($evaluatedlevel > 7) {
        $color = 'green';
    } else if ($evaluatedlevel > 3) {
        $color = 'orange';
    } else {
        $color = 'red';
    }
    $style = "font-weight:bold;color:$color";
    echo "<span style='$style'>$evaluatedlevel</span>";
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
