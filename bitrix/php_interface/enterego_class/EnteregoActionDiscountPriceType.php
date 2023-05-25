<?php

namespace Enterego;

use Bitrix\Main\Loader,
    Bitrix\Sale;
use Bitrix\Main\Localization\Loc;

if (!Loader::includeModule('catalog'))
    return;

class EnteregoActionDiscountPriceType extends \CSaleActionCtrlAction
{
    public static function GetControlDescr() {
        $description = parent::GetControlDescr();
        $description['EXECUTE_MODULE'] = 'all';//Для сохранения в таблицу
        $description['SORT'] = 500;

        return $description;
    }

    public static function GetControlID() {
        return 'ActSalePriceType';//Уникальный идентификатор
    }

    public static function GetControlShow($arParams) {
        $arAtoms = static::GetAtomsEx(false, false);
        $boolCurrency = false;
        if (static::$boolInit) {
            if (isset(static::$arInitParams['CURRENCY'])) {
                $arAtoms['Unit']['values']['Cur'] = static::$arInitParams['CURRENCY'];
                $boolCurrency = true;
            } elseif (isset(static::$arInitParams['SITE_ID'])) {
                $strCurrency = Sale\Internals\SiteCurrencyTable::getSiteCurrency(static::$arInitParams['SITE_ID']);
                if (!empty($strCurrency)) {
                    $arAtoms['Unit']['values']['Cur'] = $strCurrency;
                    $boolCurrency = true;
                }
            }
        }
        if (!$boolCurrency) {
            unset($arAtoms['Unit']['values']['Cur']);
        }
        $arResult = array(
            'controlId' => static::GetControlID(),
            'group' => true,
            'label' => 'Установить продажный вид цены',
            'defaultText' => 'Продажный вид цены',
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'visual' => static::GetVisual(),
            'control' => array(
                'Установить продажный вид цены',
                $arAtoms['Value'],
                $arAtoms['All'],
                $arAtoms['True']
            ),
            'mess' => array(
                'ADD_CONTROL' => Loc::getMessage('BT_SALE_SUBACT_ADD_CONTROL'),
                'SELECT_CONTROL' => Loc::getMessage('BT_SALE_SUBACT_SELECT_CONTROL'),
                'DELETE_CONTROL' => Loc::getMessage('BT_SALE_ACT_GROUP_DELETE_CONTROL')
            )
        );

        return $arResult;
    }

    public static function GetAtoms() {
        return static::GetAtomsEx(false, false);
    }

