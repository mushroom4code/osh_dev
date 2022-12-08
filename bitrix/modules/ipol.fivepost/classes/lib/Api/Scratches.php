<?php
namespace Ipol\Fivepost\Api;


//TODO: its file with sdk calls for IPOL-dev's. REMOVE BEFORE RELEASE!!!
use Ipol\Fivepost\Api\Entity\Request\Part\CreateOrder\Barcode;
use Ipol\Fivepost\Api\Entity\Request\Part\CreateOrder\BarcodeList;
use Ipol\Fivepost\Api\Entity\Request\Part\CreateOrder\Cargo;
use Ipol\Fivepost\Api\Entity\Request\Part\CreateOrder\CargoList;
use Ipol\Fivepost\Api\Entity\Request\Part\CreateOrder\PartnerOrder;
use Ipol\Fivepost\Api\Entity\Request\Part\CreateOrder\PartnerOrderList;
use Ipol\Fivepost\Api\Entity\Request\Part\CreateOrder\ProductValue;
use Ipol\Fivepost\Api\Entity\Request\Part\CreateOrder\ProductValueList;
use Ipol\Fivepost\Api\Entity\Request\Part\Warehouse\WarehouseElem;
use Ipol\Fivepost\Api\Entity\Request\Part\Warehouse\WarehouseElemList;
use Ipol\Fivepost\Api\Entity\Request\Warehouse;
use Ipol\Fivepost\Api\Entity\Request\Part\Warehouse\WorkingTime;
use Ipol\Fivepost\Api\Entity\Request\Part\Warehouse\WorkingTimeList;
use Ipol\Fivepost\Api\Entity\Request\Part\CreateOrder\Cost;

