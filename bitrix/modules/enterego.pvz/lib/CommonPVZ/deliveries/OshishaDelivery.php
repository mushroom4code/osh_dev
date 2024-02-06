<?php

namespace CommonPVZ;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Internals\PersonTypeTable;
use Bitrix\Sale\Location\Connector;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\Util\Assert;


class OshishaDelivery extends CommonPVZ
{
    static $MODULE_ID = 'enterego.pvz';
    public string $delivery_name = 'Oshisha';
    public string $delivery_code = 'oshisha';

    static public string $code = 'oshisha';
    private $oshisha_cache_id = 'oshisha_delivery_prices';

    private static $_current_region = null;

    static $oshisha_fields = array(
        'active',
        'pvzStrict',
        'address_type',
        'ymaps_key',
        'da_data_token',
        'timeDeliveryEndNight',
        'timeDeliveryStartNight',
        'timeDeliveryStartDay',
        'timeDeliveryEndDay',
        'cost',
    );

    private static function getLocationConnectorsForDoorDelivery() {
        //todo site  id
        if (SITE_ID === 'N2') {
            //moscow
            //moscow and moscow region
            $locationConnectors = ["0000028025","0000073738"];

        } elseif (SITE_ID === 'IA') {
            $locationConnectors = ["0000028038"];
        } else {
            //ryzan
            //ryazan region and moscow region
            $locationConnectors = ["0000028033", "0000028025"];
        }

        return $locationConnectors;
    }

    private static function getLocationConnectorsForPVZ() {
        //todo site  id
        if (SITE_ID === 'N2') {
            //moscow and moscow region
            $locationConnectors = ["0000073738"];
        }  elseif (SITE_ID === 'IA') {
            $locationConnectors = ["0000028038"];
        }else {
            //ryazan city
            $locationConnectors = ["0000197740"];
        }

        return $locationConnectors;
    }

