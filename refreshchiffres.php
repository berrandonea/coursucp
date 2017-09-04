<title>
Cron - Réactualisation des données chiffrées de la plateforme
</title>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');


$sql = "SELECT COUNT(id) AS nblogs FROM mdl_logstore_standard_log";
$nblogs = $DB->get_record_sql($sql)->nblogs;
echo "<strong>$nblogs</strong> actions réalisées sur la plateforme (consultation de document, envoi d'un message, remise d'un devoir, etc.)\n";
$sql = "UPDATE mdl_chiffres SET number = $nblogs WHERE name = 'nblogs'";
$DB->execute($sql);

$sql = "SELECT COUNT(id) AS nbviews FROM mdl_logstore_standard_log WHERE action = 'viewed' AND target = 'course'";
$nbviews = $DB->get_record_sql($sql)->nbviews;
echo "<strong>$nbviews</strong> consultations de cours ou documents.\n";
$sql = "UPDATE mdl_chiffres SET number = $nbviews WHERE name = 'nbviews'";
$DB->execute($sql);

$sql = "SELECT COUNT(id) AS nbgrades FROM mdl_grade_grades";
$nbgrades = $DB->get_record_sql($sql)->nbgrades;
echo "<strong>$nbgrades</strong> copies virtuelles notées.\n";
$sql = "UPDATE mdl_chiffres SET number = $nbgrades WHERE name = 'nbgrades'";
$DB->execute($sql);

$sql = "SELECT count( id ) as weekconnections
FROM mdl_logstore_standard_log
WHERE ACTION = 'loggedin'
AND timecreated > ( UNIX_TIMESTAMP( NOW( ) ) -7 *24 *3600 ) ";
$weekconnections = $DB->get_record_sql($sql)->weekconnections;
echo "<strong>$weekconnections</strong> connexions depuis une semaine.\n";
$sql = "UPDATE mdl_chiffres SET number = $weekconnections WHERE name = 'weekconnections'";
$DB->execute($sql);



?>
