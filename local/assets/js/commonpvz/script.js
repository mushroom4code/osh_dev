BX.namespace('BX.SaleCommonPVZ');


BX.SaleCommonPVZ = {

    pvzPopup: null,
    curCityCode: null,
    curCityName: null,
    isGetPVZ: false,
    ajaxUrlPVZ: null,
    propsMap: null,
    pvzObj: null,

    init: function (params) {
        console.log('SaleCommonPVZ init');
        this.ajaxUrlPVZ = params.ajaxUrlPVZ;
        this.propsMap = null;
        this.getCityName();
    },

    openMap: function () {
        console.log(this);
        console.dir(ymaps);

        this.createPVZPopup();
        this.buildPVZMap();
        this.pvzPopup.show();

    },

    createPVZPopup: function () {
        if (BX.PopupWindowManager.isPopupExists('wrap_pvz_map')) return;
        console.log('createPVZPopup');
        this.pvzPopup = BX.PopupWindowManager.create(
            'wrap_pvz_map',
            null,
            {
                content: '<div id="map_for_pvz" style=""></div>',
                closeIcon: {
                    left: '13px',
                    top: '10px'
                },
                resizable: true,
                overlay: {
                    backgroundColor: 'black',
                    opacity: 500
                },
                draggable: {restrict: false},
                width: '80',
                autoHide: true,
                lightShadow: true,
                events: {
                    onPopupShow: function () {
                    },
                    onPopupClose: function () {
                        console.dir(this);
                    }
                },
                closeByEsc: true
            });
    },

    /**
     *   Построение карты с PVZ
     */
    buildPVZMap: function () {
        if (this.propsMap !== null) {
            return;
        }
        var __this = this;
        ymaps.ready(function () {
            var myGeocoder = ymaps.geocode(__this.curCityName, {results: 1});
            myGeocoder.then(function (res) { // получаем координаты
                var firstGeoObject = res.geoObjects.get(0),
                    coords = firstGeoObject.geometry.getCoordinates();

                __this.propsMap = new ymaps.Map('map_for_pvz', {
                    center: [coords[0], coords[1]],
                    zoom: 10,
                    controls: ['fullscreenControl']
                });
                __this.getPVZList();

            }).catch(function (e) {
                __this.showError(__this.mainErrorsNode, 'Ошибка построения карты ПВЗ!');
                console.warn(e);
            });
        });

    },

    getCityName: function () {
        var __this = this;

        // Получаем код местопожения
        BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.forEach(function (item, index, array) {
            if (item.IS_LOCATION === 'Y') {
                __this.curCityCode = item.VALUE[0];
            }
        });

        BX.ajax({
            url: __this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                codeCity: __this.curCityCode,
                'action': 'getCityName'
            },
            onsuccess: function (res) {
                __this.curCityName = res;
            },
            onfailure: function (res) {
                console.log('error getCityName');
            }
        });
    },

    getPVZList: function () {
        var __this = this;

        BX.ajax({
            url: __this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                'cityName': __this.curCityName,
                'action': 'getPVZList'
            },
            onsuccess: function (res) {
                __this.pvzObj = JSON.parse(res) || [];
                __this.setPVZOnMap();
            },
            onfailure: function (res) {
                console.log('error getPVZList');
            }
        });
    },
    /**
     *  Установка маркеров на карту PVZ
     */
    setPVZOnMap: function () {

        var objectManager = new ymaps.ObjectManager({
            clusterize: true,
            clusterHasBalloon: false
        });
        console.dir(this.pvzObj);
        objectManager.add(this.pvzObj);

        var __this = this;

        __this.propsMap.geoObjects.add(objectManager);
        objectManager.objects.events.add(['click', 'multitouchstart'], function (e) {
            __this.pvzPopup.close();
            var objectId = e.get('objectId'),
                obj = objectManager.objects.getById(objectId);

            BX.ajax({
                url: __this.ajaxUrlPVZ,
                method: 'POST',
                data: {
                    'action': 'getPrice',
                    code_city: __this.curCityCode,
                    delivery: obj.properties.deliveryName,
                    to: obj.properties.fullAddress,
                    weight: BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_WEIGHT,
                    fivepost_zone: obj.properties.fivepostZone,
                    hubregion: obj.properties.hubregion,
                    name_city: __this.curCityName
                },
                onsuccess: BX.delegate(function (result) {
                    result = JSON.parse(result);
                    var reqData = {};
                    reqData.price = parseInt(result);
                    BX.Sale.OrderAjaxComponent.sendRequest('refreshOrderAjax', reqData);

                }, this),
                onfailure: BX.delegate(function () {
                    BX.Sale.OrderAjaxComponent.showError(BX.Sale.OrderAjaxComponent.mainErrorsNode, 'Ошибка запроса стоимости доставки!');
                    console.warn('error get price delivery');
                }),
            });
        });
    },


};
