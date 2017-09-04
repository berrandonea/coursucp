<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// HACK FOR COURSUCP 2016-2017
// This whole file is a hack. This is the scheduled task to import messages from the old message system.
// HACK BEGINNING

namespace local_mail\task;

class importmessage extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens
        return get_string('importmessage', 'local_mail');
    }

    public function execute() {

        global $DB, $SITE;

        $lastcronrecord = $DB->get_record('local_mail_crontime', array('failure' => 0));
        if (isset($lastcronrecord)) {

            $lastcrontime = $lastcronrecord->crontime;
        } else {

            $lastcrontime = 0;
        }

        $newcronrecord = array();
        $newcronrecord['crontime'] = time();
        $newcronrecord['failure'] = 1;
        $DB->insert_record('local_mail_crontime', $newcronrecord);

        if (!isset($lastcrontime)) {

            $lastcrontime = 0;
        }

        $sqlunread = "SELECT * FROM {message} WHERE timecreated >= ?";
        $sqlread = "SELECT * FROM {message_read} WHERE timecreated >= ?";

        $messagesunread = $DB->get_records_sql($sqlunread, array($lastcrontime));
        $messagesread = $DB->get_records_sql($sqlread, array($lastcrontime));

        $messages = array();

        foreach ($messagesunread as $messageunread) {

            $messages[] = $messageunread;
        }

        foreach ($messagesread as $messageread) {

            $messages[] = $messageread;
        }

        foreach ($messages as $message) {

            $alreadytreated = 0;
            $partiallytreated = 0;
            $treatedmessage = null;

            if ($message->useridfrom == $message->useridto || $message->useridfrom < 1) {

                $message->useridfrom = 2;
            }

            if (stristr($message->contexturl, 'local/mail') == FALSE &&
                    $message->useridfrom != $message->useridto) {

                $sql = "SELECT * FROM {local_mail_messages} WHERE courseid = ? AND time >= ? AND time <= ?"
                        . " AND content LIKE ?";

                $footerstart = "---------------------------------------------------------------------
Ce courriel est la copie d'un message personnel qui vous a été envoyé sur";

                $footerstarthtml = "----------------------------------------"
                        . "-----------------------------<br />"
                        . "Ce courriel est la copie d'un message personnel qui vous a été envoyé sur";

                if ($message->fullmessagehtml != "" && isset($message->fullmessagehtml)) {

                    if (strstr($message->fullmessagehtml, $footerstarthtml) != FALSE) {

                        $truncatedmessage = strstr($message->fullmessagehtml, $footerstarthtml, true);
                    } else {

                        $truncatedmessage = $message->fullmessagehtml;
                    }
                } else {

                    if (strstr($message->fullmessage, $footerstart) != FALSE) {

                        $truncatedmessage = strstr($message->fullmessage, $footerstart, true);
                    } else {

                        $truncatedmessage = $message->fullmessage;
                    }
                }

                $listmessagessametime = $DB->get_records_sql($sql, array($SITE->id,
                        ($message->timecreated - 5), ($message->timecreated + 5),
                        $truncatedmessage));

                if (isset($listmessagessametime)) {

                    foreach ($listmessagessametime as $messagesametime) {

                        $sender = $DB->get_record('local_mail_message_users',
                                array('messageid' => $messagesametime->id, 'role' => 'from'))->userid;

                        if ($sender == $message->useridfrom) {

                            $partiallytreated = 1;

                            $treatedmessage = $messagesametime;

                            $listrecipients = $DB->get_records('local_mail_message_users',
                                array('messageid' => $messagesametime->id, 'role' => 'to'));

                            foreach ($listrecipients as $recipient) {

                                if ($recipient->userid == $message->useridto) {

                                    $alreadytreated = 1;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($alreadytreated != 1) {

                    $footerstart = "---------------------------------------------------------------------
Ce courriel est la copie d'un message personnel qui vous a été envoyé sur";

                    $footerstarthtml = "----------------------------------------"
                            . "-----------------------------<br />"
                            . "Ce courriel est la copie d'un message personnel qui vous a été envoyé sur";

                    if ($message->fullmessagehtml != "" && isset($message->fullmessagehtml)) {

                        if (strstr($message->fullmessagehtml, $footerstarthtml) != FALSE) {

                            $message->fullmessagehtml = strstr($message->fullmessagehtml, $footerstarthtml, true);
                        }
                    } else {

                        if (strstr($message->fullmessage, $footerstart) != FALSE) {
                            
                            $message->fullmessage = strstr($message->fullmessage, $footerstart, true);
                        }
                    }

                    if (isset($message->timeread)) {

                        $unread = 0;
                    } else {

                        $unread = 1;
                    }

                    if ($partiallytreated == 1) {

                        $localmessageid = $treatedmessage->id;
                    }

                    if ($partiallytreated != 1 || $message->useridfrom == 2) {

                        $localmessage = array();
                        $localmessage['courseid'] = $SITE->id;
                        $localmessage['subject'] = $message->subject;
                        if (isset($message->fullmessagehtml) && $message->fullmessagehtml != "") {
                            $localmessage['content'] = $message->fullmessagehtml;
                        } else {
                            $localmessage['content'] = $message->fullmessage;
                        }
                        $localmessage['format'] = 1;
                        $localmessage['draft'] = 0;
                        $localmessage['time'] = $message->timecreated;

                        $localmessageid = $DB->insert_record('local_mail_messages', $localmessage);

                        $localuserfrom = array();
                        $localuserfrom['messageid'] = $localmessageid;
                        $localuserfrom['userid'] = $message->useridfrom;
                        $localuserfrom['role'] = "from";
                        $localuserfrom['unread'] = 0;
                        $localuserfrom['starred'] = 0;
                        $localuserfrom['deleted'] = 0;
                        $DB->insert_record('local_mail_message_users', $localuserfrom);

                        $localindexsent = array();
                        $localindexsent['userid'] = $message->useridfrom;
                        $localindexsent['type'] = "sent";
                        $localindexsent['item'] = 0;
                        $localindexsent['messageid'] = $localmessageid;
                        $localindexsent['time'] = $message->timecreated;
                        $localindexsent['unread'] = 0;
                        $DB->insert_record('local_mail_index', $localindexsent);

                        $localindexcourse = array();
                        $localindexcourse['userid'] = $message->useridfrom;
                        $localindexcourse['type'] = "course";
                        $localindexcourse['item'] = 1;
                        $localindexcourse['messageid'] = $localmessageid;
                        $localindexcourse['time'] = $message->timecreated;
                        $localindexcourse['unread'] = 0;
                        $DB->insert_record('local_mail_index', $localindexcourse);

                        $localindexattachment = array();
                        $localindexattachment['userid'] = $message->useridfrom;
                        $localindexattachment['type'] = "attachment";
                        $localindexattachment['item'] = 0;
                        $localindexattachment['messageid'] = $localmessageid;
                        $localindexattachment['time'] = $message->timecreated;
                        $localindexattachment['unread'] = 0;
                        $DB->insert_record('local_mail_index', $localindexattachment);
                    }

                    $localuserto = array();
                    $localuserto['messageid'] = $localmessageid;
                    $localuserto['userid'] = $message->useridto;
                    $localuserto['role'] = "to";
                    $localuserto['unread'] = $unread;
                    $localuserto['starred'] = 0;
                    $localuserto['deleted'] = 0;
                    $DB->insert_record('local_mail_message_users', $localuserto);

                    $localindexinbox = array();
                    $localindexinbox['userid'] = $message->useridto;
                    $localindexinbox['type'] = "inbox";
                    $localindexinbox['item'] = 0;
                    $localindexinbox['messageid'] = $localmessageid;
                    $localindexinbox['time'] = $message->timecreated;
                    $localindexinbox['unread'] = $unread;
                    $DB->insert_record('local_mail_index', $localindexinbox);

                    $localindexcourse2 = array();
                    $localindexcourse2['userid'] = $message->useridto;
                    $localindexcourse2['type'] = "course";
                    $localindexcourse2['item'] = 1;
                    $localindexcourse2['messageid'] = $localmessageid;
                    $localindexcourse2['time'] = $message->timecreated;
                    $localindexcourse2['unread'] = $unread;
                    $DB->insert_record('local_mail_index', $localindexcourse2);
                }
            }
        }

        $DB->delete_records('local_mail_crontime');
        $newcronrecord['failure'] = 0;
        $DB->insert_record('local_mail_crontime', $newcronrecord);
    }
}