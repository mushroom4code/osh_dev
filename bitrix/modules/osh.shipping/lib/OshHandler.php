<?

namespace Osh\Delivery;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Loader,
    Bitrix\Currency,
    Bitrix\Sale\Shipment,
    Bitrix\Sale\Internals\StatusTable,
    Bitrix\Main\Page\Asset,
    Bitrix\Sale\Delivery\Services\Manager,
    Osh\Delivery\Options\Config;

Loader::IncludeModule("sale");
Loader::IncludeModule("osh.shipping");

Loc::loadMessages(__FILE__);

class OshHandler extends \Bitrix\Sale\Delivery\Services\Base
{
    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = false;
    protected static $canHasProfiles = true;
    protected static $isProfile = false;

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
        $this->setDefaultLogo();
    }

    private function setDefaultLogo()
    {
        $fileId = \COshDeliveryHelper::getLogoId();
        if (!$this->logotip && $fileId) {
            $this->logotip = $fileId;
        }
    }

    public static function getClassTitle()
    {
        return Loc::getMessage("OSH_DLVR_HANDL_AUT_NAME");
    }

    public static function getClassDescription()
    {
        return Loc::getMessage("OSH_DLVR_HANDL_AUT_DESCRIPTION");
    }

    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }

    public static function getDefaultConfigValues($isDirect = null)
    {
        return array(
            'MAIN' => array(
                'MARGIN_VALUE' => 0,
                'MARGIN_TYPE' => '%',
                'ADD_TERMS' => 0,
                'SHOW_TERMS' => 'Y',
                'CALC_ALGORITM' => self::CALC_ALGO_PACK,
                'LENGTH_VALUE' => 30,
                'WIDTH_VALUE' => 20,
                'HEIGHT_VALUE' => 10,
                'WEIGHT_VALUE' => 3
            ),
            'COD' => array(
                'CALCULATION_TYPE' => $isDirect ? self::COD_NEVER : self::COD_ALWAYS,
                'INCLUDE_COD' => 'Y',
                'COST_DECLARING' => 'Y'
            )
        );
    }

    protected function getConfigStructure()
    {
        return array();
    }

    protected function calculateConcrete(Shipment $shipment = null)
    {
        throw new \Bitrix\Main\SystemException(Loc::getMessage("OSH_DLVR_HANDL_PROFILES_CALCULATE"));
    }

    public static function canHasProfiles()
    {
        return self::$canHasProfiles;
    }

    public static function getChildrenClassNames()
    {
        return ['\Osh\Delivery\ProfileHandler'];
    }

    public function getProfilesList()
    {
        $arProfiles = array();
        foreach ($this->getAvailableProfiles() as $groupId => $profile) {
            $arProfiles[$groupId] = $profile['name'];
        }
        return $arProfiles;
    }

    public function getAvailableProfiles()
    {
        $arProfiles = array();
        try {
            $arAvailableProfiles = \COshDeliveryHelper::getShippingMethods();
            foreach ($arAvailableProfiles as $arItem) {
                if ($arItem['group'] == 'aramex_courier') {
                    continue;
                }
                $additionByGroup = Loc::getMessage('OSH_DLVR_HANDL_GROUP_ADDITION_' . $arItem['group']);
                $arItem['name'] .= (' ' . $additionByGroup ?: '');
                $arProfiles[$arItem["group"]] = $arItem;
            }
        } catch (\Exception $e) {
//            Logger::force($e->getMessage());
        }
        return $arProfiles;
    }

    public function calculateMargin($price)
    {
        if ($this->config["MAIN"]["MARGIN_VALUE"] > 0) {
            switch ($this->config["MAIN"]["MARGIN_TYPE"]) {
                case self::MARGIN_PERCENT:
                default:
                    $price *= 1 + ($this->config["MAIN"]["MARGIN_VALUE"]) / 100;
                    break;
                case self::MARGIN_CURRENCY:
                    $price += $this->config["MAIN"]["MARGIN_VALUE"];
                    break;
            }
        }
        $result = array("PRICE" => $this->round($price));
        return $result;
    }

    public function round($price)
    {
        $precision = Config::getRoundingPrecision();
        switch (Config::getRoundingType()) {
            case Config::ROUND_TYPE_MATH:
                $price = round($price / $precision) * $precision;
                break;
            case Config::ROUND_TYPE_FLOOR:
                $price = floor($price / $precision) * $precision;
                break;
            case Config::ROUND_TYPE_CEIL:
                $price = ceil($price / $precision) * $precision;
                break;
            case Config::ROUND_TYPE_NONE:
            default:
                $price = roundEx($price, SALE_VALUE_PRECISION);
        }
        return $price;
    }

    public function getTerms($daysText)
    {
        $strDescription = "";
        if ($this->config['MAIN']['SHOW_TERMS'] == "Y" && !empty($daysText)) {
            $iAddTerms = intval($this->config['MAIN']['ADD_TERMS']);
            if ($iAddTerms > 0) {
                $strDescription .= $this->addDaysText($daysText, $iAddTerms);
            } else {
                $strDescription .= $this->addDaysText($daysText, 0);
            }
        }
        return $strDescription;
    }

    private function addDaysText($daysText, $i)
    {
        if (strpos($daysText, "-") !== false) {
            $periods = explode("-", $daysText);
            $dStart = intval($periods[0]) + $i;
            $dEnd = intval($periods[1]) + $i;
            $daysText = $dStart . "-" . $this->getPluralEnumDays($dEnd);
        } else {
            $dStart = intval($daysText) + $i;
            $daysText = $this->getPluralEnumDays($dStart);
        }
        return $daysText;
    }

    private function getPluralEnumDays($number)
    {
        $labels = explode("|", Loc::getMessage("OSH_WDAYS_PLURALS"));
        $variant = array(2, 0, 1, 1, 1, 2);
        return $number . " " . $labels[($number % 100 > 4 && $number % 100 < 20) ? 2 : $variant[min($number % 10, 5)]];
    }

    public function getConfigOuter()
    {
        return $this->config;
    }

    public static function createAllProfiles($parentId, $currency, $onlyDirect = null, $forceActive = false)
    {
        $arAvailableProfilesList = self::getAvailableProfiles();
        if (empty($arAvailableProfilesList)) {
            return false;
        }
        foreach ($arAvailableProfilesList as $group => $profile) {
            $isDirectProfile = (bool)(strpos($profile['category'], '-to-') !== false);
            if (($onlyDirect === true && (!$isDirectProfile || $profile['courier'] == 'osh-international'))
                || ($onlyDirect === false && ($isDirectProfile || $profile['courier'] == 'osh-international'))) {
                continue;
            }
            $arConfig = array(
                'MAIN' => array(
                    'CATEGORY' => $profile['category'],
                    'COURIER' => $profile['courier'],
                    'GROUP' => $group,
                    'NAME' => $profile['name']
                )
            );
            if ($isDirectProfile) {
                $arConfig['DIRECT']['LOCATION'] = Option::get('sale', 'location');
                $arConfig['DIRECT']['RECIEVER_TYPE'] = 'SET';
                $arConfig['DIRECT']['ADDRESS_TYPE'] = 'SET';
                $arConfig['DIRECT']['DATE_TIME_TYPE'] = 'SET';
            }
            $res = Manager::add(array(
                    'CODE' => '',
                    'PARENT_ID' => $parentId,
                    'NAME' => $profile['name'],
                    'ACTIVE' => $forceActive ? 'Y' : 'N',
                    'SORT' => 100,
                    'DESCRIPTION' => \Osh\Delivery\ProfileHandler::getClassDescription()/*$profile["description"]*/,
                    'CLASS_NAME' => '\Osh\Delivery\ProfileHandler',
                    'CURRENCY' => $currency,
                    'LOGOTIP' => \COshDeliveryHelper::getDefaultLogo($profile['courier']),
                    'CONFIG' => $arConfig
                )
            );
            if (!$res->isSuccess()) {
                return false;
            }
        }
        return true;
    }

    public static function createStockProfiles($parentId, $currency)
    {
        try {
            self::createAllProfiles($parentId, $currency, false);
            $result = true;
        } catch (\Exception $ex) {
            $result = false;
        }
        return $result;
    }

    public static function createDirectProfiles($parentId, $currency)
    {
        try {
            self::createAllProfiles($parentId, $currency, true);
            $result = true;
        } catch (\Exception $ex) {
            $result = false;
        }
        return $result;
    }

    const MODULE_ID = 'osh.shipping';
    const MARGIN_PERCENT = 'PERCENT';
    const MARGIN_CURRENCY = 'CURRENCY';
    const COD_ALWAYS = 'always';
    const COD_CERTAIN = 'coded';
    const COD_NEVER = 'never';
    const CALC_ALGO_PACK = 'N';
    const CALC_ALGO_WARE = 'Y';
    const CALC_ALGO_WEIGHT = 'W';
}