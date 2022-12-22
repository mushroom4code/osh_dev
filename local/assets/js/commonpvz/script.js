BX.namespace('BX.SaleCommonPVZ');


BX.SaleCommonPVZ = {

    pvzPopup: null,
    curCityCode: null,
    isGetPVZ: false,
    ajaxUrlPVZ: null,

    init: function (params) {
        console.log('SaleCommonPVZ init');
        console.dir(ymaps);
        this.ajaxUrlPVZ = params.ajaxUrlPVZ;
    },

    openMap: function () {
        console.log('open');
        BX.Sale.OrderAjaxComponent.sendRequest('loadPVZMap');
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
        this.createPVZPopup();

        var __this = this;
        var myGeocoder = ymaps.geocode(__this.curLocation.name_city, {results: 1});
        myGeocoder.then(function (res) { // получаем координаты
            var firstGeoObject = res.geoObjects.get(0),
                coords = firstGeoObject.geometry.getCoordinates();
            __this.curLocation['lat'] = coords[0];
            __this.curLocation['lon'] = coords[1];

            __this.propsMap = new ymaps.Map("map_for_pvz", {
                center: [__this.curLocation['lat'], __this.curLocation['lon']],
                zoom: 10,
                controls: ['fullscreenControl']
            });
            __this.setPVZOnMap(__this.propsMap);

        }).catch(function (e) {
            __this.showError(__this.mainErrorsNode, 'Ошибка построения карты ПВЗ!');
            console.warn(e)
        });
    },

    getPVZ: function () {
        if (this.isGetPVZ) return;
        this.isGetPVZ = true;
        var __this = this;

        // Получаем код местопожения
        BX.Sale.OrderAjaxComponent.ORDER_PROP.properties.forEach(function (item, index, array) {
            if (item.IS_LOCATION === 'Y') {
                __this.curCityCode = item.VALUE[0];
            }
        });

        BX.ajax({
            url: __this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                code_city: __this.curLocation.curCityCode,
                'soa-action': 'getCityName'
            },
            onsuccess: BX.delegate(function (result) {
                result = JSON.parse(result) || [];
                if (result['city']) {
                    __this.curLocation.name_city = result['city'];
                    delete result['city'];
                }
                if (result['Errors']) {
                    console.warn(result['Errors']);
                    delete result['Errors'];
                }
                __this.pvzObj = result;
                __this.buildPVZMap();
            }, this),
            onfailure: BX.delegate(function () {
                __this.showError(__this.mainErrorsNode, 'Ошибка запроса Пунктов Выдачи Заказа!');
                console.log('error getPVZ')
            }),
        })
    },

};
