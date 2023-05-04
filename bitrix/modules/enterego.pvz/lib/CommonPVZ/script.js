BX.namespace('BX.SaleCommonPVZ');

BX.SaleCommonPVZ = {

    pvzPopup: null,
    curCityCode: null,
    curCityName: null,
    isGetPVZ: false,
    ajaxUrlPVZ: '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php',
    propsMap: null,
    pvzObj: null,
    pvzPrice: null,
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
    pvzDeliveryId: null,
    shipmentCost: null,
    orderPackages: null,
    oshishaDeliveryOptions: null,
    oshishaDeliveryStatus: false,
    // deliveryBlock: BX('bx-soa-delivery'),

    init: function (params) {
        const order = BX.Sale.OrderAjaxComponent.result

        this.propAddressId            = order.ORDER_PROP.properties.find(prop => prop.CODE === 'ADDRESS')?.ID;
        this.propCommonPVZId          = order.ORDER_PROP.properties.find(prop => prop.CODE === 'COMMON_PVZ')?.ID;
        this.propTypeDeliveryId       = order.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_DELIVERY')?.ID;
        this.propZipId                = order.ORDER_PROP.properties.find(prop => prop.CODE === 'ZIP')?.ID;
        this.propCityId               = order.ORDER_PROP.properties.find(prop => prop.CODE === 'CITY')?.ID;
        this.propFiasId               = order.ORDER_PROP.properties.find(prop => prop.CODE === 'FIAS')?.ID;
        this.propKladrId              = order.ORDER_PROP.properties.find(prop => prop.CODE === 'KLADR')?.ID;
        this.propStreetKladrId        = order.ORDER_PROP.properties.find(prop => prop.CODE === 'STREET_KLADR')?.ID;
        this.propLatitudeId           = order.ORDER_PROP.properties.find(prop => prop.CODE === 'LATITUDE')?.ID;
        this.propLongitudeId           = order.ORDER_PROP.properties.find(prop => prop.CODE === 'LONGITUDE')?.ID;
        this.propDateDelivery          = order.ORDER_PROP.properties.find(prop => prop.CODE === 'DATE_DELIVERY')?.ID;
        this.propDeliveryTimeInterval = order.ORDER_PROP.properties.find(prop => prop.CODE === 'DELIVERYTIME_INTERVAL')?.ID;

        this.curDeliveryId = params.params?.curDeliveryId;
        this.doorDeliveryId = params.params?.doorDeliveryId;
        this.shipmentCost = params.params?.shipmentCost;
        this.orderPackages = params.params?.packages;
        this.oshishaDeliveryOptions = params.params?.deliveryOptions;

        this.updateDelivery(BX.Sale.OrderAjaxComponent.result)
        this.refresh()

        this.drawInterface()

        if (this.propAddressId  && this.oshishaDeliveryStatus) {
            window.Osh.bxPopup.init();
            const oshMkad = window.Osh.oshMkadDistance.init(this.oshishaDeliveryOptions);

            const latitude_value = (this.propLatitudeId)
                ? (document.querySelector('input[name="ORDER_PROP_' + this.propLatitudeId + '"]').value) : '';
            const longitude_value = (this.propLongitudeId)
                ? (document.querySelector('input[name="ORDER_PROP_' + this.propLongitudeId + '"]').value) : '';
            if (latitude_value && longitude_value) {
                const oshParams = {
                    oshMkad: oshMkad,
                    latitude: latitude_value,
                    longitude: longitude_value,
                    propAddressId: this.propAddressId,
                    propDateDelivery: (this.propDateDelivery)
                        ? this.propDateDelivery
                        : '',
                }
                setTimeout(function (oshParams) {
                    oshParams.oshMkad.afterSave = null;
                    oshParams.oshMkad.getDistance([oshParams.latitude, oshParams.longitude],
                        oshParams.propAddressId,
                        oshParams.propDateDelivery,
                        '',
                        true);
                }, 500, oshParams);
            }
        }
    },

    refresh: function () {
        const __this = this;
        if (this.propAddressId) {

            window.Osh.bxPopup.init();
            const oshMkad = window.Osh.oshMkadDistance.init(this.oshishaDeliveryOptions);

            const addressField = $(document).find('[name="ORDER_PROP_' + this.propAddressId + '"]');
            if (this.oshishaDeliveryOptions.DA_DATA_TOKEN && !addressField.hasClass('suggestions-input')) {
                addressField.suggestions({
                    token: this.oshishaDeliveryOptions.DA_DATA_TOKEN,
                    type: "ADDRESS",
                    hint: false,
                    floating: true,
                    triggerSelectOnEnter: true,
                    autoSelectFirst: true,
                    onSelect: function (suggestion) {
                        this.updatePropsFromDaData(suggestion)

                        if (suggestion.data.geo_lat !== undefined && suggestion.data.geo_lon !== undefined) {
                            if (__this.curDeliveryId == __this.doorDeliveryId && __this.oshishaDeliveryStatus) {
                                __this.oshishaDeliveryOptions.DA_DATA_ADDRESS = suggestion.value;
                                oshMkad.afterSave = null;
                                oshMkad.getDistance([suggestion.data.geo_lat, suggestion.data.geo_lon],
                                    __this.propAddressId,
                                    (__this.propDateDelivery)
                                        ? __this.propDateDelivery
                                        : '',
                                    (__this.propDateDelivery)
                                        ? (document.querySelector('input[name="ORDER_PROP_' + __this.propDateDelivery + '"]').value)
                                        : '',
                                    true);
                            }
                        }

                        BX.onCustomEvent('onDeliveryExtraServiceValueChange');
                    }.bind(this),
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
            }
        });
    },

    update: function (ajaxAns) {
        if (Object.keys(ajaxAns).indexOf("order") !== -1) {
            BX.SaleCommonPVZ.curDeliveryId = ajaxAns.order.DELIVERY.find(field => field.CHECKED === 'Y')?.ID;
            BX.SaleCommonPVZ.refresh();
            BX.SaleCommonPVZ.updateDelivery(ajaxAns.order)
        }
    },

    /**
     *
     * @param orderData array
     */
    updateDelivery: function (orderData) {
        const propsNode = document.querySelector('div.delivery.bx-soa-pp-company.bx-selected .bx-soa-pp-company');
        BX.cleanNode(propsNode)
        const doorDelivery = orderData.DELIVERY.find(delivery => delivery.ID === this.doorDeliveryId && delivery.CHECKED === 'Y')
        const checkedDelivery = orderData.DELIVERY.find(delivery => delivery.CHECKED === 'Y')
        if (doorDelivery !== undefined) {
            const deliveryInfo = JSON.parse(doorDelivery.CALCULATE_DESCRIPTION)
            deliveryInfo.forEach(delivery => {
                const propContainer = BX.create(
                    'DIV',
                    {
                        props: {
                            className: 'bx-soa-pp-company-graf-container  box_with_delivery mb-3'
                        },
                        children: [
                            BX.create('INPUT', {
                                attrs: {checked: delivery.checked},
                                props: {
                                    name: `ORDER_PROP_${this.propTypeDeliveryId}`,
                                    value: delivery.name,
                                    type: 'radio',

                                },
                                events: {click: () => BX.Sale.OrderAjaxComponent.sendRequest()},
                            }),
                            BX.create('DIV', {
                                props: {
                                    className: 'bx-soa-pp-company-smalltitle color_black font_weight_600',
                                },
                                html: `${delivery.name} - ${delivery.price}`
                            })
                        ]
                    },
                )

                propsNode.append(propContainer);
                if (delivery.code === 'oshisha') {
                    this.updateOshishaDelivery(propsNode)
                }
            })
        } else {
            const propContainer = BX.create('DIV', {
                props: {className: 'bx-soa-pp-company-block'},
                children: [
                    BX.create('DIV', {props: {className: 'bx-soa-pp-company-desc'}, html: checkedDelivery.DESCRIPTION}),
                    checkedDelivery.CALCULATE_DESCRIPTION
                        ? BX.create('DIV', {
                            props: {className: 'bx-soa-pp-company-desc'},
                            html: checkedDelivery.CALCULATE_DESCRIPTION
                        })
                        : null
                ]
            });
            propsNode.append(propContainer);
        }
    },

    updateOshishaDelivery: function(parentBlock) {
        var __this = this;

        __this.oshishaDeliveryStatus = true

        if (__this.propDateDelivery) {
            document.querySelector('input[name="ORDER_PROP_' + __this.propDateDelivery + '"]').removeAttribute('disabled');
            document.querySelector('div[data-property-id-row="' + __this.propDateDelivery + '"]').classList.remove('d-none');
        }
        if (__this.propDeliveryTimeInterval) {
            document.querySelector('select[name="ORDER_PROP_' + __this.propDeliveryTimeInterval + '"]').disabled = false;
            document.querySelector('div[data-property-id-row="' + __this.propDeliveryTimeInterval + '"]').classList.remove('d-none');
        }

        window.Osh.bxPopup.init();
        const oshMkad = window.Osh.oshMkadDistance.init(__this.oshishaDeliveryOptions);
        const propContainer = BX.create('DIV', {
            props: {id: 'oshMapButton', className: 'soa-property-container'},
            children: [
                BX.create('a',
                    {
                        props: {className: 'btn btn_red sbtn-primary '},
                        text: 'Выбрать адрес на карте (Oshisha)',
                        events: {
                            click: BX.proxy(function () {
                                oshMkad.afterSave = function (address) {
                                    __this.oshishaDeliveryOptions.DA_DATA_ADDRESS = address;
                                }.bind(this);
                                window.Osh.bxPopup.onPickerClick(
                                    (__this.propAddressId)
                                        ? __this.propAddressId
                                        : '',
                                    (__this.propDateDelivery)
                                        ? __this.propDateDelivery
                                        : '',
                                    (__this.propDateDelivery)
                                        ? (document.querySelector('input[name="ORDER_PROP_' + __this.propDateDelivery + '"]').value)
                                        : ''
                                );
                            }, this)
                        }
                    })
            ]
        });
        parentBlock.append(propContainer);
    },

    openMap: function () {
        // this.createPVZPopup();
        this.createPVZPopup1();
        // this.bufildPVZMap();
        this.buildPVZMap1();
        // this.pvzPopup.show();
        BX.show(this.pvzOverlay);
    },

    /**
     * Fill props from DADATA suggestion
     * @param suggestion
     */
    updatePropsFromDaData: function (suggestion) {
        this.updateValueProp(this.propZipId, suggestion?.data?.postal_code ?? '')
        this.updateValueProp(this.propCityId, suggestion?.data?.city ?? '')
        this.updateValueProp(this.propFiasId, suggestion?.data?.fias_id ?? '')
        this.updateValueProp(this.propKladrId, suggestion?.data?.kladr_id ?? '')
        this.updateValueProp(this.propStreetKladrId, suggestion?.data?.street_kladr_id ?? '')
        this.updateValueProp(this.propLatitudeId, suggestion?.data?.geo_lat ?? '')
        this.updateValueProp(this.propLongitudeId, suggestion?.data?.geo_lon ?? '')
    },

    /**
     *
     * @param prop_id
     * @param value
     */
    updateValueProp: function (prop_id, value) {
        if (prop_id) {
            document.querySelector(`input[name="ORDER_PROP_${prop_id}"]`).value = value
        }
    },

    closePvzPopup: function () {
        BX.hide(this.pvzOverlay);
    },

    clearPvzMap: function () {
        BX.cleanNode(BX('map_for_delivery'))
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
    createPVZPopup1: function() {
        this.pvzPopup = BX.create({
            tag: 'div',
            props: {
                id: 'wrap_pvz_map',
                className: "wrap_pvz_map"
            },
            children: [
                BX.create({
                    tag: 'div',
                    props: {
                        id: 'wrap_pvz_close',
                        className: "wrap_pvz_close js__wrap_pvz_close"
                    },
                    events: {
                        click: BX.proxy(function() {
                            this.closePvzPopup()
                        }, this)
                    }
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        id: 'pvz_user_data',
                        className: 'pvz_user_data'
                    }
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        id: 'map_for_delivery',
                        className: 'map_for_delivery'
                    }
                })
            ],
        })

        this.pvzOverlay = BX.create({
            tag: 'div',
            props: {
                id: 'wrap_pvz_overlay',
                className: "wrap_pvz_overlay"
            },
            children: [this.pvzPopup]
        })

        BX.insertAfter(this.pvzOverlay, BX('bx-soa-order'))
        this.buildDeliveryType()
            .buildDataView()
            .buildSortService()
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
    buildPVZMap1: function () {
        var __this = this;

        ymaps.ready(function () {
            var myGeocoder = ymaps.geocode(__this.curCityName, {results: 1});
            myGeocoder.then(function (res) { // получаем координаты
                var firstGeoObject = res.geoObjects.get(0),
                    coords = firstGeoObject.geometry.getCoordinates();

                __this.propsMap = new ymaps.Map('map_for_delivery', {
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

    getPVZList: function (form = 'map') {
        const __this = this;
        let pvzView = form;
        if (!BX.Sale.OrderAjaxComponent.startLoader())
            return;
        BX.ajax({
            url: __this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                'cityName': __this.curCityName,
                'codeCity': __this.curCityCode,
                'orderPackages': __this.orderPackages,
                'action': 'getPVZList'
            },
            onsuccess: function (res) {
                __this.pvzObj = JSON.parse(res) || [];

                if (pvzView == 'list') {
                    __this.buildPvzList(__this.pvzObj);
                    BX.Sale.OrderAjaxComponent.endLoader();
                } else {
                    __this.setPVZOnMap();
                }
            },
            onfailure: function (res) {
                console.log('error getPVZList');
                BX.Sale.OrderAjaxComponent.endLoader();
                BX.Sale.OrderAjaxComponent.showError(BX('bx-soa-delivery'), 'Ошибка запроса ПВЗ. Попробуйте позже.');
                __this.closePvzPopup();

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
            fivepost_rate: point.properties.fivepostRate,
            fivepost_deliverysl: point.properties.fivepostDeliverySl,
            fivepost_max_weight: point.properties.fivepostMaxWeight,
            hubregion: point.properties.hubregion,
            name_city: this.curCityName,
            postindex:  point.properties.postindex,
            code_pvz: point.properties.code_pvz,
            type_pvz: point.properties.type ?? ''
        };
    },

    selectPvz: function (objectId) {
        const __this = this
        if (!BX.Sale.OrderAjaxComponent.startLoader())
            return;

        __this.closePvzPopup();
        this.selectedPvzObjId = objectId
        const point = this.objectManager.objects.getById(objectId);

        console.log(point)
        const pvzAddress = point.properties.deliveryName + ': ' + point.properties.fullAddress;
        const pvzFullAddress = pvzAddress +
            (typeof point.properties.code_pvz !== 'undefined' ? ' #' + point.properties.code_pvz : '');

        this.updateValueProp(this.propCommonPVZId, point.properties.code_pvz)
        if (this.propAddressId ) {
            const address = document.querySelector('[name="ORDER_PROP_' + this.propAddressId + '"]');
            if (address) {
                address.value = pvzFullAddress;
            }
        }

        if (this.propTypeDeliveryId ) {
            const typeDelivery = document.querySelector('[name="ORDER_PROP_' + this.propTypeDeliveryId + '"]');
            if (typeDelivery) {
                typeDelivery.value = point.properties.deliveryName
            }
        }

        BX('selected-delivery-type').innerHTML = (point.properties.type == 'PVZ' ? 'ПВЗ ' : 'Постамат ') + point.properties.deliveryName

        if (pvzAddress) {
            BX('pvz_address').innerHTML = pvzAddress
        }
        // BX('selected-delivery-date').innerHTML = 1
        BX('delivery-choose').innerHTML = 'Выбрать другой адрес и способ доставки'

        // Подстановка значений в поля
        // BX('soa-property-26').value =
        BX('soa-property-76').value = point.properties.code_pvz;

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


                        BX('selected-delivery-price').innerHTML = item.price ? item.price + ' руб.' : 'nan'

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
                __this.closePvzPopup();
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
                    content: 'Пункт выдачи OSHISHA',
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
    },

    buildDeliveryType: function ()
    {
        const __this = this;

        BX.append(
            BX.create({
                tag: 'div',
                props: {
                    id: 'wrap_delivery_types',
                    className: "wrap_filter_block"
                },
                children: [
                    BX.create({
                        tag: 'div',
                        props: {className: "title"},
                        text: 'Способ получения'
                    }),

                    BX.create({
                        tag: 'div',
                        props: {className: "options-row"},
                        children: [
                            BX.create({
                                tag: 'label',
                                props: {
                                    className: "option-label",
                                    for: 'delivery-self',
                                },
                                text: 'Самовывоз',
                                children: [
                                    BX.create({
                                        tag: 'input',
                                        props: {
                                            id: 'delivery-self',
                                            className: 'radio-field',
                                            type: 'radio',
                                            value: 'Самовывоз',
                                            name: 'delivery_type',
                                            checked: 'checked',
                                        },
                                        events: {
                                            change: BX.proxy(function () {
                                                __this.clearPvzMap();
                                                __this.buildPVZMap1();
                                            }),
                                        },
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'radio-caption',
                                        },
                                        text: 'Самовывоз'
                                    })
                                ],
                            }),

                            BX.create({
                                    tag: 'label',
                                    props: {
                                        className: "option-label",
                                        for: 'delivery-in-hands',
                                    },
                                    text: 'Доставка в руки',
                                    children: [
                                        BX.create({
                                            tag: 'input',
                                            props: {
                                                id: 'delivery-in-hands',
                                                className: 'radio-field',
                                                type: 'radio',
                                                value: 'Доставка в руки',
                                                name: 'delivery_type',
                                            },
                                            events: {
                                                change: BX.proxy(function () {
                                                    __this.clearPvzMap();
                                                    __this.closePvzPopup();

                                                    window.Osh.bxPopup.init();
                                                    const oshMkad = window.Osh.oshMkadDistance.init(__this.oshishaDeliveryOptions);

                                                    oshMkad.afterSave = function (address) {
                                                        __this.oshishaDeliveryOptions.DA_DATA_ADDRESS = address;
                                                    }.bind(this);
                                                    window.Osh.bxPopup.onPickerClick(
                                                        (__this.propAddressId)
                                                            ? __this.propAddressId
                                                            : '',
                                                        (__this.propDateDelivery)
                                                            ? __this.propDateDelivery
                                                            : '',
                                                        (__this.propDateDelivery)
                                                            ? (document.querySelector('input[name="ORDER_PROP_' + __this.propDateDelivery + '"]').value)
                                                            : ''
                                                    );
                                                }, this)
                                            },
                                        }),
                                        BX.create({
                                            tag: 'span',
                                            props: {className: 'radio-caption'},
                                            text: 'Доставка в руки',
                                        })
                                    ],

                                }
                            )
                        ]
                    })
                ]
            }),
            BX('pvz_user_data')
        )

        return this
    },
    buildCustomerType: function ()
    {
        BX.append(
            BX.create({
                tag: 'div',
                props: {
                    id: 'wrap_payer_types',
                    className: "wrap_filter_block"
                },
                children: [
                    BX.create({
                        tag: 'div',
                        props: {className: "title"},
                        text: 'Тип плательщика'
                    }),

                    BX.create({
                        tag: 'div',
                        props: {
                            className: "options-row"
                        },
                        children: [
                            BX.create({
                                tag: 'label',
                                props: {
                                    className: "option-label",
                                    for: 'payer-individual',
                                },
                                text: 'Самовывоз',

                                children: [
                                    BX.create({
                                        tag: 'input',
                                        props: {
                                            id: 'payer-individual',
                                            className: 'radio-field',
                                            type: 'radio',
                                            value: 'Физическое лицо',
                                            name: 'payer_type',
                                            checked: 'checked',
                                        }
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'radio-caption',
                                        },
                                        text: 'Физическое лицо'
                                    })
                                ],
                            }),

                            BX.create({
                                tag: 'label',
                                props: {
                                    className: "option-label",
                                    for: 'payer-company',
                                },
                                text: 'Доставка в руки',
                                children: [
                                    BX.create({
                                        tag: 'input',
                                        props: {
                                            id: 'payer-company',
                                            className: 'radio-field',
                                            type: 'radio',
                                            value: 'Юридическое лицо',
                                            name: 'payer_type',
                                        },
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'radio-caption',
                                        },
                                        text: 'Юридическое лицо'
                                    })
                                ],
                            })
                        ]
                    })
                ]
            }),
            BX('pvz_user_data')
        )

        return this
    },
    buildDataView: function ()
    {
        const __this = this;

        BX.append(
            BX.create({
                tag: 'div',
                props: {
                    id: 'wrap_data_view',
                    className: "wrap_filter_block"
                },
                children: [
                    BX.create({
                        tag: 'div',
                        props: {className: "title"},
                        text: 'Показать'
                    }),

                    BX.create({
                        tag: 'div',
                        props: {
                            className: "options-row"
                        },
                        children: [
                            BX.create({
                                tag: 'label',
                                props: {
                                    className: "option-label",
                                    for: 'data_view_map'
                                },
                                text: 'На карте',
                                children: [
                                    BX.create({
                                        tag: 'input',
                                        props: {
                                            id: 'data_view_map',
                                            className: 'radio-field',
                                            type: 'radio',
                                            value: 'На карте',
                                            name: 'data_view',
                                            checked: 'checked'
                                        },
                                        events: {
                                            change: BX.proxy(function() {
                                                __this.clearPvzMap();
                                                __this.buildPVZMap1();
                                            })
                                        }
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'radio-caption',
                                        },
                                        text: 'На карте'
                                    })
                                ],
                            }),
                            BX.create({
                                tag: 'label',
                                props: {
                                    className: "option-label",
                                    for: 'payer-individual',
                                },
                                text: 'Списком',

                                children: [
                                    BX.create({
                                        tag: 'input',
                                        props: {
                                            id: 'data_view_list',
                                            className: 'radio-field',
                                            type: 'radio',
                                            value: 'Списком',
                                            name: 'data_view'
                                        },
                                        events: {
                                            change: BX.proxy(function () {
                                                __this.clearPvzMap();
                                                __this.getPVZList('list');
                                            })
                                        }
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'radio-caption',
                                        },
                                        text: 'Списком'
                                    })
                                ],
                            }),
                        ]
                    })
                ]
            }),
            BX('pvz_user_data')
        )

        return this
    },
    buildSortService: function ()
    {
        BX.append(
            BX.create({
                tag: 'div',
                props: {
                    id: 'wrap_sort_service',
                    className: "wrap_filter_block wrap_sort_service"
                },
                children: [
                    BX.create({
                        tag: 'div',
                        props: {
                            id: 'sort_service_select',
                            className: 'sort_service_select',
                        },
                        children: [
                            BX.create({
                                tag: 'div',
                                props: {
                                    id: 'sort-title',
                                    className: 'title'
                                },
                                text: 'Фильтрация'
                            }),
                            BX.create({
                                tag: 'div',
                                props: {
                                    id: 'active_sort_service',
                                    className: 'active_sort_service',
                                },
                                text: 'Все',
                                events: {
                                    click: BX.proxy(function () {
                                        BX.toggleClass(BX('sort_service_select'), 'active')
                                    }, this)
                                }
                            }),
                            BX.create({
                                tag: 'ul',
                                props: {
                                    id: 'sort_services_list',
                                    className: 'sort_services_list',
                                },
                                children: [
                                    BX.create({
                                        tag: 'li',
                                        props: {className: 'sort_service'},
                                        text: 'Все',
                                        events: {
                                            pickpoints: BX.proxy(function (e) {
                                                BX.adjust(BX('active_sort_service'), {text: e.target.innerHTML})
                                                BX.removeClass(BX('sort_service_select'), 'active')

                                                this.sortPvzList(e.target.getAttribute('data-target'))
                                            }, this)
                                        },
                                        dataset: {
                                            target: 'js-showall'
                                        }
                                    }),
                                    BX.create({
                                        tag: 'li',
                                        props: {className: 'sort_service'},
                                        text: '5Post',
                                        events: {
                                            click: BX.proxy(function (e) {
                                                BX.adjust(BX('active_sort_service'), {text: e.target.innerHTML})
                                                BX.removeClass(BX('sort_service_select'), 'active')

                                                this.sortPvzList(e.target.getAttribute('data-target'))
                                            }, this)
                                        },
                                        dataset: {
                                            target: 'js-5post'
                                        }
                                    }),
                                    BX.create({
                                        tag: 'li',
                                        props: {className: 'sort_service'},
                                        text: 'Oshisha',
                                        events: {
                                            click: BX.proxy(function (e) {
                                                BX.adjust(BX('active_sort_service'), {text: e.target.innerHTML})
                                                BX.removeClass(BX('sort_service_select'), 'active')

                                                this.sortPvzList(e.target.getAttribute('data-target'))
                                            }, this)
                                        },
                                        dataset: {
                                            target: 'js-oshisha'
                                        }
                                    }),
                                    BX.create({
                                        tag: 'li',
                                        props: {className: 'sort_service'},
                                        text: 'СДЭК',
                                        events: {
                                            click: BX.proxy(function (e) {
                                                BX.adjust(BX('active_sort_service'), {text: e.target.innerHTML})
                                                BX.removeClass(BX('sort_service_select'), 'active')

                                                this.sortPvzList(e.target.getAttribute('data-target'))
                                            }, this)
                                        },
                                        dataset: {
                                            target: 'js-sdek'
                                        }
                                    }),
                                    BX.create({
                                        tag: 'li',
                                        props: {className: 'sort_service'},
                                        text: 'ПЭК',
                                        events: {
                                            click: BX.proxy(function (e) {
                                                BX.adjust(BX('active_sort_service'), {text: e.target.innerHTML})
                                                BX.removeClass(BX('sort_service_select'), 'active')

                                                this.sortPvzList(e.target.getAttribute('data-target'))
                                            }, this)
                                        },
                                        dataset: {
                                            target: 'js-pek'
                                        }
                                    }),
                                    BX.create({
                                        tag: 'li',
                                        props: {className: 'sort_service'},
                                        text: 'Почта РФ',
                                        events: {
                                            click: BX.proxy(function (e) {
                                                BX.adjust(BX('active_sort_service'), {text: e.target.innerHTML})
                                                BX.removeClass(BX('sort_service_select'), 'active')

                                                this.sortPvzList(e.target.getAttribute('data-target'))
                                            }, this)
                                        },
                                        dataset: {
                                            target: 'js-rupost'
                                        }
                                    }),
                                    BX.create({
                                        tag: 'li',
                                        props: {className: 'sort_service'},
                                        text: 'Деловые линии',
                                        events: {
                                            click: BX.proxy(function (e) {
                                                BX.adjust(BX('active_sort_service'), {text: e.target.innerHTML})
                                                BX.removeClass(BX('sort_service_select'), 'active')

                                                this.sortPvzList(e.target.getAttribute('data-target'))
                                            }, this)
                                        },
                                        dataset: {
                                            target: 'js-dl'
                                        }
                                    }),
                                ]
                            })
                        ]

                    })
                ]
            }),
            BX('pvz_user_data')
        )

        return this
    },

    sortPvzList: function(jsClass)
    {
        if(BX('pickpoints-list')) {
            if (jsClass == 'js-showall') {
                BX('pickpoints-list').querySelectorAll('.pickpoint-item').forEach((el) => {
                    el.style.display = 'block'
                })
            } else {
                BX('pickpoints-list').querySelectorAll('.pickpoint-item').forEach((el) => {
                    el.style.display = 'none'
                })
                BX('pickpoints-list').querySelectorAll('.' + jsClass).forEach(el => {
                    el.style.display = 'block'
                })
            }
        }
    },

    // initCheckoutDom: function() {
    //     return {
    //         rootEl: BX('bx-soa-order'),
    //         user: { rootEl: BX('bx-soa-properties') },
    //         auth: {rootEl: BX('bx-soa-auth')},
    //         order: {rootEl: BX('bx-soa-order')},
    //         region: {rootEl: document.querySelectorAll('#bx-soa-region')},
    //         delivery: {rootEl: BX('bx-soa-delivery')},
    //         paysystem: {rootEl: BX('bx-soa-paysystem')},
    //         pickup: {rootEl: BX('bx-soa-pickup')},
    //         notice: {rootEl: BX('new_block_with_sms')},
    //         save: {rootEl: BX('bx-soa-orderSave')},
    //         total:{rootEl: BX('bx-soa-total')}
    //     }
    // },
    //
    //
    drawInterface: function ()
    {
        this.checkout = {
            rootEl: BX('bx-soa-order'),
            user: { rootEl: BX('bx-soa-properties') },
            auth: {rootEl: BX('bx-soa-auth')},
            order: {rootEl: BX('bx-soa-order')},
            region: {rootEl: document.querySelectorAll('#bx-soa-region')},
            delivery: {rootEl: BX('bx-soa-delivery')},
            paysystem: {rootEl: BX('bx-soa-paysystem')},
            pickup: {rootEl: BX('bx-soa-pickup')},
            notice: {rootEl: BX('new_block_with_sms')},
            save: {rootEl: BX('bx-soa-orderSave')},
            total:{rootEl: BX('bx-soa-total')}
        }

        this.drawDelivery()
            // .drawPayment()
            // .drawNotice()
            // .drawProps()
        return this
    },
    //
    // drawNotice: function()
    // {
    //     this.checkout.notice.title = BX.findChild(this.checkout.notice.rootEl, {'class': 'bx-soa-section-title'}, true)
    //
    //
    //     this.checkout.notice.variants = {}
    //     this.checkout.notice.variants.rootEl = BX.findChild(this.checkout.notice.rootEl, {'class': 'form-check'}, true)
    //
    //     this.checkout.notice.variants.sms = {}
    //     this.checkout.notice.variants.sms.rootEl = BX.findChild(this.checkout.notice.variants.rootEl, {'class': 'mr-5'}, true)
    //     this.checkout.notice.variants.sms.input = BX('sms')
    //     this.checkout.notice.variants.sms.title = BX.findChild(this.checkout.notice.variants.sms.rootEl, {'tag': 'label'}, true)
    //
    //     this.checkout.notice.variants.telegram = {}
    //     this.checkout.notice.variants.telegram.rootEl = BX.findChild(this.checkout.notice.variants.rootEl, {'class': 'mr-5'}, true)
    //     this.checkout.notice.variants.telegram.input = BX('telegram')
    //     this.checkout.notice.variants.telegram.title = BX.findChild(this.checkout.notice.variants.telegram.rootEl, {'tag': 'label'}, true)
    //
    //     this.checkout.notice.variants.call = {}
    //     this.checkout.notice.variants.call.rootEl = BX.findChild(this.checkout.notice.variants.rootEl, {'class': 'mr-5'}, true)
    //     this.checkout.notice.variants.call.input = BX('telephone')
    //     this.checkout.notice.variants.call.title = BX.findChild(this.checkout.notice.variants.call.rootEl, {'tag': 'label'}, true)
    //
    //     console.log(this.checkout.notice)
    //
    //     // .notice-title-icon
    //     return this
    // },
    // drawPayment: function()
    // {
    //     // блок выбора оплаты
    //     this.checkout.paysystem.titleBox = BX.findChild(this.checkout.paysystem.rootEl, {
    //         'class':'bx-soa-section-title-container'}, true)
    //     this.checkout.paysystem.title = BX.findChild(this.checkout.paysystem.titleBox, {
    //         'class':'bx-soa-section-title'}, true)
    //     this.checkout.paysystem.titleIcon = BX.create('span', {attrs: {className: 'payment-title-icon'}});
    //
    //     BX.removeClass(this.checkout.paysystem.titleBox, 'justify-content-between')
    //     BX.insertAfter(this.checkout.paysystem.titleIcon, this.checkout.paysystem.title)
    //
    //     return this
    // },
    //
    //
    drawDelivery: function()
    {
        // блок выбора доставки
        this.checkout.delivery.titleBox = BX.findChild(this.checkout.delivery.rootEl,
            {'class':'bx-soa-section-title-container'}, true)
        this.checkout.delivery.title = BX.findChild(this.checkout.delivery.titleBox,
            {'class':'bx-soa-section-title'}, true)
        this.checkout.delivery.content = BX.findChild(this.checkout.delivery.rootEl,
            {'class':'box_with_delivery_type'})
        this.checkout.delivery.titleIcon = BX.create('span', {attrs: {className: 'delivery-title-icon'}})

        this.checkout.delivery.variants = {}
        this.checkout.delivery.variants.rootEl = BX.create('div', {
                attrs: {className: 'delivery-variants'}})
        this.checkout.delivery.variants.title = BX.create('div', {
                attrs: {className: 'delivery-variants-title'},
                html: '<span class="title-accent">Укажите</span> адрес и способ доставки'
            })
        this.checkout.delivery.variants.choose = BX.create('div', {
            attrs: {className: 'delivery-choose js__delivery-choose', id: 'delivery-choose'},
            text: 'Выбрать адрес и способ доставки',
            events: {
                click: BX.proxy(function () {
                    this.openMap()
                }, this)
            }
        })
        BX.adjust(this.checkout.delivery.variants.rootEl, {
            children: [
                this.checkout.delivery.variants.title,

                BX.create({
                    tag: 'div',
                    props: {className: 'selected-delivery-type', id: 'selected-delivery-type'}
                }), // тип пункта выдачи

                BX.create({
                    tag: 'div',
                    props: {className: 'selected-delivery-price', id: 'selected-delivery-price'}
                }), // цена

                BX.create('div', {
                    attrs: {className: 'delivery-description', id: 'pvz_address'},
                    text: 'Выберите один из подходящих Вам вариантов: самовывоз, пункт выдачи заказов или доставка курьером до двери'
                }), // адрес

                BX.create({
                    tag: 'div',
                    props: {className: 'selected-delivery-date', id: 'selected-delivery-date'}
                }), // дата получения

                this.checkout.delivery.variants.choose,
            ]})

        // Прошлые доставки
        this.checkout.recentWrap


        // предыдущие доставки
        this.checkout.delivery.separator = BX.create('div', {attrs: {className: 'delivery-separator'}, text: 'Или'})
        this.checkout.delivery.recentWrap = {}
        this.checkout.delivery.recentWrap.rootEl = BX.create('div', {attrs: {className: 'last-deliveries-wrap'}})
        this.checkout.delivery.recentWrap.title = BX.create('div', {attrs: {className: 'last-deliveries-title'},
            html: '<span class="title-accent">Выберите настройки</span> достаки из прошлых заказов'})

        BX.removeClass(this.checkout.delivery.titleBox, 'justify-content-between')
        // BX.addClass(this.checkout.delivery.content, 'd-none')
        BX.insertAfter(this.checkout.delivery.titleIcon, this.checkout.delivery.title)

        BX.insertAfter(this.checkout.delivery.variants.rootEl, this.checkout.delivery.titleBox)
        // BX.insertAfter(this.checkout.delivery.separator, this.checkout.delivery.variants.rootEl)
        //
        // BX.insertAfter(this.checkout.delivery.recentWrap.rootEl, this.checkout.delivery.separator)
        // BX.append(this.checkout.delivery.recentWrap.title, this.checkout.delivery.recentWrap.rootEl)



        // this.checkout.delivery.recentWrap.

        // wrap
        // {
        //     title
        //     {
        //     }
        //     info
        //     {
        //         price
        //         {
        //         }
        //         date
        //         {
        //         }
        //     }
        //     address
        //     {
        //     }
        // }



        return this;
    },
    //
    // drawProps: function()
    // {
    //     this.checkout.user.title = BX.findChild(this.checkout.order.rootEl, {'tag':'h5'}, true);
    //     BX.addClass(this.checkout.user.title, 'checkout-block-title');
    //     BX.addClass(this.checkout.user.title, 'fw-normal');
    //     BX.addClass(BX.findChild(this.checkout.user.title, {'tag':'b'}, true), 'fw-normal');
    //
    //     // физ/юр лицо
    //     this.checkout.user.type = BX.findChild(this.checkout.user.rootEl, {'class': 'bx-soa-section-title-container'});
    //     BX.addClass(this.checkout.user.type, 'd-none');
    //
    //     // ФИО
    //     this.checkout.user.name = BX.findChild(this.checkout.user.rootEl, {'attribute': {'data-property-id-row': 1}}, true);
    //     console.log(this.checkout)
    //     BX.removeClass(this.checkout.user.name, 'col-12');
    //     BX.addClass(this.checkout.user.name, 'col-md-6 col-lg-6 col-12  checkout-name-group');
    //     BX.adjust(this.checkout.user.name, {attrs: {'id': 'checkout-name-group'}});
    //
    //     // телефон
    //     this.checkout.user.phone = BX.findChild(this.checkout.user.rootEl, {'attribute': {'data-property-id-row': 3}}, true);
    //     BX.removeClass(this.checkout.user.phone, 'col-12');
    //     BX.addClass(this.checkout.user.phone, 'col-md-6 col-lg-6 col-12 checkout-phone-group');
    //     BX.adjust(this.checkout.user.phone, {attrs: {'id': 'checkout-phone-group'}});
    //
    //     // email
    //     this.checkout.user.email = BX.findChild(this.checkout.user.rootEl, {'attribute': {'data-property-id-row': 2}}, true);
    //     BX.removeClass(this.checkout.user.email, 'col-12');
    //     BX.addClass(this.checkout.user.email, 'col-md-6 col-lg-6 col-12 checkout-email-group');
    //     BX.adjust(this.checkout.user.email, {attrs: {'id':'checkout-email-group'}});
    //
    //     // Город
    //     this.checkout.user.city = BX.findChild(this.checkout.user.rootEl, {'attribute': {'data-property-id-row': 6}}, true);
    //     BX.removeClass(this.checkout.user.city, 'd-none');
    //     BX.addClass(this.checkout.user.city, 'col-md-6 col-lg-6 col-12 checkout-city-group');
    //     BX.adjust(this.checkout.user.city, {attrs: {'id':'checkout-city-group'}});
    //
    //     // блок региона
    //     BX.addClass(this.checkout.region.rootEl[0], 'd-none');
    //     BX.remove(this.checkout.region.rootEl[1]);
    //     this.checkout.region.rootEl = this.checkout.region.rootEl[0];
    //
    //     return this
    // },

    // buildDeliveryChooseButton: function()
    // {
    //     // const __this = this
    //     // __this.showMapBtn = BX.create({
    //     //     'tag': 'div',
    //     //     'props': {'className': 'show-map-btn'},
    //     //     'text': 'Выбрать адрес и способ доставки',
    //     //     events: {click: BX.proxy(function () {__this.openMap()}, __this)}
    //     // })
    //     // BX.insertAfter(__this.showMapBtn,
    //     //     BX.findChild(BX('bx-soa-delivery'), {'class': 'bx-soa-section-title-container'})
    //     // )
    //     // BX.insertAfter(
    //     //     BX.create({
    //     //         'tag': 'span',
    //     //         'props': {'id':'pvz_address'}
    //     //     }),
    //     //     __this.showMapBtn
    //     // )
    // }

    buildPvzList: function ()
    {
        BX.append(
            BX.create({
                tag: 'div',
                props: {
                    id: 'pickpoints-list',
                    className: 'pickpoints-list',
                }
            }),
            BX('map_for_delivery')
        )


        this.pvzObj.features.forEach(el => {
            console.log(el.properties);

            let jsClass = ''
            switch (el.properties.deliveryName) {
                case 'СДЭК': jsClass = 'js-sdek'; break;
                case 'Почта России': jsClass = 'js-rupost'; break;
                case 'OSHISHA': jsClass = 'js-oshisha'; break;
            }

            BX.append(
                BX.create({
                    tag: 'div',
                    props: {
                        id: el.id,
                        className: 'pickpoint-item ' + jsClass
                    },
                    events: {
                      click: BX.proxy(function() {
                          BX.SaleCommonPVZ.selectPvz(el.id)
                      }, this)
                    },
                    text: el.properties.deliveryName + ' / адрес: ' + el.properties.fullAddress
                }),
                BX('pickpoints-list')
            )
        })
    }
};