Class Scratches
{
    public static function getJwt($apikey, $mode = 'TEST')
    {
        /*����� ����������� �������� (��������� Jwt-������) */
        $adapter = new Adapter\CurlAdapter(); //�������� ������ ��� ������ � ���
// headers: ������ �������������� ����������, ���� �����;
// mode: TEST|API - � ������ URL ����������;
// custom: ������������ �� ��������� ������������� URL �� ����� (�� ��� ������� ������ � �������);
// token: ����� ��� Token Bearer ��������� ��� ���������� � ���. � ���� ������ �� ���� ��������, ������� �� �������. � ��������� ������� ����������
        $sdk     = new Sdk($adapter, false, false, $mode, false); //�������� ������ ��� ������ � ���

        $request = new Entity\Request\JwtGenerate(); //������ ������ ������� �����������
        $request->setApikey($apikey); //������������� ���������� ����� � ������� �������

        $result = $sdk->jwtGenerate($request)->getResponse(); //������� ������ ��� ������ �������� � ����� �������� �� ���� ������ ��������

        return $result; // ��������� ������� ������ � ���� ������� Api\Entity\Response\JwtGenerate
        //�������� �� ���� ������ ����� ����� ����� �������������� ������� (������ ����� ��� ��� � ������ ������ getJwt()), ��� ����� ��� ����� getFields()
        // (getFields() ��������� ��� ������������ get/is ������ ������, � �������� ����������� ������, ��� �������, ������� � ���� ������)
    }

    public static function getPoints($jwt, $pageNum = 0, $pageSize = 1000, $mode = 'TEST')
    {
        /*����� ��������� ����� �������� */
        $adapter = new Adapter\CurlAdapter(); //�������� ������ ��� ������ � ���
// headers: ������ �������������� ����������, ���� �����;
// mode: TEST|API - � ������ URL ����������;
// custom: ������������ �� ��������� ������������� URL �� ����� (�� ��� ������� ������ � �������);
// token: ����� ��� Token Bearer ��������� ��� ���������� � ���. � ���� ������ �� ���� ��������, ������� �� �������. � ��������� ������� ����������
        $sdk     = new Sdk($adapter, $jwt, false, $mode, false); //�������� ������ ��� ������ � ���

        $request = new Entity\Request\PickupPoints(); //������ ������ ������� �����
        $request->setPageNumber($pageNum) //������������� ���������� ����� ������ �� ��������
            ->setPageSize($pageSize); //������������� ����� �������� (��������� ���������� � 0)

        $result = $sdk->pickupPoints($request)->getResponse(); //������� ������ ��� ������ �������� � ����� �������� �� ���� ������ ��������

        return $result; // ��������� ������� ������ � ���� ������� Api\Entity\Response\PickupPoints
        //�������� �� ���� ������ ����� ����� ����� �������������� �������, ��� ����� ��� ����� getFields()
        // (getFields() ��������� ��� ������������ get/is ������ ������, � �������� ����������� ������, ��� �������, ������� � ���� ������)
    }

    public static function createWarehouse($jwt, $mode = 'TEST', $warehouseId = "Warehouse_125")
    {//warehouseId ������ ��� ����� �����, ���� � �������� ������� - ����� ��������, ��� ����� ��� ������
        if($mode=='API')
        {
            throw new \Exception('����� ������� ����� � ������������ �������, ���� �� ������������� ����� ��� �������, ������������� �������� � ����');
        }
        /*����� �������� ������ */
        $adapter = new Adapter\CurlAdapter(); //�������� ������ ��� ������ � ���
// headers: ������ �������������� ����������, ���� �����;
// mode: TEST|API - � ������ URL ����������;
// custom: ������������ �� ��������� ������������� URL �� ����� (�� ��� ������� ������ � �������);
// token: ����� ��� Token Bearer ��������� ��� ���������� � ���. � ���� ������ �� ���� ��������, ������� �� �������. � ��������� ������� ����������
        $sdk     = new Sdk($adapter, $jwt, false, $mode, false); //�������� ������ ��� ������ � ���

        $obWorkingTimeCollection = new WorkingTimeList(); //��������� ������ ���������� ������
            $obWorkingTimeElem1 = new WorkingTime(); //��������� ������ ��� ������ ���
            $obWorkingTimeElem1->setDayNumber(1) //���������� ����� ��� ������ ������
                ->setTimeFrom("09:00:00") //����� �������� ������
                ->setTimeTill("20:00:00"); //����� �������� ������
            //STAR WARS ATTACK OF CLONES
            $obWorkingTimeElem2 = (clone($obWorkingTimeElem1))->setDayNumber(2); //� ��� ��� ������� ���.
            $obWorkingTimeElem3 = (clone($obWorkingTimeElem1))->setDayNumber(3); //�������, � �����������
            $obWorkingTimeElem4 = (clone($obWorkingTimeElem1))->setDayNumber(4); //�� ����������, ����� ��������� �� ��� ����,
            $obWorkingTimeElem5 = (clone($obWorkingTimeElem1))->setDayNumber(5); // � ��������� ���� ��������
            $obWorkingTimeElem6 = (clone($obWorkingTimeElem1))->setDayNumber(6);
            $obWorkingTimeElem7 = (clone($obWorkingTimeElem1))->setDayNumber(7);
            //�� ���� ��� �������� ���� ���� ��� ������� ���������
        $obWorkingTimeCollection->add($obWorkingTimeElem1) //��������� ������ ���������� ������ ��������� ��������� ����
            ->add($obWorkingTimeElem2)
            ->add($obWorkingTimeElem3)
            ->add($obWorkingTimeElem4)
            ->add($obWorkingTimeElem5)
            ->add($obWorkingTimeElem6)
            ->add($obWorkingTimeElem7);

        $obWarehouseCollection = new WarehouseElemList();
            $obWarehose1 = new WarehouseElem();

            $obWarehose1->setName('�����-1') //������������ ������ �������� (����. Romashka-1)
                    ->setCountryId('RU') //������������� ���� ����� ���� ������������� ����������� �� �������������� (iso).
                    ->setRegionCode('50') //��� �������.��������� �������� ��������� � ���� ��� (����� ���������� � ���� "01" ���)
                    ->setFederalDistrict('����������� ����������� �����') //������������ �������
                    ->setRegion("������ � ���������� �������") //������������ �������
                    ->setIndex("123557") //�������� ������ ������ ��� ������, ���� ���� � �� ����
                    ->setCity("������") //������������ ������
                    ->setStreet("����������� ���") //������������ �����
                    ->setHouseNumber("27���15") //����� ���� ������, ��� ������, ���� ������ ������
                    ->setCoordinates("55.774122, 37.575621") //�������������� ���������� ������
                    ->setContactPhoneNumber("+74957894208") //���������� ������� ������� � ������� +7**********
                    ->setTimeZone("+02:00") //������� ����, � ������� ���������� �����
                    ->setWorkingTime($obWorkingTimeCollection) //
                    ->setPartnerLocationId($warehouseId);
        $obWarehouseCollection->add($obWarehose1);

        $request = new Warehouse(); //������ ������ ������� ����������� ������
        $request->setWarehouses($obWarehouseCollection);

        $result = $sdk->warehouse($request)->getResponse(); //������� ������ ��� ������ �������� � ����� �������� �� ���� ������ ��������

        return $result; // ��������� ������� ������ � ���� ������� Api\Entity\Response\PickupPoints
        //�������� �� ���� ������ ����� ����� ����� �������������� �������, ��� ����� ��� ����� getFields()
        // (getFields() ��������� ��� ������������ get/is ������ ������, � �������� ����������� ������, ��� �������, ������� � ���� ������)
    }

    public static function createOrders($jwt, $mode = 'TEST')
    {
        /*����� �������� ������� */
        $adapter = new Adapter\CurlAdapter(); //�������� ������ ��� ������ � ���
// headers: ������ �������������� ����������, ���� �����;
// mode: TEST|API - � ������ URL ����������;
// custom: ������������ �� ��������� ������������� URL �� ����� (�� ��� ������� ������ � �������);
// token: ����� ��� Token Bearer ��������� ��� ���������� � ���. � ���� ������ �� ���� ��������, ������� �� �������. � ��������� ������� ����������
        $sdk     = new Sdk($adapter, $jwt, false, $mode, false); //�������� ������ ��� ������ � ���

        $request = new Entity\Request\CreateOrder(); //������ ������ ������� �������� ������
        //��������� ��������� ������� ��� ��������
        $obOrderCollection = new PartnerOrderList();
            $obOrder1 = new PartnerOrder();
                $obCargoCollection = new CargoList();
                    $obCargo1 = new Cargo();
                        $obProductCollection = new ProductValueList();
                            $obProduct1 = new ProductValue();
                            $obProduct1->setValue(1)
                                ->setBarcode(null)
                                ->setCodeGTD(null)
                                ->setCodeTNVED(null)
                                ->setCurrency(null)
                                ->setOriginCountry(null)
                                ->setVendorCode(null)
                                ->setName("����� 1")
                                ->setPrice(115)
                                ->setValue(1)
                                ->setVat(20);
                        $obProductCollection->add($obProduct1);

                        $obBarcodeCollection = new BarcodeList();
                            $obBarcode1 = new Barcode();
                            $obBarcode1->setValue("88110000000001");
                        $obBarcodeCollection->add($obBarcode1);
                    $obCargo1->setProductValues($obProductCollection)
                        ->setBarcodes($obBarcodeCollection)
                        ->setSenderCargoId("11300000294")
					    ->setPrice(100)
					    ->setVat(null)
					    ->setCurrency("RUB")
					    ->setHeight(100)
					    ->setLength(100)
					    ->setWidth(100)
					    ->setWeight(1000000);
                $obCargoCollection->add($obCargo1);
                $obCost = new Cost();
                $obCost->setDeliveryCost(null)
				->setDeliveryCostCurrency(null)
				->setPaymentCurrency("RUB")
				->setPaymentType("CASHLESS")
				->setPaymentValue(115)
				->setPrice(00)
				->setPriceCurrency("RUB");

            $obOrder1->setClientEmail(null)
                ->setPlannedReceiveDate(null)
                ->setSenderCreateDate(null)
                ->setShipmentDate(null)
                ->setSenderOrderId("1991921")
                ->setBrandName("������� �������")
                ->setClientOrderId("91456601")
                ->setClientName("������ ���� ��������")
                ->setClientPhone("+79251111111")
                ->setReceiverLocation("13e9d62d-1799-4e14-a27b-d218f33de7f6")
                ->setSenderLocation("2334567")
                ->setUndeliverableOption("RETURN")
                ->setCargoes($obCargoCollection)
                ->setCost($obCost);
        $obOrderCollection->add($obOrder1);

        $request->setPartnerOrders($obOrderCollection);

        $result = $sdk->createOrder($request)->getResponse(); //������� ������ ��� ������ �������� � ����� �������� �� ���� ������ ��������

        return $result; // ��������� ������� ������ � ���� ������� Api\Entity\Response\CreateOrder
        //�������� �� ���� ������ ����� ����� ����� �������������� �������, ��� ����� ��� ����� getFields()
        // (getFields() ��������� ��� ������������ get/is ������ ������, � �������� ����������� ������, ��� �������, ������� � ���� ������)
    }

    public static function cancelOrderById($jwt, $uuid, $mode = 'TEST')
    {
        /*����� ������ ������ �� ��� uuid � ������� X5 ����� ��� �������� �� ������ ������ ���������� �� ���������*/
        $adapter = new Adapter\CurlAdapter(); //�������� ������ ��� ������ � ���
// headers: ������ �������������� ����������, ���� �����;
// token: ����� ��� Token Bearer ��������� ��� ���������� � ���. � ���� ������ �� ���� ��������, ������� �� �������. � ��������� ������� ����������
        $sdk     = new Sdk($adapter, $jwt, false, $mode, false); //�������� ������ ��� ������ � ���

        $request = new Entity\Request\CancelOrderById($uuid); //������ ������ ������� ������

        $result = $sdk->CancelOrderById($request)->getResponse(); //������� ������ ��� ������ �������� � ����� �������� �� ���� ������ ��������

        return $result; // ��������� ������� ������ � ���� ������� Api\Entity\Response\CancelOrderById
        //�������� �� ���� ������ ����� ����� ����� �������������� �������, ��� ����� ��� ����� getFields()
        // (getFields() ��������� ��� ������������ get/is ������ ������, � �������� ����������� ������, ��� �������, ������� � ���� ������)
    }


}
