<?php

// This file is part of the Moodle module "EJSApp"
//
// EJSApp is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// EJSApp is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License is available on <http://www.gnu.org/licenses/>
//
// EJSApp has been developed by:
//  - Luis de la Torre: ldelatorre@dia.uned.es
//	- Ruben Heradio: rheradio@issi.uned.es
//
//  at the Computer Science and Automatic Control, Spanish Open University
//  (UNED), Madrid, Spain


/**
 * Spanish strings for ejsapp
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'EJSApp';
$string['modulenameplural'] = 'EJSApps';
$string['modulename_help'] = 'El m&oacute;dulo de actividad EJSApp permite a un profesor a&ntilde;adir applets de Java creados con Easy Java Simulations (EJS) en sus cursos de Moodle.

Los applets de EJS quedar&aacute;n embebidos dentro de los cursos de Moodle. El profesor puede seleccionar si mantener el tama&ntilde;o original del applet o permitir que Moodle lo reescale de acuerdo al espacio disponible. Si el applet fue compilado con la opci&oacute;n "A&ntilde;adir soporte idiomas" en EJS, el applet embebido en Moodle con la actividad EJSApp configurar&aacute; autom&aacute;ticamente su idioma a aquel seleccionado por el usuario de Moodle, si esto es posible. Esta actividad es compatible con la configuraci&oacute;n de restricciones de acceso condicional.

Cuando se usa junto al Navegador EJSApp de Ficheros, los estudiantes pueden guardar el estado del applet EJS, cuando lo est&eacute;n ejecutando, simplemente pulsando con el bot&oacute;n derecho del rat&oacute;n sobre el applet y seleccionando la opci&oacute;n adecuada en el men&uacute; que aparece. La informaci&oacute;n de estos estados se graba en un fichero .xml (para Java) o .json (para Javascript) que es guardado en el area de ficheros privados (Navegador EJSApp de Ficheros). Estos estados pueden recuperarse de dos maneras distintas: pulsando sobre los ficheros .xml o .json en el Navegador EJSApp de Ficheros o pulsando con el bot&oacute;n derecho del rat&oacute;n sobre el applet EJS y seleccionando la opci&oacute;n adecuada en el men&uacute;. Si el applet EJS est&aacute; preparado para tal efecto, tambi&eacute;n puede grabar ficheros de texto o im&aacute;genes y guardarlos en el &aacute;rea de ficheros privados.

Cuando se usa junto al bloque EJSApp de Sesiones Colaborativas, los usuarios de Moodle pueden trabajar con el mismo applet EJS de una manera s&iacute;ncrona, es decir, de tal forma que el applet mostrar&aacute; el mismo estado para todos los usuarios en la sesi&oacute;n colaborativa. Gracias a este bloque, los usuarios pueden crear sesiones, invitar a otros usuarios y trabajar juntos con la misma actividad EJSApp.';
$string['ejsappname'] = 'Nombre del laboratorio';
$string['ejsappname_help'] = 'Nombre con que figurar&aacute; el laboratorio en el curso';
$string['ejsapp'] = 'EJSApp';
$string['pluginadministration'] = 'Administraci&oacute;n del EJSApp';
$string['pluginname'] = 'EJSApp';
$string['noejsapps'] = 'No hay actividades EJSApp en este curso';

$string['state_load_msg'] = 'Se va a actualizar el estado del laboratorio';
$string['state_fail_msg'] = 'Error al intentar cargar el estado';

$string['controller_load_msg'] = 'Se va a cargar un controlador para el laboratorio';
$string['controller_fail_msg'] = 'Error al intentar cargar el controlador';

$string['recording_load_msg'] = 'Se va a ejecutar una grabaci&oacute;n para este laboratorio';
$string['recording_fail_msg'] = 'Error al intentar ejecutar la grabaci&oacute;n';

$string['more_text'] = 'Texto optional tras el laboratorio EJsS';

$string['css_style'] = 'Hoja de estilos CSS';

$string['jar_file'] = 'Archivo .jar o .zip que encapsula el laboratorio EJsS';

$string['appletfile'] = 'Easy Java(script) Simulation';
$string['appletfile_required'] = 'Se debe seleccionar un archivo .jar o .zip';
$string['appletfile_help'] = 'Selecione el archivo .jar o .zip que encapsula el laboratorio EJsS (Easy Java(script) Simulation). La p&aacute;gina oficial de EJsS es http://fem.um.es/Ejs/';

$string['applet_size_conf'] = 'Reescalado del applet';
$string['applet_size_conf_help'] = 'Tres opciones: 1) "Mantener tama&ntilde;o original" mantendr&aacute; el tama&ntilde;o original del applet en EJS, 2) "Permitir que Moodle fije el tama&ntilde;o" redimensionar&aacute; el applet para que ocupe todo el espacio posible a la par que respeta la relaci&oacute;n de tama&ntilde;o original, 3) "Permitir que el usuario fije el tama&ntilde;o" permitir&aacute; al usuario establecer el tama&ntilde;o del applet y seleccionar si desea mantener, o no, su relaci&oacute;n de tama&ntilde;o original.';
$string['preserve_applet_size'] = 'Mantener tama&ntilde;o original';
$string['moodle_resize'] = 'Permitir que Moodle fije el tama&ntilde;o';
$string['user_resize'] = 'Permitir que el usuario fije el tama&ntilde;o';

$string['preserve_aspect_ratio'] = 'Mantener relaci&oacute;n de tama&ntilde;o';
$string['preserve_aspect_ratio_help'] = 'Si selecciona esta opci&oacute;n, se respetar&aacute; la relaci&oacute;n de tama&ntilde;o original del applet. En ese caso, el usuario podr&aacute; modificar la anchura del applet y el sistema ajustar&aacute; autom&aacute;ticamente el valor para su altura. Si no se selecciona, el usuario podr&aacute; fijar tanto su anchura como su altura.';

$string['custom_width'] = 'Anchura del applet (px)';
$string['custom_width_required'] = 'ATENCI&Oacute;N: La anchura del applet no ha sido fijada. Debes proporcionar un valor distinto.';

$string['custom_height'] = 'Altura del applet (px)';
$string['custom_height_required'] = 'ATENCI&Oacute;N: La altura del applet no ha sido fijada. Debes proporcionar un valor distinto.';

$string['appwording'] = 'Enunciado';

$string['css_rules'] = 'Crea tus propias reglas css para cambiar el aspecto visual de la aplicaci&oacute;n javascript';

$string['css_rules_help'] = '¡Importante! Escriba cada selector y el comienzo de su declaración (la llave) en la misma línea.';

$string['state_file'] = 'Archivo .xml o .json con el estado que este laboratorio EJsS debe leer';

$string['statefile'] = 'Estado del Easy Java(script) Simulation';
$string['statefile_help'] = 'Seleccione el archivo .xml (para Java) o .json (para Javascript) con el estado que la aplicaci&oacute;n EJsS debe cargar al ejecutarse.';

$string['controller_file'] = 'Archivo .cnt con el controlador que la aplicaci&oacute;n EJS debe cargar al iniciarse';

$string['controllerfile'] = 'Controlador para el Easy Java(script) Simulation';
$string['controllerfile_help'] = 'Seleccione el archivo .cnt con el c&oacute;digo del controlador que la aplicaci&oacute;n EJS debe ejecutar al iniciarse.';

$string['recording_file'] = 'Archivo .rec con la grabaci&oacute;n que la aplicaci&oacute;n EJS debe ejecutar al cargarse';

$string['recordingfile'] = 'Grabaci&oacute;n del Easy Java(script) Simulation';
$string['recordingfile_help'] = 'Seleccione el archivo .rec con la grabación de la interacción que la aplicaci&oacute;n EJS debe ejecutar al cargarse.';

$string['personalize_vars'] = 'Personalizar variables del laboratorio EJS';

$string['use_personalized_vars'] = 'Personalizar variables para cada usuario?';
$string['use_personalized_vars_help'] = 'Seleccione "ss&iacute;" si conoce el nombre de alguna de las variables en el modelo EJS y deseas que adquieran valores diferentes para cada usuario que acceda a esta aplicaci&oacute;n.';

$string['var_name'] = 'Nombre {no}';
$string['var_name_help'] = 'Nombre de la variable en el modelo EJS.';

$string['var_type'] = 'Tipo {no}';
$string['var_type_help'] = 'Tipo de la variable en el modelo EJS.';

$string['min_value'] = 'Valor m&iacute;nimo {no}';
$string['min_value_help'] = 'M&iacute;nimo valor permitido para la variable.';

$string['max_value'] = 'M&aacute;ximo valor {no}';
$string['max_value_help'] = 'M&aacute;ximo valor permitido para la variable.';

$string['vars_required'] = 'ATENCI&Oacute;N: Si desea utilizar variables personalizadas, debe espeficificar al menos una.';
$string['vars_incorrect_type'] = 'ATENCI&Oacute;N: El tipo y los valores especificados para esta variable no se corresponden entre s&iacute;.';

$string['rem_lab_conf'] = 'Configuraci&oacute;n del laboratorio remoto';

$string['is_rem_lab'] = 'Sistema experimental remoto?';
$string['is_rem_lab_help'] = 'Si este EJSApp conecta a recursos reales de manera remota Y quieres que el Sistema de Reservas EJSApp controle su acceso, selecciona "s&iacute;". En caso contrario, selecciona "no". NOTA: Necesita el bloque Remlab Manager para que esta opci&oacute;n est&eacute; disponible.';

$string['practiceintro'] = 'Identificador de pr&aacute;ctica';
$string['practiceintro_help'] = 'El identificador de la pr&aacute;ctica, que desea usar con este sistema experimental.';
$string['practiceintro_required'] = 'ATENCI&Oacute;N: Si desea configurar esta actividad como un laboratorio remoto, necesita especificar un identificador de pr&aacute; que est&eacute; previamente definido en el bloque Remlab Manager.';

$string['file_error'] = "No pudo abrirse el fichero en el servidor";
$string['manifest_error'] = " > No se ha podido encontrar o abrir el manifiesto .mf. Revise el fichero que ha cargado.";
$string['EJS_version'] = "ATENCI&Oacute;N: El applet no fu&eacute; generado con EJS 4.37 (build 121201), o superior. Recomp&iacute;lelo con una versi&oacute;n m&aacute;s moderna de EJS.";
$string['EJS_codebase'] = "ATENCI&Oacute;N: El manifest del applet que ha subido no especifica este servidor Moodle en el par&aacute;metro 'codebase', de modo que no ha sido firmado.";

$string['inactive_lab'] = 'El laboratorio remoto es&aacute; inactivo en este momento.';
$string['no_booking'] = 'No tiene reserva para este laboratorio en este horario.';
$string['collab_access'] = 'Esta es una sesi&oacute;n colaborativa.';
$string['check_bookings'] = 'Consulte sus reservas activas con el sistema de reservas.';
$string['lab_in_use'] = 'El laboratorio est&aacute; ocupado en este instante. Pruebe de nuevo m&aacute;s adelante.';
$string['booked_lab'] = 'Este laboratorio ha sido reservado para esta hora en un curso distinto. Pruebe de nuevo m&aacute;s adelante.';

$string['ejsapp_error'] = 'La actividad EJSApp a la que est&aacute; tratando de acceder no existe.';

$string['personal_vars_button'] = 'Ver variables personalizadas';

//lib.php
$string['mail_subject_lab_not_checkable'] = 'Alerta de Estado de Laboratorio no Verificable';
$string['mail_content1_lab_not_checkable'] = 'El estado de uno de tus laboratorios remotos (';
$string['mail_content2_lab_not_checkable'] = ' - IP: ';
$string['mail_content3_lab_not_checkable'] = ') no ha podido ser verificado.';

$string['mail_subject_lab_down'] = 'Alerta de Laboratorio Inactivo';
$string['mail_content1_lab_down'] = 'Uno de tus laboratorios remotos previamente operativos (';
$string['mail_content2_lab_down'] = ' - IP: ';
$string['mail_content3_lab_down'] = ") ha dejado de estar accesible. \r\n";
$string['mail_content4_lab_down'] = "A continuaci&oacute;n se da una lista de los dispositivos inaccessibles o inoperativos: \r\n";

$string['mail_subject_lab_up'] = 'Aviso de Laboratorio Activo';
$string['mail_content1_lab_up'] = 'Uno de tus laboratorios remotos previamente innaccesibles (';
$string['mail_content2_lab_up'] = ' - IP: ';
$string['mail_content3_lab_up'] = ') vuelve a estar operativo.';

//personalized_vars_values.php
$string['personalVars_pageTitle'] = 'Valores de las variables personalizadas';
$string['users_ejsapp_selection'] = 'Seleccione los usuarios y la actividad EJSApp';
$string['ejsapp_activity_selection'] = 'Selecci&oacute;n de la actividad EJSApp';
$string['variable_name'] = 'Variable';
$string['variable_value'] = 'Valor';
$string['export_all_data'] = 'Exportar datos para todas las actividades EJSApp en este curso';
$string['export_this_data'] = 'Exportar datos para esta actividad EJSApp';
$string['no_ejsapps'] = 'La actividad EJSApp seleccionada no tiene variables personalizadas';
$string['personalized_values'] = 'valores_personalizdos_';

//kick_out.php
$string['time_is_up'] = 'Se ha agotado su tiempo con el laboratorio remoto. Si desea seguir trabajando con &eacute;l, haga una nueva reserva y/o refresque esta p&aacute;gina.';

//countdown.php
$string['seconds'] = 'segundos restantes.';
$string['refresh'] = 'Pruebe a refrescar su ventana ahora.';

//generate_embedding_code.php
$string['end_message'] = 'Fin de la reproducci&oacute;n';

//Capabilities
$string['ejsapp:accessremotelabs'] = "Acceso a todos los laboratorios remotos";
$string['ejsapp:addinstance'] = "Añadir una nueva actividad EJSApp";
$string['ejsapp:view'] = "Ver una actividad EJSApp";
$string['ejsapp:requestinformation'] = "Pedir informaci&oacute;n para plugins de terceros";

//Events
$string['event_viewed'] = "EJSApp activity viewed";
$string['event_working'] = "Working with the EJSApp activity";
$string['event_wait'] = "Waiting for the lab to be free";
$string['event_book'] = "Need to make a booking";
$string['event_collab'] = "Working with the EJSApp activity in collaborative mode";
$string['event_inactive'] = "Lab is inactive";
$string['event_booked'] = "Lab is booked in a different course";

//Settings
$string['default_general_set'] = "Opciones generales";
$string['check_activity'] = "Comprobar actividad";
$string['check_activity_description'] = "Con que frecuencia se comprueba la actividad de los usuarios en EJSApp (s)";
$string['default_certificate_set'] = "Opciones del certificado de confianza. (Importante s&oacute;lo si se desea firmar de manera autom&aacute;tica los applets subidos con EJSApp)";
$string['certificate_path'] = "Ruta al fichero del certificado de confianza";
$string['certificate_path_description'] = "La ruta en el servidor Moodle al fichero del certificado de confianza que se usar&aacute; para firmar los applets de Java";
$string['certificate_password'] = "Contraseña del certificado de confianza";
$string['certificate_password_description'] = "La contraseña requerida para usar el certificado de confianza";
$string['certificate_alias'] = "Alias del certificado de confianza";
$string['certificate_alias_description'] = "El alias asignado al certificado de confianza";