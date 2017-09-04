<?php
require('../../config.php');
include ("jpgraph-3.5.0b1/src/jpgraph.php");
include ("jpgraph-3.5.0b1/src/jpgraph_bar.php");
//phpinfo(); GD => yes
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
echo "<p name='suivietd' id='suivietd'>";

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





















 echo "</p>";
?>