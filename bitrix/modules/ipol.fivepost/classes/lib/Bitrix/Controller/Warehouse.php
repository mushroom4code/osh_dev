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
        $obWorkingTimeCollection = new WorkingTimeList(); //��������� ������ ���������� ������

        foreach ($arData[self::$MODULE_LBL.'WH_workingTime'] as $arWT){
            $obWorkingTime = new WorkingTime();
            $obWorkingTime->setDayNumber($arWT['dayNumber'])
                          ->setTimeFrom($arWT['timeFrom'])
                          ->setTimeTill($arWT['timeTill']);
            $obWorkingTimeCollection->add($obWorkingTime);
        }

        $obWarehouseCollection = new WarehouseElemList();
        $obWarehose = new WarehouseElem();

        $obWarehose->setName($arData[self::$MODULE_LBL.'WH_name']) //������������ ������ �������� (����. Romashka-1)
                   ->setCountryId('RU') //������������� ���� ����� ���� ������������� ����������� �� �������������� (iso).
                   ->setRegionCode($arData[self::$MODULE_LBL.'WH_regionCode']) //��� �������.��������� �������� ��������� � ���� ��� (����� ���������� � ���� "01" ���)
                   ->setFederalDistrict($arData[self::$MODULE_LBL.'WH_federalDistrict']) //������������ �������
                   ->setRegion($arData[self::$MODULE_LBL.'WH_region']) //������������ �������
                   ->setIndex($arData[self::$MODULE_LBL.'WH_index']) //�������� ������ ������ ��� ������, ���� ���� � �� ����
                   ->setCity($arData[self::$MODULE_LBL.'WH_city']) //������������ ������
                   ->setStreet($arData[self::$MODULE_LBL.'WH_street']) //������������ �����
                   ->setHouseNumber($arData[self::$MODULE_LBL.'WH_houseNumber']) //����� ���� ������, ��� ������, ���� ������ ������
                   ->setCoordinates($arData[self::$MODULE_LBL.'WH_coordinatesX'].', '.$arData[self::$MODULE_LBL.'WH_coordinatesY']) //�������������� ���������� ������
                   ->setContactPhoneNumber($arData[self::$MODULE_LBL.'WH_contactPhoneNumber']) //���������� ������� ������� � ������� +7**********
                   ->setTimeZone($arData[self::$MODULE_LBL.'WH_timeZone']) //������� ����, � ������� ���������� �����
                   ->setWorkingTime($obWorkingTimeCollection) //
                   ->setPartnerLocationId($arData[self::$MODULE_LBL.'WH_partnerLocationId']);
        $obWarehouseCollection->add($obWarehose);

        $this->obWarehouse = $obWarehouseCollection;

        return $this;
    }
}