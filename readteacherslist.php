<?php
define('CLI_SCRIPT', true);
require_once('config.php');

$filename = 'teacherslist.csv';
$fichiercsv = fopen($filename, 'r');

if ($fichiercsv == FALSE) {
    echo "Impossible d'ouvrir le fichier CSV<br>";
    exit;
}

while (($data = fgetcsv($fichiercsv, 200, ";")) !== FALSE) {
    $nbdata = count($data);
    if ($nbdata == 7) {
        $params = array('courseidnumber' => $data[3], 'username' => $data[5]);
        $already = $DB->record_exists('oldcourses', $params);
        if (!$already) {
            $oldcourse = new stdClass();
            $oldcourse->categoryname = $data[0];
            $oldcourse->oldcourseid = $data[1];
            $oldcourse->coursename = $data[2];
            $oldcourse->courseidnumber = $data[3];
            $oldcourse->format = $data[4];
            $oldcourse->username = $data[5];
            $oldcourse->nbsections = $data[6];
            print_object($oldcourse);
            $oldcourse->id = $DB->insert_record('oldcourses', $oldcourse);
        }
    } else {
	echo $nbdata.'\n';
    }
}
