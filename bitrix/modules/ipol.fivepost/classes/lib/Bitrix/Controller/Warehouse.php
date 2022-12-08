<?php

namespace Ipol\Fivepost\Bitrix\Controller;


use Ipol\Fivepost\Admin\BitrixLoggerController;
use Ipol\Fivepost\Api\Entity\Request\Part\Warehouse\WarehouseElem;
use Ipol\Fivepost\Api\Entity\Request\Part\Warehouse\WarehouseElemList;
use Ipol\Fivepost\Api\Entity\Request\Part\Warehouse\WorkingTime;
use Ipol\Fivepost\Api\Entity\Request\Part\Warehouse\WorkingTimeList;
use Ipol\Fivepost\Bitrix\Entity\BasicResponse;

class Warehouse extends AbstractController
{
    /**
     * @var bool|WarehouseElemList
     */
    protected $obWarehouse = false;

    public function __construct()
    {
        parent::__construct(IPOL_FIVEPOST, IPOL_FIVEPOST_LBL);

        $this->logger =
            ($this->options->fetchDebug() === 'Y' && $this->options->fetchOption('debug_warehouses') === 'Y') ?
                new BitrixLoggerController() :
                false;

        $this->application->setLogger($this->logger);
    }

    public function addWarehouse()
    {
        $obRet = new BasicResponse();
        if($this->obWarehouse){
            $obResponse = $this->application->createWarehouse($this->obWarehouse);
            if($obResponse){
                if($obResponse->isSuccess()){
                    if($obResponse->getResponse() &&
                        $obResponse->getResponse()->getWarehouses() &&
                        $obResponse->getResponse()->getWarehouses()->getFirst()->getId()
                    ){
                        $obRet->setSuccess(true);
                    } else {
                        $obRet->setSuccess(false)
                            ->setErrorText($obResponse->getResponse()->getErrorMsg());
                    }

                    $obResponse->setSuccess(true);
                } else {
                    $obRet
                        ->setSuccess(false)
                        ->setErrorText(
                            $this->application->getErrorCollection()->getLast() ?
                                $this->application->getErrorCollection()->getLast()->getMessage() :
                                '');
                }
            } else {
                $obRet->setSuccess(false)
                      ->setErrorText('No response');
            }
        } else {
            $obRet->setSuccess(false)
                  ->setErrorText('No warehouse data');
        }

        return $obRet;
    }

    public function fromRequest($arData){
        $obWorkingTimeCollection = new WorkingTimeList(); //формируем объект расписания работы

        foreach ($arData[self::$MODULE_LBL.'WH_workingTime'] as $arWT){
            $obWorkingTime = new WorkingTime();
            $obWorkingTime->setDayNumber($arWT['dayNumber'])
                          ->setTimeFrom($arWT['timeFrom'])
                          ->setTimeTill($arWT['timeTill']);
            $obWorkingTimeCollection->add($obWorkingTime);
        }

        $obWarehouseCollection = new WarehouseElemList();
        $obWarehose = new WarehouseElem();

        $obWarehose->setName($arData[self::$MODULE_LBL.'WH_name']) //Наименование склада партнера (напр. Romashka-1)
                   ->setCountryId('RU') //Двухбуквенные коды стран мира международной организации по стандартизации (iso).
                   ->setRegionCode($arData[self::$MODULE_LBL.'WH_regionCode']) //Код региона.Возможные значения приложены в доке апи (может начинаться с нуля "01" итд)
                   ->setFederalDistrict($arData[self::$MODULE_LBL.'WH_federalDistrict']) //Наименование области
                   ->setRegion($arData[self::$MODULE_LBL.'WH_region']) //Наименование региона
                   ->setIndex($arData[self::$MODULE_LBL.'WH_index']) //Почтовый индекс склада это строка, даже если и из цифр
                   ->setCity($arData[self::$MODULE_LBL.'WH_city']) //Наименование города
                   ->setStreet($arData[self::$MODULE_LBL.'WH_street']) //Наименование улицы
                   ->setHouseNumber($arData[self::$MODULE_LBL.'WH_houseNumber']) //Номер дома склада, как видите, тоже именно строка
                   ->setCoordinates($arData[self::$MODULE_LBL.'WH_coordinatesX'].', '.$arData[self::$MODULE_LBL.'WH_coordinatesY']) //Географические координаты склада
                   ->setContactPhoneNumber($arData[self::$MODULE_LBL.'WH_contactPhoneNumber']) //Контактный телефон объекта в формате +7**********
                   ->setTimeZone($arData[self::$MODULE_LBL.'WH_timeZone']) //Часовой пояс, в котором расположен склад
                   ->setWorkingTime($obWorkingTimeCollection) //
                   ->setPartnerLocationId($arData[self::$MODULE_LBL.'WH_partnerLocationId']);
        $obWarehouseCollection->add($obWarehose);

        $this->obWarehouse = $obWarehouseCollection;

        return $this;
    }
}