<?php
require_once('../../config.php');
$now = time();
$msgstring = required_param('msgstring', PARAM_TEXT);
$fromuser = required_param('fromuser', PARAM_TEXT);
$message = new stdClass();
$stringchunks = explode('"', $msgstring);
if ($stringchunks[9] == 'receiver') {
	$message->touser = $stringchunks[11];
	$message->text = $stringchunks[15];
} else {
	$message->touser = $stringchunks[13];
	$message->text = $stringchunks[9];
}


$message->fromuser = $fromuser;
$message->time = $now;
$DB->insert_record('vmchat_ucp', $message);


                                                                          //~ 9                     11                     13                             15
//~ { " cfun " : " broadcastToAll " , " arg " :{ " msg " : { " receiver                     " : " chatroom " , " msg                       " : " Ceci est le texte du message " }}}

//~ { " cfun " : " broadcastToAll " , " arg " :{ " msg " :   " Ceci est le texte du message " , " touser   " : " tcp4:192.168.10.108:59592 " }}

