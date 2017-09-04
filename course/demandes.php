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
 * Gestion des demandes de création de cours et de webconférences
 *
 * @package    core_course
 * @copyright  2015 Brice Errandonea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_login();
if (!coursecat::has_capability_on_any(array('moodle/category:manage'))) {
    // The user isn't able to manage any categories. Lets redirect them to the relevant course/index.php page.
    /* BRICE $url = new moodle_url('/course/index.php');
    if ($categoryid) {
        $url->param('categoryid', $categoryid);
    } */
    $url = "http://sefiap.u-cergy.fr";
    redirect($url);
}

$PAGE->set_title("Tableau de bord du SEFIAP");
$PAGE->set_heading("Tableau de bord du SEFIAP");
$renderer = $PAGE->get_renderer('core_course', 'management');
$renderer->enhance_management_interface();

$previewnode = $PAGE->navigation->add('SEFIAP', navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add('Tableau de bord du SEFIAP', new moodle_url('/course/demandes.php'));
$thingnode->make_active();

/*$displaycategorylisting = ($viewmode === 'default' || $viewmode === 'combined' || $viewmode === 'categories');
$displaycourselisting = ($viewmode === 'default' || $viewmode === 'combined' || $viewmode === 'courses');
$displaycoursedetail = (isset($courseid));*/

echo $renderer->header();

$url = new moodle_url('/course/demandes.php');
$systemcontext = $context = context_system::instance();
$course = null;
$courseid = null;
$category = coursecat::get_default();
$categoryid = $category->id;
$context = context_coursecat::instance($category->id);
$url->param('categoryid', $category->id);

$pageheading = format_string($SITE->fullname, true, array('context' => $systemcontext));

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');


/* BRICE $PAGE->set_title($strmanagement);
$PAGE->set_heading($pageheading); */

?>

<!-- Raccourcis -->
<a href='management.php'>Catégories</a> &nbsp; &nbsp; <a href='../admin/user.php'>Utilisateurs</a> &nbsp; &nbsp; 
<a href='available.php'>Cours disponibles</a> &nbsp; &nbsp; <a href='offrepedago.php'>Offre pédagogique</a> &nbsp; &nbsp;
<a href='https://cours.u-cergy.fr/report/componentsusage/index.php'>Utilisation des composants</a>
<br><br>

<?php 
$taken = optional_param('take', 0, PARAM_INT);
$visiotaken = optional_param('visiotake', 0, PARAM_INT);
$refusing = optional_param('no', 0, PARAM_INT);
$refused = optional_param('refused', 0, PARAM_INT);
$rejectioncause = optional_param('rejectioncause', 0, PARAM_TEXT);
$back = optional_param('back', 0, PARAM_INT);
$created = optional_param('create', 0, PARAM_INT);
$visioaccepted = optional_param('visioaccept', 0, PARAM_INT);
$visioback = optional_param('visioback', 0, PARAM_INT);
$visiorefusing = optional_param('visiorefusing', 0, PARAM_INT);
$visiorefused = optional_param('visiorefused', 0, PARAM_INT);
$visiorejectioncause = optional_param('visiorejectioncause', 0, PARAM_TEXT);


//Je m'en charge (cours)
if ($taken) {
	$sql = "UPDATE mdl_asked_courses SET answererid = $USER->id WHERE id = $taken";
	$DB->execute($sql);
}

//Je m'en charge (visio)
if ($visiotaken) {
	$sql = "SELECT instance FROM mdl_course_modules WHERE id = $visiotaken";
	$takenbbbid = $DB->get_record_sql($sql)->instance;
	$sql = "UPDATE mdl_bigbluebuttonbn SET sefiapid = $USER->id WHERE id = $takenbbbid";
	$DB->execute($sql);
}

//Justifier le rejet d'une demande création de cours
if ($refusing) {
	$sql = "SELECT * FROM mdl_asked_courses WHERE id = $refusing";
	//echo "$sql<br>";
	$refusingcourse = $DB->get_record_sql($sql);
	?>
	
	<h2>Rejet de la demande</h2>
	<table border-collapse>
		<tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>
			<td>Demandée le</td>
			<td>Demandée par</td>
			<td>Composante</td>
			<td>Niveau</td>
			<td>Code VET</td>
			<td>VET</td>
			<td>Code cours</td>
			<td>Cours</td>
			<td>Format</td>
			<td>Description</td>
			<td>Brouillon</td>
		</tr>

		<tr align = 'center'>
			<?php 
			echo "<td>$refusingcourse->askedat</td>";

			$sql = "SELECT username, firstname, lastname FROM mdl_user WHERE id = $refusingcourse->askerid";
			$asker = $DB->get_record_sql($sql);
			echo "<td>$asker->firstname $asker->lastname ($asker->username)</td>";

			$sql = "SELECT name FROM mdl_ufr WHERE code = '$refusingcourse->ufr'";
			$ufr = $DB->get_record_sql($sql)->name;
					
			echo "<td>$ufr</td>";
			echo "<td>$refusingcourse->level</td>";
			echo "<td>$refusingcourse->vetnum</td>";

			if ($refusingcourse->vettitle) {
				$vettitle = $refusingcourse->vettitle;
			} else {
				$sql = "SELECT name FROM mdl_cat_demandecours WHERE code = '$refusingcourse->vetnum'";
				$vettitle = stripslashes($DB->get_record_sql($sql)->name);
				$sql = "UPDATE mdl_asked_courses SET vettitle = '".addslashes($vettitle)."' WHERE id = $refusingcourse->id";
				$DB->execute($sql);
			}
	
			echo "<td>$vettitle</td>";
    		echo "<td>$refusingcourse->elpnum</td>";


    		if ($refusingcourse->elptitle) {
				$elptitle = $refusingcourse->elptitle;
			} else {
				$sql = "SELECT name FROM mdl_cat_demandecours WHERE code = '$refusingcourse->elpnum'";
				$elptitle = stripslashes($DB->get_record_sql($sql)->name);
				$sql = "UPDATE mdl_asked_courses SET elptitle = '".addslashes($elptitle)."' WHERE id = $refusingcourse->id";
				$DB->execute($sql);
			}
			echo "<td>$elptitle</td>";

			echo "<td>$refusingcourse->format</td>";

			if ($refusingcourse->description) {
				$alert = addslashes($refusingcourse->description);
				echo '<td style="cursor:pointer;color:#780D68" onclick="alert(\''.$alert.'\')">Lire</td>';
			} else {
				echo "<td></td>";
			}

			if ($refusingcourse->brouillonid) {
				echo "<td><a href='$CFG->wwwroot/course/view.php?id=$refusingcourse->brouillonid'>Voir</a></td>";
			} else {
				echo "<td></td>";
			}
			?>
		</tr>
	</table>

	<form method='post' action='demandes.php'>
		Motif du refus:<br>
		<textarea required name='rejectioncause' rows='4' cols='50'></textarea>
		<br><br>
		<input type='hidden' name='refused' value='<?php echo $refusing; ?>'>
		<input type='submit' value='Rejeter'>
		&nbsp&nbsp
		<a href='demandes.php'>Annuler</a>
	</form>
	<br><br>
<?php 	
}

//Rejet d'une demande création de cours
if ($refused) {
	$sql = "UPDATE mdl_asked_courses SET answererid = $USER->id, answer = 'Non', answeredat = NOW(), rejectioncause = '".addslashes(str_replace("\r\n", " ", $rejectioncause))."' WHERE id = $refused";
	$DB->execute($sql);
}

//Reconsidération d'une demande déjà traitée précédemment (cours)
if ($back) {
	$sql = "UPDATE mdl_asked_courses SET answererid = $USER->id, answer = '', answeredat = '0000-00-00 00:00:00', rejectioncause = '' WHERE id = $back";
    $DB->execute($sql);
}

//Acceptation d'une demande de création de cours
if ($created) {
    $sql = "SELECT askerid, vetnum, vettitle, elpnum, elptitle, format, brouillonid FROM mdl_asked_courses WHERE id = $created";
    //echo "$sql<br>";
    $createdcourse = $DB->get_record_sql($sql);
    //print_object($createdcourse);
    $askerid = $createdcourse->askerid;

    //On vérifie que le demandeur est bien un enseignant
    $sql = "SELECT id as isteacher FROM mdl_role_assignments WHERE userid = $askerid AND roleid = 2";
    $isteacher = $DB->get_record_sql($sql)->isteacher;
    if (! $isteacher) {
        echo "<h2>Gros soucis : l'utilisateur qui a demandé la création du cours n'est pas un enseignant de l'UCP.</h2>";
        echo "Le cours ne peut donc pas être créé.<br><br>";
        echo "<a href='$CFG->wwwroot/index.php'><button>Continuer</button></a>";
        echo $renderer->footer();
        exit;
    }

    //Si le cours existe déjà
    $sql = "SELECT id, COUNT(id) as alreadycreated FROM mdl_course WHERE idnumber = '$createdcourse->elpnum'";
    $result = $DB->get_record_sql($sql);
    $alreadycreated = $result->alreadycreated;
    if ($alreadycreated) {
        echo "<span style='font-weight:bold,text-align:center'>Le cours $createdcourse->elpnum existe déjà.</span><br>";
        $newcourseid = $result->id;
    } else {
        //Sinon, on le crée
        echo "Création du cours $createdcourse->elpnum<br>";
        $vetcategoryid = createvetifnew($createdcourse->vetnum, $createdcourse->vettitle);
        echo "vetcategoryid: $vetcategoryid<br>";
        $coursedata = array();
        $coursedata = new stdClass;
        $coursedata->fullname = $createdcourse->elptitle;
        $coursedata->category = $vetcategoryid;
        $coursedata->shortname = "Y2017-".$createdcourse->elpnum;
        $coursedata->idnumber = "Y2017-".$createdcourse->elpnum;

        //Si le cours est créé à partir d'un brouillon
        if ($createdcourse->brouillonid) {
                $sql = "UPDATE mdl_course "
                        . "SET fullname = '".addslashes($coursedata->fullname)."', "
                        . "category = $coursedata->category, "
                        . "shortname = '$coursedata->shortname', "
                        . "idnumber = '$coursedata->idnumber' "
                        . "WHERE id = $createdcourse->brouillonid";
                echo "$sql<br>";
                $DB->execute($sql);
                $newcourseid = $createdcourse->brouillonid;
                echo "newcourseid : $newcourseid<br>";
        } else {
                if ($createdcourse->format == "Une section par semaine") {
                        $coursedata->format = "weeks";
                } else {
                        $coursedata->format = "topics";
                }

                echo "Création proprement dite<br>";
                $newcourse = create_course($coursedata);
                
                $newcoursecontext = context_course::instance($newcourse->id, MUST_EXIST);
                $newcourseid = $newcourse->id;

                //On inscrit l'enseignant demandeur au cours, comme enseignant (s'il ne l'est pas déjà)
                $sql = "SELECT id FROM mdl_enrol WHERE enrol = 'manual' AND courseid = $newcourseid";
                $enrolid = $DB->get_record_sql($sql)->id;
                $now = time();
                $sql = "INSERT INTO mdl_user_enrolments (enrolid, userid, timestart, modifierid, timecreated, timemodified) VALUES ($enrolid, $askerid, $now, $USER->id, $now, $now)";
                $DB->execute($sql);
                $sql = "INSERT INTO mdl_role_assignments (roleid, contextid, userid, timemodified, modifierid) VALUES (3, $newcoursecontext->id, $askerid, $now, $USER->id)";
                $DB->execute($sql);
        }

        //Envoi d'un mail annonçant la création du cours
        $sql = "SELECT email FROM mdl_user WHERE id = $createdcourse->askerid";
        $askermail = $DB->get_record_sql($sql);
        $to  = $askermail->email;
        $subject = 'Cours créé - Plateforme Pédagogique';
        $message = "
                    <html>
                        <head>
                            <title>Cours créé - Plateforme Pédagogique</title>
                        </head>
                        <body>
                            <p>Bonjour,</p>
                            <p>Votre cours $coursedata->fullname a été créé. Vous pouvez y accéder à l'adresse <a href='$CFG->wwwroot/course/view.php?id=$newcourseid'>$CFG->wwwroot/course/view.php?id=$newcourseid</a>.</p>
                            <p>Nous vous invitons à consulter les <a href='$CFG->wwwroot/course/view.php?id=15'>'Tutoriels pour les enseignants'</a> pour tirer le meilleur parti de la plateforme.</p>
                            <p>Bonne préparation de cours.</p>
                            <p>L'équipe du SEFIAP</p>
                        </body>
                    </html>";

        // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

        // En-têtes additionnels
        //$headers .= 'Bcc: salma.el-mrabah@u-cergy.fr,brice.errandonea@u-cergy.fr,noa.randriamalaka@u-cergy.fr' . "\r\n";

        // Envoi
        if(mail($to, $subject, $message, $headers))	{
                echo "<fieldset style='padding : 10px; width: 98%;font-weight : bold; background-color:green; color:white;''>L'e-mail de confirmation a bien été envoyé à l'enseignant.</fieldset>";
        } else {
                echo "<fieldset style='padding : 10px; width: 98%;font-weight : bold; background-color:red; color:white;''>Erreur d'envoi du message de confirmation à l'enseignant.</fieldset>";
        }

    }

    echo "La demande est satisfaite.<br><br>";
    $sql = "UPDATE mdl_asked_courses SET answererid = $USER->id, answer = 'Oui', answeredat = NOW(), courseid = $newcourseid WHERE id = $created";
    $DB->execute($sql);

    if (!$alreadycreated) {
            //On redirige l'ingénieur pédagogique vers la page d'inscription des utilisateurs, pour qu'il puisse inscrire l'enseignant à son propre cours.
            echo "Redirection<br>";
            redirect(new moodle_url('/enrol/users.php', array('id'=>$newcourseid)));
    }
}

//Acceptation d'une webconférence
if ($visioaccepted) {
	$sql = "SELECT instance FROM mdl_course_modules WHERE id = $visioaccepted";
	$acceptedbbbid = $DB->get_record_sql($sql)->instance;
	$sql = "UPDATE mdl_bigbluebuttonbn SET sefiap = 2, sefiapid = $USER->id, sefiaptime = NOW() WHERE id = $acceptedbbbid";
	$DB->execute($sql);
}

//Refus d'une webconférence
if ($visiorefused) {
	$sql = "SELECT instance FROM mdl_course_modules WHERE id = $visiorefused";
	$refusedbbbid = $DB->get_record_sql($sql)->instance;
	$sql = "UPDATE mdl_bigbluebuttonbn SET sefiap = 1, sefiapid = $USER->id, sefiaptime = NOW(), rejectioncause = '".addslashes(str_replace("\r\n", " ", $visiorejectioncause))."' WHERE id = $refusedbbbid";
    $DB->execute($sql);
}

//Reconsidération d'une demande déjà traitée précédemment (visio)
if ($visioback) {
	$sql = "SELECT instance FROM mdl_course_modules WHERE id = $visioback";
	$restoredbbbid = $DB->get_record_sql($sql)->instance;
    $sql = "UPDATE mdl_bigbluebuttonbn SET sefiap = 0, sefiapid = $USER->id, rejectioncause = '' WHERE id = $restoredbbbid";
	$DB->execute($sql);
}

//Justification du rejet d'une webconférence
if ($visiorefusing) {
	?>
    <h2>Refus de la webconférence</h2>
    <table border-collapse>
        <tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>
        	<td>Demandée le</td>
        	<td>Cours</td>
       		<td>Nom</td>
       		<td>Enregistrée ?</td>
       		<td>Début</td>
       		<td>Fin</td>
       		<td>Durée (minutes)</td>
       		<td>Enseignants</td>
       	</tr>
       	<tr align = 'center'>
			<?php
    		$sql = "SELECT cm.added, cm.course, bbb.name, bbb.record, bbb.openingtime, bbb.closingtime "
    	   		. "FROM mdl_course_modules cm, mdl_bigbluebuttonbn bbb "
       			. "WHERE cm.id = $visiorefusing AND cm.instance = bbb.id";
    		$refusingvisio = $DB->get_record_sql($sql);
                $refusingvisio->timeduration = $refusingvisio->closingtime - $refusingvisio->openingtime;
        	$askedat = date("Y-m-d H:i:s", $refusingvisio->added);
        	echo "<td>$askedat</td>";

        	$sql = "SELECT fullname FROM mdl_course WHERE id = $refusingvisio->course";
        	$incoursename = $DB->get_record_sql($sql)->fullname;
        	echo "<td><a href='view.php?id=$refusingvisio->course'>$incoursename</a></td>";
        	echo "<td>$refusingvisio->name</td>";

        	if ($refusingvisio->record) {
        		echo "<td>Oui</td>";
        	} else {
        		echo "<td>Non</td>";
        	}

			$visiostart = date("Y-m-d H:i:s", $refusingvisio->openingtime);
			echo "<td>$visiostart</td>";
			$visioend = date("Y-m-d H:i:s", $refusingvisio->closingtime);
			echo "<td>$visioend</td>";
			echo "<td>".floor($refusingvisio->timeduration/60)."</td>";

        	$sql = "SELECT id FROM mdl_context WHERE contextlevel = 50 and instanceid = $refusingvisio->course";
   			$visiocoursecontextid = $DB->get_record_sql($sql)->id;
        	echo "<td><a href='../user/index.php?contextid=$visiocoursecontextid&sifirst=&silast=&roleid=3'>Voir</a></td>";
        	?>
   		</tr>
	</table>
    </p>  <!-- Balise probablement inutile -->

    <form method='post' action='demandes.php'>
        Motif du refus:<br>
        <textarea required name='visiorejectioncause' rows='4' cols='50'></textarea>
        <br><br>
        <input type='hidden' name='visiorefused' value='<?php echo $visiorefusing; ?>'>
        <input type='submit' value='Refuser'>
        &nbsp&nbsp
        <a href='demandes.php'>Annuler</a>
    </form>
    <br><br>
    <?php 
}
?>

<!-- Tableau des demandes création de cours en attente -->

<h2>Demandes de création de cours en attente</h2>
<table border-collapse>
	<tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>
		<td>Demandée le</td>
		<td>Demandée par</td>
  		<td>Composante</td>
  		<td>Niveau</td>
    	<td>Code VET</td>
    	<td>VET</td>
    	<td>Code cours</td>
    	<td>Cours</td>
    	<td>Format</td>
    	<td>Description</td>
    	<td>Brouillon</td>
    	<td>Prise en charge par</td>
    </tr>
	<tr align = 'center'>
    	<?php 
    	$sql = "SELECT * FROM mdl_asked_courses WHERE answer = '' ORDER BY askedat ASC";
    	$askedcourses = $DB->get_recordset_sql($sql);

    	foreach($askedcourses as $askedcourse) {    
        	echo "<td>$askedcourse->askedat</td>";

        	$sql = "SELECT username, firstname, lastname FROM mdl_user WHERE id = $askedcourse->askerid";
        	$asker = $DB->get_record_sql($sql);
        	echo "<td>$asker->firstname $asker->lastname ($asker->username)</td>";

        	$sql = "SELECT name FROM mdl_ufr WHERE code = '$askedcourse->ufr'";
        	$ufr = $DB->get_record_sql($sql)->name;
        	echo "<td>$ufr</td>";
			echo "<td>$askedcourse->level</td>";
			echo "<td>$askedcourse->vetnum</td>";
			if ($askedcourse->vettitle) {
    			$vettitle = $askedcourse->vettitle;
        	} else {
            	$sql = "SELECT name FROM mdl_cat_demandecours WHERE code = '$askedcourse->vetnum'";
            	$vettitle = stripslashes($DB->get_record_sql($sql)->name);
        		$sql = "UPDATE mdl_asked_courses SET vettitle = '".addslashes($vettitle)."' WHERE id = $askedcourse->id";
        		$DB->execute($sql);
        	}

        	echo "<td>$vettitle</td>";
     		echo "<td>$askedcourse->elpnum</td>";

        	if ($askedcourse->elptitle) {
        		$elptitle = $askedcourse->elptitle;
        	} else {
                        $sql = "SELECT name FROM mdl_cat_demandecours WHERE code = '$askedcourse->elpnum'";
        		$elptitle = stripslashes($DB->get_record_sql($sql)->name);
                        if ($askedcourse->suffixe) {
                            $elptitle .= " - ".stripslashes($askedcourse->suffixe);
                        }
                        $sql = "UPDATE mdl_asked_courses SET elptitle = '".addslashes($elptitle)."' WHERE id = $askedcourse->id";
                        $DB->execute($sql);
        	}

        	echo "<td>$elptitle</td>";
  			echo "<td>$askedcourse->format</td>";

   			if ($askedcourse->description) {
	  			$alert = addslashes($askedcourse->description);       		
      			echo "<td style='cursor:pointer;color:#780D68' onclick='javascript:lire(description$askedcourse->id)'>Lire</td>";
       		} else {
    	   		echo "<td></td>";
        	}

        	if ($askedcourse->brouillonid) {
        		echo "<td><a href='$CFG->wwwroot/course/view.php?id=$askedcourse->brouillonid'>Voir</a></td>";
        	} else {
        		echo "<td></td>";
        	}
        	 
        	if ($askedcourse->answererid) {
        		$sql = "SELECT firstname FROM mdl_user WHERE id = $askedcourse->answererid";
        		$answerer = $DB->get_record_sql($sql);
        		echo "<td>$answerer->firstname</td>";
        	} else {
            	echo "<td></td>";
        	}

	        echo "<td><a border=1 href='demandes.php?take=$askedcourse->id'>Je m'en charge</a></td>";
    	    echo "<td><a href='demandes.php?no=$askedcourse->id'>Rejeter</a></td>";
        	echo "<td><a href='demandes.php?create=$askedcourse->id'>Créer</a></td>";
        	

        echo "</tr>";
        echo "<tr id='description$askedcourse->id' style='display:none'>";
        echo "<td colspan = 9>";
        echo $askedcourse->description;
        echo "</td>";
        echo "</tr>";
	}
	?>
</table>

<br><br>

<!-- Tableau des demandes création de cours rejetées -->
<h2>Demandes de création de cours rejetées</h2>
<span id='rejectedshower' onclick='showrejected()' style='cursor:pointer;color:#780D68;display:block'>Montrer</span>

<p id='rejectedrequests' style='display:none'>
	<span onclick='hiderejected()' style='cursor:pointer;color:#780D68'>Cacher</span>
    <table border-collapse >
    	<tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>
    		<td>Demandée le</td>
    		<td>Demandée par</td>
    		<td>Composante</td>
    		<td>Niveau</td>
    		<td>Code VET</td>
    		<td>VET</td>
    		<td>Code cours</td>
    		<td>Cours</td>
    		<td>Format</td>
    		<td>Description</td>
    		<td>Brouillon</td>
    		<td>Rejetée par</td>
    		<td>Rejetée le</td>
    		<td>Motif</td>
    	</tr>
		<?php 
   		$sql = "SELECT * FROM mdl_asked_courses WHERE answer = 'Non' ORDER BY askedat DESC";
  		$rejectedcourses = $DB->get_recordset_sql($sql);

   		foreach($rejectedcourses as $rejectedcourse) {
	   		echo "<tr align = 'center'>";
       		echo "<td>$rejectedcourse->askedat</td>";

       		$sql = "SELECT username, firstname, lastname FROM mdl_user WHERE id = $rejectedcourse->askerid";
        	$asker = $DB->get_record_sql($sql);
        	echo "<td>$asker->firstname $asker->lastname ($asker->username)</td>";

        	$sql = "SELECT name FROM mdl_ufr WHERE code = '$rejectedcourse->ufr'";
        	$ufr = $DB->get_record_sql($sql)->name;        
        	echo "<td>$ufr</td>";
			echo "<td>$rejectedcourse->level</td>";
			echo "<td>$rejectedcourse->vetnum</td>";

        	if ($rejectedcourse->vettitle) {
            	$vettitle = $rejectedcourse->vettitle;
        	} else {
            	$sql = "SELECT name FROM mdl_cat_demandecours WHERE code = '$rejectedcourse->vetnum'";
            	$vettitle = stripslashes($DB->get_record_sql($sql)->name);
            	$sql = "UPDATE mdl_asked_courses SET vettitle = '".addslashes($vettitle)."' WHERE id = $rejectedcourse->id";
				$DB->execute($sql);
        	}
        
        	echo "<td>$vettitle</td>";
        	echo "<td>$rejectedcourse->elpnum</td>";

        	if ($rejectedcourse->elptitle) {
        		$elptitle = $rejectedcourse->elptitle;
        	} else {
            	$sql = "SELECT name FROM mdl_cat_demandecours WHERE code = '$rejectedcourse->elpnum'";
       			$elptitle = stripslashes($DB->get_record_sql($sql)->name);
       			$sql = "UPDATE mdl_asked_courses SET elptitle = '".addslashes($elptitle)."' WHERE id = $rejectedcourse->id";
       			$DB->execute($sql);
        	}
        
        	echo "<td>$elptitle</td>";
        	echo "<td>$rejectedcourse->format</td>";

  			if ($rejectedcourse->description) {
       			$alert = addslashes($rejectedcourse->description);            
       			echo "<td style='cursor:pointer;color:#780D68' onclick='javascript:lire(description$rejectedcourse->id)'>Lire</td>";
        	} else {
	        	echo "<td></td>";
        	}

        	if ($rejectedcourse->brouillonid) {
    	    	echo "<td><a href='$CFG->wwwroot/course/view.php?id=$rejectedcourse->brouillonid'>Voir</a></td>";
        	} else {
        		echo "<td></td>";
        	}

        	if ($rejectedcourse->answererid) {
        		$sql = "SELECT firstname FROM mdl_user WHERE id = $rejectedcourse->answererid";
        		$answerer = $DB->get_record_sql($sql);
        		echo "<td>$answerer->firstname</td>";
        	} else {
        		echo "<td></td>";
        	}

        	echo "<td>$rejectedcourse->answeredat</td>";

        	if ($rejectedcourse->rejectioncause) {
        		$alert = addslashes($rejectedcourse->rejectioncause);
        		echo '<td style="cursor:pointer;color:#780D68" onclick="alert(\''.$alert.'\')">Lire</td>';
        	} else {
        		echo "<td></td>";
        	}

        	echo "<td><a href='demandes.php?back=$rejectedcourse->id'>Réactiver</a></td>";
        	echo "</tr>";
        	
        	echo "<tr id='description$rejectedcourse->id' style='display:none'>";
        	echo "<td colspan = 9>";
        	echo $rejectedcourse->description;
        	echo "</td>";
        	echo "</tr>";
		} ?>
    </table>
</p>

<br><br>


<!-- Tableau des demandes création de cours satisfaites -->
<h2>Demandes de création de cours satisfaites</h2>
<span id='acceptedshower' onclick='showaccepted()' style='cursor:pointer;color:#780D68;display:block'>Montrer</span>
<p id='acceptedrequests' style='display:none'>
	<span onclick='hideaccepted()' style='cursor:pointer;color:#780D68'>Cacher</span>
    <table border-collapse >
    	<tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>
    		<td>Demandée le</td>
    		<td>Demandée par</td>
    		<td>Composante</td>
    		<td>Niveau</td>
    		<td>Code VET</td>
    		<td>VET</td>
    		<td>Code cours</td>
    		<td>Cours</td>
    		<td>Format</td>
    		<td>Description</td>
    		<td>Créée par</td>
    		<td>Créée le</td>
    	</tr>

    	<?php 
    	$sql = "SELECT * FROM mdl_asked_courses WHERE answer = 'Oui' ORDER BY askedat DESC";
    	$acceptedcourses = $DB->get_recordset_sql($sql);

    	foreach($acceptedcourses as $acceptedcourse) {
    		echo "<tr align = 'center'>";
    		echo "<td>$acceptedcourse->askedat</td>";

    		$sql = "SELECT username, firstname, lastname FROM mdl_user WHERE id = $acceptedcourse->askerid";
    		$asker = $DB->get_record_sql($sql);
    		echo "<td>$asker->firstname $asker->lastname ($asker->username)</td>";

    		$sql = "SELECT name FROM mdl_ufr WHERE code = '$acceptedcourse->ufr'";
    		$ufr = $DB->get_record_sql($sql)->name;
    
    		echo "<td>$ufr</td>";
			echo "<td>$acceptedcourse->level</td>";
			echo "<td>$acceptedcourse->vetnum</td>";

    		if ($acceptedcourse->vettitle) {
                    $vettitle = $acceptedcourse->vettitle;
		} else {
                    $sql = "SELECT name FROM mdl_cat_demandecours WHERE code = '$acceptedcourse->vetnum'";
                    $vetnamerecord = $DB->get_record_sql($sql);
                    if ($vetnamerecord) {
                        $vettitle = stripslashes($vetnamerecord->name);
                    } else {
                        $vettitle = "";
                    }
                    $sql = "UPDATE mdl_asked_courses SET vettitle = '".addslashes($vettitle)."' WHERE id = $acceptedcourse->id";
                    $DB->execute($sql);
		}

			echo "<td>$vettitle</td>";
			echo "<td>$acceptedcourse->elpnum</td>";

    		if ($acceptedcourse->elptitle) {
				$elptitle = $acceptedcourse->elptitle;
    		} else {
				$sql = "SELECT name FROM mdl_cat_demandecours WHERE code = '$acceptedcourse->elpnum'";
                                $elpnamerecord = $DB->get_record_sql($sql);
                                if ($elpnamerecord) {
                                    $elptitle = stripslashes($elpnamerecord->name);
                                } else {
                                    $elptitle = "";
                                }
				
				$sql = "UPDATE mdl_asked_courses SET elptitle = '".addslashes($elptitle)."' WHERE id = $acceptedcourse->id";
				$DB->execute($sql);
			}
	
			echo "<td>$elptitle</td>";
    		echo "<td>$acceptedcourse->format</td>";

    		if ($acceptedcourse->description) {
        		$alert = addslashes($acceptedcourse->description);
	        	echo "<td style='cursor:pointer;color:#780D68' onclick='javascript:lire(description$acceptedcourse->id)'>Lire</td>";
			} else {
				echo "<td></td>";
			}

			if ($acceptedcourse->answererid) {
				$sql = "SELECT firstname FROM mdl_user WHERE id = $acceptedcourse->answererid";
				$answerer = $DB->get_record_sql($sql);
				echo "<td>$answerer->firstname</td>";
			} else {
				echo "<td></td>";
			}

			echo "<td>$acceptedcourse->answeredat</td>";
			echo "<td><a href='view.php?id=$acceptedcourse->courseid'>Voir</a></td>";
			echo "<td><a href='demandes.php?back=$acceptedcourse->id'>Réactiver</a></td>";
			echo "</tr>";
		
			echo "<tr id='description$acceptedcourse->id' style='display:none'>";
			echo "<td colspan = 0>";
			echo $acceptedcourse->description;
			echo "</td>";
			echo "</tr>";
		} ?>
		
    </table>
</p>

<br><br>


<!-- Tableau des demandes de webconférence en attente -->
<h2>Demandes de webconférences en attente</h2>
<table border-collapse>
    <tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>
    	<td>Demandée le</td>
		<td>Cours</td>
		<td>Nom</td>
		<td>Enregistrée ?</td>
		<td>Début</td>
		<td>Fin</td>
		<td>Durée (minutes)</td>
		<td>Enseignants</td>
		<td>Paramètres</td>
		<td>Prise en charge par</td>
    </tr>
    
    <?php 

    $sql = "SELECT cm.added, cm.course, bbb.name, bbb.record, bbb.openingtime, bbb.closingtime, cm.id, bbb.sefiapid "
    		. "FROM mdl_course_modules cm, mdl_bigbluebuttonbn bbb "
    		. "WHERE cm.module = 6 AND cm.instance = bbb.id AND bbb.sefiap = 0 "	// 6 id de bigbluebutton dans la table mdl_module (change d'une plateforme à l'autre) 
    		. "ORDER BY cm.added ASC";    
    $askedvisios = $DB->get_recordset_sql($sql);
    
    foreach($askedvisios as $askedvisio) {
        $askedvisio->timeduration = $askedvisio->closingtime -$askedvisio->openingtime;
    	echo "<tr align = 'center'>";
    	$askedat = date("Y-m-d H:i:s", $askedvisio->added);
    	echo "<td>$askedat</td>";

    	$sql = "SELECT fullname FROM mdl_course WHERE id = $askedvisio->course";
    	$incoursename = $DB->get_record_sql($sql)->fullname;
    	echo "<td><a href='view.php?id=$askedvisio->course'>$incoursename</a></td>";

    	echo "<td>$askedvisio->name</td>";

    	if ($askedvisio->record) {
    		echo "<td>Oui</td>";
    	} else {
    		echo "<td>Non</td>";
    	}

    	if ($askedvisio->openingtime - time() < (5 * 24 * 3600)) {
    		$visiostartcolor = "red";
		} else {
			$visiostartcolor = "black";
		}

		$visiostart = date("Y-m-d H:i:s", $askedvisio->openingtime);
		echo "<td style='color:$visiostartcolor'>$visiostart</td>";
		$visioend = date("Y-m-d H:i:s", $askedvisio->closingtime);
		echo "<td>$visioend</td>";
    	echo "<td>".floor($askedvisio->timeduration/60)."</td>";

		$sql = "SELECT id FROM mdl_context WHERE contextlevel = 50 and instanceid = $askedvisio->course";
		$visiocoursecontextid = $DB->get_record_sql($sql)->id;
    	echo "<td><a href='../user/index.php?contextid=$visiocoursecontextid&sifirst=&silast=&roleid=3'>Voir</a></td>";

    	echo "<td><a href='modedit.php?update=$askedvisio->id&return=0&sr=0'>Editer</a></td>";

		if ($askedvisio->sefiapid) {
    		$sql = "SELECT firstname FROM mdl_user WHERE id = $askedvisio->sefiapid";
        	$sefiapname = $DB->get_record_sql($sql)->firstname;
			echo "<td>$sefiapname</td>";
		} else {
        	echo "<td></td>";
		}

		echo "<td><a border=1 href='demandes.php?visiotake=$askedvisio->id'>Je m'en charge</a></td>";
    	echo "<td><a href='demandes.php?visiorefusing=$askedvisio->id'>Refuser</a></td>";
    	echo "<td><a href='demandes.php?visioaccept=$askedvisio->id'>Accepter</a></td>";
	    echo "</tr>";
	} ?>
</table>

<br><br>

<!-- Tableau des demandes de webconférences rejetées -->
<h2>Demandes de webconférences rejetées</h2>
<span id='visiorejectedshower' onclick='visioshowrejected()' style='cursor:pointer;color:#780D68;display:block'>Montrer</span>

<p id='visiorejectedrequests' style='display:none'>
	<span onclick='visiohiderejected()' style='cursor:pointer;color:#780D68'>Cacher</span>
	<table border-collapse >
		<tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>
			<td>Demandée le</td>
			<td>Cours</td>
			<td>Nom</td>
			<td>Enregistrée ?</td>
			<td>Début</td>
			<td>Fin</td>
			<td>Durée (minutes)</td>
			<td>Enseignants</td>
			<td>Rejetée par</td>
			<td>Rejetée le</td>
			<td>Motif</td>
		</tr>

		<?php 
		$sql = "SELECT cm.added, cm.course, bbb.name, bbb.record, bbb.openingtime, bbb.closingtime, "
					. "cm.id, bbb.sefiapid, bbb.sefiaptime, bbb.rejectioncause "
					. "FROM mdl_course_modules cm, mdl_bigbluebuttonbn bbb "
					. "WHERE cm.module = 27 AND cm.instance = bbb.id AND bbb.sefiap = 1 "
					. "ORDER BY cm.added DESC"; //3: bigbluebutton
		$rejectedvisios = $DB->get_recordset_sql($sql);

		foreach($rejectedvisios as $rejectedvisio) {
                    $rejectedvisio->timeduration = $rejectedvisio->closingtime -$rejectedvisio->openingtime;
			echo "<tr align = 'center'>";
			$askedat = date("Y-m-d H:i:s", $rejectedvisio->added);
			echo "<td>$askedat</td>";

			$sql = "SELECT fullname FROM mdl_course WHERE id = $rejectedvisio->course";
                        $coursefullnamerecord = $DB->get_record_sql($sql);
                        if ($coursefullnamerecord) {
                            $incoursename = $coursefullnamerecord->fullname;
                        } else {
                            $incoursename = "";
                        }
			
			echo "<td><a href='view.php?id=$rejectedvisio->course'>$incoursename</a></td>";
			echo "<td>$rejectedvisio->name</td>";

			if ($rejectedvisio->record) {
				echo "<td>Oui</td>";
			} else {
				echo "<td>Non</td>";
			}

			$visiostart = date("Y-m-d H:i:s", $rejectedvisio->openingtime);
			echo "<td>$visiostart</td>";

			$visioend = date("Y-m-d H:i:s", $rejectedvisio->closingtime);
			echo "<td>$visioend</td>";

			echo "<td>".floor($rejectedvisio->timeduration/60)."</td>";

			$sql = "SELECT id FROM mdl_context WHERE contextlevel = 50 and instanceid = $rejectedvisio->course";
			$visiocoursecontext = $DB->get_record_sql($sql);
                        if ($visiocoursecontext) {
                            echo "<td><a href='../user/index.php?contextid=$visiocoursecontext->id&sifirst=&silast=&roleid=3'>Voir</a></td>";
                        } else {
                            echo "<td>Non trouvé</td>";
                        }
                        
    	    


        	if ($rejectedvisio->sefiapid) {
				$sql = "SELECT firstname FROM mdl_user WHERE id = $rejectedvisio->sefiapid";
            	$sefiapname = $DB->get_record_sql($sql)->firstname;
				echo "<td>$sefiapname</td>";
        	} else {
				echo "<td></td>";
        	}

        	echo "<td>$rejectedvisio->sefiaptime</td>";

			$alert = addslashes($rejectedvisio->rejectioncause);
			echo '<td style="cursor:pointer;color:#780D68" onclick="alert(\''.$alert.'\')">Lire</td>';
			echo "<td><a href='demandes.php?visioback=$rejectedvisio->id'>Réexaminer</a></td>";
			echo "</tr>";
    	} ?>
						 
	</table>
</p>

<br><br>


<!-- Tableau des demandes de webconférences satisfaites -->	
<h2>Demandes de webconférences satisfaites</h2>
<span id='visioacceptedshower' onclick='visioshowaccepted()' style='cursor:pointer;color:#780D68;display:none'>Montrer</span>
<p id='visioacceptedrequests' style='display:block'>
    <span onclick='visiohideaccepted()' style='cursor:pointer;color:#780D68'>Cacher</span>
    <table border-collapse >
		<tr align = 'center' style = 'font-weight:bold;color:#731472' bgcolor='#780D68'>
                    <td>Demandée le</td>
                    <td>Cours</td>
                    <td>Nom</td>
                    <td>Enregistrée ?</td>
                    <td>Début</td>
                    <td>Fin</td>
                    <td>Durée (minutes)</td>
                    <td>Enseignants</td>
                    <td>Autorisée par</td>
                    <td>Autorisée le</td>
		</tr>
		<?php 
		$now = time();
		$sql = "SELECT cm.added, cm.course, bbb.name, bbb.record, bbb.openingtime, bbb.closingtime, cm.id, bbb.sefiapid, bbb.sefiaptime "
				. "FROM mdl_course_modules cm, mdl_bigbluebuttonbn bbb "
				. "WHERE cm.module = 27 AND cm.instance = bbb.id AND bbb.sefiap = 2 "
				//. "AND bbb.timedue >= $now "
				. "ORDER BY cm.added DESC"; //3: bigbluebutton
		$acceptedvisios = $DB->get_recordset_sql($sql);

		foreach($acceptedvisios as $acceptedvisio) {
                    $acceptedvisio->timeduration = $acceptedvisio->closingtime -$acceptedvisio->openingtime;
			echo "<tr align = 'center'>";
			$askedat = date("Y-m-d H:i:s", $acceptedvisio->added);
			echo "<td>$askedat</td>";

			$sql = "SELECT fullname FROM mdl_course WHERE id = $acceptedvisio->course";
			$incoursename = $DB->get_record_sql($sql)->fullname;
			echo "<td><a href='view.php?id=$acceptedvisio->course'>$incoursename</a></td>";
			echo "<td>$acceptedvisio->name</td>";

			if ($acceptedvisio->record) {
				echo "<td>Oui</td>";
			} else {
				echo "<td>Non</td>";
			}

			$datecolor = "black";
			$visiosearched = $visiotaken + $visioback; //Si on vient de cliquer sur Je m'en charge, Réexaminer ou Remettre en cause

			if ($visiosearched) {                       //On affiche en rouge les horaires des webconférences qui chevauchent celle qu'on examine
						 					//timestamps de début et de fin de la webconférence examinée
				$sql = "SELECT bbb.openingtime, bbb.closingtime "
						. "FROM mdl_bigbluebuttonbn bbb, mdl_course_modules cm "
                    	. "WHERE cm.instance = bbb.id AND cm.id = $visiosearched";
            	$newvisiotimes = $DB->get_record_sql($sql);

            	if (($acceptedvisio->openingtime <= $newvisiotimes->closingtime)&&($acceptedvisio->closingtime >= $newvisiotimes->openingtime)) {
                	$datecolor = "red";
            	}
        	}


        	$visiostart = date("Y-m-d H:i:s", $acceptedvisio->openingtime);
        	echo "<td style='color:$datecolor'>$visiostart</td>";

			$visioend = date("Y-m-d H:i:s", $acceptedvisio->closingtime);
        	echo "<td style='color:$datecolor'>$visioend</td>";

        	echo "<td>".floor($acceptedvisio->timeduration/60)."</td>";

        	$sql = "SELECT id FROM mdl_context WHERE contextlevel = 50 and instanceid = $acceptedvisio->course";
        	$visiocoursecontext = $DB->get_record_sql($sql);
                if ($visiocoursecontext) {
                    echo "<td><a href='../user/index.php?contextid=$visiocoursecontext->id&sifirst=&silast=&roleid=3'>Voir</a></td>";
                } else {
                    echo "<td>Non trouvé</td>";
                }
        	


        	if ($acceptedvisio->sefiapid) {
            	$sql = "SELECT firstname FROM mdl_user WHERE id = $acceptedvisio->sefiapid";
            	$sefiapname = $DB->get_record_sql($sql)->firstname;
        		echo "<td>$sefiapname</td><td>$acceptedvisio->sefiaptime</td>";
			} else {
				echo "<td></td><td></td>";
			}

			echo "<td><a href='demandes.php?visioback=$acceptedvisio->id'>Remettre en cause</a></td>";
			echo "</tr>";
		} ?>

	</table>
</p>



<?php 

echo $renderer->management_form_start();
echo $renderer->management_form_end();

echo $renderer->footer();
?>

<script type="text/javascript">
function lire(id) {
    if (id.style.display == 'none') {
        id.style.display = 'block';
    } else {
        id.style.display = 'none';
    }
}    
function showrejected(menu) {
    rejectedrequests.style.display = 'block';    
    rejectedshower.style.display = 'none';
}

function hiderejected(menu) {
    rejectedrequests.style.display = 'none';    
    rejectedshower.style.display = 'block';
}

function showaccepted(menu) {
    acceptedrequests.style.display = 'block';    
    acceptedshower.style.display = 'none';
}

function hideaccepted(menu) {
    acceptedrequests.style.display = 'none';    
    acceptedshower.style.display = 'block';
}

function visioshowrejected(menu) {
    visiorejectedrequests.style.display = 'block';    
    visiorejectedshower.style.display = 'none';
}

function visiohiderejected(menu) {
    visiorejectedrequests.style.display = 'none';    
    visiorejectedshower.style.display = 'block';
}

function visioshowaccepted(menu) {
    visioacceptedrequests.style.display = 'block';    
    visioacceptedshower.style.display = 'none';
}

function visiohideaccepted(menu) {
    visioacceptedrequests.style.display = 'none';    
    visioacceptedshower.style.display = 'block';
}
</script>



