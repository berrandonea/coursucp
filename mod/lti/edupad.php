<?php
require_once("../../config2.php");
$id = optional_param('id', 0, PARAM_INT);// Course ID


$PAGE->set_url('/mod/lti/edupad.php?id=$id');
$PAGE->set_pagelayout('general');


//echo "$OUTPUT->header();

?>
<h1>Test pour edu-pad</h1>

<iframe frameborder="0" width="660" height="380" src="http://edu-pad.ac-versailles.fr/p/brice"></iframe>

<?php
//echo $OUTPUT->footer;
?>

