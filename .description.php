<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('T_COMPONENT_NAME'),
	'DESCRIPTION' => GetMessage('T_COMPONENT_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'COMPLEX' => 'Y',
	'PATH' => array(
		"ID" => "other",
		"CHILD" => array(
			"ID" => "dfa_system",
			"NAME" => GetMessage("DFA_DESC_SYSTEM_SECTION_NAME"),
		),	
	),
);
?>