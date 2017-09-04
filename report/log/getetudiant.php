<?php
require('../../config.php');
echo "<select name='Mesetudiants'  id='Mesetudiants' onchange='affichebuttonsub()'> <option value='-1'>Choisissez votre étudiant</option>";

if(isset($_REQUEST["idGenre"])){
	$query ="SELECT distinct r.id, u.firstname, u.lastname, u.email
                                    FROM mdl_user u, mdl_role_assignments r, mdl_context cx
                                    WHERE u.id = r.userid
                                    AND r.contextid = cx.id
                                    AND r.roleid =5
                                    AND cx.contextlevel =50                           
                                    AND cx.instanceid  = '".$_REQUEST["idGenre"]."'";

$mesetudiants = $DB->get_recordset_sql($query);  

    foreach ($mesetudiants as $me)
     {
        echo"<option value ='$me->id'>$me->firstname $me->lastname</option>&nbsp;&nbsp;"; 
    }
} else {
    echo "<option value='-1'>étudiant</option>";
}
echo "</select>";
 
?>