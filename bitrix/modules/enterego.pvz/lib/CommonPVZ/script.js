BX.namespace('BX.SaleCommonPVZ');

BX.SaleCommonPVZ = {

    pvzPopup: null,
    curCityCode: null,
    curCityName: null,
    isGetPVZ: false,
    ajaxUrlPVZ: '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php',
    propsMap: null,
    pvzObj: null,
    pvzAddress: null,
    pvzFullAddress: null,

    init: function (params) {
        console.log('... CommonPVZ init ...');
        this.refresh();
    },

    openMap: function () {
        this.refresh();
        this.createPVZPopup();
        this.buildPVZMap();
        this.pvzPopup.show();
    },

    refresh: function () {
        var __this = this;

        BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.forEach(function (item, index, array) {
            if (item.IS_LOCATION === 'Y') {
                if (__this.curCityCode !== item.VALUE[0]) {
                    __this.curCityCode = item.VALUE[0];
                    __this.propsMap = null;
                    __this.getCityName();
                }
            }
        });
    },

    createPVZPopup: function () {
        if (BX.PopupWindowManager.isPopupExists('wrap_pvz_map')) return;
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
        var loaderTimer, __this = this;
        if (!BX.Sale.OrderAjaxComponent.startLoader())
            return;
        BX.ajax({
            url: __this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                'cityName': __this.curCityName,
                'codeCity': __this.curCityCode,
                'action': 'getPVZList'
            },
            onsuccess: function (res) {
                __this.pvzObj = JSON.parse(res) || [];
                __this.setPVZOnMap();
            },
            onfailure: function (res) {
                console.log('error getPVZList');
                BX.Sale.OrderAjaxComponent.endLoader();
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
        objectManager.add(this.pvzObj);

        var __this = this;

        __this.propsMap.geoObjects.add(objectManager);
        BX.Sale.OrderAjaxComponent.endLoader();

        objectManager.objects.events.add(['click', 'multitouchstart'], function (e) {
            if (!BX.Sale.OrderAjaxComponent.startLoader())
                return;
            __this.pvzPopup.close();
            var objectId = e.get('objectId'),
                obj = objectManager.objects.getById(objectId);
            __this.pvzAddress = obj.properties.deliveryName + ': ' + obj.properties.fullAddress;
            __this.pvzFullAddress = obj.properties.deliveryName + ': ' + obj.properties.fullAddress + ' #' + obj.properties.code_pvz;

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
                    reqData.price = parseInt(result) || 0;
                    reqData.address = obj.properties.deliveryName + ': ' + obj.properties.fullAddress + ' #' + obj.properties.code_pvz || '';
                    __this.sendRequestToComponent('refreshOrderAjax', reqData);
                }, this),
                onfailure: BX.delegate(function () {
                    BX.Sale.OrderAjaxComponent.showError(BX.Sale.OrderAjaxComponent.mainErrorsNode, 'Ошибка запроса стоимости доставки!');
                    console.warn('error get price delivery');
                }),
            });
        });
    },

    sendRequestToComponent: function (action, actionData) {
        var __this = this;

        BX.ajax({
            method: 'POST',
            dataType: 'json',
            url: BX.Sale.OrderAjaxComponent.ajaxUrl,
            data: this.getDataForPVZ(action, actionData),
            onsuccess: BX.delegate(function (result) {
                if (action === 'refreshOrderAjax') {
                    BX.Sale.OrderAjaxComponent.refreshOrder(result);
                }
                // TODO - неправильно что по определенным номерам св-в
                BX('pvz_address').innerHTML = __this.pvzFullAddress;
                if (BX('soa-property-7')) {
                    BX('soa-property-7').value = __this.pvzFullAddress;
                    BX.addClass(BX('soa-property-7'), 'disabled');
                    BX("soa-property-7").setAttribute("readonly","readonly");
                }
                if (BX('soa-property-19')) {
                    BX('soa-property-19').value = __this.pvzFullAddress;
                    BX.addClass(BX('soa-property-19'), 'disabled');
                    BX("soa-property-19").setAttribute("readonly","readonly");
                }

                BX.Sale.OrderAjaxComponent.endLoader();
            }, this),
            onfailure: BX.delegate(function () {
                console.warn('error sendRequestToComponent');
                BX.Sale.OrderAjaxComponent.endLoader();
            }, this)
        });
    },

    getDataForPVZ: function (action, actionData) {
        var data = {
            order: BX.Sale.OrderAjaxComponent.getAllFormData(),
            sessid: BX.bitrix_sessid(),
            via_ajax: 'Y',
            SITE_ID: BX.Sale.OrderAjaxComponent.siteId,
            signedParamsString: BX.Sale.OrderAjaxComponent.signedParamsString,
            price: actionData.price,
            address: actionData.address
        };

        data[BX.Sale.OrderAjaxComponent.params.ACTION_VARIABLE] = action;

        return data;
    }
};

window.addEventListener('load', function () {
    BX.SaleCommonPVZ.init();
});
