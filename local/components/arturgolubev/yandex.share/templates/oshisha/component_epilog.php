<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION;

if($arParams["OLD_BROWSERS"] == 'Y'){
	$APPLICATION->AddHeadString('<script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js" charset="UTF-8"></script>', true);
}

$APPLICATION->AddHeadString('<script src="//yastatic.net/share2/share.js" charset="UTF-8" async></script>', true);
?>