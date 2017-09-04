<?php
function db_connect() {
	$db = mysqli_connect('enp15.u-cergy.fr', 'moodle', 'XycJNBaGBduxvqwy', 'moodle', '3306');
	$test = mysql_select_db('moodle', $db);
	 var_dump($test);
        if (!mysql_set_charset('utf8', $db)) {
            echo "Erreur : Impossible de définir le jeu de caractères.\n";
            exit;
        }
        
        //mysql_query("SET NAMES 'utf8'");        
	return $db;
}

function sanitize_string($str) {
	if (get_magic_quotes_gpc()) {
		$sanitize = mysql_real_escape_string(stripslashes($str));	 
	} else {
		$sanitize = mysql_real_escape_string($str);	
	} 
	return $sanitize;
}

function db_query($sql) {
        db_connect();
	$result = mysql_query($sql) or die('Erreur MySQL!<br>'.$sql.'<br>'.mysql_error());
        mysql_close();
	return $result;
}

function db_insert($sql) {
    db_connect();
    mysql_query($sql) or die('Erreur MySQL!<br>'.$sql.'<br>'.mysql_error());    
    $insertedid = mysql_insert_id();
    mysql_close();
    return $insertedid;
}

function rejectuser() {
    echo "<h3>Cette page n'existe pas ou vous n'avez pas le droit d'y accéder.</h3>";
    echo "Peut-être avez-vous: <ul>";
    echo "<li>Oublié de vous connecter.</li>";
    echo "<li>Saisi une adresse erronée ou incomplète.</li></ul>";
    echo "À tout moment, vous pouvez revenir à la page d'accueil en cliquant sur le logo en haut à gauche.<br><br></div></div>";
    include 'footer.php';
    exit;            
}  


function print_array($array) {
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}
?>