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
        /*Метод авторизации клиентов (получение Jwt-токена) */
        $adapter = new Adapter\CurlAdapter(); //получаем объект для работы с АПИ
// headers: массив дополнительных заголовкой, если нужны;
// mode: TEST|API - к какому URL обращаться;
// custom: использовать ли кастомное переопеделние URL из файла (не для обычной работы с модулем);
// token: токен для Token Bearer заголовка при обращениях к АПИ. В этом методе мы егои получаем, поэтому не передаём. В остальных методах обязателен
        $sdk     = new Sdk($adapter, false, false, $mode, false); //получаем объект для работы с СДК

        $request = new Entity\Request\JwtGenerate(); //создаём объект запроса авторизации
        $request->setApikey($apikey); //устанавливаем клиентские ключи в объекте запроса

        $result = $sdk->jwtGenerate($request)->getResponse(); //передаём методу СДК объект реквеста и сразу получаем от него объект респонса

        return $result; // возращаем респонс именно в виде объекта Api\Entity\Response\JwtGenerate
        //получить из него данные потом можно через соответсвующие геттеры (важнее всего для нас в данном случае getJwt()), или сразу все через getFields()
        // (getFields() выполняет все существующие get/is методы класса, к которому принадлежит объект, для свойств, которые у него заданы)
    }

    public static function getPoints($jwt, $pageNum = 0, $pageSize = 1000, $mode = 'TEST')
    {
        /*Метод получения точек доставки */
        $adapter = new Adapter\CurlAdapter(); //получаем объект для работы с АПИ
// headers: массив дополнительных заголовкой, если нужны;
// mode: TEST|API - к какому URL обращаться;
// custom: использовать ли кастомное переопеделние URL из файла (не для обычной работы с модулем);
// token: токен для Token Bearer заголовка при обращениях к АПИ. В этом методе мы егои получаем, поэтому не передаём. В остальных методах обязателен
        $sdk     = new Sdk($adapter, $jwt, false, $mode, false); //получаем объект для работы с СДК

        $request = new Entity\Request\PickupPoints(); //создаём объект запроса точек
        $request->setPageNumber($pageNum) //Устанавливаем количество точек выдачи на странице
            ->setPageSize($pageSize); //Устанавливаем номер страницы (нумерация начинается с 0)

        $result = $sdk->pickupPoints($request)->getResponse(); //передаём методу СДК объект реквеста и сразу получаем от него объект респонса

        return $result; // возращаем респонс именно в виде объекта Api\Entity\Response\PickupPoints
        //получить из него данные потом можно через соответсвующие геттеры, или сразу все через getFields()
        // (getFields() выполняет все существующие get/is методы класса, к которому принадлежит объект, для свойств, которые у него заданы)
    }

    public static function createWarehouse($jwt, $mode = 'TEST', $warehouseId = "Warehouse_125")
    {//warehouseId каждый раз нужен новый, даже в тестовом контуре - иначе напомнят, что такой уже создан
        if($mode=='API')
        {
            throw new \Exception('Чтобы создать склад в продуктивном контуре, если ты ДЕЙСТВИТЕЛЬНО хотел это сделать, закомментируй проверку в коде');
        }
        /*Метод создания склада */
        $adapter = new Adapter\CurlAdapter(); //получаем объект для работы с АПИ
// headers: массив дополнительных заголовкой, если нужны;
// mode: TEST|API - к какому URL обращаться;
// custom: использовать ли кастомное переопеделние URL из файла (не для обычной работы с модулем);
// token: токен для Token Bearer заголовка при обращениях к АПИ. В этом методе мы егои получаем, поэтому не передаём. В остальных методах обязателен
        $sdk     = new Sdk($adapter, $jwt, false, $mode, false); //получаем объект для работы с СДК

        $obWorkingTimeCollection = new WorkingTimeList(); //формируем объект расписания работы
            $obWorkingTimeElem1 = new WorkingTime(); //формируем объект для одного дня
            $obWorkingTimeElem1->setDayNumber(1) //порядковый номер дня работы склада
                ->setTimeFrom("09:00:00") //Время открытия работы
                ->setTimeTill("20:00:00"); //Время закрытия работы
            //STAR WARS ATTACK OF CLONES
            $obWorkingTimeElem2 = (clone($obWorkingTimeElem1))->setDayNumber(2); //и так для каждого дня.
            $obWorkingTimeElem3 = (clone($obWorkingTimeElem1))->setDayNumber(3); //Полагаю, в зависимости
            $obWorkingTimeElem4 = (clone($obWorkingTimeElem1))->setDayNumber(4); //от расписания, можно заполнять не все семь,
            $obWorkingTimeElem5 = (clone($obWorkingTimeElem1))->setDayNumber(5); // а пропуская свои выходные
            $obWorkingTimeElem6 = (clone($obWorkingTimeElem1))->setDayNumber(6);
            $obWorkingTimeElem7 = (clone($obWorkingTimeElem1))->setDayNumber(7);
            //ну лень мне отдельно семь дней для примера заполнять
        $obWorkingTimeCollection->add($obWorkingTimeElem1) //заполняем объект расписания недели объектами отдельных дней
            ->add($obWorkingTimeElem2)
            ->add($obWorkingTimeElem3)
            ->add($obWorkingTimeElem4)
            ->add($obWorkingTimeElem5)
            ->add($obWorkingTimeElem6)
            ->add($obWorkingTimeElem7);

        $obWarehouseCollection = new WarehouseElemList();
            $obWarehose1 = new WarehouseElem();

            $obWarehose1->setName('Склад-1') //Наименование склада партнера (напр. Romashka-1)
                    ->setCountryId('RU') //Двухбуквенные коды стран мира международной организации по стандартизации (iso).
                    ->setRegionCode('50') //Код региона.Возможные значения приложены в доке апи (может начинаться с нуля "01" итд)
                    ->setFederalDistrict('Центральный федеральный округ') //Наименование области
                    ->setRegion("Москва и Московская область") //Наименование региона
                    ->setIndex("123557") //Почтовый индекс склада это строка, даже если и из цифр
                    ->setCity("Москва") //Наименование города
                    ->setStreet("Пресненский Вал") //Наименование улицы
                    ->setHouseNumber("27стр15") //Номер дома склада, как видите, тоже именно строка
                    ->setCoordinates("55.774122, 37.575621") //Географические координаты склада
                    ->setContactPhoneNumber("+74957894208") //Контактный телефон объекта в формате +7**********
                    ->setTimeZone("+02:00") //Часовой пояс, в котором расположен склад
                    ->setWorkingTime($obWorkingTimeCollection) //
                    ->setPartnerLocationId($warehouseId);
        $obWarehouseCollection->add($obWarehose1);

        $request = new Warehouse(); //создаём объект запроса регистрации склада
        $request->setWarehouses($obWarehouseCollection);

        $result = $sdk->warehouse($request)->getResponse(); //передаём методу СДК объект реквеста и сразу получаем от него объект респонса

        return $result; // возращаем респонс именно в виде объекта Api\Entity\Response\PickupPoints
        //получить из него данные потом можно через соответсвующие геттеры, или сразу все через getFields()
        // (getFields() выполняет все существующие get/is методы класса, к которому принадлежит объект, для свойств, которые у него заданы)
    }

    public static function createOrders($jwt, $mode = 'TEST')
    {
        /*Метод выгрузки заказов */
        $adapter = new Adapter\CurlAdapter(); //получаем объект для работы с АПИ
// headers: массив дополнительных заголовкой, если нужны;
// mode: TEST|API - к какому URL обращаться;
// custom: использовать ли кастомное переопеделние URL из файла (не для обычной работы с модулем);
// token: токен для Token Bearer заголовка при обращениях к АПИ. В этом методе мы егои получаем, поэтому не передаём. В остальных методах обязателен
        $sdk     = new Sdk($adapter, $jwt, false, $mode, false); //получаем объект для работы с СДК

        $request = new Entity\Request\CreateOrder(); //создаём объект запроса создания заказа
        //заполняем коллекцию заказов для выгрузки
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
                                ->setName("Книга 1")
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
                ->setBrandName("Магазин Ромашка")
                ->setClientOrderId("91456601")
                ->setClientName("Иванов Иван Иванович")
                ->setClientPhone("+79251111111")
                ->setReceiverLocation("13e9d62d-1799-4e14-a27b-d218f33de7f6")
                ->setSenderLocation("2334567")
                ->setUndeliverableOption("RETURN")
                ->setCargoes($obCargoCollection)
                ->setCost($obCost);
        $obOrderCollection->add($obOrder1);

        $request->setPartnerOrders($obOrderCollection);

        $result = $sdk->createOrder($request)->getResponse(); //передаём методу СДК объект реквеста и сразу получаем от него объект респонса

        return $result; // возращаем респонс именно в виде объекта Api\Entity\Response\CreateOrder
        //получить из него данные потом можно через соответсвующие геттеры, или сразу все через getFields()
        // (getFields() выполняет все существующие get/is методы класса, к которому принадлежит объект, для свойств, которые у него заданы)
    }

    public static function cancelOrderById($jwt, $uuid, $mode = 'TEST')
    {
        /*Метод отмены заказа по его uuid в системе X5 Метод для удаления по номеру заказа аналогичен по структуре*/
        $adapter = new Adapter\CurlAdapter(); //получаем объект для работы с АПИ
// headers: массив дополнительных заголовкой, если нужны;
// token: токен для Token Bearer заголовка при обращениях к АПИ. В этом методе мы егои получаем, поэтому не передаём. В остальных методах обязателен
        $sdk     = new Sdk($adapter, $jwt, false, $mode, false); //получаем объект для работы с СДК

        $request = new Entity\Request\CancelOrderById($uuid); //создаём объект запроса отмены

        $result = $sdk->CancelOrderById($request)->getResponse(); //передаём методу СДК объект реквеста и сразу получаем от него объект респонса

        return $result; // возращаем респонс именно в виде объекта Api\Entity\Response\CancelOrderById
        //получить из него данные потом можно через соответсвующие геттеры, или сразу все через getFields()
        // (getFields() выполняет все существующие get/is методы класса, к которому принадлежит объект, для свойств, которые у него заданы)
    }


}
