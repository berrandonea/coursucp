<title>
Correction des semestres des ELP
</title>

<?php
define('CLI_SCRIPT', true);
require_once('config.php');

$courses = $DB->get_recordset('course', array('term' => 0));
foreach ($courses as $course) {
	if (substr($course->idnumber, 0, 6) == 'Y2017-') {
		$codes = explode('-', $course->idnumber);
		if (!isset($codes[2])) {
			continue;
		}
		//~ echo $codes[2].'<br>';
		$termcode = goodterm($codes[2]);
	    if (!$termcode) {
		    continue;
	    }
	    if ($termcode % 2) {
		    $course->term = 1;
	    } else {
		    $course->term = 2;
	    }
	    $DB->update_record('course', $course);
	}
}
$courses->close();

function goodterm($idnumber) {
	if (strpos($idnumber, '2017E')) {
		return 0;
	}
	if (strpos($idnumber, '2016E')) {
		return 0;
	}
	if (strpos($idnumber, '2015E')) {
		return 0;
	}
	$termcode = 0;
	$length = strlen($idnumber);
	for ($i = 1; $i < $length; $i++) {
		$codechar = substr($idnumber, $i, 1);
		if (is_numeric($codechar)) {
			$termcode = $codechar;			
		}
	}
	//~ echo "$idnumber $termcode\n";
	return $termcode;
}
