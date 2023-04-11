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
    propAddressId: null,
    propCommonPVZId: null,
    propTypeDeliveryId: null,
    propZipId: null,
    propCityId: null,
    propFiasId: null,
    propKladrId: null,
    propStreetKladrId: null,
    propLatitudeId: null,
    propLongitudeId: null,
    propDateDelivery: null,
    propDeliveryTimeInterval: null,
    curDeliveryId: null,
    doorDeliveryId: null,
    shipmentCost: null,
    orderPackages: null,
    oshishaDeliveryOptions: null,


    init: function (params) {
        this.propAddressId = params.params?.propAddress;
        this.propCommonPVZId = params.params?.propCommonPVZ;
        this.propTypeDeliveryId = params.params?.propTypeDelivery;
        this.propZipId = params.params?.propZip;
        this.propCityId = params.params?.propCity;
        this.propFiasId = params.params?.propFias;
        this.propKladrId = params.params?.propKladr;
        this.propStreetKladrId = params.params?.propStreetKladr;
        this.propLatitudeId = params.params?.propLatitude;
        this.propLongitudeId = params.params?.propLongitude;
        this.propDateDelivery = params.params?.propDateDelivery;
        this.propDeliveryTimeInterval = params.params?.propDateTimeInterval;
        this.curDeliveryId = params.params?.curDeliveryId;
        this.doorDeliveryId = params.params?.doorDeliveryId;
        this.shipmentCost = params.params?.shipmentCost;
        this.orderPackages = params.params?.packages;
        this.oshishaDeliveryOptions = params.params?.deliveryOptions;

        this.refresh();
        this.isInit = true;
    },

    update: function (ajaxAns) {
        if (Object.keys(ajaxAns).indexOf("order") !== -1) {
            BX.SaleCommonPVZ.propAddressId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'ADDRESS')?.ID;
            BX.SaleCommonPVZ.propCommonPVZId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'COMMON_PVZ')?.ID;
            BX.SaleCommonPVZ.propTypeDeliveryId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_DELIVERY')?.ID;
            BX.SaleCommonPVZ.propZipId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'ZIP')?.ID;
            BX.SaleCommonPVZ.propCityId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'CITY')?.ID;
            BX.SaleCommonPVZ.propFiasId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'FIAS')?.ID;
            BX.SaleCommonPVZ.propKladrId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'KLADR')?.ID;
            BX.SaleCommonPVZ.propStreetKladrId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'STREET_KLADR')?.ID;
            BX.SaleCommonPVZ.propLatitudeId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'LATITUDE')?.ID;
            BX.SaleCommonPVZ.propLongitudeId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'LONGITUDE')?.ID;
            BX.SaleCommonPVZ.propDateDelivery = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'DATE_DELIVERY')?.ID;
            BX.SaleCommonPVZ.propDeliveryTimeInterval = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'DELIVERYTIME_INTERVAL')?.ID;
            BX.SaleCommonPVZ.curDeliveryId = ajaxAns.order.DELIVERY.find(field => field.CHECKED === 'Y')?.ID;

            BX.SaleCommonPVZ.refresh();
        }
    },

    openMap: function () {
        this.createPVZPopup();
        this.buildPVZMap();
        this.pvzPopup.show();
    },

    refresh: function () {
        const __this = this;
        if (this.propAddressId) {
            var addressField = $(document).find('[name="ORDER_PROP_' + this.propAddressId + '"]');
            if (this.oshishaDeliveryOptions.DA_DATA_TOKEN !== undefined && !addressField.hasClass('suggestions-input')) {
                addressField.suggestions({
                    token: this.oshishaDeliveryOptions.DA_DATA_TOKEN,
                    type: "ADDRESS",
                    hint: false,
                    floating: true,
                    onSelect: function (suggestion) {
                        (this.propZipId) ? (document.querySelector('input[name="ORDER_PROP_' + this.propZipId + '"]').value = suggestion.data.postal_code) : '';
                        (this.propCityId) ? (document.querySelector('input[name="ORDER_PROP_' + this.propCityId + '"]').value = suggestion.data.city) : '';
                        (this.propFiasId) ? (document.querySelector('input[name="ORDER_PROP_' + this.propFiasId + '"]').value = suggestion.data.fias_id) : '';
                        (this.propKladrId) ? (document.querySelector('input[name="ORDER_PROP_' + this.propKladrId + '"]').value = suggestion.data.kladr_id) : '';
                        (this.propStreetKladrId) ? (document.querySelector('input[name="ORDER_PROP_' + this.propStreetKladrId + '"]').value = suggestion.data.street_kladr_id) : '';
                        (this.propLatitudeId) ? (document.querySelector('input[name="ORDER_PROP_' + this.propLatitudeId + '"]').value = suggestion.data.geo_lat) : '';
                        (this.propLongitudeId) ? (document.querySelector('input[name="ORDER_PROP_' + this.propLongitudeId + '"]').value = suggestion.data.geo_lon) : '';
                        BX.onCustomEvent('onDeliveryExtraServiceValueChange');
                    }.bind(this)
                });
                if (this.curCityName) {
                    if (this.curCityName == 'Москва') {
                        $(document).find('[name="ORDER_PROP_' + __this.propAddressId + '"]').suggestions().setOptions({
                            constraints: {
                                locations: [{region: "Московская"}, {region: "Москва"}]
                            }
                        });
                    } else {
                        addressField.suggestions().setOptions({
                            constraints: {
                                locations: [{city: this.curCityName}]
                            }
                        });
                    }
                }
            }
        } else {
            alert('Свойство адреса не найдено');
        }

        if (this.propCommonPVZId ) {
            const commonPVZ = document.querySelector('[name="ORDER_PROP_' + this.propCommonPVZId + '"]');
            if (commonPVZ) {
                commonPVZ.readOnly = true;
            }
        }

        BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.forEach(function (item, index, array) {
            if (item.IS_LOCATION === 'Y') {
                if (__this.curCityCode !== item.VALUE[0]) {
                    __this.curCityCode = item.VALUE[0];
                    __this.propsMap = null;
                    __this.getCityName();
                }
                __this.checkMoscowOrNot();
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
                if (__this.propAddressId) {
                    if (__this.curCityName == 'Москва') {
                        $(document).find('[name="ORDER_PROP_' + __this.propAddressId + '"]').suggestions().setOptions({
                            constraints: {
                                locations: [{region: "Московская"}, {region: "Москва"}]
                            }
                        });
                    } else {
                        $(document).find('[name="ORDER_PROP_' + __this.propAddressId + '"]').suggestions().setOptions({
                            constraints: {
                                locations: [{city: __this.curCityName}]
                            }
                        });
                    }
                }
            },
            onfailure: function (res) {
                console.log('error getCityName');
            }
        });
    },

    checkMoscowOrNot: function() {
        var __this = this;
        BX.ajax({
            url: __this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                codeCity: __this.curCityCode,
                'action': 'checkMoscowOrNot'
            },
            onsuccess: function (res) {
                __this.isMoscow = res;
                if (__this.isMoscow == '1') {
                    if (__this.curDeliveryId == __this.doorDeliveryId) {
                        if (__this.propDateDelivery) {
                            document.querySelector('input[name="ORDER_PROP_' + __this.propDateDelivery + '"]').removeAttribute('disabled');
                            document.querySelector('div[data-property-id-row="' + __this.propDateDelivery + '"]').classList.remove('d-none');
                        }
                        if (__this.propDeliveryTimeInterval) {
                            document.querySelector('select[name="ORDER_PROP_' + __this.propDeliveryTimeInterval + '"]').disabled = false;
                            document.querySelector('div[data-property-id-row="' + __this.propDeliveryTimeInterval + '"]').classList.remove('d-none');
                        }

                        var propsNode = document.querySelector('div.delivery.bx-soa-pp-company.bx-selected');
                        window.Osh.bxPopup.init();
                        var oshMkad = window.Osh.oshMkadDistance.init(__this.oshishaDeliveryOptions);

                        if (!document.querySelector('#oshMapButton')) {
                            var propContainer = BX.create('DIV', {props: {id: 'oshMapButton', className: 'soa-property-container'}});
                            var nodeOpenMap = BX.create('a',
                                {
                                    props: {className: 'btn btn-primary'},
                                    text: 'Выбрать адрес на карте (Oshisha)',
                                    events: {
                                        click: BX.proxy(function () {
                                            oshMkad.afterSave = function (address) {
                                                __this.oshishaDeliveryOptions.DA_DATA_ADDRESS = address;
                                            }.bind(this);
                                            window.Osh.bxPopup.onPickerClick(this)
                                        }, this)
                                    }
                                });
                            propContainer.append(nodeOpenMap);
                            propsNode.append(propContainer);
                        }
                    }
                    $(document).find('[name="ORDER_PROP_' + __this.propAddressId + '"]').suggestions().setOptions({
                            onSelect: function (suggestion) {
                                (__this.propZipId) ? (document.querySelector('input[name="ORDER_PROP_' + __this.propZipId + '"]').value = suggestion.data.postal_code) : '';
                                (__this.propCityId) ? (document.querySelector('input[name="ORDER_PROP_' + __this.propCityId + '"]').value = suggestion.data.city) : '';
                                (__this.propFiasId) ? (document.querySelector('input[name="ORDER_PROP_' + __this.propFiasId + '"]').value = suggestion.data.fias_id) : '';
                                (__this.propKladrId) ? (document.querySelector('input[name="ORDER_PROP_' + __this.propKladrId + '"]').value = suggestion.data.kladr_id) : '';
                                (__this.propStreetKladrId) ? (document.querySelector('input[name="ORDER_PROP_' + __this.propStreetKladrId + '"]').value = suggestion.data.street_kladr_id) : '';
                                (__this.propLatitudeId) ? (document.querySelector('input[name="ORDER_PROP_' + __this.propLatitudeId + '"]').value = suggestion.data.geo_lat) : '';
                                (__this.propLongitudeId) ? (document.querySelector('input[name="ORDER_PROP_' + __this.propLongitudeId + '"]').value = suggestion.data.geo_lon) : '';
                                if (suggestion.data.geo_lat !== undefined && suggestion.data.geo_lon !== undefined) {
                                    if (__this.curDeliveryId == __this.doorDeliveryId) {
                                        __this.oshishaDeliveryOptions.DA_DATA_ADDRESS = suggestion.value;
                                        oshMkad.afterSave = null;
                                        oshMkad.getDistance([suggestion.data.geo_lat, suggestion.data.geo_lon], true);
                                    }
                                }
                                BX.onCustomEvent('onDeliveryExtraServiceValueChange');
                            }
                        });
                } else {
                    if (__this.propDateDelivery) {
                        document.querySelector('input[name="ORDER_PROP_' + __this.propDateDelivery + '"]').setAttribute('disabled', '');
                        document.querySelector('div[data-property-id-row="' + __this.propDateDelivery + '"]').classList.add('d-none');
                    }
                    if (__this.propDeliveryTimeInterval) {
                        document.querySelector('select[name="ORDER_PROP_' + __this.propDeliveryTimeInterval + '"]').disabled = true;
                        document.querySelector('div[data-property-id-row="' + __this.propDeliveryTimeInterval + '"]').classList.add('d-none');
                    }
                }
            },
            onfailure: function (res) {
                console.log('error checkMoscowOrNot');
            }
        });
    },

    getPVZList: function () {
        const __this = this;
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

    /**
     *
     * @param point
     * @returns {{delivery: *, fivepost_zone: *, code_city: null, hubregion, action: string, weight: *, code_pvz, id, to: *, name_city: null}}
     */
    getPointData: function (point) {
        return {
            id: point.id,
            action: 'getPrice',
            code_city: this.curCityCode,
            delivery: point.properties.deliveryName,
            to: point.properties.fullAddress,
            weight: BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_WEIGHT,
            cost: this.shipmentCost,
            packages: this.orderPackages,
            street_kladr: point.properties.street_kladr ?? '',
            latitude: point.geometry.coordinates[0],
            longitude: point.geometry.coordinates[1],
            fivepost_zone: point.properties.fivepostZone,
            hubregion: point.properties.hubregion,
            name_city: this.curCityName,
            code_pvz: point.properties.code_pvz
        };
    },

    selectPvz: function (objectId) {
        const __this = this
        if (!BX.Sale.OrderAjaxComponent.startLoader())
            return;

        __this.pvzPopup.close();
        const point = this.objectManager.objects.getById(objectId);
        __this.pvzAddress = point.properties.deliveryName + ': ' + point.properties.fullAddress;
        this.pvzFullAddress = typeof point.properties.code_pvz !== 'undefined'
            ? point.properties.deliveryName + ': ' + point.properties.fullAddress + ' #' + point.properties.code_pvz
            : point.properties.deliveryName + ': ' + point.properties.fullAddress;

        if (this.propCommonPVZId) {
            const commonPVZ = document.querySelector('[name="ORDER_PROP_' + this.propCommonPVZId + '"]');
            if (commonPVZ) {
                commonPVZ.value = this.pvzFullAddress;
                BX('pvz_address').innerHTML = 'Вы выбрали: <span>' + this.pvzAddress + '</span>';
            }
        }

        if (this.propAddressId ) {
            const address = document.querySelector('[name="ORDER_PROP_' + this.propAddressId + '"]');
            if (address) {
                address.value = this.pvzFullAddress;
            }
        }

        if (this.propTypeDeliveryId ) {
            const typeDelivery = document.querySelector('[name="ORDER_PROP_' + this.propTypeDeliveryId + '"]');
            if (typeDelivery) {
                typeDelivery.value = point.properties.deliveryName
            }
        }

        const dataToHandler = this.getPointData(point);
        __this.sendRequestToComponent('refreshOrderAjax', dataToHandler);
    },

    /**
     *
     * @param points
     * @param clusterId
     */
    getSelectPvzPrice: function (points, clusterId=undefined) {
        const __this = this;
        const data = points.reduce((result, point) => {
            if (!point.properties.balloonContent) {
                point.properties.balloonContent = "Идет загрузка данных...";
                if (clusterId===undefined) {
                    __this.objectManager.objects.balloon.setData(point);
                }
                return result.concat (this.getPointData(point))
            }
            return result;
        }, [])

        if (data.length === 0)
            return;

        if (clusterId !== undefined && __this.objectManager.clusters.balloon.isOpen(clusterId)) {
            __this.objectManager.clusters.balloon.setData(__this.objectManager.clusters.balloon.getData());
        }

        BX.ajax({
            url: __this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                'dataToHandler': data,
                'action': 'getPVZPrice'
            },
            dataType: 'json',
            onsuccess: function (res) {
                if (res?.status === 'success') {
                    res.data.forEach(item => {
                        const point = __this.objectManager.objects.getById(item.id)
                        const balloonContent = "".concat(
                            `<div><b>${point.properties?.type === "POSTAMAT" ? 'Постомат' : 'ПВЗ' } - ${item.price} руб.</b></div>`,
                            `<div>${point.properties.fullAddress}</div>`,
                            point.properties.phone  ? `<div>${point.properties.phone}</div>` : '',
                            point.properties.workTime  ? `<div>${point.properties.workTime}</div>` : '',
                            point.properties.comment ? `<div><i>${point.properties.comment}</i></div>` : '',
                            `<a class="btn btn_basket mt-2" href="javascript:void(0)" onclick="BX.SaleCommonPVZ.selectPvz(${item.id})" >Выбрать</a>`
                        )
                        point.properties = {
                            ...point.properties,
                            balloonContent: balloonContent,
                        };
                        if (clusterId===undefined) {
                            __this.objectManager.objects.balloon.setData(point);
                        }
                    })
                    if (clusterId !== undefined && __this.objectManager.clusters.balloon.isOpen(clusterId)) {
                        __this.objectManager.clusters.balloon.setData(__this.objectManager.clusters.balloon.getData());
                    }
                }
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
        const osh_pvz = objectManager.objects.getAll()
            .find((item) => item?.properties?.deliveryName==='OSHISHA');

        if (osh_pvz) {
            const button = new ymaps.control.Button({
                data: {
                    image: 'images/button.jpg',
                    content: 'Пукнт выдачи OSHISHA',
                    title: 'Показать на карте пункт выдачи'
                },
                options: {
                    selectOnClick: false,
                    maxWidth: [230, 230, 230]
                }
            });
            button.events.add('click', () => {
                __this.propsMap.setZoom(15)
                objectManager.objects.balloon.open(osh_pvz.id);
            })
            __this.propsMap.controls.add(button, { float: 'right', floatIndex: 100 });
        }
        BX.Sale.OrderAjaxComponent.endLoader();

        objectManager.clusters.events.add(['balloonopen'], function (e){
            const clusterId = e.get('objectId');
            const cluster = objectManager.clusters.getById(clusterId);
            if (objectManager.clusters.balloon.isOpen(clusterId)) {
                __this.getSelectPvzPrice(cluster.properties.geoObjects, clusterId);
            }
        });

        objectManager.objects.events.add('click', function (e) {
            var objectId = e.get('objectId')
            objectManager.objects.balloon.open(objectId);
        });

        objectManager.objects.events.add('balloonopen', function (e) {
            var objectId = e.get('objectId'),
                obj = objectManager.objects.getById(objectId);

            __this.getSelectPvzPrice([obj]);
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


