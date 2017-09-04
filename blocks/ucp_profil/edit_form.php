<?php
 
class block_ucp_tableau_de_bord_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        $mform->setDefault('config_text', '');
        $mform->setType('config_text', PARAM_RAW);        
 
    }
}