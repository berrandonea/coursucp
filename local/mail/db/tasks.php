<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// HACK FOR COURSUCP 2016-2017
// This whole file is a hack. This add the import task as a scheduled task.
// HACK BEGINNING

$tasks = array(
    array(
        'classname' => 'local_mail\task\importmessage',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);

// HACK END