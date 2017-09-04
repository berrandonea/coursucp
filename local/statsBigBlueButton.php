<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require("../config.php");

global $DB, $CFG;

require_once($CFG->libdir . '/csvlib.class.php');

require_login();

if (is_siteadmin()) {

    $url = new moodle_url('/report/log/statsBigBlueButton.php', null);
    $PAGE->set_url($url);

    $startdate = optional_param('startdate', '', PARAM_TEXT);
    $enddate = optional_param('enddate', '', PARAM_TEXT);

    if($startdate != '' && $enddate != '') {

        $startingtimestamp = strtotime($startdate);

        $startingmonth = date(m, $startingtimestamp);

        $startingyear = date(Y, $startingtimestamp);

        $endingtimestamp = strtotime($enddate);

        $endingmonth = date(m, $endingtimestamp);

        $endingyear = date(Y, $endingtimestamp);

        $lastindex = ($endingyear - $startingyear)*12 + $endingmonth;

        if ($startingtimestamp < $endingtimestamp) {

            $listmonths[] = '';

            $currentyear = $startingyear;
            $currentmonth = $startingmonth;

            while ($currentyear != $endingyear || $currentmonth != $endingmonth+1) {

                $listmonths[] = "$currentmonth/$currentyear";
                $currentmonth ++;
                if ($currentmonth < 10) {
                    $currentmonth = "0".$currentmonth;
                }
                if($currentmonth > 12) {

                    $currentmonth = "01";
                    $currentyear++;
                }
            }

            $csvexporter = new csv_export_writer();

            $csvexporter->set_filename('stats BigBlueButton');

            $title = array('Statistiques');
            $csvexporter->add_data($title);
            $csvexporter->add_data($listmonths);

            $selectvisiocreated = "event = ? AND timecreated > ? AND timecreated < ?";

            $listcreatedvisio = $DB->get_records_select('bigbluebuttonbn_log', $selectvisiocreated,
                    array("Create", $startingtimestamp, $endingtimestamp));

            processline($csvexporter, $listcreatedvisio, 'timecreated', 'Nombre de visios créés',
                    $startingyear, $startingmonth, $lastindex);


            // Trier par mois.
            $internmonth = $startingmonth;
            $internyear = $startingyear;
            $interntimestamp = $startingtimestamp;
            $firstturn = true;

            $finallistdistinctstudentjoined = array();
            $finallistdistinctteacherjoined = array();
            $finallistdistinctmanagerjoined = array();
            $finallistdistinctstudentjoined[] = "Nombre d'étudiants distincts";
            $finallistdistinctteacherjoined[] = "Nombre de professeurs distincts";
            $finallistdistinctmanagerjoined[] = "Nombre d'ingénieurs pédagogiques distincts";


            foreach($listmonths as $month) {

                if ($firstturn != true) {

                    $arraymonth = explode('/', $month);

                    $arraymonth[0] += 1;

                    if ($arraymonth[0] < 10) {

                        $arraymonth[0] = "0".$arraymonth[0];
                    }
                    if($arraymonth[0] > 12) {

                        $arraymonth[0] = "01";
                        $arraymonth[1]++;
                    }

                    $newdate = $arraymonth[0].'/01/'.$arraymonth[1];
                    $nexttimestamp = strtotime($newdate);

                    if ($nexttimestamp > $endingtimestamp) {

                        $nexttimestamp = $endingtimestamp;
                    }


                    $sqllistdistinctvisiojoined = "SELECT distinct userid, timecreated FROM"
                            . " {bigbluebuttonbn_log} WHERE"
                            . " (event LIKE ? OR event LIKE ?) AND timecreated > ? AND timecreated < ?";

                    $listdistinctvisiojoined = $DB->get_records_sql($sqllistdistinctvisiojoined,
                            array('Join', 'Create', $interntimestamp, $nexttimestamp));

                    $listdistinctstudentjoined = array();
                    $listdistinctteacherjoined = array();
                    $listdistinctmanagerjoined = array();

                    foreach ($listdistinctvisiojoined as $distinctvisiojoined) {

                        if ($DB->record_exists('role_assignments',
                                array('contextid' => 1,
                                    'userid' => $distinctvisiojoined->userid, 'roleid' => 1))) {

                            $listdistinctmanagerjoined[] = $distinctvisiojoined;
                        } else if ($DB->record_exists('role_assignments',
                                array('contextid' => 1,
                                    'userid' => $distinctvisiojoined->userid, 'roleid' => 2))) {

                            $listdistinctteacherjoined[] = $distinctvisiojoined;
                        } else {

                            $listdistinctstudentjoined[] = $distinctvisiojoined;
                        }
                    }

                    $finallistdistinctstudentjoined[] = count($listdistinctstudentjoined);
                    $finallistdistinctteacherjoined[] = count($listdistinctteacherjoined);
                    $finallistdistinctmanagerjoined[] = count($listdistinctmanagerjoined);

                    $interntimestamp = $nexttimestamp;
                } else {

                    $firstturn = false;
                }
            }

            $csvexporter->add_data($finallistdistinctstudentjoined);
            $csvexporter->add_data($finallistdistinctteacherjoined);
            $csvexporter->add_data($finallistdistinctmanagerjoined);

            // Fin trier par mois

            $sqllistvisiojoined = "SELECT * FROM {bigbluebuttonbn_log} WHERE"
                    . " (event LIKE ? OR event LIKE ?) AND timecreated > ? AND timecreated < ?";

            $listvisiojoined = $DB->get_records_sql($sqllistvisiojoined,
                    array('Join', 'Create', $startingtimestamp, $endingtimestamp));

            $liststudentjoined = array();
            $listteacherjoined = array();
            $listmanagerjoined = array();

            foreach ($listvisiojoined as $visiojoined) {

                if ($DB->record_exists('role_assignments',
                        array('contextid' => 1, 'userid' => $visiojoined->userid, 'roleid' => 1))) {

                    $listmanagerjoined[] = $visiojoined;
                } else if ($DB->record_exists('role_assignments',
                        array('contextid' => 1, 'userid' => $visiojoined->userid, 'roleid' => 2))) {

                    $listteacherjoined[] = $visiojoined;
                } else {

                    $liststudentjoined[] = $visiojoined;
                }
            }

            processline($csvexporter, $liststudentjoined, 'timecreated',
                    'Nombre d\'étudiants total', $startingyear, $startingmonth, $lastindex);

            processline($csvexporter, $listteacherjoined, 'timecreated',
                    'Nombre de professeurs total', $startingyear, $startingmonth, $lastindex);

            processline($csvexporter, $listmanagerjoined, 'timecreated',
                    'Nombre d\'ingénieurs pédagogiques total', $startingyear, $startingmonth, $lastindex);

            $csvexporter->download_file();
        }

    } else {

        echo $OUTPUT->header();

        echo ""
        . "<form action=statsBigBlueButton.php>"
                . "<text>Input Date with format DD-MM-YYYY : </text>"
                . "<input name=startdate type=text />"
                . "<br>"
                . "<text>Ending Date with format DD-MM-YYYY : </text>"
                . "<input name=enddate type=text />"
                . "<br>"
                . "<input type=submit>"
        . "</form>";

        echo $OUTPUT->footer();
    }
}

function processline ($exporter, $listline, $timefield, $linename, $startingyear, $startingmonth, $lastindex) {

    $countelement = array();

    $countelement[] = utf8_decode($linename);

    for ($i = $startingmonth; $i <= $lastindex; $i++) {

        $countelement[$i] = 0;
    }

    foreach ($listline as $lineelement) {

        $monthelement = date(m, $lineelement->$timefield);

        $yearelement = date(Y, $lineelement->$timefield);

        $diffyearelement = $yearelement - $startingyear;

        $idmonthelement = $monthelement + $diffyearelement*12;

        if ($idmonthelement < 10) {

            $idmonthelement = "0".$idmonthelement;
        }

        $countelement[$idmonthelement]++;
    }

    $exporter->add_data($countelement);
}