    /**
     * validate location delivery on moscow or moscow region
     *
     * @param $locationCode string code location for delivery
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function checkDeliveryLocation(string $locationCode, array $locationConnectors): bool
    {
        $node = LocationTable::getList(
            array(
                'filter' => array('=CODE' => $locationCode),
                'select' => array('ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'),
                'limit' => 1
            )
        )->fetch();
        if (empty($node)) {
            return  false;
        }

        $node = Assert::expectNotEmptyArray($node, '$nodeInfo[]');
        $node['ID'] = Assert::expectIntegerPositive($node['ID'], '$nodeInfo[][ID]');
        $node['LEFT_MARGIN'] = Assert::expectIntegerNonNegative($node['LEFT_MARGIN'], '$nodeInfo[][LEFT_MARGIN]');
        $node['RIGHT_MARGIN'] = Assert::expectIntegerPositive($node['RIGHT_MARGIN'], '$nodeInfo[][RIGHT_MARGIN]');

        $connectors = LocationTable::getList(
            array(
                'filter' => array('=CODE' => $locationConnectors),
                'select' => array('ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'),
            )
        );
        while ($connector = $connectors->fetch()) {
            if($connector['ID'] === $node['ID']) {
                return true;
            } elseif($node['LEFT_MARGIN'] >= $connector['LEFT_MARGIN']
                && $node['RIGHT_MARGIN'] <= $connector['RIGHT_MARGIN']) {

                return true;
            }
        }

        return false;
    }

    public static function getInstanceForDoor($deliveryParams): array
    {
        if (empty(Option::get(DeliveryHelper::$MODULE_ID, 'Oshisha_door_active'))) {
            return [];
        }

        $res = self::checkDeliveryLocation($deliveryParams['location'] ?? '',
            self::getLocationConnectorsForDoorDelivery());

        if ($res) {
            return [new OshishaDelivery()];
        }
        return [];
    }

    public static function getInstanceForPvz($deliveryParams): array
    {
        if (empty(Option::get(DeliveryHelper::$MODULE_ID, 'Oshisha_pvz_active'))) {
            return [];
        }

        $res = self::checkDeliveryLocation($deliveryParams['codeCity'],
            self::getLocationConnectorsForPVZ());

        if ($res) {
            return [new OshishaDelivery()];
        }
        return [];
    }

    public static function getOshishaDataValue($name)
    {
        return Option::get(self::$MODULE_ID, "Oshisha_{$name}");
    }

    public static function getOshishaYMapsKey()
    {
        return self::getOshishaDataValue('ymapskey');
    }

    public static function getOshishaDaDataToken()
    {
        return self::getOshishaDataValue('dadatatoken');
    }

    public static function getOshishaDaDataSecret()
    {
        return self::getOshishaDataValue('dadata_secret');
    }

    public static function getOshishaCost()
    {
        return self::getOshishaDataValue('cost');
    }

    public static function getOshishaStartCost()
    {
        return 299;
    }

    public static function getOshishaLimitBasket(){
        return 4000;
    }

    public function getNoMarkupDays() {
        //todo site id and settings
        if (SITE_ID === 'RZ') {
            return [];
        }

        return [
            ['days' =>explode(',', $this->configs['northdays']),     'color' => 'rgba(154, 251, 0, 0.5)' ],
            ['days' =>explode(',', $this->configs['southeastdays']), 'color' => 'rgba(0, 195, 250, 0.5)' ],
            ['days' =>explode(',', $this->configs['southwestdays']), 'color' => 'rgba(245, 20, 39, 0.5)' ],
        ];
    }

    /** Получает области и способы расчета в разрее филиалов
     * @return array
     */
    public function getOshishaDeliveryRegions(): array
    {
        if (self::$_current_region !== null) {
            return self::$_current_region;
        }

        $regionsContent = file_get_contents(__DIR__ . "/../../../data/regions.json");
        $regions = json_decode($regionsContent);

        //todo site id and settings
        if (SITE_ID === 'RZ') {
            $regionName = 'ryzan';
            self::$_current_region = [
                'center_point' => $regions->{$regionName}->center_point,
                'locations' => [['region'=> "Московская"], ['region'=> "Рязанская"]],
                'regions' => [
                    [
                        'name' => $regionName,
                        'calculation' => [
                            'type' => 'static',
                            'cost' => 0
                        ],
                        'template' => '{delivery_address} - {delivery_price} руб.',
                        'delivery_intervals' => $regions->{$regionName}->delivery_intervals,
                        'points' => $regions->{$regionName}->points,
                        'property' => [
                            'fillColor' => 'rgba(255,94,89,0.12)',
                            'strokeColo r' => 'rgba(255,94,89,0.22)',
                            'opacity' => 1,
                            'strokeWidt h' => 0.1,
                            'zIndex' => -999,
                            'zIndexActive' => -999,
                        ]
                    ]
                ]
            ];
        } elseif (SITE_ID === 'IA') {
            $regionName = 'yaroslavl';
            self::$_current_region = [
                'center_point' => $regions->{$regionName}->center_point,
                'locations' => [['region'=> "Ярославская"]],
                'regions' => [
                    [
                        'name' => $regionName,
                        'calculation' => [
                            'type' => 'static',
                            'cost' => 0
                        ],
                        'template' => '{delivery_address} - {delivery_price} руб.',
                        'delivery_intervals' => $regions->{$regionName}->delivery_intervals,
                        'points' => $regions->{$regionName}->points,
                        'property' => [
                            'fillColor' => 'rgba(255,94,89,0.12)',
                            'strokeColo r' => 'rgba(255,94,89,0.22)',
                            'opacity' => 1,
                            'strokeWidt h' => 0.1,
                            'zIndex' => -999,
                            'zIndexActive' => -999,
                        ]
                    ]
                ]
            ];
        } else {
            $regionName = 'MKAD';
            self::$_current_region = [
                'center_point' => $regions->{$regionName}->center_point,
                'locations' => [['region'=> "Московская"], ['region'=> "Москва"]],
                'regions' => [
                    [
                        'name' => 'MKAD',
                        'calculation' => [
                            'type' => 'static',
                            'cost' => OshishaDelivery::getOshishaStartCost(),
                        ],
                        'template' => 'В пределах МКАД - {delivery_price} руб.',
                        'delivery_intervals' => $regions->{$regionName}->delivery_intervals,
                        'points' => $regions->{$regionName}->points,
                        'property' => [
                            'fillColor' => 'rgba(255,94,89,0.12)',
                            'strokeColor' => 'rgba(255,94,89,0.22)',
                            'opacity' => 1,
                            'strokeWidth' => 0.1,
                            'zIndex' => -999,
                            'zIndexActive' => -999,
                        ]
                    ],
                    [
                        'name' => 'SOUTHWEST',
                        'calculation' => [
                            'type' => 'path',
                            'cost' => OshishaDelivery::getOshishaStartCost(),
                            'costKm' => OshishaDelivery::getOshishaCost()
                        ],
                        'no_markup_days' => explode(',', $this->configs['southwestdays']),
                        'delivery_intervals' => $regions->SOUTHWEST->delivery_intervals,
                        'points' => $regions->SOUTHWEST->points,
                        'property' => [
                            'fillColor' => 'rgba(245, 20, 39, 0.15)',
                            'strokeColor' => 'rgba(245,20,39,0.2)',
                            'opacity' => 1,
                            'strokeWidth' => 0.1,
                            'zIndex' => -999,
                            'zIndexActive' => -999,
                            'cursor' => 'none',
                        ]
                    ],
                    [
                        'name' => 'SOUTHEAST',
                        'calculation' => [
                            'type' => 'path',
                            'cost' => OshishaDelivery::getOshishaStartCost(),
                            'costKm' => OshishaDelivery::getOshishaCost()
                        ],
                        'no_markup_days' => explode(',', $this->configs['southeastdays']),
                        'delivery_intervals' => $regions->SOUTHEAST->delivery_intervals,
                        'points' => $regions->SOUTHEAST->points,
                        'property' => [
                            'fillColor' => 'rgba(0, 195, 250, 0.11)',
                            'strokeColor' => 'rgba(0,195,250,0.22)',
                            'opacity' => 1,
                            'strokeWidth' => 0.1,
                            'zIndex' => -999,
                            'zIndexActive' => -999,
                            'cursor' => 'none',
                        ]
                    ],
                    [
                        'name' => 'NORTH',
                        'calculation' => [
                            'type' => 'path',
                            'cost' => OshishaDelivery::getOshishaStartCost(),
                            'costKm' => OshishaDelivery::getOshishaCost()
                        ],
                        'no_markup_days' => explode(',', $this->configs['northdays']),
                        'delivery_intervals' => $regions->NORTH->delivery_intervals,
                        'points' => $regions->NORTH->points,
                        'property' => [
                            // цвет заливки.
                            'fillColor' => 'rgba(154, 251, 0, 0.11)',
                            // цвет обводки.
                            'strokeColor' => 'rgba(154,251,0,0.22)',
                            // Прозрачность.
                            'opacity' => 1,
                            // ширина обводки.
                            'strokeWidth' => 0.1,
                            'zIndex' => -999,
                            'zIndexActive' => -999,
                            'cursor' => 'none',
                        ]
                    ]
                ]
            ];
        }

        return self::$_current_region;
    }

