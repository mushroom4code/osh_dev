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

    init: function (params) {
        this.propAddressId = params.params?.propAddress;
        this.propCommonPVZId = params.params?.propCommonPVZ;
        this.propTypeDeliveryId = params.params?.propTypeDelivery;

        this.refresh();
        this.isInit = true;
    },

    update: function (ajaxAns) {
        if (Object.keys(ajaxAns).indexOf("order") !== -1) {
            BX.SaleCommonPVZ.propAddressId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'ADDRESS')?.ID;
            BX.SaleCommonPVZ.propCommonPVZId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'COMMON_PVZ')?.ID;
            BX.SaleCommonPVZ.propTypeDeliveryId = ajaxAns.order.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_DELIVERY')?.ID;
            BX.SaleCommonPVZ.refresh();
        }
    },

    openMap: function () {
        this.createPVZPopup();
        this.buildPVZMap();
        this.pvzPopup.show();
    },

    refresh: function () {
        const __this = this
        if (this.propCommonPVZId) {

            if (this.propAddressId ) {
                const address = document.querySelector('[name="ORDER_PROP_' + this.propAddressId + '"]');
                if (address) {
                    address.readOnly = true;
                }
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
                }
            });
        } else {
            console.log('Property common PVZ not defined')
        }
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


