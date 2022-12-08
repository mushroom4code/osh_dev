<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);

$countWriteOff=0;

if(\Bitrix\Main\Loader::includeModule('skyweb24.loyaltyprogram')) {
    $data = \Skyweb24\Loyaltyprogram\Entity\WriteOffTable::getList([
        'filter' => ['status' => 'request'],
        'select' => ['CNT'],
        'runtime' => [new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')]

    ]);

    while ($arData = $data->fetch()) {
        $countWriteOff = $arData["CNT"];
    }
}
$writeOffIcon=$countWriteOff>0?'subscribe_menu_icon':'';

?><?
$aMenu = [
    "parent_menu" => "global_menu_marketing", // �������� � ������ "���������"
    "sort"        => 100,                    // ��� ������ ����
    "url"         => "",  // ������ �� ������ ����
    "text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_MAIN"),       // ����� ������ ����
    "title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_MAIN_TITLE"), // ����� ����������� ���������
    "icon"        => "skwb24_loyaltyprogram_menu_icon", // ����� ������
    "items_id"    => "skyweb24_loyaltyprogram",  // ������������� �����
    "items"       => [// ��������� ������ ���� ���������� ����.
		[
			"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_MANAGE_TITLE"), // ����� ����������� ���������
			"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_MANAGE"),       // ����� ������ ����
			"items_id"    => "menu_sw24_loyal_manage",  // ������������� �����
			"items"       => [
				[
					"url"         => "skyweb24_loyaltyprogram_profiles.php?lang=".LANGUAGE_ID,  // ������ �� ������ ����
					"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_PROFILE_TITLE"), // ����� ����������� ���������
					"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_PROFILE"),       // ����� ������ ����
					"items_id"    => "sw24_loyal_manage_profile"
				],
				[
					"url"         => "skyweb24_loyaltyprogram_referrals.php?lang=".LANGUAGE_ID,  // ������ �� ������ ����
					"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_REFERRALS_TITLE"), // ����� ����������� ���������
					"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_REFERRALS"),       // ����� ������ ����
				],
				[
					"url"         => "skyweb24_loyaltyprogram_ranks.php?lang=".LANGUAGE_ID,  // ������ �� ������ ����
					"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_RANKS_TITLE"), // ����� ����������� ���������
					"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_RANKS"),       // ����� ������ ����
					"items_id"    => "menu_sw24_loyal_manage_ranks",
					"items"        => [
						[
							"url"         =>"skyweb24_loyaltyprogram_ranks_list_users.php?lang=".LANGUAGE_ID,
							"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_RANKS_LIST_USERS_TITLE"), // ����� ����������� ���������
							"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_RANKS_LIST_USERS"),       // ����� ������ ����
						],
					]
				],
				[
					"url"         => "skyweb24_loyaltyprogram_groups.php?lang=".LANGUAGE_ID,   // ������ �� ������ ����
					"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_GROUPS_TITLE"), // ����� ����������� ���������
					"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_GROUPS"),       // ����� ������ ����
				],
				[
					"url"         => "skyweb24_loyaltyprogram_writeoff.php?lang=".LANGUAGE_ID,   // ������ �� ������ ����
					"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_WRITEOFF_TITLE"), // ����� ����������� ���������
					"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_WRITEOFF"),       // ����� ������ ����
                    "icon"    => $writeOffIcon,
				],
				[
					"url"         => "skyweb24_loyaltyprogram_import.php?lang=".LANGUAGE_ID,   // ������ �� ������ ����
					"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_IMPORT_TITLE"), // ����� ����������� ���������
					"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_IMPORT"),       // ����� ������ ����
				],
				[
					"url"         => "skyweb24_loyaltyprogram_queue.php?lang=".LANGUAGE_ID,   // ������ �� ������ ����
					"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_EDITQUEUE_TITLE"), // ����� ����������� ���������
					"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_EDITQUEUE"),       // ����� ������ ����
				]
			],
		],
		[
			"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_BALANCE_TITLE"), // ����� ����������� ���������
			"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_BALANCE"),       // ����� ������ ����
			"items_id"    => "menu_sw24_loyal_balance",  // ������������� �����
			"items"       => [
				[
					"url"         => "/bitrix/admin/sale_account_admin.php?lang=".LANGUAGE_ID,  // ������ �� ������ ����
					"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_ACCOUNT_TITLE"), // ����� ����������� ���������
					"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_ACCOUNT"),       // ����� ������ ����
				],
				[
					"url"         => "skyweb24_loyaltyprogram_transact.php?lang=".LANGUAGE_ID,  // ������ �� ������ ����
					"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_TRANSACT_TITLE"), // ����� ����������� ���������
					"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_TRANSACT"),       // ����� ������ ����
				]
			]
		],
		[
			"url"         => "skyweb24_loyaltyprogram_documentation.php?lang=".LANGUAGE_ID,   // ������ �� ������ ����
			"title"       => Loc::getMessage("skyweb24.loyaltyprogram_MENU_DOCUMENTATION_TITLE"), // ����� ����������� ���������
			"text"        => Loc::getMessage("skyweb24.loyaltyprogram_MENU_DOCUMENTATION"),       // ����� ������ ����
		],
		[
			"url"         => "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid_menu=1&mid=skyweb24.loyaltyprogram",
			"title"       => GetMessage("skyweb24.loyaltyprogram_MENU_SETTING_TITLE"), // ����� ����������� ���������
			"text"        => GetMessage("skyweb24.loyaltyprogram_MENU_SETTING"),       // ����� ������ ����
		]
	]   
];

return $aMenu;
?>