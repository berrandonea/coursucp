<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../config.php'); 

global $USER;

$csv = $_POST['csv'];

$tabcsv = explode("£µ£", $csv);

$filename = "csv/workshop".$USER->id."_".time().".csv";
$exportedfile = fopen($filename, 'w');
//var_dump($exportedfile);

foreach ($tabcsv as $csvline) {
    $tabcsvline = explode(";", $csvline);
    fputcsv($exportedfile, $tabcsvline, ";");
}

fclose($exportedfile);
header('Location: '.$filename);
