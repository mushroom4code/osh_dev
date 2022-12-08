<?
$MESS["OSH_PVZ_NAME"] = "Пункт выдачи Oshisha";
$MESS["OSH_MODULE_NAME"] = "Служба доставки Oshisha";
$MESS["OSH_MODULE_DESC"] = "Служба доставки Oshisha";
$MESS["OSH_PARTNER_NAME"] = "Enterego";
$MESS["OSH_PARTNER_URI"] = "https://enterego.ru";

//$MESS['WSD_STEP1_TITLE'] = 'Мастер быстрой активации модуля Агрегатор служб доставки Osh';
//$MESS['WSD_STEP1_CONTENT'] = '<p>Для того чтобы модуль начал работать, нужно указать ключ API. Его можно взять в вашем <a href="https://osh.ru/account/settings/api" target="_blank">ЛК Osh</a></p>';
//$MESS['WSD_STEP1_API_LABEL'] = 'Ваш ключ API: ';
//$MESS['WSD_STEP1_WHERE'] = '<p>Если у вас он уже указан, переходите к следующему шагу.</p>';

//$MESS['WSD_NEXT'] = 'Далее';
//$MESS['WSD_FINISH'] = 'Завершить';

//$MESS['WSD_STEP2_TITLE'] = 'Создание и настройка службы доставки';
//$MESS['WSD_STEP2_ERROR_NO_API_KEY'] = '<span style="color:red;">Вы не указали ключ API</span>';
//$MESS['WSD_STEP2_ERROR_WRONG_API_KEY'] = '<span style="color:red;">Ключ API неверен!</span>';
//$MESS['WSD_STEP2_ERROR_CREATING'] = '<span style="color:red;">Не удалось создать родительскую службу доставки!</span>';
//$MESS['WSD_STEP2_ERROR_SMTH'] = '<span style="color:red;">Не удалось получить ответ от API Osh</span>';
//$MESS['WSD_STEP2_ALREADY_EXISTS'] = '<p>У вас есть родительская служба доставки Osh</p>';
//$MESS['WSD_STEP2_CREATED'] = '<p>Создана родительская служба доставки Osh</p>';
//$MESS['WSD_STEP2_PROFILES_CREATED'] = '<p>Созданы все доступные профили доставки Osh. Перейдите в <a href="/bitrix/admin/sale_delivery_service_edit.php?lang=ru&PARENT_ID=0&ID=#ID#" target="_blank">настройки службы доставки</a> во вкладку Профили и активируйте нужные вам.</p>';

