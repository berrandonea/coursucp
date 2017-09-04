<?php 
/***************************************************************************************************/
/********** INITIALISATION *************************************************************************/
/***************************************************************************************************/

$host= 'localhost';
$user= 'root';
$password = 'so\)j3gBaé';
$databases = 'moodle';

//déclaration du nom du dump (localhost_essai_20120313.sql)
$path = '/var/www/moodle/mysqldump/';
$dump = $path . $host .'_'.$databases."_".date('Ymd').'.sql';
echo "$dump\n";


//déclaration du chemin de la commande myqldump
$path_mysqldump = '';


/***************************************************************************************************/
/********** UTILISATION ****************************************************************************/
/***************************************************************************************************/
system($path_mysqldump.'mysqldump --host='.$host.' --user='.$user.' --password='.$password.' --no-create-db --default-character-set=utf8 --lock-tables=FALSE --tables '.$databases.' > '.$dump);
// decommenter la ligne ci-dessous pour zipper le fichier sql
//system("gzip ".$dump);
?>
