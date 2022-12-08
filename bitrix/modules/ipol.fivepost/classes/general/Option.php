<?
namespace Ipol\Fivepost;

use Ipol\Fivepost\Bitrix\Adapter;
use Ipol\Fivepost\Bitrix\Entity\BasicResponse;
use Ipol\Fivepost\Bitrix\Handler\PaySystems;
use Ipol\Fivepost\Bitrix\Handler\Statuses;
use Ipol\Fivepost\Bitrix\Tools;

IncludeModuleLangFile(__FILE__);

/**
 * Class Option
 * @package Ipol\Fivepost\
 * Storing and working with module options
 */
class Option extends AbstractGeneral
{
    public static $ABYSS = array();

    public static $collection  = false;

    /**
     * Get module option, auto deserialize multiple values
     * @param $option
     * @return mixed|string
     */
    public static function get($option)
    {
        $self = \COption::GetOptionString(self::$MODULE_ID, $option, self::getDefault($option));
        if (
            unserialize($self) !== false &&
            self::checkMultiple($option)
        )
            $self = unserialize($self);
        return $self;
    }

    /**
     * Set module option
     * @param $option
     * @param $val
     * @param bool $doSerialise
     */
    public static function set($option, $val, $doSerialise = false)
    {
        if ($doSerialise) {
            $val = serialize($val);
        }
        $self = \COption::SetOptionString(self::$MODULE_ID, $option, $val);
    }

    /**
     * Delete option
     * @param $option
     */
    public static function delete($option)
    {
        \COption::RemoveOption(self::$MODULE_ID, $option);
    }

    /**
     * Get default option value
     * @param $option
     * @return bool
     */
    public static function getDefault($option)
    {
        $opt = self::collection();
        if (array_key_exists($option, $opt))
            return $opt[$option]['default'];
        return false;
    }

    /**
     * Checks if option can handle multiple values
     * @param $option
     * @return bool
     */
    public static function checkMultiple($option)
    {
        $opt = self::collection();
        if (array_key_exists($option, $opt) && array_key_exists('multiple', $opt[$option])) {
            return $opt[$option]['multiple'];
        }
        return false;
    }

    /**
     * Make array for Bitrix module options page. Add hints if exists. @see ShowParamsHTMLByArray
     * @param bool $helpMakros
     * @return array
     */
    public static function toOptions($helpMakros = false)
    {
        if (!$helpMakros)
            $helpMakros = "<a href='#' class='".self::$MODULE_LBL."PropHint' onclick='return ".self::$MODULE_LBL."setups.popup(\"pop-#CODE#\", this);'></a>";

        $arOptions = array();
        foreach (self::collection() as $optCode => $optVal) {
            if (!array_key_exists('group',$optVal) || !$optVal['group'])
                continue;

            if (!array_key_exists($optVal['group'], $arOptions))
                $arOptions[$optVal['group']] = array();

            $name = ($optVal['hasHint'] == 'Y') ? " ".str_replace('#CODE#', $optCode, $helpMakros) : '';

            $arDescription = array(
                $optCode,
                Tools::getMessage("OPT_{$optCode}").$name,
                $optVal['default'],
                array($optVal['type']),
                'N'
            );

            if (array_key_exists('required', $optVal) && $optVal['required']) {
                $arDescription [] = ' *';
            }

            $arOptions[$optVal['group']][] = $arDescription;
        }

        return $arOptions;
    }

