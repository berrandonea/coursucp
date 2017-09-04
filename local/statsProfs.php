<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require('../config.php');

global $DB, $CFG;

require_once($CFG->libdir . '/csvlib.class.php');

if (is_siteadmin()) {

    $csvexporter = new csv_export_writer();

    $csvexporter->set_filename('statsprofs');

    $title = array(utf8_decode('Statistiques Profs'));
    $csvexporter->add_data($title);

    $columnname = array(utf8_decode('Nom catégorie'), utf8_decode('Nombre profs actifs'),
        utf8_decode('Nombre profs totals avec cours'), utf8_decode('Nombre profs totals'));

//    $fieldidtypeprof = $DB->get_record('user_info_field', array('shortname' => 'typeprof'))->id;
    $sqllisttypeprofs = "SELECT distinct typeteacher FROM {teacher_type} WHERE 1";
    $templisttypeprofs = $DB->get_records_sql($sqllisttypeprofs, array());
    $listtypeprofs = array();

    foreach ($templisttypeprofs as $temptypeprof) {

        $listtypeprofs[] = $temptypeprof->typeteacher;
    }

    $csvexporter->add_data($columnname);


    $sqlcategories = "SELECT distinct ufrcode FROM {ufr_teacher}";
    $listcategories = $DB->get_records_sql($sqlcategories);
    $numberother = 0;
    $numberotherwithcourses = 0;
    $numberothertotal = 0;

    $numberothertype = array();
    $numberotherwithcoursestype = array();
    $numberothertotaltype = array();

    foreach ($listcategories as $category) {

//        print_object("DEBUT");
//
//        print_object($category);
//        print_object($category->ufrcode);

        switch ($category->ufrcode) {

            case 2 :

                $categoryname = "DOIP";
                break;

            case 'C' :

                $categoryname = "Sciences PO";
                break;

            case 'S' :

                $categoryname = "Sciences et Techniques";
                break;

            case 'L' :

                $categoryname = "Langues Etudes Internationales";
                break;

            case 'E' :

                $categoryname = "Economie et Gestion";
                break;

            case 'D' :

                $categoryname = "Droit";
                break;

            case 'I' :

                $categoryname = "IUT";
                break;

            case 'H' :

                $categoryname = "Lettres et sciences humaines";
                break;

            case 'V' :

                $categoryname = "ESPE";
                break;

            default :

                $categoryname = "Autre";
                break;
        }

//        print_object($categoryname);

        $sqllistteachersincategory = "SELECT distinct userid FROM {ufr_teacher} WHERE ufrcode LIKE ?";
        $listteachersincategory = $DB->get_records_sql($sqllistteachersincategory, array($category->ufrcode));

//        $listteachersincategory = $DB->get_records('ufr_teacher', array('ufrcode' => $category->ufrcode));

//        print_object($listteachersincategory);

        $finallistteacherincategory = array();
        $listteacherincategorywithcourses = array();

        foreach ($listteachersincategory as $teacherincategory) {

            $firstaccess = $DB->get_record('user', array('id' => $teacherincategory->userid))->firstaccess;
            $hasteacherrole = 0;

            if ($DB->record_exists('role_assignments',
                    array('userid' => $teacherincategory->userid, 'roleid' => 3)) ||
                    $DB->record_exists('role_assignments',
                    array('userid' => $teacherincategory->userid, 'roleid' => 4))) {

                $hasteacherrole = 1;
            }

            if ($firstaccess > 0 && $hasteacherrole == 1) {

                $finallistteacherincategory[] = $teacherincategory->userid;
            }
            
            if ($hasteacherrole == 1) {

                $listteacherincategorywithcourses[] = $teacherincategory->userid;
            }
        }

        if ($categoryname != "Autre") {
            
            $resultcategory = array();
            $resultcategory[] = utf8_decode($categoryname);
            $resultcategory[] = count($finallistteacherincategory);
            $resultcategory[] = count($listteacherincategorywithcourses);
            $resultcategory[] = count($listteachersincategory);
            $csvexporter->add_data($resultcategory);
        } else {

            $numberother += count($finallistteacherincategory);
            $numberotherwithcourses += count($listteacherincategorywithcourses);
            $numberothertotal += count($listteachersincategory);
        }

        foreach ($listtypeprofs as $typeprof) {


            $listteacheroftype = array();
            $listteacheroftypewithcourses = array();
            $listteacheroftypetotal = array();

            foreach ($finallistteacherincategory as $teacherincategory) {

                $sqlexists = "SELECT * FROM {teacher_type} WHERE userid = ? AND typeteacher LIKE ?";

                if ($DB->record_exists_sql($sqlexists,
                        array($teacherincategory, $typeprof))) {

                    $listteacheroftype[] = $teacherincategory;
                }
            }

            foreach ($listteacherincategorywithcourses as $teacherincategorywithcourses) {

                $sqlexists = "SELECT * FROM {teacher_type} WHERE userid = ? AND typeteacher LIKE ?";

                if ($DB->record_exists_sql($sqlexists,
                        array($teacherincategorywithcourses, $typeprof))) {

                    $listteacheroftypewithcourses[] = $teacherincategorywithcourses;
                }
            }

            foreach ($listteachersincategory as $teachersincategory) {

                $sqlexists = "SELECT * FROM {teacher_type} WHERE userid = ? AND typeteacher LIKE ?";

                if ($DB->record_exists_sql($sqlexists,
                        array($teachersincategory->userid, $typeprof))) {

                    $listteacheroftypetotal[] = $teachersincategory->userid;
                }
            }

            if ($categoryname != "Autre") {

                $resultlistteacher = array();
                $resultlistteacher[] = utf8_decode('Dont '.$typeprof);
                $resultlistteacher[] = count($listteacheroftype);
                $resultlistteacher[] = count($listteacheroftypewithcourses);
                $resultlistteacher[] = count($listteacheroftypetotal);
                $csvexporter->add_data($resultlistteacher);
            } else {

                $numberothertype[$typeprof] += count($listteacheroftype);
                $numberotherwithcoursestype[$typeprof] += count($listteacheroftypewithcourses);
                $numberothertotaltype[$typeprof] += count($listteacheroftypetotal);
            }
        }

        if ($categoryname != "Autre") {
            
            $emptyline = array();
            $csvexporter->add_data($emptyline);
        }
    }

    $resultcategory = array();
    $resultcategory[] = utf8_decode("Autre");
    $resultcategory[] = $numberother;
    $resultcategory[] = $numberotherwithcourses;
    $resultcategory[] = $numberothertotal;
    $csvexporter->add_data($resultcategory);

    foreach ($listtypeprofs as $typeprof) {

        $resultlistteacher = array();
        $resultlistteacher[] = utf8_decode('Dont '.$typeprof);
        $resultlistteacher[] = $numberothertype[$typeprof];
        $resultlistteacher[] = $numberotherwithcoursestype[$typeprof];
        $resultlistteacher[] = $numberothertotaltype[$typeprof];
        $csvexporter->add_data($resultlistteacher);
    }

    $emptyline = array();
    $csvexporter->add_data($emptyline);

    foreach ($listtypeprofs as $typeprof) {

        // Total par type hors système de composante

        $sqllistteacherwithtype = "SELECT distinct userid FROM {teacher_type}"
                . " WHERE typeteacher LIKE ?";

        $listteacherwithtype = $DB->get_records_sql($sqllistteacherwithtype,
                array($typeprof));
        $listteacherwithtypeandcourses = array();
        $finallistteacherwithtype = array();

        foreach ($listteacherwithtype as $teacherwithtype) {

            $firstaccess = $DB->get_record('user', array('id' => $teacherwithtype->userid))->firstaccess;
            $hasteacherrole = 0;

            if ($DB->record_exists('role_assignments',
                    array('userid' => $teacherwithtype->userid, 'roleid' => 3)) ||
                    $DB->record_exists('role_assignments',
                    array('userid' => $teacherwithtype->userid, 'roleid' => 4))) {

                $hasteacherrole = 1;
            }

            if ($firstaccess > 0 && $hasteacherrole == 1) {

                $finallistteacherwithtype[] = $teacherwithtype->userid;
            }

            if ($hasteacherrole == 1) {

                $listteacherwithtypeandcourses[] = $teacherwithtype->userid;
            }
        }

        // Insérer les données totales
        $resultlistteacherwithtype = array();
        $resultlistteacherwithtype[] = utf8_decode('Total '.$typeprof);
        $resultlistteacherwithtype[] = count($finallistteacherwithtype);
        $resultlistteacherwithtype[] = count($listteacherwithtypeandcourses);
        $resultlistteacherwithtype[] = count($listteacherwithtype);
        $csvexporter->add_data($resultlistteacherwithtype);
    }

    $csvexporter->download_file();

}