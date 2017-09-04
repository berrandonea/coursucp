<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Displays different views of the logs.
 *
 * @package    report_log
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/report/log/locallib.php');
require_once($CFG->libdir.'/adminlib.php');

$id          = optional_param('id', 0, PARAM_INT);// Course ID
$host_course = optional_param('host_course', '', PARAM_PATH);// Course ID

if (empty($host_course)) {
    $hostid = $CFG->mnet_localhost_id;
    if (empty($id)) {
        $site = get_site();
        $id = $site->id;
    }
} else {
    list($hostid, $id) = explode('/', $host_course);
}
$PAGE->set_url('/report/log/fichesuivietudiant.php');
$PAGE->set_pagelayout('report');
$course = $DB->get_record('course', array('id'=>1), '*', MUST_EXIST);
require_login($course);
$context = context_course::instance($course->id);
$sql = "SELECT COUNT(id) AS isteacher FROM mdl_role_assignments WHERE (roleid = 2 OR roleid = 1) AND userid = $USER->id";
$isteacher = $DB->get_record_sql($sql)->isteacher;
if ($isteacher == 0) {
    require_capability('report/log:view', $context);
}
$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_pagelayout('frontpage');

$editing = $PAGE->user_is_editing();
$PAGE->set_title("Les activités de mes étudiants");
$PAGE->set_heading("Les activités de mes étudiants");