    /**
     * List of all module options
     * group    - option group for ShowParamsHTMLByArray($arAllOptions["group"]);
     * hasHint  - 'Y'|'N' - make option hint with '?' sign placed near option name
     * default  - default value
     * type     - option type: text | checkbox | selectbox | textbox
     * multiple - true|false - can option handle multiple values or not
     * required - true|false - is option value required for filling
     * For type 'selectbox' @see Option::getSelectVals
     *
     * @return array
     */
    public static function collection()
    {
        if (self::$collection) {
            $arOptions = self::$collection;
        } else {
            // name - always IPOL_FIVEPOST_OPT_<code>
            $arOptions = array(
                // auth
                'apikey' => array(
                    //'group' => 'auth',
                    'hasHint' => 'N',
                    'default' => '',
                    'type' => 'text',
                    'required' => true
                ),
                // common
                'termIncrease' => array(
                    'group' => 'common',
                    'hasHint' => 'Y',
                    'default' => '0',
                    'type' => "text"//array('text',2)
                ),
                'showInOrders' => array(
                    'group' => 'common',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type' => 'selectbox'
                ),
                'brandName' => array(
                    'group' => 'common',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type' => 'text'
                ),
                'undeliverableOption' => array(
                    'group' => 'common',
                    'hasHint' => 'N',
                    'default' => 'RETURN',
                    'type' => 'selectbox'
                ),
                // defaultGabarites
                'lengthD' => array(
                    'group'   => 'defaultGabarites',
                    'hasHint' => 'N',
                    'default' => '400',
                    'type'    => "text"//array("text",6)
                ),
                'widthD' => array(
                    'group'   => 'defaultGabarites',
                    'hasHint' => 'N',
                    'default' => '300',
                    'type'    => "text"//array("text",6)
                ),
                'heightD' => array(
                    'group'   => 'defaultGabarites',
                    'hasHint' => 'N',
                    'default' => '200',
                    'type'    => "text"//array("text",6)
                ),
                'weightD' => array(
                    'group'   => 'defaultGabarites',
                    'hasHint' => 'N',
                    'default' => '1000',
                    'type'    => "text"//array("text",6)
                ),
                'defMode' => array(
                    'group'   => 'defaultGabarites',
                    'hasHint' => 'N',
                    'default' => 'O',
                    'type'    => 'selectbox'
                ),
                // orderProps
                'fullName' => array(
                    'group'   => 'orderProps',
                    'hasHint' => 'N',
                    'default' => 'FIO',
                    'type'    => 'text'
                ),
                'email' => array(
                    'group'   => 'orderProps',
                    'hasHint' => 'N',
                    'default' => 'EMAIL',
                    'type'    => 'text'
                ),
                'phone' => array(
                    'group'   => 'orderProps',
                    'hasHint' => 'Y',
                    'default' => 'PHONE',
                    'type'    => 'text'
                ),
                // goodprops
                'articul' => array(
                    'group'   => 'goodprops',
                    'hasHint' => 'N',
                    'default' => 'ARTNUMBER',
                    'type'    => 'text'
                ),
                'barcode' => array(
                    'group'   => 'goodprops',
                    'hasHint' => 'N',
                    'default' => 'BARCODE',
                    'type'    => 'text'
                ),
                'ndsUseCatalog' => array(
                    'group'   => 'goodprops',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),
                'ndsDefault' => array(
                    'group'   => 'goodprops',
                    'hasHint' => 'N',
                    'default' => '20',
                    'type'    => 'selectbox'
                ),

                // statuses
                'status_sended' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_valid' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_rejected' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_warehouse' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_inpostamat' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_interrupted' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_lost' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_reclaim' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_repickup' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_unclaimed' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_done' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_canceled' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'addTracking' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'checkbox'
                ),
                'markPayed' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type'    => 'checkbox'
                ),
                // delivery
                'basicTarif' => array(
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => '3',
                    'type'    => 'text'
                ),
                'overweight' => array(
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => '1',
                    'type'    => 'text'
                ),
                'noPVZnoOrder' => array(
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox'
                ),

                // barcodes
                'barkGenerateByServer' => array(
                    'group'   => 'barcodes',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox'
                ),
                'barkID' => array(
                    'group'    => 'barcodes',
                    'hasHint'  => 'Y',
                    'default'  => '',
                    'type'     => 'text',
                    'required' => true
                ),
                'barkCompany' => array(
                    'group'    => 'barcodes',
                    'hasHint'  => 'Y',
                    'default'  => '',
                    'type'     => 'text',
                    'required' => true
                ),
                'barkLogo' => array(
                    'group'   => 'barcodes',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type'    => 'image'
                ),
                'barkCounter' => array(
                    'group'   => 'barcodes',
                    'hasHint' => 'Y',
                    'default' => 0,
                    'type'    => 'sign',
                ),
                // widjet
                'pvzID' => array(
                    'group' => 'widjet',
                    'hasHint' => '',
                    'default' => '',
                    'type'    => 'text',
                ),
                'pvzPicker' => array(
                    'group' => 'widjet',
                    'hasHint' => 'Y',
                    'default' => 'ADDRESS',
                    'type'    => 'text',
                ),
                'pvzLabel' => array(
                    'group' => 'widjet',
                    'hasHint' => '',
                    'default' => '',
                    'type'    => 'text',
                ),
                'ymapsAPIKey' => array(
                    'group' => 'widjet',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type'    => 'text',
                ),
                'noYmaps' => array(
                    'group'   => 'widjet',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ),
                'widgetSearch' => array(
                    'group'   => 'widjet',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ),
                'widgetSearchMark' => array(
                    'group'   => 'widjet',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ),
                // payments
                'payNal' => array(
                    'group'   => 'payments',
                    'hasHint' => 'N',
                    'default' => 'N',
                    'multiple' => true,
                    'type'    => 'selectbox',
                ),
                'payCard' => array(
                    'group'   => 'payments',
                    'hasHint' => 'N',
                    'default' => 'N',
                    'multiple' => true,
                    'type'    => 'selectbox',
                ),
                'paySystemDefaultType' => array(
                    'group'   => 'payments',
                    'hasHint' => 'Y',
                    'default' => 'BILL',
                    'type'    => 'selectbox',
                ),
                'checkPayed' => array(
                    'group'   => 'payments',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ),

                // service
                'timeout' => array(
                    'group' => 'service',
                    'hasHint' => 'Y',
                    'default' => '6',
                    'type' => 'text'
                ),
                'debug' => array(
                    'group' => 'service',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type' => 'checkbox'
                ),

                // Default VAT rate for delivery handler profiles
                'desired_vat_rate' => array(
                    'group' => 'service',
                    'hasHint' => 'N',
                    'default' => 20,
                    'type' => 'text'
                ),

                // Sync data
                'sync_data_lastdate' => array(
                    'group' => 'service',
                    'hasHint' => 'N',
                    'default' => false,
                    'type' => 'special'
                ),
                'sync_data_step' => array(
                    'group' => 'service',
                    'hasHint' => 'N',
                    'default' => 'SYNC_REFRESH_DATA',
                    'type' => 'sign'
                ),
                'sync_data_pagesize' => array(
                    'group' => 'service',
                    'hasHint' => 'N',
                    'default' => 1000,
                    'type' => 'text'
                ),
                'sync_data_pagenumber' => array(
                    'group' => 'service',
                    'hasHint' => 'N',
                    'default' => 0,
                    'type' => 'sign'
                ),
                'sync_data_completed' => array(
                    'group' => 'service',
                    'hasHint' => 'N',
                    'default' => 'N',
                    'type' => 'checkbox'
                ),

                // logging
                'debug_fileMode' => array(
                    'group'   => 'logging',
                    'hasHint' => 'Y',
                    'default' => 'w',
                    'type'    => 'selectbox'
                ),
                /*'debug_calculationFP' => array(
                    'group'   => 'logging',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),
                'debug_calculationNoWJ' => array(
                    'group'   => 'logging',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),
                'debug_compability' => array(
                    'group'   => 'logging',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),
                'debug_calculate' => array(
                    'group'   => 'logging',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),*/
                'debug_order' => array(
                    'group'   => 'logging',
                    'hasHint' => 'N',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),
                'debug_warehouses' => array(
                    'group'   => 'logging',
                    'hasHint' => 'N',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),
                'debug_status' => array(
                    'group'   => 'logging',
                    'hasHint' => 'N',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),
                // other options : isTest
            );