    public static function getOshishaPersonTypes(){
        $arSelectPt = array("ID","NAME_WS");
        $arOrderPt = array("SORT" => "ASC");
        $arFilter = array("ACTIVE" => "Y");
        $arRuntime = array(new Entity\ExpressionField('NAME_WS', "CONCAT('(',%s,') ',%s)",array("LID","NAME")));
        $dbPersonTypes = PersonTypeTable::getList(array("select" => $arSelectPt, "order" => $arOrderPt,
            "filter" => $arFilter, "runtime" => $arRuntime));
        $arPersonTypes = array();
        while($arPersonType = $dbPersonTypes->fetch()){
            $arPersonTypes[$arPersonType["ID"]] = array("NAME" => $arPersonType["NAME_WS"]);
        }
        return $arPersonTypes;
    }

    public static function getOshishaOptionsData()
    {
        $data = array();
    }

    public static function generate($arOptions, $arConfigData)
    {
        foreach ($arOptions as $optionName => $arOption):?>
            <tr>
            <?php if ($arOption['type'] === 'news') { ?>
                <tr class="heading" dataId="<?= $arOption["id"] ?>">
                    <td colspan="2">
                        <?= $arOption["name"] ?>
                    </td>
                </tr>
            <?php } ?>
            <? if ($arOption["type"] == "NOTE"): ?>
                <td colspan="2" align="center">
                    <?= BeginNote(); ?>
                    <img src="/bitrix/js/main/core/images/hint.gif" style="margin-right: 5px;"/><?= $arOption["name"] ?>
                    <?= EndNote(); ?>
                </td>
            <? elseif ($arOption["type"] == "heading"): ?>
                <td colspan="2" class="heading">
                    <?= $arOption["name"] ?>
                </td>
            <?
            else:
                $value = isset($arConfigData[$optionName]) ? $arConfigData[$optionName] : $arOption["default"];

                if ($arOption["type"] !== 'news') {
                    ?>
                    <td class="field-name" style="width:45%"><?= $arOption["name"] ?>:</td>
                <?php } else { ?><td class="field-name" style="width:45%"></td><?php } ?>
                <td>
                    <? switch ($arOption["type"]):
                        case "text":
                        case "email":
                        case "number";
                            $arOption["name"] = $optionName;
                            $arOption["value"] = $value;
                            echo self::input($arOption, '');
                            break;
                        case "textarea":
                            $arParams = array(
                                "ATTRS" => array(
                                    "name" => $optionName
                                ),
                                "VALUE" => $value
                            );
                            echo self::pairTag($arOption["type"], $arParams);
                            break;
                        case "news":
                            $elem = '';
                            $id = $arOption["id"];
                            if ($id === 'dayDelivery') {
                                $start_json = Option::get(DeliveryHelper::$MODULE_ID, 'osh_timeDeliveryStartDay');
                                $end_json = Option::get(DeliveryHelper::$MODULE_ID, 'osh_timeDeliveryEndDay');
                            } else {
                                $start_json = Option::get(DeliveryHelper::$MODULE_ID, 'osh_timeDeliveryStartNight');
                                $end_json = Option::get(DeliveryHelper::$MODULE_ID, 'osh_timeDeliveryEndNight');
                            }

                            $start = json_decode($start_json);
                            $end = json_decode($end_json);

                            $delete = "<a href='javascript:void(0)' class='flex-align-items' 
                                        onClick='settingsDeleteRow(this)'> 
                                       <img src='/bitrix/themes/.default/images/actions/delete_button.gif' 
                                        border='0' width='20' height='20'/></a>";

                            if (empty($start) || count($start) == 0) {
                                $start[] = '';
                                $end[] = '';
                            }
                            $dayItem = $arOption["elems"][0];
                            $nightItem = $arOption["elems"][1];

                            foreach ($start as $key => $elems_start) {

                                $elem .= "<div class='flex-row d-flex padding-10'>";
                                $dayItem['value'] = $elems_start;
                                $elem .= self::input($dayItem, 'elems');

                                $nightItem['value'] = $end[$key];
                                $elem .= self::input($nightItem, 'elems');

                                $elem .= $delete;
                                $elem .= "</div>";
                            }
                            $dop_insert = "<a href='javascript:void(0)' onclick='settingsAddRights(this)' 
                                    hidefocus='true' class='button_red' dataId='$id'>Добавить поле </a>";

                            echo "<div class='flex-justify-content flex-column'><div data-type='$id' id='$id'>$elem</div>
                              <div><div class='margin-left-10'>$dop_insert</div> 
                              </div></div>";


                            break;
                        case "select":
                            if ($arOption["multiple"]) {
                                $values = explode("|", $value);
                            } else {
                                $values = [$value];
                            }
                            ?>

                            <select name="<?= $optionName ?><? if ($arOption["multiple"]): ?>[]<? endif ?>"
                                <?php if ($arOption["multiple"]): ?>
                                    multiple
                                    <?php if ($arOption["size"]): ?>
                                        size="<?php echo $arOption["size"] ?>"
                                    <? endif ?>
                                <? endif ?>
                                    <? if ($arOption["onchange"]): ?>onChange="<?= $arOption["onchange"] ?>"<? endif ?>
                            >
                                <? foreach ($arOption["options"] as $id => $text): ?>
                                    <option <? if (in_array($id, $values)) echo " selected " ?>
                                        value="<?= $id ?>"><?= $text ?></option>
                                <? endforeach ?>

                            </select>
                            <? break;
                    endswitch ?>
                    <? if (!empty($arOption["hint"])): ?>
                        <img src="/bitrix/js/main/core/images/hint.gif" style="margin-right: 5px;cursor:pointer"
                             title="<?= $arOption["hint"] ?>"
                            <? if (!empty($arOption["href"])): ?>
                                onclick="window.open('<?= $arOption["href"] ?>');"
                            <? endif ?>
                        />
                    <? endif ?>
                    <? if (!empty($arOption["link"])): ?>
                        <a href="<?= $arOption["link"]['href'] ?>" target="blank"
                           style="padding-left: 10px;"><?= $arOption["link"]['text'] ?></a>
                    <? endif ?>
                </td>
            <? endif ?>

            </tr><?
        endforeach;
    }

