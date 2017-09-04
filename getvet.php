<?php
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

echo "<select name='niveau'  id='niveau' onchange='goesp()'> <option value='-1'>Choisissez votre niveau</option>";

if(isset($_REQUEST["idGenre"])){    
    $sql = "SELECT id FROM mdl_cat_demandecours WHERE code = '".$_REQUEST["idGenre"]."'";
    $ufrid = $DB->get_record_sql($sql)->id;
    
    $query = "SELECT * FROM mdl_cat_demandecours WHERE parent_id = '$ufrid' ORDER BY name";
    $levels = $DB->get_recordset_sql($query); 
    foreach ($levels as $level) {
        echo "<option value='".$level->code."'>".$level->name."</option>";
    }
} else {
    echo "<option value='-1'>VET</option>";
}
echo "</select>";
 
?>