            self::$collection = $arOptions;
        }

        return $arOptions;
    }

    /**
     * Get option data from collection
     * @param $code
     * @return false|mixed
     */
    public static function getColOption($code)
    {
        $arCol = self::collection();

        if (array_key_exists($code, $arCol)) {
            return $arCol[$code];
        } else {
            return false;
        }
    }

    /**
     * Validate option value for given option
     * @param $code
     * @param $val
     * @return BasicResponse|mixed
     */
    public static function validate($code, $val)
    {
        $result = new BasicResponse();

        $checker = self::getValidator($code);

        if ($checker) {
            $result = $checker($val);
        }

        $optDescr = self::getColOption($code);

        if (array_key_exists('required', $optDescr) && $optDescr['required'] && !$val) {
            $result->setSuccess(false)->setErrorText(\Ipol\Fivepost\Bitrix\Tools::getMessage('ERROR_OPTSAVE_UNGIVEN'));
        }

        return $result;
    }

    /**
     * Return validator function for given option
     * @param $code
     * @return \Closure|false
     */
    public static function getValidator($code)
    {
        $checker = false;
        switch ($code) {
//            case 'key' : $checker = function($val){
//                $result = new BasicResponse();
//                if (strlen($val) < 5) {
//                    $result->setSuccess(false)->setErrorText('Too short emae');
//                }
//                return $result;
//            };
//            break;
            case 'barkID':
                $checker = function($val) {
                    $result = new BasicResponse();
                    if (strlen($val) != 4) {
                        $result->setSuccess(false)->setErrorText(Tools::getMessage('LBL_ERR_barkID'));
                    }
                    return $result;
                };
                break;
        }

        return $checker;
    }

    /**
     * Get values for type 'selectbox' options
     * @param $code
     * @return array|bool
     */
    public static function getSelectVals($code)
    {
        $arVals = false;

        switch ($code) {
            case 'showInOrders':
                $arVals = array("Y" => Tools::getMessage("LBL_ALWAYS"), "N" => Tools::getMessage("LBL_ONLYMODULE"));
                break;
            case 'defMode':
                $arVals = array("O" => Tools::getMessage("LBL_defModeO"), "G" => Tools::getMessage("LBL_defModeG"));
                break;
            case 'undeliverableOption':
                $arVals = Adapter::getUO();
                break;

            case 'debug_fileMode':
                $arVals = array("w" => Tools::getMessage("LBL_FILEMODE_W"), "a" => Tools::getMessage("LBL_FILEMODE_A"));
                break;

            case 'payNal':
            case 'payCard':
                if (array_key_exists('paysystems',self::$ABYSS)) {
                    $arVals = self::$ABYSS['paysystems'];
                } else {
                    $arVals = PaySystems::getAll();
                    self::$ABYSS['paysystems'] = $arVals;
                }
                break;
            case 'paySystemDefaultType':
                $arVals = array(
                    'CASH' => Tools::getMessage("LBL_PAYMENT_CASH"),
                    'CARD' => Tools::getMessage("LBL_PAYMENT_CARD"),
                    'BILL' => Tools::getMessage("LBL_PAYMENT_BILL")
                );
                break;

            case 'status_sended':
            case 'status_valid':
            case 'status_rejected':
            case 'status_warehouse':
            case 'status_inpostamat':
            case 'status_interrupted':
            case 'status_lost':
            case 'status_reclaim':
            case 'status_repickup':
            case 'status_unclaimed':
            case 'status_done':
            case 'status_canceled':
                if (array_key_exists('statuses', self::$ABYSS)) {
                    $arVals = self::$ABYSS['statuses'];
                } else {
                    $arVals = array(0 => '');
                    $arVals = array_merge($arVals, Statuses::getOrderStatuses());
                    self::$ABYSS['statuses'] = $arVals;
                }
                break;

            case 'ndsDefault':
                $arVals = array('0' => Tools::getMessage('LBL_NONDS'), '10' => '10%', '20' => '20%');
                break;
        }

        return $arVals;
    }
}