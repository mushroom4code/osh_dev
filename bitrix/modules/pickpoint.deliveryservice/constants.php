<?php
$arServiceTypes = array(
    0 => 'STD',
    1 => 'STDCOD',
    2 => 'PRIO',
    3 => 'PRIOCOD',
);
$arServiceTypesCodes = array(
    0 => 10001,
    1 => 10003,
    2 => 10002,
    3 => 10004,
);

// Legacy Getting Types - deprecated and will be removed 
$arEnclosingTypes = array(
    0 => 'CUR',
    1 => 'WIN',
    2 => 'APTCON',
    3 => 'APT',
);
$arEnclosingTypesCodes = array(
    0 => 101, //"CUR",
    1 => 102, //"WIN",
    2 => 103, //"APTCON",
    3 => 104, //"APT"
);
// --

$arPayedServiceTypes = array(1, 3); //Available types to pay thorough PickPoint

$arSizes = array(
    'S' => array('NAME' => 'S', 'SIZE_X' => 15, 'SIZE_Y' => 36, 'SIZE_Z' => 60),
    'M' => array('NAME' => 'M', 'SIZE_X' => 20, 'SIZE_Y' => 36, 'SIZE_Z' => 60),
    'L' => array('NAME' => 'L', 'SIZE_X' => 36, 'SIZE_Y' => 36, 'SIZE_Z' => 60),
);

$arOptionDefaults = array(
    'FIO' => array(
        'TYPE' => 'USER',
        'VALUE' => 'NAME',
    ),
    'ADDITIONAL_PHONES' => array(
        'TYPE' => 'USER',
        'VALUE' => 'PERSONAL_MOBILE',
    ),
    'NUMBER_P' => array(
        'TYPE' => 'ORDER',
        'VALUE' => 'ID',
    ),
    'EMAIL' => array(
        'TYPE' => 'USER',
        'VALUE' => 'EMAIL',
    ),
    'ORDER_PHONE' =>  array(
        'TYPE' => 'ORDER',
        'VALUE' => 'ID',
    ),
    'ORDER_LOCATION' =>  array(
        'TYPE' => 'ORDER',
        'VALUE' => 'ID',
    )
);

// Editable fields to status mapping
$statusTable = array(
    101 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_101'),
        'PostamatNumber' => true,
        'MobilePhone' => true,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    102 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_102'),
        'PostamatNumber' => true,
        'MobilePhone' => true,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    103 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_103'),
        'PostamatNumber' => true,
        'MobilePhone' => true,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    104 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_104'),
        'PostamatNumber' => true,
        'MobilePhone' => true,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    105 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_105'),
        'PostamatNumber' => false,
        'MobilePhone' => true,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    106 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_106'),
        'PostamatNumber' => false,
        'MobilePhone' => true,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    107 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_107'),
        'PostamatNumber' => false,
        'MobilePhone' => true,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    108 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_108'),
        'PostamatNumber' => false,
        'MobilePhone' => true,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    109 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_109'),
        'PostamatNumber' => false,
        'MobilePhone' => true,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    110 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_110'),
        'PostamatNumber' => false,
        'MobilePhone' => false,
        'RecipientName' => false,
        'Email' => false,
        'Sum' => false
    ),
    111 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_111'),
        'PostamatNumber' => false,
        'MobilePhone' => false,
        'RecipientName' => true,
        'Email' => true,
        'Sum' => true
    ),
    112 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_112'),
        'PostamatNumber' => false,
        'MobilePhone' => false,
        'RecipientName' => false,
        'Email' => false,
        'Sum' => false
    ),
    113 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_113'),
        'PostamatNumber' => false,
        'MobilePhone' => false,
        'RecipientName' => false,
        'Email' => false,
        'Sum' => false
    ),
    114 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_114'),
        'PostamatNumber' => false,
        'MobilePhone' => false,
        'RecipientName' => false,
        'Email' => false,
        'Sum' => false
    ),
    115 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_115'),
        'PostamatNumber' => false,
        'MobilePhone' => false,
        'RecipientName' => false,
        'Email' => false,
        'Sum' => false
    ),
    116 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_116'),
        'PostamatNumber' => false,
        'MobilePhone' => false,
        'RecipientName' => false,
        'Email' => false,
        'Sum' => false
    ),
    117 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_117'),
        'PostamatNumber' => false,
        'MobilePhone' => false,
        'RecipientName' => false,
        'Email' => false,
        'Sum' => false
    ),
    123 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_123'),
        'PostamatNumber' => false,
        'MobilePhone' => false,
        'RecipientName' => false,
        'Email' => false,
        'Sum' => false
    ),
	124 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_124'),
    ),
    125 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_125'),
    ),	
    126 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_126'),
    ),
    127 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_127'),
    ),
    128 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_128'),
    ),
    129 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_129'),
    ),
    130 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_130'),
    ),
    131 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_131'),
    ),
    132 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_132'),
    ),
    133 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_133'),
    ),
    134 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_134'),
    ),
    135 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_135'),
    ),
    136 => array(
        'TEXT' => GetMessage('PP_STATUS_TEXT_136'),
    ),    
);