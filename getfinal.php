<?php

require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

echo "<select name='elp'  id='elp' onchange='affichebuttonsub()'><option value='-1'>Choisissez votre ELP</option>";

if(isset($_REQUEST["idEsp"])){
    $sql = "SELECT id FROM mdl_cat_demandecours WHERE code = '".$_REQUEST["idEsp"]."'";
    $vetid = $DB->get_record_sql($sql)->id;
    
    $query = "SELECT * FROM mdl_cat_demandecours WHERE parent_id = '$vetid' ORDER BY name";


    //$query = "SELECT * FROM mdl_cat_demandecours WHERE parent_id = '".$_REQUEST["idEsp"]."' ORDER BY id";
    $elps = $DB->get_recordset_sql($query); 

    foreach ($elps as $elp) {
        
        echo "<option value='".$elp->code."'>($elp->code) ".stripslashes($elp->name)."</option>";
    }
    echo "<option value='999999'>Autre</option>";
}
else echo "<option value='-1'>VET</option>";

echo "</select>";
 
?>