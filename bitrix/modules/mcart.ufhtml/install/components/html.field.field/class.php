<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use CmcartUserFieldHtml;

/**
 * Class HtmlUfComponent
 */
class HtmlUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return CmcartUserFieldHtml::USER_TYPE_ID;
	}
}
