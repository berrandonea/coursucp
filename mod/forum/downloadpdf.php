<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../config.php'); 
require_once($CFG->dirroot . '/html2pdf/html2pdf.class.php');
//echo '<link rel="stylesheet" type="text/css" href="style/style.css" />';
//require_once('lib.php');

global $USER;

$html = $_POST['tool'];

//echo $html;


try
{
    $html2pdf = new HTML2PDF('P', 'A4', 'fr');

    $html2pdf->setDefaultFont('Arial');
    $html2pdf->writeHTML($html);    
    
    $html2pdf->Output('historic.pdf');

    header('Location: historic.pdf');

}

catch(HTML2PDF_exception $e) {
    echo $e;
    exit;
}



