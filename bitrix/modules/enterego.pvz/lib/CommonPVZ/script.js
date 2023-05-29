BX.namespace('BX.SaleCommonPVZ');

const typeDisplayPVZ = {map: 'map', list: 'list'}

BX.SaleCommonPVZ = {
    curCityCode: null,
    curCityName: null,
    curCityType: null,
    curCityArea: null,
    curParentCityName: null,
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
    propLocationId: null,
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
    shipmentCost: undefined,
    orderPackages: null,
    oshishaDeliveryOptions: null,
    oshishaDeliveryStatus: false,
    propDefaultPvzAddressId: null,
    propTypePvzId: null,
    componentParams: {
        'displayPVZ': typeDisplayPVZ.map,
        'filterDelivery': null,
    },

    init: function (params) {
        this.curDeliveryId = params.params?.curDeliveryId;
        this.doorDeliveryId = params.params?.doorDeliveryId;
        this.pvzDeliveryId = params.params?.pvzDeliveryId;
        this.shipmentCost = params.params?.shipmentCost;
        this.orderPackages = params.params?.packages;
        this.oshishaDeliveryOptions = params.params?.deliveryOptions;

        this.refresh()
        this.drawInterface()
        this.updateDelivery(BX.Sale.OrderAjaxComponent.result)

        if (this.propAddressId && this.oshishaDeliveryStatus) {
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
        const order = BX.Sale.OrderAjaxComponent.result
        this.propAddressId            = order.ORDER_PROP.properties.find(prop => prop.CODE === 'ADDRESS')?.ID;
        this.propCommonPVZId          = order.ORDER_PROP.properties.find(prop => prop.CODE === 'COMMON_PVZ')?.ID;
        this.propTypeDeliveryId       = order.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_DELIVERY')?.ID;
        this.propZipId                = order.ORDER_PROP.properties.find(prop => prop.CODE === 'ZIP')?.ID;
        this.propLocationId           = order.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION')?.ID;
        this.propCityId               = order.ORDER_PROP.properties.find(prop => prop.CODE === 'CITY')?.ID;
        this.propFiasId               = order.ORDER_PROP.properties.find(prop => prop.CODE === 'FIAS')?.ID;
        this.propKladrId              = order.ORDER_PROP.properties.find(prop => prop.CODE === 'KLADR')?.ID;
        this.propStreetKladrId        = order.ORDER_PROP.properties.find(prop => prop.CODE === 'STREET_KLADR')?.ID;
        this.propLatitudeId           = order.ORDER_PROP.properties.find(prop => prop.CODE === 'LATITUDE')?.ID;
        this.propLongitudeId           = order.ORDER_PROP.properties.find(prop => prop.CODE === 'LONGITUDE')?.ID;
        this.propDateDelivery          = order.ORDER_PROP.properties.find(prop => prop.CODE === 'DATE_DELIVERY')?.ID;
        this.propDeliveryTimeInterval = order.ORDER_PROP.properties.find(prop => prop.CODE === 'DELIVERYTIME_INTERVAL')?.ID;
        this.propDefaultPvzAddressId = order.ORDER_PROP.properties.find(prop => prop.CODE === 'DEFAULT_ADDRESS_PVZ')?.ID;
        this.propTypePvzId = order.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_PVZ')?.ID;

        this.propAddressId = BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.find(prop => prop.CODE === 'ADDRESS')?.ID;
        if (this.propAddressId) {
            window.Osh.bxPopup.init();
        } else {
            alert('Свойство адреса не найдено');
        }

        if (this.propCommonPVZId) {
            const commonPVZ = document.querySelector('[name="ORDER_PROP_' + this.propCommonPVZId + '"]');
            if (commonPVZ) {
                commonPVZ.readOnly = true;
            }
        }
        try {
            if (BX.Sale.OrderAjaxComponent.locations[this.propLocationId][0].lastValue) {
                this.curCityCode = BX.Sale.OrderAjaxComponent.locations[this.propLocationId][0].lastValue;
            } else {
                this.curCityCode = BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION')?.VALUE[0]
            }
        } catch (e) {
            this.curCityCode = BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION')?.VALUE[0]
        }
        this.propsMap = null;
        this.getCityName();
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
        BX.cleanNode('deliveries-list')

        const curDelivery = orderData.DELIVERY.find(delivery =>
            delivery.ID === this.doorDeliveryId || delivery.ID === this.pvzDeliveryId && delivery.CHECKED === 'Y')

        BX.cleanNode('delivery-description')
        if (curDelivery !== undefined && curDelivery?.PRICE !== undefined) {
            BX.adjust(
                BX('delivery-description'),
                {
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {className: 'col-6', id: 'selected-delivery-price'},
                            html: `<b class="mr-1">Способ доставки:</b>${this.getValueProp(this.propTypeDeliveryId)}`
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className: 'col-6', id: 'selected-delivery-price'},
                            html: `<b class="mr-1">Стоимость:</b>${curDelivery?.PRICE_FORMATED}`
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className: 'col-6', id: 'selected-delivery-price'},
                            html: `<b class="mr-1">Адрес:</b>${this.getValueProp(this.propAddressId)}`
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className: 'col-6', id: 'selected-delivery-price'},
                            html: `<b class="mr-1">Дата получения:</b> - `
                        })
                    ]
                }
            )

            BX.addClass(BX('delivery-variants'), 'active')
        } else {
            BX.cleanNode(BX('delivery-description'))
            BX.append(
                BX.create({
                    tag: 'p',
                    html: 'Выберите один из подходящих Вам вариантов: самовывоз, пункт выдачи заказов или доставка курьером до двери'
                }),
                BX('delivery-description')
            )
        }

        const doorDelivery = orderData.DELIVERY.find(delivery => delivery.ID === this.doorDeliveryId && delivery.CHECKED === 'Y')
        const checkedDelivery = orderData.DELIVERY.find(delivery => delivery.CHECKED === 'Y')
        if (doorDelivery !== undefined) {
            const deliveryInfo = JSON.parse(doorDelivery.CALCULATE_DESCRIPTION)
            let i = 1;
            deliveryInfo.forEach(delivery => {
                if (delivery.error) {
                    console.log('Delivery calculation error');
                    console.log(delivery.error);
                } else {
                    let oshClass = delivery.code === 'oshisha' ? 'oshisha' : ''
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
                                        className: 'js-delivery-prop-' + i,
                                    },
                                    events: {
                                        click: () => {
                                            BX.Sale.OrderAjaxComponent.sendRequest()
                                        }
                                    },
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

                    const propPopupContainer = BX.create(
                        'DIV',
                        {
                            props: {
                                className: 'courier-delivery ' + oshClass
                            },
                            children: [
                                BX.create({
                                    tag: 'div',
                                    props: {className: 'top-row'},
                                    children: [
                                        BX.create('INPUT', {
                                            attrs: {checked: delivery.checked},
                                            props: {
                                                type: 'radio',
                                                name: 'delivery',
                                            },
                                            dataset: {target: 'js-delivery-prop-' + i},
                                            events: {
                                                click: BX.proxy(function () {
                                                    const target = $(this).data('target')
                                                    let address = $(document).find('#user-address').val()
                                                    $(document).find('.' + target).prop('checked', true)
                                                    $(document).find('input[name="ORDER_PROP_' + this.propAddressId + '"]').val(address)
                                                    BX.Sale.OrderAjaxComponent.sendRequest()
                                                })
                                            }
                                        }),
                                        BX.create('DIV', {
                                            props: {
                                                className: 'courier-delivery-name',
                                            },
                                            html: `${delivery.name}`
                                        }),
                                        BX.create('DIV', {
                                            props: {
                                                className: 'courier-delivery-price',
                                            },
                                            html: delivery.price != 0 ? delivery.price + ' руб.' : 'Бесплатно'
                                        })
                                    ]
                                }),
                                BX.create({
                                    tag: 'div',
                                    props: {className: 'bottom-row'},
                                    children: [
                                        BX.create({
                                            tag: 'span',
                                            props: {className: 'delivery-address'},
                                            text: $(document).find('#user-address').val()
                                        }),
                                        BX.create({
                                            tag: 'span',
                                            props: {className: 'delivery-time'},
                                            text: 'Срок доставки: от 2 дней'
                                        })
                                    ]
                                })
                            ]
                        })


                    propsNode.append(propContainer);
                    BX.append(propPopupContainer, BX('deliveries-list'))

                    i++;

                    if (delivery.code === 'oshisha') {
                        this.updateOshishaDelivery(propsNode)
                    }
                }
            })
        } else {
            if (checkedDelivery['CALCULATE_ERRORS']) {
                console.log('Delivery calculation error');
                console.log(checkedDelivery.CALCULATE_DESCRIPTION);
            }
            const propPopupContainer = BX.create('DIV', {
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
            // propsNode.append(propContainer);
            BX.append(propPopupContainer, BX('deliveries-list'))
        }
    },

    updateOshishaDelivery: function (parentBlock) {
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
        this.createPVZPopup();

        //если доставка не принадлежит ни одному из профилей единой доставки, то ставим по умолчанию доставку до ПВЗ
        if (this.curDeliveryId !== this.doorDeliveryId && this.pvzDeliveryId !== this.pvzDeliveryId) {
            this.curDeliveryId = this.pvzDeliveryId
        }

        if (this.curDeliveryId === this.doorDeliveryId) {
            this.buildAddressField()
            this.buildDoorDeliveryContent();
        } else  {
            this.buildPVZMap();
        }
    },

    /**
     * Fill props from DADATA suggestion
     * @param suggestion
     */
    updatePropsFromDaData: function (suggestion) {
        this.updateValueProp(this.propAddressId, suggestion?.value ?? '')
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

    getValueProp: function (prop_id) {
        return document.querySelector(`input[name="ORDER_PROP_${prop_id}"]`).value ?? ''
    },

    closePvzPopup: function () {
        BX.hide(this.pvzOverlay);
        this.clearDeliveryBlock()
    },

    clearDeliveryBlock: function () {
        BX.cleanNode(BX('map_for_delivery'))
    },

    createPVZPopup: function () {
        if (BX('wrap_pvz_overlay')) {
            this.pvzOverlay = BX('wrap_pvz_overlay')
        } else {
            const pvzPopup = BX.create({
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
                            click: BX.proxy(function () {
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
                children: [pvzPopup]
            })

            BX.insertAfter(this.pvzOverlay, BX('bx-soa-order'))
        }

        this.buildDeliveryType()
            .buildDataView()
            .buildSortService()
            .buildMobileControls()

        BX.adjust(this.pvzOverlay, {style: {display: 'flex'}})
    },

    /**
     *   Построение карты с PVZ
     */
    buildPVZMap: function () {
        BX.remove(BX('user-address-wrap'))
        BX.show(BX('wrap_data_view'))
        BX.show(BX('wrap_sort_service'))
        this.getPVZList();
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
                res = JSON.parse(res);
                __this.curCityName = res.LOCATION_NAME;
                __this.curParentCityName = res.PARENT_LOCATION_NAME;
                __this.curCityArea = res.AREA_NAME;
                __this.curCityType = res.TYPE;
                if (__this.propAddressId) {
                    const userAddress = $(document).find('#user-address');
                    if (userAddress.length) {
                        if (__this.curCityName == 'Москва') {
                            userAddress.suggestions().setOptions({
                                constraints: {
                                    locations: [{region: "Московская"}, {region: "Москва"}]
                                }
                            });
                        } else {
                            if (Number(__this.curCityType) === 6) {
                                userAddress.suggestions().setOptions({
                                    constraints: {
                                        locations: [{region: __this.curCityArea}, {area: __this.curParentCityName}]
                                    }
                                });
                            } else {
                                // $(document).find('[name="ORDER_PROP_' + __this.propAddressId + '"]').suggestions().setOptions({
                                userAddress.suggestions().setOptions({
                                    constraints: {
                                        locations: [{city: __this.curCityName}]
                                    }
                                });
                            }
                        }
                    }
                }
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
                'orderPackages': __this.orderPackages,
                'action': 'getPVZList'
            },
            onsuccess: function (res) {
                __this.pvzObj = JSON.parse(res) || [];
                __this.showPVZ();
                BX.Sale.OrderAjaxComponent.endLoader();
            },
            onfailure: function (res) {
                console.log('error getPVZList');
                BX.Sale.OrderAjaxComponent.endLoader();
                BX.Sale.OrderAjaxComponent.showError(BX('bx-soa-delivery'), 'Ошибка запроса ПВЗ. Попробуйте позже.');
                __this.closePvzPopup();
                BX.Sale.OrderAjaxComponent.endLoader();
            }
        });
    },

    /**
     * Отображает ПВЗ с учетом фильтра
     */
    showPVZ: function () {
        const pvzList = this.componentParams.filterDelivery === null
            ? this.pvzObj.features
            : this.pvzObj.features.filter( item => this.componentParams.filterDelivery === item.properties.deliveryName )

        this.clearDeliveryBlock()
        if (this.componentParams.displayPVZ === typeDisplayPVZ.list) {
            this.buildPvzList(pvzList);
        } else {
            this.setPVZOnMap(pvzList);
        }
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
            hubregion: point.properties.hubregion,
            name_city: this.curCityName,
            postindex: point.properties.postindex,
            code_pvz: point.properties.code_pvz,
            type_pvz: point.properties.type ?? ''
        };
    },

    selectPvz: function (objectId) {
        const __this = this
        if (!BX.Sale.OrderAjaxComponent.startLoader())
            return;

        __this.closePvzPopup();
        const point = this.objectManager.objects.getById(objectId);

        const pvzAddress = point.properties.deliveryName + ': ' + point.properties.fullAddress;
        const pvzFullAddress = pvzAddress +
            (typeof point.properties.code_pvz !== 'undefined' ? ' #' + point.properties.code_pvz : '');
        BX.Sale.OrderAjaxComponent.result.DELIVERY.forEach((delivery) => {
            if (delivery['CHECKED'])
                delete delivery['CHECKED']
            if (delivery['ID'] == this.pvzDeliveryId)
                delivery['CHECKED'] = 'Y'
        })
        BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.forEach((property) => {
            if (property['CODE'] == 'COMMON_PVZ' && point.properties.code_pvz) {
                property['VALUE'][0] = point.properties.code_pvz;
            }
            if (property['CODE'] == 'TYPE_DELIVERY') {
                property['VALUE'][0] = point.properties.deliveryName;
            }
            if (property['CODE'] == 'ADDRESS') {
                property['VALUE'][0] = pvzFullAddress
            }
            if (property['CODE'] == 'LATITUDE') {
                property['VALUE'][0] = String(point.geometry.coordinates[0]);
            }
            if (property['CODE'] == 'LONGITUDE') {
                property['VALUE'][0] = String(point.geometry.coordinates[1]);
            }
            if (property['CODE'] == 'TYPE_PVZ') {
                property['VALUE'][0] = point.properties.type;
            }
            if (property['CODE'] == 'DEFAULT_ADDRESS_PVZ') {
                property['VALUE'][0] = point.properties.fullAddress;
            }
            if (property['CODE'] == 'ZIP' && point.properties.postindex) {
                property['VALUE'][0] = point.properties.postindex;
            }
            // if (property['CODE'] == 'LOCATION') {
            //     property['VALUE'][0] = point.properties.code_pvz;
            // }
        });

        BX('delivery-choose').innerHTML = 'Выбрать другой адрес и способ доставки'

        // Подстановка значений в поля
        // BX('soa-property-26').value =
        // BX('soa-property-76').value = point.properties.code_pvz;


        const dataToHandler = this.getPointData(point);
        var tempLocations = BX.Sale.OrderAjaxComponent.locations;
        Object.keys(tempLocations).forEach((locationKey) => {
            tempLocations[locationKey] = tempLocations[locationKey][0];
        });
        var payload = {error: false, locations: tempLocations, order: BX.Sale.OrderAjaxComponent.result};
        BX.Sale.OrderAjaxComponent.refreshOrder(payload);
        __this.sendRequestToComponent('refreshOrderAjax', dataToHandler);
    },

    /**
     *
     * @param points
     * @param clusterId
     */
    getSelectPvzPrice: function (points, clusterId = undefined) {
        const __this = this;
        const data = points.reduce((result, point) => {
            if (!point.properties.balloonContent) {
                point.properties.balloonContent = "Идет загрузка данных...";
                if (clusterId === undefined) {
                    __this.objectManager.objects.balloon.setData(point);
                }
                return result.concat(this.getPointData(point))
            }
            return result;
        }, [])

        if (data.length === 0)
            return;

        if (clusterId !== undefined && __this.objectManager.clusters.balloon.isOpen(clusterId)) {
            __this.objectManager.clusters.balloon.setData(__this.objectManager.clusters.balloon.getData());
        }

        const afterSuccess = function (data) {
            if (clusterId !== undefined && __this.objectManager.clusters.balloon.isOpen(clusterId)) {
                __this.objectManager.clusters.balloon.setData(__this.objectManager.clusters.balloon.getData());
            }
        }

        this.getRequestGetPvzPrice(data, afterSuccess)
    },

    /**
     * Получение и перезаполнение цены для определенного ПВЗ
     * @param point
     * @param currentItemNode
     */
    getPvzItemPrice: function (point, currentItemNode) {
        const __this = this;
        const data = [this.getPointData(point)]

        const afterSuccess = function (res) {
            const point = BX.SaleCommonPVZ.objectManager.objects.getById(res[0].id)
            BX.cleanNode(currentItemNode)
            __this.buildPvzItemSelectRow(point, currentItemNode)
            BX.Sale.OrderAjaxComponent.endLoader()
        }
        this.getRequestGetPvzPrice(data, afterSuccess)
    },

    /**
     * Отправка запроса на получение и вызов соответствующего обработчика
     * @param data
     * @param afterSuccess
     */
    getRequestGetPvzPrice: function (data, afterSuccess=undefined) {
        const __this = this

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
                    res.data.forEach(__this.afterGetPvzItemPrice())
                    if (afterSuccess !== undefined) {
                        afterSuccess(res.data)
                    }
                }
            },
            onfailure: function (res) {
                console.log('error getRequestGetPvzPrice');
                BX.Sale.OrderAjaxComponent.endLoader();
                BX.Sale.OrderAjaxComponent.showError(BX('bx-soa-delivery'), 'Ошибка запроса ПВЗ. Попробуйте позже.');
                __this.closePvzPopup();
            }
        })
    },

    afterGetPvzItemPrice: (clusterId=undefined) => function (item) {
        const point = BX.SaleCommonPVZ.objectManager.objects.getById(item.id)
        const balloonContent = "".concat(
            `<div><b>${point.properties?.type === "POSTAMAT" ? 'Постомат' : 'ПВЗ'}${item.price ? ' - ' + item.price : ''} руб.</b></div>`,
            `<div>${point.properties.fullAddress}</div>`,
            point.properties.phone ? `<div>${point.properties.phone}</div>` : '',
            point.properties.workTime ? `<div>${point.properties.workTime}</div>` : '',
            point.properties.comment ? `<div><i>${point.properties.comment}</i></div>` : '',
            point.properties.postindex ? `<div><i>${point.properties.postindex}</i></div>` : '',
            item['error'] ? `<div>При расчете стоимости произошла ошибка, пожалуйста выберите другой ПВЗ или вид доставки</div>` :
                `<a class="btn btn_basket mt-2" href="javascript:void(0)" onclick="BX.SaleCommonPVZ.selectPvz(${item.id})" >Выбрать</a>`
        )

        point.properties = {
            ...point.properties,
            price: item.price,
            balloonContent: balloonContent,
        };
        if (clusterId === undefined) {
            BX.SaleCommonPVZ.objectManager.objects.balloon.setData(point);
        }
    },

    /**
     *  Установка маркеров на карту PVZ
     */
    setPVZOnMap: function (pvzList) {
        const oshDelivery = this

        ymaps.ready(function () {
            const myGeocoder = ymaps.geocode(oshDelivery.curCityName, {results: 1});
            myGeocoder.then(function (res) { // получаем координаты
                const firstGeoObject = res.geoObjects.get(0),
                    coords = firstGeoObject.geometry.getCoordinates();

                oshDelivery.propsMap = new ymaps.Map('map_for_delivery', {
                    center: [coords[0], coords[1]],
                    zoom: 12,
                    controls: ['fullscreenControl']
                });

                const objectManager = new ymaps.ObjectManager({
                    clusterize: true,
                    clusterHasBalloon: true
                });
                objectManager.add({type: 'FeatureCollection', features: pvzList});
                oshDelivery.objectManager = objectManager;

                oshDelivery.propsMap.geoObjects.add(objectManager);
                const osh_pvz = objectManager.objects.getAll()
                    .find((item) => item?.properties?.deliveryName === 'OSHISHA');

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
                        oshDelivery.propsMap.setZoom(15)
                        objectManager.objects.balloon.open(osh_pvz.id);
                    })
                    oshDelivery.propsMap.controls.add(button, {float: 'right', floatIndex: 100});
                }

                objectManager.clusters.events.add(['balloonopen'], function (e) {
                    const clusterId = e.get('objectId');
                    const cluster = objectManager.clusters.getById(clusterId);
                    if (objectManager.clusters.balloon.isOpen(clusterId)) {
                        oshDelivery.getSelectPvzPrice(cluster.properties.geoObjects, clusterId);
                    }
                });

                objectManager.objects.events.add('click', function (e) {
                    const objectId = e.get('objectId')
                    objectManager.objects.balloon.open(objectId);
                });

                objectManager.objects.events.add('balloonopen', function (e) {
                    const objectId = e.get('objectId'),
                        obj = objectManager.objects.getById(objectId);

                    oshDelivery.getSelectPvzPrice([obj]);
                });
            });
        }).catch(function (e) {
            oshDelivery.showError(oshDelivery.mainErrorsNode, 'Ошибка построения карты ПВЗ!');
            console.warn(e);
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

    buildAddressField: function () {
        BX.remove(BX('user-address-wrap'))
        BX.hide(BX('wrap_data_view'))
        BX.hide(BX('wrap_sort_service'))

        BX.append(
            BX.create({
                tag: 'div',
                props: {className: 'user-address-wrap', id: 'user-address-wrap'},
                children: [
                    BX.create({
                        tag: 'div',
                        props: {
                            className: 'address-box',
                        },
                        children: [
                            BX.create({
                                tag: 'div',
                                props: {
                                    className: 'user-address-title',
                                },
                                text: 'Введите адрес'
                            }),
                            BX.create({
                                tag: 'input',
                                props: {
                                    id: 'user-address',
                                    className: 'form-control bx-soa-customer-input bx-ios-fix',
                                }
                            }),
                        ]
                    }),
                ]
            }),
            BX('pvz_user_data')
        )
        this.buildDaDataField()

        return this
    },

    buildDaDataField: function () {
        const __this=this

        const address = $(document).find('#user-address')
        address.suggestions({
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
                        const oshMkad = window.Osh.oshMkadDistance.init(this.oshishaDeliveryOptions);
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
        })

        if (this.curCityName) {
            if (this.curCityName == 'Москва') {
                address.suggestions().setOptions({
                    constraints: {
                        locations: [{region: "Московская"}, {region: "Москва"}]
                    }
                });
            } else if (this.curCityType == 6) {
                address.suggestions().setOptions({
                    constraints: {
                        locations: [{region: this.curCityArea}, {area: this.curParentCityName}]
                    }
                });
            } else {
                address.suggestions().setOptions({
                    constraints: {
                        locations: [{city: this.curCityName}]
                    }
                });
            }
        }
    },

    buildDoorDeliveryContent: function () {
        const __this = this

        BX.append(
            BX.create({
                tag: 'div',
                props: {className: 'deliveries-list', id: 'deliveries-list'},
            }),
            BX('map_for_delivery')
        )
        BX.append(
            BX.create({
                tag: 'a',
                props: {
                    id: 'select-door-delivery-item',
                    href: "javascript:void(0)",
                    className: "btn btn_basket mt-2",
                },
                text: 'Выбрать',
                events: {
                    click: BX.proxy(function () {

                        __this.closePvzPopup()
                        BX.Sale.OrderAjaxComponent.sendRequest()

                    })
                }
            }),
            BX('map_for_delivery')
        )
    },

    buildDeliveryType: function () {
        const __this = this;

        const propPvzDelivery =  {
            id: 'delivery-self',
                className: 'radio-field',
            type: 'radio',
            value: 'Самовывоз',
            name: 'delivery_type',
        }
        if (this.curDeliveryId === this.pvzDeliveryId ) {
            propPvzDelivery.checked = 'checked'
        }

        const propDoorDelivery = {
            id: 'delivery-in-hands',
            className: 'radio-field',
            type: 'radio',
            value: 'Доставка в руки',
            name: 'delivery_type',
        }
        if (this.curDeliveryId === this.doorDeliveryId ) {
            propDoorDelivery.checked = 'checked'
        }

        if (!BX('wrap_delivery_types')) {
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
                                            props: propPvzDelivery,
                                            events: {
                                                change: BX.proxy(function () {
                                                    BX('ID_DELIVERY_ID_' + __this.pvzDeliveryId).checked = true
                                                    BX('data_view_map').checked = true

                                                    __this.clearDeliveryBlock();
                                                    __this.buildPVZMap();
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
                                                props: propDoorDelivery,
                                                events: {
                                                    change: BX.proxy(function () {
                                                        BX('ID_DELIVERY_ID_' + __this.doorDeliveryId).checked = true

                                                        __this.clearDeliveryBlock()
                                                        __this.buildAddressField()
                                                        __this.buildDoorDeliveryContent();
                                                    }),
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
        }

        return this
    },

    buildDataView: function () {
        const __this = this;

        const propsOnMap = {
            id: 'data_view_map',
            className: 'radio-field',
            type: 'radio',
            value: 'На карте',
            name: 'data_view',
        }
        if (this.componentParams.displayPVZ === typeDisplayPVZ.map) {
            propsOnMap.checked = 'checked'
        }

        const propsList = {
            id: 'data_view_list',
            className: 'radio-field',
            type: 'radio',
            value: 'Списком',
            name: 'data_view',
        }
        if (this.componentParams.displayPVZ === typeDisplayPVZ.list) {
            propsList.checked = 'checked'
        }

        if (!BX('wrap_data_view')) {
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
                                            props: propsOnMap,
                                            events: {
                                                change: BX.proxy(function () {
                                                    if (BX('delivery-self').checked) {
                                                        __this.clearDeliveryBlock();
                                                        __this.componentParams.displayPVZ = typeDisplayPVZ.map
                                                        __this.showPVZ();
                                                    } else {
                                                        __this.clearDeliveryBlock();
                                                    }
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
                                            props: propsList,
                                            events: {
                                                change: BX.proxy(function () {
                                                    if (BX('delivery-self').checked) {
                                                        __this.clearDeliveryBlock();
                                                        __this.componentParams.displayPVZ = typeDisplayPVZ.list
                                                        __this.showPVZ();

                                                        BX.onCustomEvent('onDeliveryExtraServiceValueChange')
                                                    } else {
                                                        __this.clearDeliveryBlock();
                                                    }
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
        }
        return this
    },

    buildSortService: function () {
        if (!BX('wrap_sort_service')) {
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
                                    children: ['Все', '5Post', 'OSHISHA', 'СДЭК', 'Почта России','Деловые линии'].map(item => {
                                            return BX.create({
                                                tag: 'li',
                                                props: {className: 'sort_service'},
                                                text: item,
                                                events: {
                                                    click: BX.proxy(function (e) {
                                                        BX.adjust(BX('active_sort_service'), {text: e.target.innerHTML})
                                                        BX.removeClass(BX('sort_service_select'), 'active')

                                                        this.filterPvzList(e.target.getAttribute('data-target'))
                                                    }, this)
                                                },
                                                dataset: {
                                                    target: item
                                                }
                                            })
                                        }),
                                })
                            ]

                        })
                    ]
                }),
                BX('pvz_user_data')
            )
        }
        return this
    },

    buildMobileControls: function ()
    {
        if (!BX('scroll-down-panel')) {
            BX.append(
                BX.create({
                    tag: 'div',
                    props: {
                        id: 'scroll-down-panel',
                        className: 'scroll-down-panel'
                    },
                    events: {
                        click: BX.proxy(function () {
                            $('#pvz_user_data').toggleClass('active')
                        })
                    },
                    children: [
                        BX.create({
                            tag: 'span',
                            props: {
                                id: 'scroll_down_btn',
                                className: 'scroll-down-btn',
                            },
                            text: 'Настройки поиска'
                        })
                    ]
                }),
                BX('pvz_user_data')
            )
        }

        return this
    },

    buildPvzList: function (pvzList)
    {
        const __this = this
        const pvzListNode = BX.create({
            tag: 'div',
            props: {
                className: 'container-fluid d-flex flex-column overflow-auto my-2'
            }
        })
        BX.append(pvzListNode, BX('map_for_delivery'))

        pvzList.forEach(el => {
            this.buildPvzItem(el, pvzListNode)
        })

        BX.append(
            BX.create({
                tag: 'div',
                props: {className: 'text-center mb-3'},
                children:
                    [
                        BX.create({
                            tag: 'a',
                            props: {
                                id: 'select-pvz-item',
                                href: "javascript:void(0)",
                                className: "link_red_button text-white",
                            },
                            text: 'Выбрать',
                            events: {
                                click: BX.proxy(function () {
                                    BX.SaleCommonPVZ.selectPvz(this.dataset.pvzid)
                                })
                            }
                        }),
                    ]
            }),
            BX('map_for_delivery')
        )
    },

    buildPvzItem: function (el, pvzListNode) {
        const deliveryTopRowNode = BX.create({
            tag: 'div',
            props: {
                className: 'd-flex align-items-center col-12 mb-1'
            },
        })

        this.buildPvzItemSelectRow(el, deliveryTopRowNode)

        BX.append(
            BX.create({
                tag: 'div',
                props: {
                    className: 'column mb-2'
                },
                children: [
                    BX.create({
                        tag: 'div',
                        props: {
                            className: 'row'
                        },
                        children: [
                            deliveryTopRowNode,
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'col-6 pl-4 mb-2'
                                },
                                text: el.properties.fullAddress
                            }),
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'col-3'
                                },
                                children: [
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'font-weight-bold'
                                        },
                                        text: 'Срок доставки:'
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'ml-2'
                                        },
                                        text: 'от 1 дня'
                                    }),
                                ]

                            }),
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'col-3'
                                },
                                children: [
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'worktime-title'
                                        },
                                        text: 'Время работы:'
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'worktime-shedule'
                                        },
                                        text: el.properties.workTime
                                    })
                                ],
                            })
                        ]
                    })
                ]
            }),
            pvzListNode
        )
    },

    buildPvzItemSelectRow: function (el, deliveryTopRowNode) {
        //checkbox
        BX.append(
            BX.create({
                tag: 'input',
                props: {
                    type: 'radio',
                    id: el.id,
                    name: 'pvz',
                },
                events: {
                    change: BX.proxy(function (e) {
                        BX.adjust(
                            BX('select-pvz-item'),
                            {
                                dataset: {
                                    pvzid: el.id
                                }
                            }
                        )
                    })
                }
            }), deliveryTopRowNode
        )

        //delivery name
        BX.append(
            BX.create({
                tag: 'span',
                props: {
                    className: 'font-weight-bold ml-2'
                },
                text: el.properties.deliveryName
            }), deliveryTopRowNode
        )

        if (el.properties?.price  === undefined) {
            //delivery name
            BX.append(
                BX.create({
                    tag: 'span',
                    props: {
                        className: 'red_text font-weight-bold ml-3',
                        role: 'button'
                    },
                    text: 'Узнать цену',
                    events: {
                        click: BX.proxy(function (e) {
                            BX.Sale.OrderAjaxComponent.startLoader()
                            const parentNode = BX.findParent(e.target, {tag: 'div'})
                            this.getPvzItemPrice(el, parentNode)
                        }.bind(this))

                    }
                }), deliveryTopRowNode
            )
        } else {
            //delivery price
            BX.append(
                BX.create({
                    tag: 'span',
                    props: {
                        className: 'red_text font-weight-bold ml-3'
                    },
                    text: el.properties?.price + ' руб.'
                }), deliveryTopRowNode
            )
        }
    },

    /**
     * Фильтрация пунктов пвз по грузоперезвочику
     * @param deliveryName
     */
    filterPvzList: function(deliveryName)
    {
        this.componentParams.filterDelivery = deliveryName === 'Все' ? null : deliveryName
        this.showPVZ()
    },

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

        this.drawProps()
            .drawDelivery()
            .drawPayment()
            .drawNotice()
        return this
    },
    drawNotice: function()
    {
        this.checkout.notice.title = BX.findChild(this.checkout.notice.rootEl, {'class': 'bx-soa-section-title'}, true)
        this.checkout.notice.variants = {}
        this.checkout.notice.variants.rootEl = BX.findChild(this.checkout.notice.rootEl, {'class': 'form-check'}, true)
        this.checkout.notice.variants.sms = {}
        this.checkout.notice.variants.sms.rootEl = BX.findChild(this.checkout.notice.variants.rootEl, {'class': 'mr-5'}, true)
        this.checkout.notice.variants.sms.input = BX('sms')
        this.checkout.notice.variants.sms.title = BX.findChild(this.checkout.notice.variants.sms.rootEl, {'tag': 'label'}, true)
        this.checkout.notice.variants.telegram = {}
        this.checkout.notice.variants.telegram.rootEl = BX.findChild(this.checkout.notice.variants.rootEl, {'class': 'mr-5'}, true)
        this.checkout.notice.variants.telegram.input = BX('telegram')
        this.checkout.notice.variants.telegram.title = BX.findChild(this.checkout.notice.variants.telegram.rootEl, {'tag': 'label'}, true)
        this.checkout.notice.variants.call = {}
        this.checkout.notice.variants.call.rootEl = BX.findChild(this.checkout.notice.variants.rootEl, {'class': 'mr-5'}, true)
        this.checkout.notice.variants.call.input = BX('telephone')
        this.checkout.notice.variants.call.title = BX.findChild(this.checkout.notice.variants.call.rootEl, {'tag': 'label'}, true)
        return this
    },
    drawPayment: function()
    {
        // блок выбора оплаты
        this.checkout.paysystem.titleBox = BX.findChild(this.checkout.paysystem.rootEl, {
            'class':'bx-soa-section-title-container'}, true)
        this.checkout.paysystem.title = BX.findChild(this.checkout.paysystem.titleBox, {
            'class':'bx-soa-section-title'}, true)
        this.checkout.paysystem.titleIcon = BX.create('span', {attrs: {className: 'payment-title-icon'}});

        BX.removeClass(this.checkout.paysystem.titleBox, 'justify-content-between')
        BX.insertAfter(this.checkout.paysystem.titleIcon, this.checkout.paysystem.title)

        return this
    },

    drawDelivery: function()
    {
        const __this = this
        // скрытие адресных полей заказа
        // this.checkout.delivery.rootEl.querySelector('.box_with_delivery_type').classList.add('d-none')

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
            props: {className: 'delivery-variants', id: 'delivery-variants'}
        })
        this.checkout.delivery.variants.title = BX.create('div', {
            attrs: {className: 'delivery-variants-title'},
            html: '<span class="title-accent">Укажите</span> адрес и способ доставки'
        })
        this.checkout.delivery.variants.choose = BX.create('div', {
            attrs: {className: 'delivery-choose js__delivery-choose', id: 'delivery-choose'},
            text: 'Выбрать адрес и способ доставки',
            events: {
                click: BX.proxy(function () {
                    __this.openMap()
                })
            }
        })

        BX.adjust(this.checkout.delivery.variants.rootEl, {
            children: [
                this.checkout.delivery.variants.title,

                BX.create('div', {
                    props: {className: 'delivery-description row mb-3', id: 'delivery-description'},
                }), // адрес

                this.checkout.delivery.variants.choose,
            ]
        })

        BX.removeClass(this.checkout.delivery.titleBox, 'justify-content-between')
        BX.insertAfter(this.checkout.delivery.titleIcon, this.checkout.delivery.title)

        BX.insertAfter(this.checkout.delivery.variants.rootEl, this.checkout.delivery.titleBox)

        // предыдущие доставки
        // this.checkout.recentWrap
        if (BX.Sale.OrderAjaxComponent.savedDeliveryProfiles.length) {
            BX.SavedDeliveryProfiles.drawSavedProfiles(this);
        }
        return this;
    },

    drawProps: function()
    {
        this.checkout.user.title = BX.findChild(this.checkout.order.rootEl, {'tag':'h5'}, true);
        BX.addClass(this.checkout.user.title, 'checkout-block-title');
        BX.addClass(this.checkout.user.title, 'fw-normal');
        BX.addClass(BX.findChild(this.checkout.user.title, {'tag':'b'}, true), 'fw-normal');

        // физ/юр лицо
        this.checkout.user.type = BX.findChild(this.checkout.user.rootEl, {'class': 'bx-soa-section-title-container'});
        // BX.addClass(this.checkout.user.type, 'd-none');

        // ФИО
        this.checkout.user.name = BX.findChild(this.checkout.user.rootEl, {'attribute': {'data-property-id-row': 1}}, true);
        BX.removeClass(this.checkout.user.name, 'col-12');
        BX.addClass(this.checkout.user.name, 'col-md-6 col-lg-6 col-12  checkout-name-group');
        BX.adjust(this.checkout.user.name, {attrs: {'id': 'checkout-name-group'}});

        // телефон
        this.checkout.user.phone = BX.findChild(this.checkout.user.rootEl, {'attribute': {'data-property-id-row': 3}}, true);
        BX.removeClass(this.checkout.user.phone, 'col-12');
        BX.addClass(this.checkout.user.phone, 'col-md-6 col-lg-6 col-12 checkout-phone-group');
        BX.adjust(this.checkout.user.phone, {attrs: {'id': 'checkout-phone-group'}});

        // email
        this.checkout.user.email = BX.findChild(this.checkout.user.rootEl, {'attribute': {'data-property-id-row': 2}}, true);
        BX.removeClass(this.checkout.user.email, 'col-12');
        BX.addClass(this.checkout.user.email, 'col-md-6 col-lg-6 col-12 checkout-email-group');
        BX.adjust(this.checkout.user.email, {attrs: {'id':'checkout-email-group'}});

        // Город
        this.checkout.user.city = BX.findChild(this.checkout.user.rootEl, {'attribute': {'data-property-id-row': 6}}, true);
        BX.removeClass(this.checkout.user.city, 'd-none');
        BX.addClass(this.checkout.user.city, 'col-md-6 col-lg-6 col-12 checkout-city-group');
        BX.adjust(this.checkout.user.city, {attrs: {'id':'checkout-city-group'}});

        // блок региона
        BX.addClass(this.checkout.region.rootEl[0], 'd-none');
        BX.remove(this.checkout.region.rootEl[1]);
        this.checkout.region.rootEl = this.checkout.region.rootEl[0];

        return this
    },

};