    static function input($arParams, string $param)
    {
        if ($param === 'elems') {
            return self::singleTag("input", $arParams);
        } else {
            return self::generateHtml("input", $arParams);
        }
    }

    public
    static function singleTag($tag, $tagParams)
    {
        $params = array();
        foreach ($tagParams as $name => $value) {
            $params[] = $name . '="' . $value . '"';
        }

        return "<$tag " . implode(" ", $params) . " class='margin-right-20' />";
    }

    public
    static function generateHtml($tagName, $tagParams)
    {
        $params = array();
        foreach ($tagParams as $name => $value) {
            $params[] = $name . '="' . $value . '"';
        }

        return "<{$tagName} " . implode(" ", $params) . " />";
    }

    protected function connect()
    {

    }

    /**
     * @param string $city_name
     * @param array $result_array
     * @param array $id_feature
     * @param string $code_city
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city, array $packages, $dimensionsHash, $sumDimensions)
    {
        $params = ['filter' => [
            'ACTIVE' => 'Y',
            'ISSUING_CENTER' => 'Y',
            '!=GPS_N' => '0',
            '!=GPS_S' => '0',
            'SITE_ID' => SITE_ID]];
        $rsRes = StoreTable::getList($params);

        while ($arStore = $rsRes->fetch()) {
            $features_obj = [];
            $features_obj['type'] = 'Feature';
            $features_obj['id'] = $id_feature;
            $id_feature += 1;
            $features_obj['geometry'] = [
                'type' => 'Point',
                'coordinates' => [
                    $arStore['GPS_N'],
                    $arStore['GPS_S']
                ]
            ];

            $features_obj['properties'] = [
                'code_pvz' => $arStore['ID'],
                'type' => 'PVZ',
                'fullAddress' => $arStore['ADDRESS'],
                'comment' => $arStore['DESCRIPTION'],
                'deliveryName' => $this::$code,
                'iconCaption' => 'OSHISHA',
                'hintContent' => $arStore['ADDRESS'],
                'openEmptyBalloon' => true,
                'clusterCaption' => 'OSHISHA',
            ];

            $features_obj['options'] = [
                'iconImageSize' => [64, 64],
                'iconImageOffset' => [-30, -60],
                'iconLayout'=> 'default#imageWithContent',
                'iconImageHref'=> '/bitrix/modules/enterego.pvz/assets/images/osh.png',
            ];

            $result_array[] = $features_obj;
        }
    }

    public function getPrice($array)
    {
        return 0;
    }

    /** Рассчитывает следующий день без доставки
     * @param $noMarkUpDays
     * @return string
     */
    public function getNextNoMarkupDate($noMarkUpDays): string
    {
        $tempDate = '';
        foreach ($noMarkUpDays as $noMarkupDay) {
            if ($tempDate) {
                if ((new \DateTime('now'))->modify('next '.DeliveryHelper::getDayOfTheWeekString($noMarkupDay)) < $tempDate) {
                    $tempDate = (new \DateTime('now'))->modify('next '.DeliveryHelper::getDayOfTheWeekString($noMarkupDay));
                }
            } else {
                $tempDate = (new \DateTime('now'))->modify('next '.DeliveryHelper::getDayOfTheWeekString($noMarkupDay));
            }
        }
        return $tempDate->format('d.m.Y');
    }

