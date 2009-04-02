<?php

/*
	Form Definition

	Tabledefinition

	Datatypes:
	- INTEGER (Forces the input to Int)
	- DOUBLE
	- CURRENCY (Formats the values to currency notation)
	- VARCHAR (no format check, maxlength: 255)
	- TEXT (no format check)
	- DATE (Dateformat, automatic conversion to timestamps)

	Formtype:
	- TEXT (Textfield)
	- TEXTAREA (Textarea)
	- PASSWORD (Password textfield, input is not shown when edited)
	- SELECT (Select option field)
	- RADIO
	- CHECKBOX
	- CHECKBOXARRAY
	- FILE

	VALUE:
	- Wert oder Array

	Hint:
	The ID field of the database table is not part of the datafield definition.
	The ID field must be always auto incement (int or bigint).


*/

$form["title"] 			= "Server Config";
$form["description"] 	= "";
$form["name"] 			= "server_config";
$form["action"]			= "server_config_edit.php";
$form["db_table"]		= "server";
$form["db_table_idx"]	= "server_id";
$form["db_history"]		= "yes";
$form["tab_default"]	= "server";
$form["list_default"]	= "server_config_list.php";
$form["auth"]			= 'yes'; // yes / no

$form["auth_preset"]["userid"]  = 0; // 0 = id of the user, > 0 id must match with id of current user
$form["auth_preset"]["groupid"] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form["auth_preset"]["perm_user"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_group"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_other"] = ''; //r = read, i = insert, u = update, d = delete

$form["tabs"]['server'] = array (
	'title' 	=> "Server",
	'width' 	=> 70,
	'template' 	=> "templates/server_config_server_edit.htm",
	'fields' 	=> array (
	##################################
	# Begin Datatable fields
	##################################
		'auto_network_configuration' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'CHECKBOX',
			'default'	=> 'n',
			'value'		=> array(0 => 'n',1 => 'y')
		),
		'ip_address' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'default'	=> '192.168.0.105',
			'validators'	=> array ( 	0 => array (	'type'	=> 'ISIPV4',
														'errmsg'=> 'ip_address_error_wrong'),
									),
			'value'		=> '',
			'width'		=> '15',
			'maxlength'	=> '255'
		),
		'netmask' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'default'	=> '255.255.255.0',
			'validators'	=> array ( 	0 => array (	'type'	=> 'ISIPV4',
														'errmsg'=> 'netmask_error_wrong'),
									),
			'value'		=> '',
			'width'		=> '15',
			'maxlength'	=> '255'
		),
		'gateway' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'default'	=> '192.168.0.1',
			'validators'	=> array ( 	0 => array (	'type'	=> 'ISIPV4',
														'errmsg'=> 'gateway_error_wrong'),
									),
			'value'		=> '',
			'width'		=> '15',
			'maxlength'	=> '255'
		),
		'hostname' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'default'	=> 'server1.domain.tld',
			'validators'	=> array ( 	0 => array (	'type'	=> 'NOTEMPTY',
														'errmsg'=> 'hostname_error_empty'),
									),
			'value'		=> '',
			'width'		=> '40',
			'maxlength'	=> '255'
		),
		'nameservers' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'default'	=> '192.168.0.1,192.168.0.2',
			'validators'	=> array ( 	0 => array (	'type'	=> 'NOTEMPTY',
														'errmsg'=> 'nameservers_error_empty'),
									),
			'value'		=> '',
			'width'		=> '40',
			'maxlength'	=> '255'
		),
	##################################
	# ENDE Datatable fields
	##################################
	)
);

?>