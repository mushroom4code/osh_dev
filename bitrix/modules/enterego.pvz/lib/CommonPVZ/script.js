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
    propAddressPvzId: null,
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
    propDateDeliveryId: null,
    propDeliveryTimeInterval: null,
    curDeliveryId: null,
    doorDeliveryId: null,
    pvzDeliveryId: null,
    shipmentCost: undefined,
    orderPackages: null,
    oshishaDeliveryOptions: null,
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
        this.updateFromDaData()
        this.updateDeliveryWidget(BX.Sale.OrderAjaxComponent.result)
    },

    refresh: function () {
        const order = BX.Sale.OrderAjaxComponent.result

        this.propAddressId            = order.ORDER_PROP.properties.find(prop => prop.CODE === 'ADDRESS')?.ID;
        this.propAddressPvzId         = order.ORDER_PROP.properties.find(prop => prop.CODE === 'ADDRESS_PVZ')?.ID;
        this.propCommonPVZId          = order.ORDER_PROP.properties.find(prop => prop.CODE === 'COMMON_PVZ')?.ID;
        this.propTypeDeliveryId       = order.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_DELIVERY')?.ID;
        this.propZipId                = order.ORDER_PROP.properties.find(prop => prop.CODE === 'ZIP')?.ID;
        this.propLocationId           = order.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION')?.ID;
        this.propCityId               = order.ORDER_PROP.properties.find(prop => prop.CODE === 'CITY')?.ID;
        this.propFiasId               = order.ORDER_PROP.properties.find(prop => prop.CODE === 'FIAS')?.ID;
        this.propKladrId              = order.ORDER_PROP.properties.find(prop => prop.CODE === 'KLADR')?.ID;
        this.propStreetKladrId        = order.ORDER_PROP.properties.find(prop => prop.CODE === 'STREET_KLADR')?.ID;
        this.propLatitudeId           = order.ORDER_PROP.properties.find(prop => prop.CODE === 'LATITUDE')?.ID;
        this.propLongitudeId          = order.ORDER_PROP.properties.find(prop => prop.CODE === 'LONGITUDE')?.ID;
        this.propDateDeliveryId       = order.ORDER_PROP.properties.find(prop => prop.CODE === 'DATE_DELIVERY')?.ID;
        this.propDeliveryTimeInterval = order.ORDER_PROP.properties.find(prop => prop.CODE === 'DELIVERYTIME_INTERVAL')?.ID;
        this.propTypePvzId            = order.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_PVZ')?.ID;

        this.hidePropField(this.propTypeDeliveryId)
        this.hidePropField(this.propAddressPvzId)
        this.hidePropField(this.propFiasId)
        this.hidePropField(this.propKladrId)
        this.hidePropField(this.propTypePvzId)
        this.hidePropField(this.propLatitudeId)
        this.hidePropField(this.propLongitudeId)
        this.hidePropField(this.propCommonPVZId)
        this.hidePropField(this.propDateDeliveryId)
        this.hidePropField(this.propDeliveryTimeInterval)
        this.hidePropField(this.propStreetKladrId)

        this.propAddressId = BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.find(prop => prop.CODE === 'ADDRESS')?.ID;
        if (this.propAddressId) {
            window.commonDelivery.bxPopup.init();
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
            BX.SaleCommonPVZ.updateDeliveryWidget(ajaxAns.order)

            if (BX.SaleCommonPVZ.curDeliveryId === BX.SaleCommonPVZ.doorDeliveryId) {
                BX.SaleCommonPVZ.buildDoorDelivery(ajaxAns.order)
            }
        }
    },

    updateDeliveryWidget: function (orderData) {
        const curDelivery = orderData.DELIVERY.find(delivery =>
            (delivery.ID === this.doorDeliveryId || delivery.ID === this.pvzDeliveryId) && delivery.CHECKED === 'Y')
        if (curDelivery === undefined)
            return

        this.drawInterface()
        let deliveryName = this.getValueProp(this.propTypeDeliveryId);

        if (curDelivery.CALCULATE_DESCRIPTION !== '') {
            const deliveryBox = JSON.parse(curDelivery.CALCULATE_DESCRIPTION ?? []).find(name => name.checked === true ||
                name.code === deliveryName )
            deliveryName = deliveryBox?.name;
        }

        let date = BX.Sale.OrderAjaxComponent.result.ORDER_PROP
            .properties.find(prop => prop.CODE === 'DATE_DELIVERY')?.VALUE[0] ?? '-';

        const address = this.curDeliveryId === this.pvzDeliveryId
            ? this.getValueProp(this.propAddressPvzId)
            : this.getValueProp(this.propAddressId)
        BX.cleanNode('delivery-description')

        if ((curDelivery  && curDelivery.PRICE_FORMATED) !== undefined ) {
            BX.adjust(
                BX('delivery-description'),
                {
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {className: 'col-md-6 col-lg-6 col-12', id: 'selected-delivery-type'},
                            html: `<span class="font-weight-600 font-lg-13"> Способ доставки: </span>
                                   <span class="ml-2 font-lg-13">${deliveryName}</span>`
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className: 'col-md-6 col-lg-6 col-12', id: 'selected-delivery-price'},
                            html: ` <span class="font-weight-600 font-lg-13">Стоимость:</span>
                                    <span class="ml-2 font-lg-13"> ${curDelivery?.PRICE_FORMATED ?? 'необходимо выбрать другую доставку'}</span>`
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className: 'col-md-6 col-lg-6 col-12', id: 'selected-delivery-address'},
                            html: `<span class="font-weight-600 font-lg-13">Адрес</span>: 
                                   <span class="ml-2 font-lg-13">${address}</span>`
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className: 'col-md-6 col-lg-6 col-12', id: 'selected-delivery-date'},
                            html: `<span class="font-weight-600 font-lg-13">Предпочтительная дата получения: </span>
                                   <span class="ml-2 font-lg-13">${date}</span>`
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
                    html: 'Выберите один из подходящих Вам вариантов: <br>' +
                        ' самовывоз, пункт выдачи заказов или доставка курьером до двери'
                }),
                BX('delivery-description')
            )
        }
    },

    /**
     *
     * @param orderData array
     */
    buildDoorDelivery: function (orderData) {
        const __this = this

        const propsNode = document.querySelector('div.delivery.bx-soa-pp-company.bx-selected .bx-soa-pp-company');
        BX.cleanNode(propsNode)
        this.clearDeliveryBlock()

       let pvzBox = BX.create({
            tag: 'div',
            props: {
                className: 'container-fluid d-flex flex-column overflow-auto my-2'
            },
            children: [
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'container-fluid d-lg-flex d-md-flex d-none flex-row flex-wrap table-header'
                    },
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'col-6'
                            },
                            html: '<i class="fa fa-map-marker color-redLight font-20 mr-2" aria-hidden="true"></i> ' +
                                '<span class="font-weight-500">Доставка + цена </span>'
                        }),
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'col-6'
                            },
                            html: '<i class="fa fa-truck color-redLight font-20 mr-2" aria-hidden="true"></i> ' +
                                '<span class="font-weight-500">Срок доставки </span>'
                        }),
                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'container-fluid overflow-auto my-2 table-body border-1-gray',
                        id: 'deliveries-list'
                    },
                }),
            ]
        })
        BX.append(pvzBox, BX('map_for_delivery'))

        const doorDelivery = orderData.DELIVERY.find(delivery => delivery.ID === this.doorDeliveryId && delivery.CHECKED === 'Y')
        const currentTypeDelivery = this.getValueProp(this.propTypeDeliveryId)

        if (doorDelivery !== undefined) {

            const deliveryInfo = JSON.parse(doorDelivery.CALCULATE_DESCRIPTION)

            deliveryInfo.forEach(delivery => {
                if (!delivery.error) {
                    const propsRadio = {type: 'radio', name: 'delivery'}
                    if (currentTypeDelivery === delivery.code) {
                        propsRadio.checked = "checked"
                    }
                    const boxWithDeliveryInfo = BX.create({
                        tag: 'div',
                        props: {
                            className: 'd-flex flex-lg-row flex-md-row flex-column col-lg-6 col-md-6 col-12 p-0'
                        },
                        children: [
                            //checkbox
                            BX.create({
                                tag: 'input',
                                dataset: {
                                    code: delivery.code,
                                    name_for_view: delivery.name
                                },
                                props: {
                                    type: 'radio',
                                    name: 'delivery',
                                    checked: delivery.code === 'oshisha' ? 'checked' : '',
                                    className: 'form-check-input',
                                },
                                events: {
                                    change: BX.proxy(function (e) {
                                        BX.adjust(
                                            BX('DELIVERY_SELECT_FOR_ORDER'),
                                            {
                                                html: '<i class="fa fa-map-marker color-redLight font-20 mr-2" ' +
                                                    'aria-hidden="true"></i> ' +
                                                    '<span class="font-weight-600 font-15">' + delivery.name + '</span>'
                                            }
                                        )
                                        __this.unlockSubmitButton();
                                    })
                                }
                            }),
                            BX.create({
                                tag: 'div',
                                props: {
                                    className: 'd-flex flex-column box-with-props-delivery'
                                },
                                children: [
                                    BX.create({
                                        tag: 'div',
                                        props: {
                                            className: 'd-flex flex-lg-row flex-md-row flex-column' +
                                                ' mb-1 box-with-price-delivery pl-3'
                                        },
                                        children: [
                                            // deliveryName
                                            BX.create({
                                                tag: 'div',
                                                props: {
                                                    className: 'font-weight-bold mb-1'
                                                },
                                                text: `${delivery.name}`
                                            }),
                                            BX.create({
                                                tag: 'div',
                                                props: {
                                                    className: 'pl-lg-3 pl-md-3 pl-0 red_text font-weight-bold'
                                                },
                                                html: delivery.price !== 0 ?
                                                    '<div class="d-flex flex-row">' +
                                                    '<i class="fa fa-rub color-redLight font-15 mr-2 d-lg-none d-md-none d-block"' +
                                                    ' aria-hidden="true"></i> ' +
                                                    delivery.price + ' руб.</div>' :
                                                    '<div class="d-flex flex-row">' +
                                                    '<i class="fa fa-rub color-redLight font-15 mr-2 d-lg-none d-md-none d-block"' +
                                                    ' aria-hidden="true"></i> Бесплатно</div> ',
                                            }),
                                        ]
                                    }),
                                ]
                            }),
                        ]
                    })

                    if (delivery.code === 'oshisha') {
                        var osh_block = BX.findChildByClassName(boxWithDeliveryInfo, 'box-with-props-delivery')
                        if (delivery.noMarkup != false) {
                            osh_block.appendChild(
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        className: 'pl-lg-3 pl-md-3 pl-3 mb-1 red_text font-weight-bold'
                                    },
                                    html: '<div class="d-flex flex-row">' +
                                        'В выбранном регионе следующая доставка без наценки будет ' + delivery.noMarkup +
                                        '</div>'
                                }),
                            );
                        }
                        osh_block.appendChild(this.updateOshishaDelivery());
                    }
                    BX.append(
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'column mb-3 bx-selected-delivery ',
                            },
                            children: [
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        className: 'd-flex flex-lg-row flex-md-row flex-column flex-wrap'
                                    },
                                    children: [
                                        boxWithDeliveryInfo,
                                        BX.create({
                                            tag: 'span',
                                            props: {
                                                className: 'col-lg-3 col-md-3 col-12 mb-2'
                                            },
                                            children: [
                                                BX.create({
                                                    tag: 'span',
                                                    props: {
                                                        className: 'd-lg-none d-md-none d-block'
                                                    },
                                                    html: '<i class="fa fa-truck color-redLight font-18 mr-2" aria-hidden="true"></i> ' +
                                                        '<span class="font-weight-500">Срок доставки: </span>'
                                                }),
                                                BX.create({
                                                    tag: 'span',
                                                    props: {
                                                        className: 'ml-lg-2 ml-md-2 ml-0 font-13'
                                                    },
                                                    text: 'от 2 дней'
                                                }),
                                            ]

                                        })
                                    ]
                                })
                            ]
                        }),
                        BX('deliveries-list')
                    );

                }
            })
            if (deliveryInfo.find(delivery => delivery.code === 'oshisha'))
                this.unlockSubmitButton()
        } else {
            const propPopupContainer = BX.create('DIV', {
                props: {className: 'container-fluid'},
                children: [
                    BX.create(
                        'h5', {
                            props: {className: 'font-weight-bold'},
                            html: "Укажите адрес доставки для расчета стоимости."
                        }
                    ),
                ]
            });

            BX.append(propPopupContainer, BX('deliveries-list'))
        }
        this.buildSuccessButtonDelivery()
    },

    buildDaDataField: function () {
        const __this=this

        const address = $(document).find('#user-address').val(this.getValueProp(this.propAddressId))
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
                    if (__this.curDeliveryId == __this.doorDeliveryId) {
                        __this.oshishaDeliveryOptions.DA_DATA_ADDRESS = suggestion.value;
                        __this.getSavedOshishaDelivery(Number('' + suggestion.data.geo_lat).toPrecision(6),
                            Number('' + suggestion.data.geo_lon).toPrecision(6));
                    }
                }
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

    updateOshishaDelivery: function (parentBlock) {
        var __this = this;

        window.commonDelivery.bxPopup.init();
        const oshMkad = window.commonDelivery.oshMkadDistance.init(__this.oshishaDeliveryOptions);
        return BX.create('a',
            {
                props: {
                    className: 'red_text text-decoration-underline font-weight-bold ml-3',
                    href: "javascript:void(0)",
                },
                html: '<span><i class="fa fa-map-marker color-redLight font-18 mr-2" aria-hidden="true"></i> Выбрать адрес на карте</span>',
                events: {
                    click: BX.proxy(function () {
                        oshMkad.afterSave = function (address) {
                            __this.oshishaDeliveryOptions.DA_DATA_ADDRESS = address;
                        }.bind(this);
                        window.commonDelivery.bxPopup.onPickerClick(__this.getValueProp(__this.propLatitudeId) ?? '',
                            __this.getValueProp(__this.propLongitudeId) ?? '',
                            __this.getValueProp(__this.propDateDeliveryId) ?? '',
                            __this.getValueProp(__this.propAddressId) ?? ''
                        );
                    }, this)
                }
            })
    },

    updateFromDaData: function () {
        const address = this.getValueProp(this.propAddressId)
        if (address !== '') {
            BX.ajax({
                url: this.ajaxUrlPVZ,
                method: 'POST',
                dataType: 'json',
                data: {
                    sessid: BX.bitrix_sessid(),
                    address: address,
                    'action': 'getDaData'
                },
                onsuccess: function (response) {
                    if (response.status === 'success') {
                        this.updatePropsFromDaData(response);
                        this.getSavedOshishaDelivery(Number('' + response.data.geo_lat).toPrecision(6),
                            Number('' + response.data.geo_lon).toPrecision(6));
                    } else {
                        this.updatePropsFromDaData({})
                    }

                }.bind(this)
            })
        }
    },

    openMap: function () {
        this.createPVZPopup();

        //если доставка не принадлежит ни одному из профилей единой доставки, то ставим по умолчанию доставку до ПВЗ
        if (this.curDeliveryId !== this.doorDeliveryId && this.pvzDeliveryId !== this.pvzDeliveryId) {
            this.curDeliveryId = this.pvzDeliveryId
        }

        if (this.curDeliveryId === this.doorDeliveryId) {
            this.buildDeliveryDate()
            this.buildAddressField()
            this.buildDoorDelivery(BX.Sale.OrderAjaxComponent.result)
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
        this.updateValueProp(this.propLatitudeId, suggestion?.data?.geo_lat ? Number('' + suggestion.data.geo_lat).toPrecision(6) : '');
        this.updateValueProp(this.propLongitudeId, suggestion?.data?.geo_lon ? Number('' + suggestion.data.geo_lon).toPrecision(6) : '');
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
        return document.querySelector(`input[name="ORDER_PROP_${prop_id}"]`)?.value ?? ''
    },

    hidePropField: function (prop_id) {
        if (!prop_id) {
            return
        }
        const propField = document.querySelector(`input[name="ORDER_PROP_${prop_id}"]`)
        if (!propField) {
            return;
        }
        BX.hide(BX.findParent(propField, {class: 'form-group'}))
    },

    closePvzPopup: function () {
        BX.hide(this.pvzOverlay);
        this.clearDeliveryBlock()
    },

    clearDeliveryBlock: function () {
        BX.cleanNode(BX('map_for_delivery'))
    },

    unlockSubmitButton: function () {
        BX.adjust(
            BX('select-door-delivery-item'),
            {
                props:{
                    style: '',
                }
            }
        )
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
                            className: 'pvz_user_data flex-lg-row flex-md-row flex-wrap'
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
            .buildDeliverySelect()
            // .buildMobileControls()

        BX.adjust(this.pvzOverlay, {style: {display: 'flex'}})
    },

    /**
     *   Построение карты с PVZ
     */
    buildPVZMap: function () {
        this.removeDeliveryDate()
        BX.remove(BX('user-address-wrap'))
        BX.remove(BX('button-success-delivery'))
        BX.show(BX('wrap_data_view'))
        BX.show(BX('wrap_sort_service'))
        // BX.show(BX('wrap_delivery_date'))
        this.buildSuccessButtonPVZ()
        this.getPVZList();
    },

    getCityName: function () {
        var __this = this;

        BX.ajax({
            url: __this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                sessid: BX.bitrix_sessid(),
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

    getSavedOshishaDelivery: function (latitude, longitude) {
        BX.ajax({
            url: this.ajaxUrlPVZ,
            method: 'POST',
            data: {
                sessid: BX.bitrix_sessid(),
                latitude: latitude,
                longitude: longitude,
                'action': 'getSavedOshishaDelivery'
            },
            onsuccess: function (res) {
                res = JSON.parse(res);
                if (res) {
                    BX.onCustomEvent('onDeliveryExtraServiceValueChange');
                } else {
                    window.commonDelivery.oshMkadDistance.init(this.oshishaDeliveryOptions).then(oshMkad => {
                        oshMkad.afterSave = null;
                        oshMkad.getDistance([latitude, longitude], this.getValueProp(this.propDateDeliveryId),
                            this.getValueProp(this.propAddressId), true);
                    })
                }
            }.bind(this),
            onfailure: function (res) {
                console.log('error getSavedOshishaDelivery');
            }
        });
    },

    saveOshishaDelivery: function(params) {
        var __this = this;
        BX.ajax({
            url: this.ajaxUrlPVZ,
            method: 'POST',
            dataType: 'json',
            data: {
                sessid: BX.bitrix_sessid(),
                params: params,
                'action': 'saveOshishaDelivery'
            },
            onsuccess: function (res) {
                if (!res) {
                    console.log('error while saving oshisha delivery to db');
                }
                __this.unlockSubmitButton();
                BX.onCustomEvent('onDeliveryExtraServiceValueChange');
            }.bind(this)
        });
    },

    reverseGeocodeAddress: async function (coordinates) {
        BX.ajax({
            url: this.ajaxUrlPVZ,
            method: 'POST',
            dataType: 'json',
            data: {
                sessid: BX.bitrix_sessid(),
                latitude: coordinates[0],
                longitude: coordinates[1],
                'action': 'reverseGeocodeAddress'
            },
            onsuccess: function (response) {
                if (response.status === 'success') {
                    document.querySelector(`input#user-address`).value = response?.value ?? '';
                    this.updatePropsFromDaData(response);
                    window.commonDelivery.oshMkadDistance.init(this.oshishaDeliveryOptions).then(oshMkad => {
                        oshMkad.afterSave = null;
                        oshMkad.getDistance([response.data.geo_lat, response.data.geo_lon],
                            this.getValueProp(this.propDateDeliveryId), this.getValueProp(this.propAddressId), true);
                    })
                } else {
                    window.commonDelivery.oshMkadDistance.init(this.oshishaDeliveryOptions).then(oshMkad => {
                        oshMkad.afterSave = null;
                        oshMkad.getDistance(coordinates, this.getValueProp(this.propDateDeliveryId),
                            this.getValueProp(this.propAddressId), true);
                    })
                }
            }.bind(this)
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
                sessid: BX.bitrix_sessid(),
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

        __this.closePvzPopup();
        const point = this.objectManager.objects.getById(objectId);
        BX.Sale.OrderAjaxComponent.result.DELIVERY.forEach((delivery) => {
            if (delivery['CHECKED'])
                delete delivery['CHECKED']
            if (delivery['ID'] === this.pvzDeliveryId)
                delivery['CHECKED'] = 'Y'
        })

        __this.updateValueProp(__this.propCommonPVZId, point.properties.code_pvz)
        __this.updateValueProp(__this.propTypeDeliveryId, point.properties.deliveryName)
        __this.updateValueProp(__this.propAddressPvzId, point.properties.fullAddress)
        __this.updateValueProp(__this.propTypePvzId, point.properties.type)
        BX.adjust(BX('DELIVERY_SELECT_FOR_ORDER'), {
                html: '<i class="fa fa-map-marker color-redLight font-20 mr-2" aria-hidden="true"></i>' +
                    '<b class="font-15">' + point.properties?.deliveryName + '</b> ' +
                    '<br> <span class="font-13">' + point.properties?.fullAddress+'</span>'
        })
        BX.Sale.OrderAjaxComponent.sendRequest()

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
                sessid: BX.bitrix_sessid(),
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
        const point = BX.SaleCommonPVZ.pvzObj.features.find(feature => feature.id == item.id)
        // const point = BX.SaleCommonPVZ.objectManager.objects.getById(item.id)

        const balloonContent = "".concat(
            `<div><b>${point.properties?.type === "POSTAMAT" ? 'Постомат' : 'ПВЗ'}${item.price ? ' - ' + item.price : ''} руб.</b></div>`,
            `<div>${point.properties.fullAddress}</div>`,
            point.properties.phone ? `<div>${point.properties.phone}</div>` : '',
            point.properties.workTime ? `<div>${point.properties.workTime}</div>` : '',
            point.properties.comment ? `<div><i>${point.properties.comment}</i></div>` : '',
            point.properties.postindex ? `<div><i>${point.properties.postindex}</i></div>` : '',
            item['error'] ? `<div>При расчете стоимости произошла ошибка, пожалуйста выберите другой ПВЗ или вид доставки</div>` :
                `<a class="btn btn_basket mt-2" href="javascript:void(0)" 
                    onclick="BX.SaleCommonPVZ.selectPvz(${item.id});" >Выбрать</a>`)

        point.properties = {
            ...point.properties,
            price: item.price,
            balloonContent: balloonContent,
        };
        if (clusterId === undefined && BX.SaleCommonPVZ.componentParams.displayPVZ === typeDisplayPVZ.map) {
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
        BX.remove(BX('button-success-delivery'))
        BX.remove(BX('button-success-pvz'))
        BX.hide(BX('wrap_data_view'))
        BX.hide(BX('wrap_sort_service'))

        BX.append(
            BX.create({
                tag: 'div',
                props: {className: 'order-6 col-12 wrap_filter_block mr-2', id: 'user-address-wrap'},
                children: [
                    BX.create({
                        tag: 'div',
                        props: {
                            className: 'd-flex flex-lg-row flex-md-row flex-column ',
                        },
                        children: [
                            BX.create({
                                tag: 'div',
                                props: {
                                    className: 'width-100',
                                },
                                children: [
                                    BX.create({
                                        tag: 'label',
                                        props: {className: 'title'},
                                        text: 'Введите адрес:'
                                    }),
                                    BX.create({
                                        tag: 'input',
                                        props: {
                                            id: 'user-address',
                                            className: 'form-control bx-soa-customer-input bx-ios-fix min-width-700',
                                        }
                                    })
                                ]
                            }),
                        ]
                    }),
                ]
            }),
            BX('pvz_user_data')
        )
        this.buildDaDataField()
        this.buildSuccessButtonDelivery()
        return this
    },

    buildDeliveryType: function () {
        const __this = this;

        const propPvzDelivery =  {
            id: 'delivery-self',
                className: 'radio-field form-check-input',
            type: 'radio',
            value: 'Самовывоз',
            name: 'delivery_type',
        }
        if (this.curDeliveryId === this.pvzDeliveryId ) {
            propPvzDelivery.checked = 'checked'
        }

        const propDoorDelivery = {
            id: 'delivery-in-hands',
            className: 'radio-field form-check-input',
            type: 'radio',
            value: 'Доставка курьером',
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
                        className: "wrap_filter_block mr-2"
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
                                                    __this.buildPVZMap();
                                                    BX.hide(BX('button-success-pvz'))
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
                                        text: 'Доставка курьером',
                                        children: [
                                            BX.create({
                                                tag: 'input',
                                                props: propDoorDelivery,
                                                events: {
                                                    change: BX.proxy(function () {
                                                        BX('ID_DELIVERY_ID_' + __this.doorDeliveryId).checked = true
                                                        //TODO default delivery type if not send
                                                        __this.buildDeliveryDate()
                                                        __this.buildAddressField()
                                                        BX.Sale.OrderAjaxComponent.sendRequest()

                                                    }),
                                                },
                                            }),
                                            BX.create({
                                                tag: 'span',
                                                props: {className: 'radio-caption'},
                                                text: 'Доставка курьером',
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

    removeDeliveryDate: function () {
      if (BX('wrap_delivery_date')){
          BX.remove(BX('wrap_delivery_date'));
      }
    },

    buildDeliveryDate: function () {
        const dateDeliveryNode = BX.create({
            tag: 'input',
            props: {
                type: 'text',
                readOnly: 'readonly',
                className: 'datepicker_order readonly form-control bx-soa-customer-input bx-ios-fix',
                style: 'background-color: unset',
            },
            dataset: {name: 'DATE_DELIVERY'},
        })

        if (!BX('wrap_delivery_date')) {
            BX.append(
                BX.create({
                    tag: 'div',
                    props: {
                        id: 'wrap_delivery_date',
                        className: "wrap_filter_block mr-2 order-5"
                    },
                    children: [
                        BX.create('DIV',{
                            children: [
                                BX.create({
                                    tag: 'label',
                                    props: {className: 'title'},
                                    text: 'Плановая дата доставки:'
                                }),
                                BX.create({
                                        tag: 'div',
                                        children: [
                                            dateDeliveryNode
                                        ]
                                    }
                                )
                            ]
                        })
                    ]
                }),
                BX('pvz_user_data')
            );

            const tomorrow    = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            let curDate = new Date(this.getValueProp(this.propDateDeliveryId))
            if (isNaN(curDate)) {
                curDate = tomorrow
            }
            var datepicker =  $(dateDeliveryNode).datepicker({
                minDate: tomorrow,
                selectedDates: curDate,
                onSelect: function (date, opts, datepicker) {
                    this.updateValueProp(this.propDateDeliveryId, date)
                    if (datepicker.opts.silentBool !== true) {
                        window.commonDelivery.oshMkadDistance.init(this.oshishaDeliveryOptions).then(oshMkad => {
                            oshMkad.afterSave = null;
                            oshMkad.getDistance([this.getValueProp(this.propLatitudeId), this.getValueProp(this.propLongitudeId)], this.getValueProp(this.propDateDeliveryId),
                                this.getValueProp(this.propAddressId), true);
                        })
                        BX.Sale.OrderAjaxComponent.sendRequest()
                    }
                }.bind(this)
            }).data('datepicker');
            datepicker.selectDate(curDate,(datepicker.opts.silentBool = true));
            datepicker.opts.silentBool = false;
        }

        return this
    },

    buildSuccessButtonPVZ: function () {
        if (!BX('button-success-pvz')) {
            BX.append(
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'wrap_filter_block order-5',
                        id: 'button-success-pvz'
                    },
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'd-flex align-items-end justify-content-center height-100',
                            },
                            children: [
                                BX.create({
                                    tag: 'a',
                                    props: {
                                        id: 'select-pvz-item',
                                        href: "javascript:void(0)",
                                        className: "link_red_button text-white mb-2 d-flex align-items-center justify-content-center",
                                        style: 'pointer-events: none;opacity: 0.5;'
                                    },
                                    text: 'Подтвердить',
                                    events: {
                                        click: BX.proxy(function () {
                                            BX.SaleCommonPVZ.selectPvz(this.dataset.pvzid)
                                        })
                                    }
                                }),
                            ]
                        })
                    ]
                }), BX('pvz_user_data'))
        }
        return this
    },

    buildSuccessButtonDelivery: function () {
        if (!BX('button-success-delivery')) {
            BX.append(
                BX.create({
                    tag: 'div',
                    props: {className: 'wrap_filter_block order-5 mt-2', id: 'button-success-delivery'},
                    children: [
                        BX.create({
                            tag: 'a',
                            props: {
                                id: 'select-door-delivery-item',
                                href: "javascript:void(0)",
                                className: "link_red_button text-white text-center mt-lg-4 mt-md-4 mt-1" +
                                    " d-flex align-items-center justify-content-center",
                                style: 'pointer-events: none;opacity: 0.5;'
                            },
                            text: 'Подтвердить ',
                            events: {
                                click: BX.proxy(function () {

                                    const selectDeliveryNode = BX('map_for_delivery').querySelector('input[type="radio"]:checked');

                                    this.updateValueProp(this.propTypeDeliveryId, selectDeliveryNode.dataset?.code)
                                    this.closePvzPopup()
                                    BX.Sale.OrderAjaxComponent.sendRequest()

                                }.bind(this))
                            }
                        })
                    ]
                }),
                BX('pvz_user_data')
            )
        }
        return this
    },

    buildDeliverySelect: function () {

        if (!BX('wrap_delivery_select')) {
            BX.append(
                BX.create({
                    tag: 'div',
                    props: {
                        id: 'wrap_delivery_select',
                        className: "wrap_filter_block mr-2 col-12 order-7"
                    },
                    children: [
                        BX.create('DIV',{
                            children: [
                                BX.create({
                                        tag: 'div',
                                        children: [
                                            BX.create({
                                                tag: 'div',
                                                props: {
                                                    className: 'mb-2 mt-2 pb-2 border-bottom-1-red',
                                                    id: 'DELIVERY_SELECT_FOR_ORDER'
                                                },
                                                dataset: {name: 'DELIVERY_SELECT_FOR_ORDER'},
                                                html: '<i class="fa fa-map-marker color-redLight font-20 mr-2" ' +
                                                    'aria-hidden="true"></i>' +
                                                    ' <span class="font-15">' +
                                                    'Здесь отображается выбранная вами доставка...</span>',
                                            })
                                        ]
                                    }
                                )
                            ]
                        })
                    ]
                }),
                BX('pvz_user_data')
            );
        }

        return this
    },

    buildDataView: function () {
        const __this = this;

        const propsOnMap = {
            id: 'data_view_map',
            className: 'radio-field form-check-input',
            type: 'radio',
            value: 'На карте',
            name: 'data_view',
        }
        if (this.componentParams.displayPVZ === typeDisplayPVZ.map) {
            propsOnMap.checked = 'checked'
        }

        const propsList = {
            id: 'data_view_list',
            className: 'radio-field form-check-input',
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
                        className: "wrap_filter_block mr-2"
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
                                                        BX.hide(BX('button-success-pvz'))
                                                        __this.componentParams.displayPVZ = typeDisplayPVZ.map
                                                        __this.showPVZ();
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
                                                        __this.componentParams.displayPVZ = typeDisplayPVZ.list
                                                        __this.showPVZ();
                                                        BX.show(BX('button-success-pvz'))
                                                        BX.onCustomEvent('onDeliveryExtraServiceValueChange')
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
        let pvzBox;
        pvzBox = BX.create({
            tag: 'div',
            props: {
                className: 'container-fluid d-flex flex-column overflow-auto box-with-pvz'
            },
            children: [
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'container-fluid d-lg-flex d-md-flex d-none flex-row flex-wrap table-header pr-5'
                    },
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'col-6'
                            },
                            html: '<i class="fa fa-map-marker color-redLight font-20 mr-2" aria-hidden="true"></i> ' +
                                '<span class="font-weight-500">Доставка + цена </span>'
                        }),
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'col-3'
                            },
                            html: '<i class="fa fa-truck color-redLight font-20 mr-2" aria-hidden="true"></i> ' +
                                '<span class="font-weight-500">Срок доставки </span>'
                        }),
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'col-3'
                            },
                            html: '<i class="fa fa-clock-o color-redLight font-20 mr-2" aria-hidden="true"></i> ' +
                                '<span class="font-weight-500">Режим работы </span>'
                        })
                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'container-fluid d-flex flex-column overflow-auto my-2 table-body border-1-gray p-0 ' +
                            'pr-lg-4 pr-md-4 pt-3'
                    },
                }),
            ]
        })
        BX.append(pvzBox, BX('map_for_delivery'))

        let pvzTableContain = BX.findChildByClassName(pvzBox, 'table-body');
        pvzList.forEach(el => {
            this.buildPvzItem(el, pvzTableContain)
        })

    },

    buildPvzItem: function (el, pvzListNode) {
        const boxWithDeliveryInfo = BX.create({
            tag: 'div',
            props: {
                className: 'd-flex flex-lg-row flex-md-row flex-column col-lg-6 col-md-6 col-12 p-0'
            },
            children: [
                //checkbox
                BX.create({
                    tag: 'input',
                    props: {
                        type: 'radio',
                        id: el.id,
                        name: 'pvz',
                        className: 'form-check-input',
                    },
                    dataset: {
                        code: el.properties.code_pvz,
                        name_for_view: el.properties.deliveryName + ':  ' + el.properties.fullAddress
                    },
                    events: {
                        change: BX.proxy(function (e) {
                            BX.adjust(
                                BX('select-pvz-item'),
                                {
                                    dataset: {
                                        pvzid: el.id
                                    },
                                    props:{
                                        style: '',
                                    }
                                }
                            )
                            BX.adjust(
                                BX('DELIVERY_SELECT_FOR_ORDER'),
                                {
                                    html: '<i class="fa fa-map-marker color-redLight font-20 mr-2" ' +
                                        'aria-hidden="true"></i><span class="font-weight-600 font-15">'
                                        + el.properties.deliveryName + '</span><br>' +
                                        ' <span class="font-13">' + el.properties.fullAddress + '</span>'
                                }
                            )
                        })
                    }
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'd-flex flex-column'
                    },
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'd-flex flex-lg-row flex-md-row flex-column' +
                                    ' mb-1 box-with-price-delivery pl-3'
                            },
                            children: [
                                // deliveryName
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        className: 'font-weight-bold mb-1'
                                    },
                                    text: el.properties.deliveryName
                                }),
                            ]
                        }),
                        BX.create({
                            tag: 'span',
                            props: {
                                className: 'pl-3 mb-2 font-13'
                            },
                            html: '<i class="fa fa-map-pin color-redLight font-15 mr-2" aria-hidden="true"></i>' + el.properties.fullAddress
                        })
                    ]
                }),
            ]
        })

        this.buildPvzItemPrice(el, BX.findChildByClassName(boxWithDeliveryInfo, 'box-with-price-delivery'))


        BX.append(
            BX.create({
                tag: 'div',
                props: {
                    className: 'column mb-3 bx-selected-delivery ',
                },
                children: [
                    BX.create({
                        tag: 'div',
                        props: {
                            className: 'd-flex flex-lg-row flex-md-row flex-column flex-wrap'
                        },
                        children: [
                            boxWithDeliveryInfo,
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'col-lg-3 col-md-3 col-12 mb-2'
                                },
                                children: [
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'font-weight-bold d-lg-none d-md-none d-block'
                                        },
                                        html: '<i class="fa fa-truck color-redLight font-18 mr-2" aria-hidden="true"></i> ' +
                                            '<span class="font-weight-500">Срок доставки: </span>'
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'ml-lg-2 ml-md-2 ml-0 font-13'
                                        },
                                        text: 'от 1 дня'
                                    }),
                                ]

                            }),
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'col-lg-3 col-md-3 col-12 d-flex flex-column mb-2'
                                },
                                children: [
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'worktime-title d-lg-none d-md-none d-block font-weight-bold'
                                        },
                                        html: '<i class="fa fa-clock-o color-redLight font-18 mr-2" aria-hidden="true"></i> ' +
                                            '<span class="font-weight-500">Режим работы: </span>'
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'worktime-shedule font-13'
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

    buildPvzItemPrice: function (el, deliveryTopRowNode) {
        if (el.properties?.price  === undefined) {
            //delivery name
            BX.append(
                BX.create({
                    tag: 'span',
                    props: {
                        className: 'red_text font-weight-bold ml-lg-3 ml-md-3 ml-0',
                        role: 'button'
                    },
                    text: 'Узнать цену',
                    events: {
                        click: BX.proxy(function (e) {
                            BX.Sale.OrderAjaxComponent.startLoader()
                            const data = [this.getPointData(el)]
                            const afterSuccess = function (res) {
                                const curPoint = BX.SaleCommonPVZ.pvzObj.features.find(feature => feature.id == res[0].id)
                                const parentNode = BX.findParent(e.target, {tag: 'div'})
                                BX.remove(e.target)
                                this.buildPvzItemPrice(curPoint, parentNode)
                                BX.Sale.OrderAjaxComponent.endLoader()
                            }.bind(this)
                            this.getRequestGetPvzPrice(data, afterSuccess)
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
                        className: 'red_text font-weight-bold ml-lg-3 ml-md-3 mb-2 d-flex flex-row'
                    },
                    html: '<i class="fa fa-rub color-redLight font-15 mr-2 d-lg-none' +
                        ' d-md-none d-block" aria-hidden="true"></i>' +el.properties?.price + ' руб.'
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

        const deliveryTitleBox = BX.findChild(this.checkout.delivery.rootEl,
            {'class':'bx-soa-section-title-container'}, true)

        this.checkout.delivery.title = BX.findChild(deliveryTitleBox,
            {'class':'bx-soa-section-title'}, true)
        BX.removeClass(deliveryTitleBox, 'justify-content-between')
        BX.insertAfter(BX.create('span', {attrs: {className: 'delivery-title-icon'}}), this.checkout.delivery.title)

        //Поиск блока с единой доставкой и замена его на виджет
        const pvzCheckBox = BX('ID_DELIVERY_ID_' + this.pvzDeliveryId)
        const listBox = BX.findParent(pvzCheckBox, {class: 'box_with_del_js'})
        BX.removeClass(listBox, 'd-flex')
        BX.addClass(listBox, 'd-none')
        const rootDelivery = BX.create({
            tag: 'div',
            props: {id: 'common-delivery-section'},
        })
        BX.insertBefore(rootDelivery, listBox)
        this.checkout.delivery.variants = {}

        const chooseBlock = BX.create('div', {
            attrs: {className: 'delivery-choose js__delivery-choose text-decoration-underline', id: 'delivery-choose'},
            text: 'Выбрать адрес и способ доставки',
            events: {
                click: BX.proxy(function () {
                    __this.openMap()
                })
            }
        })

        this.checkout.delivery.variants.rootEl = BX.create('div', {
            props: {className: 'delivery-variants', id: 'delivery-variants'},
            children: [
                BX.create('div', {
                    attrs: {className: 'delivery-variants-title'},
                    html: '<span class="title-accent">Укажите</span> адрес и способ доставки'
                }),

                BX.create('div', {
                    props: {className: 'delivery-description row mb-3', id: 'delivery-description'},
                }), // адрес

                chooseBlock,
            ]
        })

        BX.append(this.checkout.delivery.variants.rootEl, rootDelivery)

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


