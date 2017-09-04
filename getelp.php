<?php
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

echo "<select name='vet'  id='vet' onchange='goesp2()'><option value='-1'>Choisissez votre VET</option>";
if(isset($_REQUEST["idEsp"])){
    $sql = "SELECT id FROM mdl_cat_demandecours WHERE code = '".$_REQUEST["idEsp"]."'";
    $levelid = $DB->get_record_sql($sql)->id;    
    $query = "SELECT * FROM mdl_cat_demandecours WHERE parent_id = '$levelid' ORDER BY name";
    $vets = $DB->get_recordset_sql($query); 
    foreach ($vets as $vet) {
        echo "<option value='".$vet->code."'>".stripslashes($vet->name)."</option>";
    }
    echo "<option value='888888'>Autre</option>";
} else {
    echo "<option value='-1'>VET</option>";
}
echo "</select>";
 
?>