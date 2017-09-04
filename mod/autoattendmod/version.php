<?php 

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of autoattendmod
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

defined('MOODLE_INTERNAL') || die();

//
$plugin->requires  = 2012120300;			// Moodle 2.4
$plugin->component = 'mod_autoattendmod';
$plugin->cron      = 300;
$plugin->maturity  = MATURITY_STABLE;

// email function
$plugin->version   = 2016031900;
$plugin->release   = '2.5.1';
