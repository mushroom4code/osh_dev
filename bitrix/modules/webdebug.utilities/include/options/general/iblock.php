<?
namespace WD\Utilities\Export;

use
	\WD\Utilities\Helper,
	\WD\Utilities\Options;

Helper::loadMessages();

return [
	'OPTIONS' => [
		'iblock_add_detail_link' => [
			'TYPE' => 'select',
			'VALUES' => [
				'N' => Options::getMessage('IBLOCK_ADD_DETAIL_LINK_NO'),
				'Y' => Options::getMessage('IBLOCK_ADD_DETAIL_LINK_SUBMENU'),
				'S' => Options::getMessage('IBLOCK_ADD_DETAIL_LINK_SEPARATE'),
			],
		],
		'iblock_show_element_id' => [],
		'iblock_just_this_site' => [],
		'iblock_hide_empty_types' => [],
	],
];
?>