    public function getPriceDoorDelivery($params)
    {
        try {
            $limitBasket = self::getOshishaLimitBasket();
            $moreThanLimit = intval($params['shipment_cost']) >= $limitBasket;
            $hashed_values = array($params['latitude'], $params['longitude'], $params['date_delivery'],
                $this->configs['northdays'], $this->configs['southeastdays'], $this->configs['southwestdays'],
                $moreThanLimit);
            $hash_string = md5(implode('', $hashed_values));

            $is_cache_on = Option::get(DeliveryHelper::$MODULE_ID, 'Common_iscacheon');

            $cache = \Bitrix\Main\Data\Cache::createInstance(); // получаем экземпляр класса
            if ($cache->initCache(3600, $this->oshisha_cache_id)) { // проверяем кеш и задаём настройки
                if ($is_cache_on == 'Y') {
                    $cached_vars = $cache->getVars();
                    if (!empty($cached_vars)) {
                        foreach ($cached_vars as $varKey => $var) {
                            if ($varKey === $hash_string) {
                                return $var;
                            }
                        }
                    }
                }
            }

            if (!empty($params['latitude']) && !empty($params['longitude'])) {
                $point = OshishaSavedDeliveriesTable::getRow(array('filter' => array(
                    'LATITUDE'  =>  number_format($params['latitude'], 4, '.', ''),
                    'LONGITUDE' => number_format($params['longitude'], 4, '.', '')
                )));
            } else {
                $point = null;
            }
            if ($point) {
                $dayDateDelivery = $params['date_delivery'] ? date('w' ,strtotime($params['date_delivery']))
                    : date('w', strtotime('+1 day'));
                $itBigOrder = intval($params['shipment_cost']) >= $limitBasket;
                $distance = ceil(($point['DISTANCE'] ?? 0) - 0.8);
                $noMarkup = false;
                $nextNoMarkup = false;

                $deliveryRegions = $this->getOshishaDeliveryRegions();
                foreach ($deliveryRegions['regions'] as $region) {
                    if ($point['ZONE'] === $region['name']) {
                        $cost = $region['calculation']['cost'];

                        if (!empty($region['no_markup_days'])) {
                            foreach ($region['no_markup_days'] as $noMarkupDay) {
                                if ($noMarkupDay == $dayDateDelivery) {
                                    $noMarkup = true;
                                }
                            }
                            $nextNoMarkup = $this->getNextNoMarkupDate($region['no_markup_days']);
                        }

                        $typeCalculation = $region['calculation']['type'];
                        if ($typeCalculation === 'static') {
                            $delivery_price = $itBigOrder ? 0 : $cost;
                        } elseif ($typeCalculation === 'path') {
                            if ($noMarkup) {
                                $delivery_price = $itBigOrder ? 0 : $cost;
                            } else {
                                $delivery_price = $itBigOrder
                                    ? max($distance - 5, 0) * $region['calculation']['costKm']
                                    : $cost + $distance * $region['calculation']['costKm'];
                            }
                        } else {
                            $this->errors[] = 'Не корректный формат расчетов';
                            return array('errors' => $this->errors);
                        }

                        $priceArr = array('price' => $delivery_price, 'noMarkup' => $nextNoMarkup,
                            'deliveryIntervals'=>$region['delivery_intervals']);
                        break;
                    }
                }

                if (!isset($delivery_price)) {
                    $this->errors[] = 'Не удалось рассчитать стоимость доставки';
                    return array('errors' => $this->errors);
                }

                $cache->forceRewriting(true);
                if ($cache->startDataCache()) {
                    $cache->endDataCache((isset($cached_vars) && !empty($cached_vars))
                        ? array_merge($cached_vars, array($hash_string => $priceArr))
                        : array($hash_string => $priceArr));
                }
                return $priceArr;
            } else {
                $this->errors[] = 'no data found on point';
                return array('errors' => $this->errors);
            }
        } catch(\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }
}