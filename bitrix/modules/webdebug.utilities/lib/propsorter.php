<?
namespace WD\Utilities;

use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class PropSorterTable
 * @package WD\Utilities
 */
class PropSorterTable extends Entity\DataManager {
	
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(){
		return 'wdu_propsorter';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap() {
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('WDU_PROPSORTER_ID'),
			)),
			'IBLOCK_ID' => new Entity\IntegerField('IBLOCK_ID', array(
				'title' => Loc::getMessage('WDU_PROPSORTER_IBLOCK_ID'),
			)),
			'PROPERTY_ID' => new Entity\IntegerField('PROPERTY_ID', array(
				'title' => Loc::getMessage('WDU_PROPSORTER_PROPERTY_ID'),
			)),
			'GROUP_TITLE' => new Entity\StringField('GROUP_TITLE', array(
				'title' => Loc::getMessage('WDU_PROPSORTER_GROUP_TITLE'),
			)),
			'GROUP_ACTIVE' => new Entity\StringField('GROUP_ACTIVE', array(
				'title' => Loc::getMessage('WDU_PROPSORTER_GROUP_ACTIVE'),
			)),
			'SORT' => new Entity\IntegerField('SORT', array(
				'title' => Loc::getMessage('WDU_PROPSORTER_SORT'),
			)),
		);
	}
	
	/**
	 *	Get all iblock properties
	 */
	public static function getIBlockProperties($intIBlockId){
		$arResult = [];
		$resProps = \CIBlockProperty::GetList(['SORT'=>'ASC','ID'=>'ASC'], ['IBLOCK_ID'=>$intIBlockId,'ACTIVE'=>'Y']);
		while ($arProp = $resProps->GetNext(false,false)) {
			$arResult[$arProp['ID']] = $arProp;
		}
		return $arResult;
	}
	
	/**
	 *	
	 */
	public static function loadIBlockData($intIBlockId){
		$arResult = [];
		if($intIBlockId){
			$arQuery = [
				'order' => [
					'SORT' => 'ASC',
				],
				'filter' => [
					'IBLOCK_ID' => $intIBlockId,
				],
			];
			$resItems = static::getList($arQuery);
			while($arItem = $resItems->fetch()){
				$arItem['ACTIVE'] = $arItem['GROUP_ACTIVE'];
				$arResult[] = $arItem;
			}
		}
		# Complement props
		$arExistPropsId = [];
		$intSort = 0;
		foreach($arResult as $arProp){
			if(is_numeric($arProp['PROPERTY_ID'])){
				$arExistPropsId[] = $arProp['PROPERTY_ID'];
			}
			$intSort = $arProp['SORT'];
		}
		$arPropsAll = static::getIBlockProperties($intIBlockId);
		foreach($arPropsAll as $arProp){
			if(!in_array($arProp['ID'], $arExistPropsId)){
				$arResult[] = [
					'ID' => 0,
					'IBLOCK_ID' => $arProp['IBLOCK_ID'],
					'PROPERTY_ID' => $arProp['ID'],
					'GROUP_TITLE' => '',
					'ACTIVE' => '',
					'SORT' => ++$intSort,
				];
			}
		}
		#
		foreach($arResult as $key => $arProp){
			if(is_numeric($arProp['PROPERTY_ID'])){
				$arResult[$key]['PROPERTY'] = $arPropsAll[$arProp['PROPERTY_ID']];
			}
		}
		#
		return $arResult;
	}


}