$courserenderer = $PAGE->get_renderer('core', 'course');
?>
    <script type="text/javascript">
    var xhr = null; 
    nomcomplet = "Titre du cours";
    function affiche( scriptName, args ){
            var xhr_object = null;
    
            if(window.XMLHttpRequest) // Firefox
                xhr_object = new XMLHttpRequest();
            else if(window.ActiveXObject) // Internet Explorer
                xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
            else { // XMLHttpRequest non support� par le navigateur
                alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
                return xhr_object;
            }
    }
            function getXhr(){
                if(window.XMLHttpRequest) // Firefox et autres
                   xhr = new XMLHttpRequest(); 
                else if(window.ActiveXObject){ // Internet Explorer 
                   try {
                            xhr = new ActiveXObject("Msxml2.XMLHTTP");
                        } catch (e) {
                            xhr = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                }
                else { // XMLHttpRequest non support� par le navigateur 
                   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
                   xhr = false; 
                } 
            }
            function goetudiant(){
                getXhr();
                // On d�fini ce qu'on va faire quand on aura la r�ponse
                xhr.onreadystatechange = function(){
                    // On ne fait quelque chose que si on a tout re�u et que le serveur est ok
                    if(xhr.readyState == 4 && xhr.status == 200){
                        leselect = xhr.responseText;
                        // On se sert de innerHTML pour rajouter les options a la liste
                        document.getElementById('Mesetudiants').innerHTML = leselect;
                        document.getElementById('Mesetudiants').disabled=false;                       
                    }
                }
    
                // Ici on va voir comment faire du post
                xhr.open("POST","getetudiant.php",true);
                // ne pas oublier �a pour le post
                xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                // ne pas oublier de poster les arguments
                // ici, l'id de l'auteur
                sel = document.getElementById('Mescours');
                idgenre = sel.options[sel.selectedIndex].value;
                xhr.send("idGenre="+idgenre);
            }
            function cordetudiant(){
                getXhr();
                // On d�fini ce qu'on va faire quand on aura la r�ponse
                xhr.onreadystatechange = function(){
                    // On ne fait quelque chose que si on a tout re�u et que le serveur est ok
                    if(xhr.readyState == 4 && xhr.status == 200){
                        leselect = xhr.responseText;
                        // On se sert de innerHTML pour rajouter les options a la liste
                        document.getElementById('cordetd').innerHTML = leselect;
                        document.getElementById('cordetd').disabled=false;                       
                    }
                }
    
                // Ici on va voir comment faire du post
                xhr.open("POST","getcordetudiant.php",true);
                // ne pas oublier �a pour le post
                xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                // ne pas oublier de poster les arguments
                // ici, l'id de l'auteur
                sel = document.getElementById('Mesetudiants');
                idgenre = sel.options[sel.selectedIndex].value;
                xhr.send("idGenres="+idgenre);
            }
   
            function affichebuttonsub()
            {
                document.getElementById("autrecours").style.display="block";
            }
            function goesp(){
                getXhr();
                // On d�fini ce qu'on va faire quand on aura la r�ponse
                xhr.onreadystatechange = function(){
                    // On ne fait quelque chose que si on a tout re�u et que le serveur est ok
                    if(xhr.readyState == 4 && xhr.status == 200){
                        leselect = xhr.responseText;
                        // On se sert de innerHTML pour rajouter les options a la liste
                        document.getElementById('vet').innerHTML = leselect;
                          document.getElementById('vet').disabled=false;  
    
                    }
                }
    
                // Ici on va voir comment faire du post
                xhr.open("POST","getelp.php",true);
                // ne pas oublier �a pour le post
                xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                // ne pas oublier de poster les arguments
                // ici, l'id de l'auteur
                sel = document.getElementById('Mesetudiants');
                idesp = sel.options[sel.selectedIndex].value;
                xhr.send("idEsp="+idesp);
            }
            function recupertitre() {
                titrelong = document.getElementById('Mescours').options[document.getElementById('Mescours').selectedIndex].text;
                //document.getElementById('titrelongcache').value = titrelong;
            }
            
           </script>
<?php 

echo $OUTPUT->header();

  

echo $OUTPUT->heading('Les activités de mes étudiants');
//les cours de l'enseignant
$query ="SELECT c.fullname,c.id
                                    FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c
                                    WHERE u.id = r.userid
                                    AND r.contextid = cx.id
                                    AND cx.instanceid = c.id
                                    AND r.roleid =3
                                    AND cx.contextlevel =50
                                    AND u.id = ".$USER->id;
$mescours = $DB->get_recordset_sql($query); 
//echo $mescours->fullname;
echo "<tr><td>Mes cours :&nbsp;&nbsp;<select name='Mescours' id ='Mescours' onchange='goetudiant()' style='width:200px'>"; 
echo " <option value='-1'>Choisissez votre cours</option>";
		foreach ($mescours as $cc)
		{
			
			echo"<option value ='$cc->id'>$cc->fullname</option>&nbsp;&nbsp;"; 
		}
echo"</select></td></tr> "; 
//Mes étudiants

echo "<tr><td>Mes étudiants :&nbsp;&nbsp;<select name='Mesetudiants' id='Mesetudiants' onchange='cordetudiant()' style='width:200px' disabled>"; 
echo " <option value='-1'>Choisissez un étudiant</option>";

echo"</select></td></tr><br></table>"; 
echo "<p name='cordetd' id='cordetd'>";
echo "</p>";

/*echo "<strong><h2 id='autrecours' style='display:none;'>Les coordonnées de l'étudiant :</h2></strong><br>";
$query="select id,firstname,lastname, email,picture from mdl_user where id ='".$_REQUEST["Mesetudiants"]."'";
$coordonnesetd = $DB->get_record_sql($query);
 $studentobject = new stdClass;
  $studentobject->id = $coordonnesetd->id;
 $studentobject->picture = $coordonnesetd->picture;
 print_object($studentobject);
 $pictureheight = 50;
 $userpicture = $OUTPUT->user_picture($studentobject, array('size'=>$pictureheight, 'alttext'=>false, 'link'=>false));
 $picturearray = explode('"', $userpicture);
 print_object($picturearray);
echo "<table><tr><td><strong>Nom :</strong></td><td>$coordonnesetd->lastname</td>";
echo "<td><strong>Prénom :</strong></td><td>$coordonnesetd->firstname</td>";
echo "<td><strong>Email :</strong></td><td>$coordonnesetd->email</td></tr>";


   echo "<image id='picture$coordonnesetd->id' x='60' y='50' width='50px' height='$pictureheight' src='$picturearray[1]' />"; */
    
//echo "<tr><td><img src=images/$coordonnesetd->picture width= 60px height=60px></td</tr></table>";












echo $OUTPUT->footer();


