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
    pvzPrice: null,
    isInit: false,
    dataPVZ: null,
    objectManager: null,

    init: function (params) {
        console.log('... CommonPVZ init ...');
        this.params = params.params;
        this.refresh();
        this.isInit = true;

        $(document).on("click", "#commmon_pvz_select_point", function() {
            console.log(this)
        });
    },

    openMap: function () {
        this.createPVZPopup();
        this.buildPVZMap();
        this.pvzPopup.show();
    },

    refresh: function () {
        var __this = this;
        var adr = $('[name="ORDER_PROP_' + __this.params.arPropsAddr[1] + '"]') || $('[name="ORDER_PROP_' + __this.params.arPropsAddr[0] + '"]');
        if (__this.pvzFullAddress) {
            adr.val(__this.pvzFullAddress);
            $('#pvz_address').html('Вы выбрали: <span>' + __this.pvzAddress + '</span>');
        }
        adr.attr('readonly', 'readonly');

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
        var __this = this;
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
                        if (__this.propsMap)
                            __this.propsMap.destroy();
                    }
                },
                closeByEsc: true
            });
    },

    /**
     *   Построение карты с PVZ
     */
    buildPVZMap: function () {
        var __this = this;

        ymaps.ready(function () {
            var myGeocoder = ymaps.geocode(__this.curCityName, {results: 1});
            myGeocoder.then(function (res) { // получаем координаты
                var firstGeoObject = res.geoObjects.get(0),
                    coords = firstGeoObject.geometry.getCoordinates();

                __this.propsMap = new ymaps.Map('map_for_pvz', {
                    center: [coords[0], coords[1]],
                    zoom: 12,
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
                BX.Sale.OrderAjaxComponent.showError(BX('bx-soa-delivery'), 'Ошибка запроса ПВЗ. Попробуйте позже.');
                __this.pvzPopup.close();

            }
        });
    },

    getPointData: function (point) {
        this.pvzAddress = point.properties.deliveryName + ': ' + point.properties.fullAddress;
        if (typeof point.properties.code_pvz !== 'undefined') {
            this.pvzFullAddress = point.properties.deliveryName + ': ' + point.properties.fullAddress + ' #' + point.properties.code_pvz;
        }
        else {
            this.pvzFullAddress = point.properties.deliveryName + ': ' + point.properties.fullAddress;
        }

        var dataToHandler = {
            action: 'getPrice',
            code_city: this.curCityCode,
            delivery: point.properties.deliveryName,
            to: point.properties.fullAddress,
            weight: BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_WEIGHT,
            fivepost_zone: point.properties.fivepostZone,
            hubregion: point.properties.hubregion,
            name_city: this.curCityName,
            code_pvz: point.properties.code_pvz
        };

        return dataToHandler;
    },

    selectPvz: function (objectId) {

        const __this = this
        if (!BX.Sale.OrderAjaxComponent.startLoader())
            return;

        __this.pvzPopup.close();

        var obj = this.objectManager.objects.getById(objectId);
        var dataToHandler = this.getPointData(obj);

        __this.refresh();
        __this.sendRequestToComponent('refreshOrderAjax', dataToHandler);
    },

    getSelectPvzPrice: function (point) {
        var dataToHandler = this.getPointData(point);

        const __this = this;
        BX.ajax({
            url: __this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                'dataToHandler': dataToHandler,
                'action': 'getPVZPrice'
            },
            onsuccess: function (res) {
                point.properties = {
                    ...point.properties,
                    balloonContent: `
                        <div>Цена: ${res}</div> 
                        <a href="javascript:void(0)" onclick="BX.SaleCommonPVZ.selectPvz(${point.id})" >Выбрать</a>
                    `
                };
                __this.objectManager.objects.balloon.setData(point);
            },
            onfailure: function (res) {
                console.log('error getPVZList');
                BX.Sale.OrderAjaxComponent.endLoader();
                BX.Sale.OrderAjaxComponent.showError(BX('bx-soa-delivery'), 'Ошибка запроса ПВЗ. Попробуйте позже.');
                __this.pvzPopup.close();

            }
        });
    },

    /**
     *  Установка маркеров на карту PVZ
     */
    setPVZOnMap: function () {
        var objectManager = new ymaps.ObjectManager({
            clusterize: true,
            clusterHasBalloon: true
        });
        objectManager.add(this.pvzObj);
        this.objectManager = objectManager;
        var __this = this;

        __this.propsMap.geoObjects.add(objectManager);
        BX.Sale.OrderAjaxComponent.endLoader();

        objectManager.clusters.events.add(['balloonopen'], function (e){
            const clusterId = e.get('objectId');
            const cluster = objectManager.clusters.getById(clusterId);
            if (objectManager.clusters.balloon.isOpen(clusterId)) {
                console.log('is open')
            }

            cluster.features = cluster.features.map((feature)=> {
                // feature.properties.set('balloonContent', "Идет загрузка данных...");
                const y = 2;
            });

            objectManager.objects.balloon.close();

            const t = 1;
        });

        objectManager.objects.events.add(['balloonopen'], function (e) {
            var objectId = e.get('objectId'),
                object = objectManager.objects.getById(objectId);

            if (objectManager.objects.balloon.isOpen(objectId)) {
                __this.getSelectPvzPrice(object);
            }
        });
    },

    sendRequestToComponent: function (action, actionData) {
        BX.ajax({
            method: 'POST',
            dataType: 'json',
            url: BX.Sale.OrderAjaxComponent.ajaxUrl,
            data: this.getDataForPVZ(action, actionData),
            onsuccess: BX.delegate(function (result) {
                if (action === 'refreshOrderAjax') {
                    if (actionData.error) {
                        result.error = actionData.error;
                    }
                    BX.Sale.OrderAjaxComponent.refreshOrder(result);
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
            dataToHandler: actionData
        };

        data[BX.Sale.OrderAjaxComponent.params.ACTION_VARIABLE] = action;

        return data;
    }
};