    public static function GetAtomsEx($strControlID = false, $boolEx = false) {

        $boolEx = (true === $boolEx ? true : false);
        $arAtomList = array(
            'Value' => array(
                'JS' => array(
                    'id' => 'Value',
                    'name' => 'extra_size',
                    'type' => 'input'
                ),
                'ATOM' => array(
                    'ID' => 'Value',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => ''
                )
            ),
            'All' => array(
                'JS' => array(
                    'id' => 'All',
                    'name' => 'aggregator',
                    'type' => 'select',
                    'values' => array(
                        'AND' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_ALL_EXT'),
                        'OR' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_ANY_EXT')
                    ),
                    'defaultText' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_DEF'),
                    'defaultValue' => 'AND',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'All',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            ),
            'True' => array(
                'JS' => array(
                    'id' => 'True',
                    'name' => 'value',
                    'type' => 'select',
                    'values' => array(
                        'True' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_TRUE'),
                        'False' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_FALSE')
                    ),
                    'defaultText' => Loc::getMessage('BT_CLOBAL_COND_GROUP_SELECT_DEF'),
                    'defaultValue' => 'True',
                    'first_option' => '...'
                ),
                'ATOM' => array(
                    'ID' => 'True',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                )
            )
        );

        if (!$boolEx) {
            foreach ($arAtomList as &$arOneAtom) {
                $arOneAtom = $arOneAtom['JS'];
            }
            if (isset($arOneAtom))
                unset($arOneAtom);
        }

        return $arAtomList;
    }

    public static function GetVisual()
    {
        return array(
            'controls' => array(
                'All',
                'True'
            ),
            'values' => array(
                array(
                    'All' => 'AND',
                    'True' => 'True'
                ),
                array(
                    'All' => 'AND',
                    'True' => 'False'
                ),
                array(
                    'All' => 'OR',
                    'True' => 'True'
                ),
                array(
                    'All' => 'OR',
                    'True' => 'False'
                ),
            ),
            'logic' => array(
                array(
                    'style' => 'condition-logic-and',
                    'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_AND')
                ),
                array(
                    'style' => 'condition-logic-and',
                    'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_NOT_AND')
                ),
                array(
                    'style' => 'condition-logic-or',
                    'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_OR')
                ),
                array(
                    'style' => 'condition-logic-or',
                    'message' => Loc::getMessage('BT_SALE_ACT_GROUP_LOGIC_NOT_OR')
                )
            )
        );
    }

    public static function GetShowIn($arControls) {
        return array(\CSaleActionCtrlGroup::GetControlID());
    }

    public static function GetConditionShow($arParams)
    {
        if (!isset($arParams['ID']))
            return false;
        if ($arParams['ID'] != static::GetControlID())
            return false;
        $arControl = array(
            'ID' => $arParams['ID'],
            'ATOMS' => static::GetAtomsEx(false, true)
        );

        return static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
    }

    public static function Parse($arOneCondition)
    {
        if (!isset($arOneCondition['controlId']))
            return false;
        if ($arOneCondition['controlId'] != static::GetControlID())
            return false;
        $arControl = array(
            'ID' => $arOneCondition['controlId'],
            'ATOMS' => static::GetAtomsEx(false, true),
        );

        return static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);
    }

    /**
     * Функция должна вернуть колбэк того что должно быть выполнено при наступлении условий
     * @param type $arOneCondition
     * @param type $arParams
     * @param type $arControl
     * @param type $arSubs
     * @return string
     */
    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false) {

        $mxResult = '';

        if (is_string($arControl)) {
            if ($arControl == static::GetControlID()) {
                $arControl = array(
                    'ID' => static::GetControlID(),
                    'ATOMS' => static::GetAtoms()
                );
            }
        }
        $boolError = !is_array($arControl);

        if (!$boolError) {
            $arOneCondition['Value'] = $arOneCondition['Value'];
            $actionParams = array(
                'VALUE' => $arOneCondition['Value'],
            );
            if (!empty($arSubs))
            {
                $filter = '$saleact'.$arParams['FUNC_ID'];

                if ($arOneCondition['All'] == 'AND')
                {
                    $prefix = '';
                    $logic = ' && ';
                    $itemPrefix = ($arOneCondition['True'] == 'True' ? '' : '!');
                }
                else
                {
                    $itemPrefix = '';
                    if ($arOneCondition['True'] == 'True')
                    {
                        $prefix = '';
                        $logic = ' || ';
                    }
                    else
                    {
                        $prefix = '!';
                        $logic = ' && ';
                    }
                }

                $commandLine = $itemPrefix.implode($logic.$itemPrefix, $arSubs);
                if ($prefix != '')
                    $commandLine = $prefix.'('.$commandLine.')';

                $mxResult = $filter.'=function($row){';
                $mxResult .= 'return ('.$commandLine.');';
                $mxResult .= '};';
                //callback function
                $mxResult .= '\Enterego\EnteregoBasket::SetSpecialPriceType(' . $arParams['ORDER'] . ', '
                    . var_export($actionParams, true) . ', '.$filter.')';
                unset($filter);
            }
            else {
                //callback function
                $mxResult .= '\Enterego\EnteregoBasket::SetSpecialPriceType(' . $arParams['ORDER'] . ', '
                    . var_export($actionParams, true) . ')';
            }
            unset($actionParams);
        }

        return $mxResult;
    }
}