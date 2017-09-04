<title>
Compare la liste des étudiants d'un cours avec celle d'un fichier xml
</title>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');


$courseid = required_param('id', PARAM_INT);
$filename = required_param('file', PARAM_TEXT);

//Recherche du cours concerné
$sql = "SELECT fullname, shortname FROM mdl_course WHERE id = $courseid";
$coursedata = $DB->get_record_sql($sql);

echo "Comparaison de la liste des étudiants du cours n° $courseid, ($coursedata->shortname) $coursedata->fullname avec le fichier $filename.xml\n";



/* ON CHARGE LE XML */

$xmldoc = new DOMDocument();
$xmldoc->load('/home/referentiel/DOKEOS_Etudiants_Inscriptions.xml');
$xpathvar = new Domxpath($xmldoc);    

$querystudent = $xpathvar->query('//Student');

foreach($querystudent as $result){
    
    $studentuid = $result->getAttribute('StudentUID');

    if($studentuid){
        // ON CHERCHE L'ETUDIANT DANS LA BDD
        $query = $DB->get_record('user', array('username'=>$studentuid));
    

        // SI UTILISATEUR EST DEJA DANS MOODLE      
        
        
        if($query->id) {	
          
            // mise a jour des etudiants qui n'aurait ni nom ni prenom ni mail

            //nom

            if(!$query->firstname) {  
                echo "test<br/>";
                
                $DB->execute("UPDATE mdl_user SET firstname = '".ucwords(strtolower($result->getAttribute('StudentFirstName')))."' WHERE username ='".$studentuid."'");

            }

             if(!$query->lastname) { 
                echo "test<br/>";
                                $DB->execute("UPDATE mdl_user SET lastname = '".ucwords(strtolower($result->getAttribute('StudentName')))."' WHERE username ='".$studentuid."'");

            }
             
             if(!$query->email) { 
                 echo "test<br/>";
                                $DB->execute("UPDATE mdl_user SET email = '".ucwords(strtolower($result->getAttribute('StudentEmail')))."' WHERE username ='".$studentuid."'");

            }   


            }
            // SINON (PAS DANS LA BDD) ALORS => INSCRIPTION DU COMPTE UTILISATEUR
        else {
            
          
            
            $email = $result->getAttribute('StudentEmail');
            
            $lastname = ucwords(strtolower($result->getAttribute('StudentName')));
            $firstname = ucwords(strtolower($result->getAttribute('StudentFirstName')));                       

            $queryyear = $xpathvar->query('//Student[@StudentUID="'.$studentuid.'"]/Annee_universitaire');

            foreach($queryyear as $yearresult) {
                $year = $yearresult->getAttribute('AnneeUniv');

                if ($year == "2014") {

                     $query = $DB->get_record('user', array('username'=>$studentuid));
    

              // SI UTILISATEUR EST DEJA DANS MOODLE      
        
        
                     if(!$query->id) {    

                            $user = new StdClass();
                            $user->auth = 'cas';
                            $user->confirmed = 1;
                            $user->mnethostid = 1;
                            $user->email = $email;
                            $user->username = $studentuid;
                            $user->password = '';
                            $user->lastname = $lastname;
                            $user->firstname = $firstname;
                            $user->timecreated = time();
                            $user->timemodified = time();
                            $user->id = $DB->insert_record('user', $user);                  
                            echo "Nouvel étudiant: $firstname $lastname ($studentuid)\n<br>";
                            $nbstudents++;


                        }




                }
            }
        }
    }
}

/***************************** FIN CONTENT **************************************************************************************************/
/********************************************************************************************************************************************/

?>
