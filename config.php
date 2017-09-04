<?php  // Moodle configuration file
if (!defined('CLI_SCRIPT')) {
//Redirection depuis https://enp16.u-cergy.fr
	if ($_SERVER['HTTP_HOST'] === 'enp16.u-cergy.fr') {
      		$uriparts = explode('/', $_SERVER['REQUEST_URI']);
	      	$uri = '';
		$i = 0;
		foreach($uriparts as $uripart) {
		    	if ($i > 0) {
        			$uri = "$uri/$uripart";
		        }
		        $i++;
		}
      		header("Status: 301 Moved Permanently");
      		header("Location: https://cours.u-cergy.fr$uri");
	}//*/

	//Forcer https
	if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
	    if(!headers_sent()) {
        	header("Status: 301 Moved Permanently");
	        header(sprintf(
        	'Location: https://cours.u-cergy.fr%s',
	//            $_SERVER['HTTP_HOST'],
        	    $_SERVER['REQUEST_URI']
	        ));
        	exit();
	    }
	}
}

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle';
$CFG->dbpass    = 'DDdmUJW4DQR3UEQJ';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => 3306,
  'dbsocket' => '',
);

$CFG->wwwroot   = 'https://cours.u-cergy.fr';
$CFG->dataroot  = '/var/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;
$CFG->catbrouillonsid = 2;
$CFG->calendar_startwday = 1;
$CFG->forcelogin = true;

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