$MESS['WSD_STEP2_STOCK_PARAMS'] = 'Укажите настройки складской обработки';
$MESS['WSD_STEP2_USER_TYPE_TEXT'] = 'Тип учетной записи Osh';
$MESS['WSD_STEP2_USER_TYPE_PHYS'] = 'Физическое лицо';
$MESS['WSD_STEP2_USER_TYPE_JUR'] = 'Юридическое лицо';
$MESS['WSD_STEP2_STOCK_TEXT'] = 'Склад Osh';
$MESS['WSD_STEP2_DIRECT_FIO_TEXT'] = 'ФИО отправителя';
$MESS['WSD_STEP2_DIRECT_PHONE_TEXT'] = 'Телефон';
$MESS['WSD_STEP2_DIRECT_EMAIL_TEXT'] = 'Email';
$MESS['WSD_STEP2_DIRECT_ZIP_TEXT'] = 'Почтовый код';
$MESS['WSD_STEP2_DIRECT_STREET_TEXT'] = 'Улица';
$MESS['WSD_STEP2_DIRECT_HOUSE_TEXT'] = 'Дом';
$MESS['WSD_STEP2_DELIVERY_HANDLER_PARAMS'] = 'Создайте профили доставки';
$MESS['WSD_STEP2_DELIVERY_CREATE_TEXT'] = 'Создать профили доставки';
$MESS['WSD_STEP2_DELIVERY_CREATE_ACTIVE'] = 'и активировать';
$MESS['WSD_STEP2_DELIVERY_CREATE_NACTIVE'] = 'и не активировать';
$MESS['WSD_STEP2_DELIVERY_CREATE_NO'] = 'не создавать';
$MESS['WSD_STEP2_DELIVERY_CREATE_INFO'] = 'Если вы выберете создавать профили доставки, то они будут создаваться только если не обнаружена активная служба доставки Osh';
$MESS['WSD_STEP2_DEFAULT_SIZE_PARAMS'] = 'Укажите измерения и вес по умолчанию для посылки или товара';
$MESS['WSD_STEP2_DP_LENGTH'] = 'Длина, см';
$MESS['WSD_STEP2_DP_WIDTH'] = 'Ширина, см';
$MESS['WSD_STEP2_DP_HEIGHT'] = 'Высота, см';
$MESS['WSD_STEP2_DP_WEIGHT'] = 'Вес, кг';
$MESS['WSD_STEP2_DEFAULT_CALC_ALGO'] = 'Выберите алгоритм расчета габаритов';
$MESS['WSD_STEP2_DP_CALC_ALGORITM'] = 'Тип расчета';
$MESS['WSD_STEP2_DEFAULT_CALC_ALGO_SIMPLE'] = 'По умолчанию для посылки';
$MESS['WSD_STEP2_DEFAULT_CA_SIMPLE'] = 'В этом режиме габариты по умолчанию будут использоваться в качестве габаритов всей посылки, если у вашего товара в каталоге не заполнены габариты или его количество превышает 1';
$MESS['WSD_STEP2_DEFAULT_CALC_ALGO_COMPLEX'] = 'Автоматически для товаров';
$MESS['WSD_STEP2_DEFAULT_CA_COMPLEX'] = 'В этом режиме габариты по умолчанию будут использоваться если у какого-либо из товаров в заказе не заполнены габариты, и будут использованы в качестве габаритов такого товара. Итоговый габарит посылки будет расчитываться, как эффективная величина из суммарного объема товаров заказа.';
$MESS['WSD_STEP2_ADDRESS_PROPS'] = 'Настройте привязки свойств заказов';
$MESS['WSD_STEP2_ADDRESS_TYPE'] = 'Тип поля Адрес';
$MESS['WSD_STEP2_ADDRESS_TYPE_SIMPLE'] = 'Единое';
$MESS['WSD_STEP2_ADDRESS_TYPE_COMPLEX'] = 'Составное';
$MESS['WSD_STEP2_ADDRESS_PROP_ID'] = 'Свойство типа Адрес для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_STREET_PROP_ID'] = 'Свойство Улица для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_BLD_PROP_ID'] = 'Свойство Дом для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_CORP_PROP_ID'] = 'Свойство Корпус для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_FLAT_PROP_ID'] = 'Свойство Квартира для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_PVZ_PROP_ID'] = 'Свойство типа Код ПВЗ для <b>#PERSON_TYPE#</b>';

$MESS['WSD_FINALSTEP_TITLE'] = 'Настройка завершена';
$MESS['WSD_FINALSTEP_CONTENT_HEAD'] = '<span style="color:green;">Базовые настройки модуля успешно завершены!</span>';
$MESS['WSD_FINALSTEP_CONTENT_FAST_DOC_LINK'] = 'Чтобы ознакомиться с документацией к модулю воспользуйтесь ссылкой <a href="https://osh.ru/help/integration/bitrix#bitrix-fast-start" target="_blank">Быстрый старт</a>';
$MESS['WSD_FINALSTEP_CONTENT_MODULE_LINK'] = 'Настройки модуля расположены <a href="/bitrix/admin/settings.php?lang=ru&mid=osh.shipping&mid_menu=1" target="_blank">здесь</a>';
$MESS['WSD_FINALSTEP_CONTENT_DELIVERY_LINK'] = 'Настройки службы доставки расположены <a href="/bitrix/admin/sale_delivery_service_edit.php?lang=ru&PARENT_ID=0&ID=#ID#" target="_blank">здесь</a>';