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
        this.updateDeliveryWidget(BX.OrderPageComponents.result)
    },

    refresh: function () {
        const order = BX.OrderPageComponents.result

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

        // this.hidePropField(this.propTypeDeliveryId)
        // this.hidePropField(this.propAddressPvzId)
        // this.hidePropField(this.propFiasId)
        // this.hidePropField(this.propKladrId)
        // this.hidePropField(this.propTypePvzId)
        // this.hidePropField(this.propLatitudeId)
        // this.hidePropField(this.propLongitudeId)
        // this.hidePropField(this.propCommonPVZId)
        // this.hidePropField(this.propDateDeliveryId)
        // this.hidePropField(this.propDeliveryTimeInterval)
        // this.hidePropField(this.propStreetKladrId)

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
                            props: {className: 'col-md-6 col-lg-6 col-12 basis-1/2', id: 'selected-delivery-type'},
                            html: `<span class="font-bold font-lg-13"> Способ доставки: </span>
                                   <span class="ml-2 font-lg-13">${deliveryName}</span>`
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className: 'basis-1/2', id: 'selected-delivery-price'},
                            html: ` <span class="font-bold font-lg-13">Стоимость:</span>
                                    <span class="ml-2 font-lg-13"> ${curDelivery?.PRICE_FORMATED ?? 'необходимо выбрать другую доставку'}</span>`
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className: 'basis-1/2', id: 'selected-delivery-address'},
                            html: `<span class="font-bold font-lg-13">Адрес</span>:
                                   <span class="ml-2 font-lg-13">${address}</span>`
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className: 'basis-1/2', id: 'selected-delivery-date'},
                            html: `<span class="font-bold font-lg-13">Предпочтительная дата получения: </span>
                                   <span class="ml-2 font-lg-13">${date}</span>`
                        })
                    ]
                }
            )
            // BX.addClass(BX('delivery-variants'), 'active')
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
                className: 'w-full pl-[20px] pr-[28px] mx-auto flex flex-col overflow-auto my-2'
            },
            children: [
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'w-full px-[15px] mx-auto lg:flex md:flex hidden flex-row flex-wrap table-header'
                    },
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'basis-1/2'
                            },
                            html: '<svg class="inline-block mr-2" width="15" height="20" viewBox="0 0 15 20"' +
                                ' fill="none" xmlns="http://www.w3.org/2000/svg"><path class="fill-light-red' +
                                ' dark:fill-grayLight" d="M12.4381 11.175C11.797' +
                                ' 12.5025 10.9283 13.825 10.04 15.0125C9.19742 16.132 8.29643 17.2044 7.341' +
                                ' 18.225C6.38555 17.2044 5.48457 16.132 4.64196 15.0125C3.7537 13.825 2.88501' +
                                ' 12.5025 2.2439 11.175C1.59544 9.83375 1.2235 8.5775 1.2235 7.5C1.2235 5.8424' +
                                ' 1.86802 4.25269 3.01528 3.08058C4.16253 1.90848 5.71854 1.25 7.341 1.25C8.96346' +
                                ' 1.25 10.5195 1.90848 11.6667 3.08058C12.814 4.25269 13.4585 5.8424 13.4585' +
                                ' 7.5C13.4585 8.5775 13.0853 9.83375 12.4381 11.175ZM7.341 20C7.341 20 14.682' +
                                ' 12.8925 14.682 7.5C14.682 5.51088 13.9086 3.60322 12.5319 2.1967C11.1552 0.790176' +
                                ' 9.28796 0 7.341 0C5.39405 0 3.52683 0.790176 2.15013 2.1967C0.773425 3.60322' +
                                ' 2.90119e-08 5.51088 0 7.5C0 12.8925 7.341 20 7.341 20Z" fill="#CD1D1D"/><path' +
                                ' class="fill-light-red dark:fill-grayLight" d="M7.34116 10C6.69217 10 6.06977' +
                                ' 9.73661 5.61086 9.26777C5.15196 8.79893' +
                                ' 4.89415 8.16304 4.89415 7.5C4.89415 6.83696 5.15196 6.20107 5.61086' +
                                ' 5.73223C6.06977 5.26339 6.69217 5 7.34116 5C7.99014 5 8.61254 5.26339' +
                                ' 9.07145 5.73223C9.53035 6.20107 9.78816 6.83696 9.78816 7.5C9.78816 8.16304' +
                                ' 9.53035 8.79893 9.07145 9.26777C8.61254 9.73661 7.99014 10 7.34116 10ZM7.34116' +
                                ' 11.25C8.31463 11.25 9.24824 10.8549 9.93659 10.1517C10.6249 9.44839 11.0117' +
                                ' 8.49456 11.0117 7.5C11.0117 6.50544 10.6249 5.55161 9.93659 4.84835C9.24824' +
                                ' 4.14509 8.31463 3.75 7.34116 3.75C6.36768 3.75 5.43407 4.14509 4.74572' +
                                ' 4.84835C4.05737 5.55161 3.67065 6.50544 3.67065 7.5C3.67065 8.49456 4.05737' +
                                ' 9.44839 4.74572 10.1517C5.43407 10.8549 6.36768 11.25 7.34116 11.25Z"' +
                                ' fill="#CD1D1D"/></svg>' +
                                '<span class="font-medium text-[15px]">Доставка + цена </span>'
                        }),
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'basis-1/2'
                            },
                            html: '<svg class="inline-block mr-2" width="36" height="21" viewBox="0 0 36 21" fill="none"' +
                                ' xmlns="http://www.w3.org/2000/svg"><path class="stroke-light-red' +
                                ' dark:stroke-grayLight" d="M11.5928 19.3313C12.3955 19.3313' +
                                ' 13.1653 19.0342 13.7328 18.5053C14.3004 17.9765 14.6193 17.2591 14.6193' +
                                ' 16.5112C14.6193 15.7632 14.3004 15.0459 13.7328 14.517C13.1653 13.9882 12.3955' +
                                ' 13.691 11.5928 13.691C10.7902 13.691 10.0204 13.9882 9.45283 14.517C8.88526' +
                                ' 15.0459 8.56641 15.7632 8.56641 16.5112C8.56641 17.2591 8.88526 17.9765 9.45283' +
                                ' 18.5053C10.0204 19.0342 10.7902 19.3313 11.5928 19.3313ZM26.725 19.3313C27.5277' +
                                ' 19.3313 28.2974 19.0342 28.865 18.5053C29.4326 17.9765 29.7514 17.2591 29.7514' +
                                ' 16.5112C29.7514 15.7632 29.4326 15.0459 28.865 14.517C28.2974 13.9882 27.5277' +
                                ' 13.691 26.725 13.691C25.9223 13.691 25.1526 13.9882 24.585 14.517C24.0174 15.0459' +
                                ' 23.6986 15.7632 23.6986 16.5112C23.6986 17.2591 24.0174 17.9765 24.585' +
                                ' 18.5053C25.1526 19.0342 25.9223 19.3313 26.725 19.3313Z" stroke="#CD1D1D"' +
                                ' stroke-width="1.5" stroke-miterlimit="1.5" stroke-linecap="round"' +
                                ' stroke-linejoin="round"/><path class="stroke-light-red dark:stroke-grayLight"' +
                                ' d="M14.6946 16.5108H22.185V1.84604C22.185' +
                                ' 1.62166 22.0894 1.40647 21.9191 1.2478C21.7488 1.08914 21.5179 1 21.2771' +
                                ' 1H1M8.03646 16.5108H4.93436C4.81513 16.5108 4.69707 16.4889' +
                                ' 4.58691 16.4464C4.47676 16.4039 4.37667 16.3416 4.29236 16.263C4.20805' +
                                ' 16.1845 4.14117 16.0912 4.09554 15.9885C4.04992 15.8859 4.02643 15.7759' +
                                ' 4.02643 15.6648V8.75541" stroke="#CD1D1D" stroke-width="1.5" stroke-linecap="round"/>' +
                                '<path class="stroke-light-red dark:stroke-grayLight" d="M2.51758 5.23016H8.57044"' +
                                ' stroke="#CD1D1D" stroke-width="1.5"' +
                                ' stroke-linecap="round" stroke-linejoin="round"/><path class="stroke-light-red' +
                                ' dark:stroke-grayLight" d="M22.1836' +
                                ' 5.23016H30.6727C30.8482 5.2302 31.02 5.27764 31.1671 5.36673C31.3143 5.45582' +
                                ' 31.4306 5.58275 31.502 5.73215L34.2106 11.4119C34.2622 11.5198 34.289' +
                                ' 11.6365 34.2893 11.7546V15.6647C34.2893 15.7758 34.2658 15.8858 34.2202' +
                                ' 15.9885C34.1746 16.0911 34.1077 16.1844 34.0234 16.263C33.9391 16.3415' +
                                ' 33.839 16.4038 33.7288 16.4464C33.6187 16.4889 33.5006 16.5108 33.3814' +
                                ' 16.5108H30.5063M22.1836 16.5108H23.6968" stroke="#CD1D1D" stroke-width="1.5"' +
                                ' stroke-linecap="round"/></svg> ' +
                                '<span class="font-medium text-[15px]">Срок доставки </span>'
                        }),
                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'w-full px-[15px] pt-3 mx-auto overflow-auto my-2 table-body border border-x-0' +
                            ' border-grey-line-order dark:border-grayLight',
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
                            className: 'flex lg:flex-row md:flex-row flex-col lg:basis-1/2 lg:max-w-[50%]' +
                                ' md:basis-1/2 md:max-w-[50%] basis-full max-w-[100%] p-0'
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
                                    className: 'form-check-input radio-field form-check-input ring-0 focus:ring-0' +
                                        ' focus:ring-transparent focus:ring-offset-transparent focus:shadow-none' +
                                        ' focus:outline-none',
                                },
                                events: {
                                    change: BX.proxy(function (e) {
                                        BX.adjust(
                                            BX('DELIVERY_SELECT_FOR_ORDER'),
                                            {
                                                html: '<i class="fa fa-map-marker color-redLight font-20 mr-2" ' +
                                                    'aria-hidden="true"></i> ' +
                                                    '<span class="font-bold text-lg">' + delivery.name + '</span>'
                                            }
                                        )
                                        __this.unlockSubmitButton();
                                    })
                                }
                            }),
                            BX.create({
                                tag: 'div',
                                props: {
                                    className: 'flex flex-col box-with-props-delivery'
                                },
                                children: [
                                    BX.create({
                                        tag: 'div',
                                        props: {
                                            className: 'flex lg:flex-row md:flex-row flex-col' +
                                                ' lg:mb-1 md:mb-1 mb-0 box-with-price-delivery lg:pl-4 md:pl-4 pl-6'
                                        },
                                        children: [
                                            // deliveryName
                                            BX.create({
                                                tag: 'div',
                                                props: {
                                                    className: 'font-bold text-lg mb-3 !leading-4'
                                                },
                                                text: `${delivery.name}`
                                            }),
                                            BX.create({
                                                tag: 'div',
                                                props: {
                                                    className: 'lg:pl-4 md:pl-4 pl-0 text-light-red dark:text-white'
                                                },
                                                html: delivery.price !== 0 ?
                                                    '<span class="flex flex-row lg:items-start md:items-start' +
                                                    ' lg:mb-0 md:mb-0 mb-3' +
                                                    ' items-center lg:text-lg md:text-lg text-[13px] font-bold' +
                                                    ' !leading-4">' +
                                                    '<svg class="lg:hidden md:hidden inline-flex mr-3"' +
                                                    ' width="25" height="25" viewBox="0 0 24 24" fill="none"' +
                                                    ' xmlns="http://www.w3.org/2000/svg">' +
                                                    '<g clip-path="url(#clip0_2881_7234)">' +
                                                    '<path class="fill-light-red dark:fill-white"' +
                                                    ' fill-rule="evenodd" clip-rule="evenodd" d="M11.7637' +
                                                    ' 23.1953C5.41208 23.1953 0.263672 18.0469 0.263672' +
                                                    ' 11.6953C0.263672 5.34372 5.41208 0.195312 11.7637' +
                                                    ' 0.195312C18.1153 0.195312 23.2637 5.34372 23.2637' +
                                                    ' 11.6953C23.2637 18.0469 18.1153 23.1953 11.7637' +
                                                    ' 23.1953ZM7.81055 11.1476V12.4471H9.07842V14.3216H7' +
                                                    '.81055V15.5471H9.07842V18.1641H10.5073V15.5471H13.' +
                                                    '3823V14.3216H10.5073V12.4471H12.3106C12.86 12.4517 13.4072' +
                                                    ' 12.3767 13.935 12.2243C14.4353 12.0755 14.8694 11.8506' +
                                                    ' 15.2388 11.5465C15.6083 11.2439 15.9001 10.8637 16.1143' +
                                                    ' 10.4052C16.3285 9.94731 16.4355 9.40897 16.4355' +
                                                    ' 8.79012C16.4355 8.172 16.3349 7.64012 16.1322 7.1945C15.9295' +
                                                    ' 6.74888 15.65 6.38016 15.2927 6.08978C14.9355 5.79941 14.5071' +
                                                    ' 5.58234 14.0069 5.44003C13.466 5.29236 12.9072 5.22052' +
                                                    ' 12.3466 5.22656H9.07842V11.1476H7.81055ZM12.3459' +
                                                    ' 11.1476H10.5073V6.52606H12.3466C13.1674 6.52606 13.8135' +
                                                    ' 6.70575 14.2836 7.06441C14.7537 7.42306 14.9894 7.99878' +
                                                    ' 14.9894 8.79012C14.9894 9.58219 14.7537 10.173 14.2836' +
                                                    ' 10.5633C13.8135 10.9528 13.1674 11.1476 12.3466' +
                                                    ' 11.1476H12.3459Z" fill="white"/>'+
                                                '</g>' +
                                                '<defs>' +
                                                    '<clipPath id="clip0_2881_7234">' +
                                                        '<rect width="23" height="23" fill="white"' +
                                                        ' transform="translate(0.263672 0.195312)"/>' +
                                                    '</clipPath>' +
                                                '</defs>' +
                                                '</svg> ' +
                                                    delivery.price + ' руб.</span>' :
                                                    '<span class="flex flex-row lg:items-start md:items-start' +
                                                    ' lg:mb-0 md:mb-0 mb-3' +
                                                    ' items-center lg:text-lg md:text-lg text-[13px] font-bold' +
                                                    ' !leading-4">' +
                                                    '<svg class="lg:hidden md:hidden inline-flex mr-3"' +
                                                    ' width="25" height="25" viewBox="0 0 24 24" fill="none"' +
                                                    ' xmlns="http://www.w3.org/2000/svg">' +
                                                    '<g clip-path="url(#clip0_2881_7234)">' +
                                                    '<path class="fill-light-red dark:fill-white"' +
                                                    ' fill-rule="evenodd" clip-rule="evenodd" d="M11.7637' +
                                                    ' 23.1953C5.41208 23.1953 0.263672 18.0469 0.263672' +
                                                    ' 11.6953C0.263672 5.34372 5.41208 0.195312 11.7637' +
                                                    ' 0.195312C18.1153 0.195312 23.2637 5.34372 23.2637' +
                                                    ' 11.6953C23.2637 18.0469 18.1153 23.1953 11.7637' +
                                                    ' 23.1953ZM7.81055 11.1476V12.4471H9.07842V14.3216H7' +
                                                    '.81055V15.5471H9.07842V18.1641H10.5073V15.5471H13.' +
                                                    '3823V14.3216H10.5073V12.4471H12.3106C12.86 12.4517 13.4072' +
                                                    ' 12.3767 13.935 12.2243C14.4353 12.0755 14.8694 11.8506' +
                                                    ' 15.2388 11.5465C15.6083 11.2439 15.9001 10.8637 16.1143' +
                                                    ' 10.4052C16.3285 9.94731 16.4355 9.40897 16.4355' +
                                                    ' 8.79012C16.4355 8.172 16.3349 7.64012 16.1322 7.1945C15.9295' +
                                                    ' 6.74888 15.65 6.38016 15.2927 6.08978C14.9355 5.79941 14.5071' +
                                                    ' 5.58234 14.0069 5.44003C13.466 5.29236 12.9072 5.22052' +
                                                    ' 12.3466 5.22656H9.07842V11.1476H7.81055ZM12.3459' +
                                                    ' 11.1476H10.5073V6.52606H12.3466C13.1674 6.52606 13.8135' +
                                                    ' 6.70575 14.2836 7.06441C14.7537 7.42306 14.9894 7.99878' +
                                                    ' 14.9894 8.79012C14.9894 9.58219 14.7537 10.173 14.2836' +
                                                    ' 10.5633C13.8135 10.9528 13.1674 11.1476 12.3466' +
                                                    ' 11.1476H12.3459Z" fill="white"/>'+
                                                    '</g>' +
                                                    '<defs>' +
                                                    '<clipPath id="clip0_2881_7234">' +
                                                    '<rect width="23" height="23" fill="white"' +
                                                    ' transform="translate(0.263672 0.195312)"/>' +
                                                    '</clipPath>' +
                                                    '</defs>' +
                                                    '</svg> ' +
                                                    ' Бесплатно</span> ',
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
                                    html: '<div class="flex flex-row">' +
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
                                className: 'column mb-3 bx-selected-delivery p-[19px] rounded-[10px] border' +
                                    ' dark:border-0 border-grey-line-order dark:!border-white' +
                                    ' bg-transparent dark:bg-lightGrayBg',
                            },
                            children: [
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        className: 'flex lg:flex-row md:flex-row flex-col flex-wrap'
                                    },
                                    children: [
                                        boxWithDeliveryInfo,
                                        BX.create({
                                            tag: 'span',
                                            props: {
                                                className: 'lg:basis-1/4 lg:max-w-[100%] md:basis-1/4 md:max-w-[100%]' +
                                                    ' basis-full max-w-[100%] mb-2 lg:pl-4 md:pl-4 pl-6'
                                            },
                                            children: [
                                                BX.create({
                                                    tag: 'span',
                                                    props: {
                                                        className: 'ml-lg-2 ml-md-2 ml-0 lg:mb-0 md:mb-0 mb-3 font-13'
                                                    },
                                                    html: '<svg class="lg:hidden md:hidden inline-flex mr-2"' +
                                                        ' width="25" height="25" viewBox="0 0 36 21" fill="none"' +
                                                        ' xmlns="http://www.w3.org/2000/svg"><path class="stroke-light-red' +
                                                        ' dark:stroke-white" d="M11.5928 19.3313C12.3955 19.3313' +
                                                        ' 13.1653 19.0342 13.7328 18.5053C14.3004 17.9765 14.6193 17.2591 14.6193' +
                                                        ' 16.5112C14.6193 15.7632 14.3004 15.0459 13.7328 14.517C13.1653 13.9882 12.3955' +
                                                        ' 13.691 11.5928 13.691C10.7902 13.691 10.0204 13.9882 9.45283 14.517C8.88526' +
                                                        ' 15.0459 8.56641 15.7632 8.56641 16.5112C8.56641 17.2591 8.88526 17.9765 9.45283' +
                                                        ' 18.5053C10.0204 19.0342 10.7902 19.3313 11.5928 19.3313ZM26.725 19.3313C27.5277' +
                                                        ' 19.3313 28.2974 19.0342 28.865 18.5053C29.4326 17.9765 29.7514 17.2591 29.7514' +
                                                        ' 16.5112C29.7514 15.7632 29.4326 15.0459 28.865 14.517C28.2974 13.9882 27.5277' +
                                                        ' 13.691 26.725 13.691C25.9223 13.691 25.1526 13.9882 24.585 14.517C24.0174 15.0459' +
                                                        ' 23.6986 15.7632 23.6986 16.5112C23.6986 17.2591 24.0174 17.9765 24.585' +
                                                        ' 18.5053C25.1526 19.0342 25.9223 19.3313 26.725 19.3313Z" stroke="#CD1D1D"' +
                                                        ' stroke-width="1.5" stroke-miterlimit="1.5" stroke-linecap="round"' +
                                                        ' stroke-linejoin="round"/><path class="stroke-light-red dark:stroke-white"' +
                                                        ' d="M14.6946 16.5108H22.185V1.84604C22.185' +
                                                        ' 1.62166 22.0894 1.40647 21.9191 1.2478C21.7488 1.08914 21.5179 1 21.2771' +
                                                        ' 1H1M8.03646 16.5108H4.93436C4.81513 16.5108 4.69707 16.4889' +
                                                        ' 4.58691 16.4464C4.47676 16.4039 4.37667 16.3416 4.29236 16.263C4.20805' +
                                                        ' 16.1845 4.14117 16.0912 4.09554 15.9885C4.04992 15.8859 4.02643 15.7759' +
                                                        ' 4.02643 15.6648V8.75541" stroke="#CD1D1D" stroke-width="1.5" stroke-linecap="round"/>' +
                                                        '<path class="stroke-light-red dark:stroke-white" d="M2.51758 5.23016H8.57044"' +
                                                        ' stroke="#CD1D1D" stroke-width="1.5"' +
                                                        ' stroke-linecap="round" stroke-linejoin="round"/><path class="stroke-light-red' +
                                                        ' dark:stroke-white" d="M22.1836' +
                                                        ' 5.23016H30.6727C30.8482 5.2302 31.02 5.27764 31.1671 5.36673C31.3143 5.45582' +
                                                        ' 31.4306 5.58275 31.502 5.73215L34.2106 11.4119C34.2622 11.5198 34.289' +
                                                        ' 11.6365 34.2893 11.7546V15.6647C34.2893 15.7758 34.2658 15.8858 34.2202' +
                                                        ' 15.9885C34.1746 16.0911 34.1077 16.1844 34.0234 16.263C33.9391 16.3415' +
                                                        ' 33.839 16.4038 33.7288 16.4464C33.6187 16.4889 33.5006 16.5108 33.3814' +
                                                        ' 16.5108H30.5063M22.1836 16.5108H23.6968" stroke="#CD1D1D" stroke-width="1.5"' +
                                                        ' stroke-linecap="round"/></svg> ' + 'от 2 дней'
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
                props: {className: 'w-full px-[15px] mx-auto'},
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
                    className: 'red_text underline lg:pl-4 md:pl-4 pl-6 mb-2',
                    href: "javascript:void(0)",
                },
                html: '<span class="font-bold lg:text-lg md:text-lg text-[13px] !leading-4 text-light-red' +
                    ' dark:text-white">' +
                    '<svg class="inline-flex lg:w-[15px] md:w-[15px] lg:h-[20px] md:h-[20px] w-[25px]' +
                    ' h-[25px] mr-3" width="15" height="20" viewBox="0 0 15 20"' +
                    ' fill="none" xmlns="http://www.w3.org/2000/svg"><path class="fill-light-red' +
                    ' dark:fill-white" d="M12.4381 11.175C11.797' +
                    ' 12.5025 10.9283 13.825 10.04 15.0125C9.19742 16.132 8.29643 17.2044 7.341' +
                    ' 18.225C6.38555 17.2044 5.48457 16.132 4.64196 15.0125C3.7537 13.825 2.88501' +
                    ' 12.5025 2.2439 11.175C1.59544 9.83375 1.2235 8.5775 1.2235 7.5C1.2235 5.8424' +
                    ' 1.86802 4.25269 3.01528 3.08058C4.16253 1.90848 5.71854 1.25 7.341 1.25C8.96346' +
                    ' 1.25 10.5195 1.90848 11.6667 3.08058C12.814 4.25269 13.4585 5.8424 13.4585' +
                    ' 7.5C13.4585 8.5775 13.0853 9.83375 12.4381 11.175ZM7.341 20C7.341 20 14.682' +
                    ' 12.8925 14.682 7.5C14.682 5.51088 13.9086 3.60322 12.5319 2.1967C11.1552 0.790176' +
                    ' 9.28796 0 7.341 0C5.39405 0 3.52683 0.790176 2.15013 2.1967C0.773425 3.60322' +
                    ' 2.90119e-08 5.51088 0 7.5C0 12.8925 7.341 20 7.341 20Z" fill="#CD1D1D"/><path' +
                    ' class="fill-light-red dark:fill-white" d="M7.34116 10C6.69217 10 6.06977 9.73661 5.61086' +
                    ' 9.26777C5.15196 8.79893' +
                    ' 4.89415 8.16304 4.89415 7.5C4.89415 6.83696 5.15196 6.20107 5.61086' +
                    ' 5.73223C6.06977 5.26339 6.69217 5 7.34116 5C7.99014 5 8.61254 5.26339' +
                    ' 9.07145 5.73223C9.53035 6.20107 9.78816 6.83696 9.78816 7.5C9.78816 8.16304' +
                    ' 9.53035 8.79893 9.07145 9.26777C8.61254 9.73661 7.99014 10 7.34116 10ZM7.34116' +
                    ' 11.25C8.31463 11.25 9.24824 10.8549 9.93659 10.1517C10.6249 9.44839 11.0117' +
                    ' 8.49456 11.0117 7.5C11.0117 6.50544 10.6249 5.55161 9.93659 4.84835C9.24824' +
                    ' 4.14509 8.31463 3.75 7.34116 3.75C6.36768 3.75 5.43407 4.14509 4.74572' +
                    ' 4.84835C4.05737 5.55161 3.67065 6.50544 3.67065 7.5C3.67065 8.49456 4.05737' +
                    ' 9.44839 4.74572 10.1517C5.43407 10.8549 6.36768 11.25 7.34116 11.25Z"' +
                    ' fill="#CD1D1D"/></svg>' +
                    'Выбрать адрес на карте' +
                    '</span>',
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
            this.buildDeliveryTime()
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
        console.log('create pvz');

        if (BX('wrap_pvz_overlay')) {
            this.pvzOverlay = BX('wrap_pvz_overlay')
        } else {
            const pvzPopup = BX.create({
                tag: 'div',
                props: {
                    id: 'wrap_pvz_map',
                    className: "wrap_pvz_map bg-white dark:bg-darkBox dark:text-white dark:border-grey-line-order"
                },
                children: [
                    BX.create({
                        tag: 'div',
                        props: {
                            id: 'wrap_pvz_close',
                            className: "wrap_pvz_close before:bg-grayLight after:bg-grayLight" +
                                " hover:before:bg-light-red hover:after:bg-light-red" +
                                " dark:hover:before:bg-white dark:hover:after:bg-white js__wrap_pvz_close"
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
        this.removeDeliveryTime()
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
                `<a class="btn btn_basket mt-2 dark:text-textDark shadow-md text-white dark:bg-dark-red bg-light-red
                    lg:py-2 py-3 px-4 rounded-5 block text-center font-semibold" href="javascript:void(0)"
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
                props: {className: 'order-6 col-12 px-0 py-[1em] w-full', id: 'user-address-wrap'},
                children: [
                    BX.create({
                        tag: 'div',
                        props: {
                            className: 'flex lg:flex-row md:flex-row flex-col',
                        },
                        children: [
                            BX.create({
                                tag: 'div',
                                props: {
                                    className: 'flex-auto',
                                },
                                children: [
                                    BX.create({
                                        tag: 'div',
                                        props: {className: 'title font-medium mb-[0.8em] uppercase'},
                                        text: 'Введите адрес:'
                                    }),
                                    BX.create({
                                        tag: 'input',
                                        props: {
                                            id: 'user-address',
                                            className: 'form-control bx-soa-customer-input bx-ios-fix min-width-700' +
                                                'w-full text-sm cursor-text border-grey-line-order ' +
                                                'ring:grey-line-order dark:border-grayButton rounded-lg dark:bg-grayButton',
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
                className: 'radio-field mr-[1em] form-check-input ring-0 focus:ring-0 focus:ring-transparent' +
                    ' focus:ring-offset-transparent focus:shadow-none focus:outline-none',
            type: 'radio',
            value: 'Самовывоз',
            name: 'delivery_type',
        }
        if (this.curDeliveryId === this.pvzDeliveryId ) {
            propPvzDelivery.checked = 'checked'
        }

        const propDoorDelivery = {
            id: 'delivery-in-hands',
            className: 'radio-field mr-[1em] form-check-input ring-0 focus:ring-0 focus:ring-transparent' +
                ' focus:ring-offset-transparent focus:shadow-none focus:outline-none',
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
                        className: "pl-0 pr-[2em] lg:py-[1em] md:py-[1em] py-[0.5em] mr-2"
                    },
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {className: "title font-medium mb-[0.8em] uppercase"},
                            text: 'Способ получения'
                        }),

                        BX.create({
                            tag: 'div',
                            props: {className: "options-row flex w-full flex-col items-start justify-start"},
                            children: [
                                BX.create({
                                    tag: 'label',
                                    props: {
                                        className: "option-label w-full flex leading-[1] mb-[0.5em] items-center justify-start",
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
                                            className: "option-label w-full flex leading-[1] mb-[0.5em] items-center justify-start",
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
                                                        __this.buildDeliveryTime()
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

    removeDeliveryTime: function () {
        if (BX('wrap_delivery_time')){
            BX.remove(BX('wrap_delivery_time'));
        }
    },

    buildDeliveryDate: function () {
        const dateDeliveryNode = BX.create({
            tag: 'input',
            props: {
                type: 'text',
                readOnly: 'readonly',
                className: 'datepicker_order date_delivery_main readonly form-control bx-soa-customer-input bx-ios-fix' +
                    'w-full text-sm cursor-text border-grey-line-order ring:grey-line-order dark:border-grayButton ' +
                    'rounded-lg dark:bg-grayButton',
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
                                    tag: 'div',
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

            let tomorrow    = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            let curDate = new Date(this.getValueProp(this.propDateDeliveryId))
            if (isNaN(curDate)) {
                curDate = tomorrow
            }
            var datepicker =  $(dateDeliveryNode).datepicker({
                minDate: tomorrow,
                selectedDates: curDate,
                onSelect: function (date, opts, datepicker) {
                    let datepicker_osh_input = $('input.datepicker_order.date_delivery_osh');
                    if (datepicker_osh_input.length !== 0) {
                        datepicker_osh_input.val(date)
                    }
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

    buildDeliveryTime: function () {
        let __this = this;
        let datetime_interval_order = $('[name="ORDER_PROP_'+this.propDeliveryTimeInterval+'"]');
        const TimeDeliveryNode = BX.create({
            tag: 'div',
            html: '<select style="height: 40px; padding: 0 23px;"' +
                ' class="form-control bx-soa-customer-input bx-ios-fix w-full text-sm cursor-text ' +
                'border-grey-line-order ring:grey-line-order dark:border-grayButton rounded-lg dark:bg-grayButton"' +
                ' id="datetime_interval_popup">' +
                datetime_interval_order.html()+'</select>',
            dataset: {name: 'DELIVERYTIME_INTERVAL'},
        })

        if (!BX('wrap_delivery_time')) {
            BX.append(
                BX.create({
                    tag: 'div',
                    props: {
                        id: 'wrap_delivery_time',
                        className: "wrap_filter_block mr-2 order-5"
                    },
                    children: [
                        BX.create('DIV', {
                            children: [
                                BX.create({
                                    tag: 'div',
                                    props: {className: 'title'},
                                    text: 'Удобное время получения:'
                                }),
                                BX.create({
                                        tag: 'div',
                                        children: [
                                            TimeDeliveryNode
                                        ]
                                    }
                                )
                            ]
                        })
                    ]
                }),
                BX('pvz_user_data')
            );

            let datetime_interval_popup = $('#datetime_interval_popup');
            datetime_interval_popup.val(datetime_interval_order.val());
            datetime_interval_popup.on("change", function () {
                $('[name="ORDER_PROP_'+__this.propDeliveryTimeInterval+'"]').val(this.value);
            });
        }
    },

    buildSuccessButtonPVZ: function () {
        if (!BX('button-success-pvz')) {
            BX.append(
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'lg:pl-8 md:pl-8 pl-0 flex pb-2 hidden',
                        id: 'button-success-pvz'
                    },
                    children: [
                        BX.create({
                            tag: 'a',
                            props: {
                                id: 'select-pvz-item',
                                href: "javascript:void(0)",
                                className: "link_red_button text-white text-center" +
                                    " flex items-center justify-content-center dark:text-textDark shadow-md  " +
                                    "text-white dark:bg-dark-red bg-light-red py-2 lg:px-16 md:px-16 px-10 rounded-5 block " +
                                    "text-center font-bold",
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
                }), BX('wrap_delivery_select'))
        }
        return this
    },

    buildSuccessButtonDelivery: function () {
        if (!BX('button-success-delivery')) {
            BX.append(
                BX.create({
                    tag: 'div',
                    props: {className: 'lg:pl-8 md:pl-8 pl-0 flex pb-2', id: 'button-success-delivery'},
                    children: [
                        BX.create({
                            tag: 'a',
                            props: {
                                id: 'select-door-delivery-item',
                                href: "javascript:void(0)",
                                className: "link_red_button text-white text-center" +
                                    " flex items-center justify-content-center dark:text-textDark shadow-md  " +
                                    "text-white dark:bg-dark-red bg-light-red py-2 lg:px-16 md:px-16 px-10 rounded-5 block " +
                                    "text-center font-bold",
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
                BX('wrap_delivery_select')
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
                        className: "px-0 py-[1em] flex col-12 order-7 w-full flex-auto"
                    },
                    children: [
                        BX.create('DIV',{
                            props: {
                                className: "flex-auto"
                            },
                            children: [
                                BX.create({
                                        tag: 'div',

                                        children: [
                                            BX.create({
                                                tag: 'div',
                                                props: {
                                                    className: 'mb-2 mt-2 pb-2 border-b border-light-red dark:border-grayLight flex items-center',
                                                    id: 'DELIVERY_SELECT_FOR_ORDER'
                                                },
                                                dataset: {name: 'DELIVERY_SELECT_FOR_ORDER'},
                                                html: '<svg class="inline-block mr-2" width="15" height="20" viewBox="0 0 15 20" fill="none"' +
                                                    ' xmlns="http://www.w3.org/2000/svg"><path d="M10.1074' +
                                                    ' 9.33333L8.12732 10.6667H7.46729H6.80727L4.8272 10L4.16717' +
                                                    ' 8L5.48722 4.66667L7.46729 4L9.44736 4.66667L10.7674' +
                                                    ' 7.33333L10.1074 9.33333ZM7.50203 20C7.50203 20 14.797 12.8925' +
                                                    ' 14.797 7.5C14.797 5.51088 14.0285 3.60322 12.6604 2.1967C11.2923' +
                                                    ' 0.790176 9.43679 0 7.50203 0C5.56728 0 3.71177 0.790176 2.34369' +
                                                    ' 2.1967C0.975609 3.60322 0.207031 5.51088 0.207031 7.5C0.207031' +
                                                    ' 12.8925 7.50203 20 7.50203 20Z" fill="#CD1D1D"/><path d="M7.47465' +
                                                    ' 10C6.82973 10 6.21123 9.73661 5.7552 9.26777C5.29917 8.79893' +
                                                    ' 5.04298 8.16304 5.04298 7.5C5.04298 6.83696 5.29917 6.20107' +
                                                    ' 5.7552 5.73223C6.21123 5.26339 6.82973 5 7.47465 5C8.11957' +
                                                    ' 5 8.73807 5.26339 9.1941 5.73223C9.65012 6.20107 9.90631' +
                                                    ' 6.83696 9.90631 7.5C9.90631 8.16304 9.65012 8.79893 9.1941' +
                                                    ' 9.26777C8.73807 9.73661 8.11957 10 7.47465 10ZM7.47465' +
                                                    ' 11.25C8.44203 11.25 9.36978 10.8549 10.0538 10.1517C10.7379' +
                                                    ' 9.44839 11.1221 8.49456 11.1221 7.5C11.1221 6.50544 10.7379' +
                                                    ' 5.55161 10.0538 4.84835C9.36978 4.14509 8.44203 3.75 7.47465' +
                                                    ' 3.75C6.50727 3.75 5.57952 4.14509 4.89548 4.84835C4.21144' +
                                                    ' 5.55161 3.82715 6.50544 3.82715 7.5C3.82715 8.49456 4.21144' +
                                                    ' 9.44839 4.89548 10.1517C5.57952 10.8549 6.50727 11.25 7.47465' +
                                                    ' 11.25Z" fill="#CD1D1D"/></svg>' +
                                                    ' <span class="font-15 text-grayLight">' +
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
            className: 'radio-field form-check-input form-check-input ring-0 focus:ring-0 focus:ring-transparent' +
                ' focus:ring-offset-transparent focus:shadow-none focus:outline-none',
            type: 'radio',
            value: 'На карте',
            name: 'data_view',
        }
        if (this.componentParams.displayPVZ === typeDisplayPVZ.map) {
            propsOnMap.checked = 'checked'
        }

        const propsList = {
            id: 'data_view_list',
            className: 'radio-field form-check-input form-check-input ring-0 focus:ring-0 focus:ring-transparent' +
                ' focus:ring-offset-transparent focus:shadow-none focus:outline-none',
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
                                        className: 'active_sort_service bg-transparent border rounded-[5px]' +
                                            ' dark:bg-grayButton dark:border-grayButton',
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
                className: 'w-full px-[15px] mx-auto flex flex-col overflow-auto box-with-pvz'
            },
            children: [
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'w-full px-[15px] mx-auto lg:flex md:flex hidden flex-row flex-wrap table-header pr-5'
                    },
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'basis-1/2'
                            },
                            html: '<svg class="inline-block mr-2" width="15" height="20" viewBox="0 0 15 20"' +
                                ' fill="none" xmlns="http://www.w3.org/2000/svg"><path class="dark:fill-grayLight' +
                                ' fill-light-red" d="M12.4381 11.175C11.797' +
                                ' 12.5025 10.9283 13.825 10.04 15.0125C9.19742 16.132 8.29643 17.2044 7.341' +
                                ' 18.225C6.38555 17.2044 5.48457 16.132 4.64196 15.0125C3.7537 13.825 2.88501' +
                                ' 12.5025 2.2439 11.175C1.59544 9.83375 1.2235 8.5775 1.2235 7.5C1.2235 5.8424' +
                                ' 1.86802 4.25269 3.01528 3.08058C4.16253 1.90848 5.71854 1.25 7.341 1.25C8.96346' +
                                ' 1.25 10.5195 1.90848 11.6667 3.08058C12.814 4.25269 13.4585 5.8424 13.4585' +
                                ' 7.5C13.4585 8.5775 13.0853 9.83375 12.4381 11.175ZM7.341 20C7.341 20 14.682' +
                                ' 12.8925 14.682 7.5C14.682 5.51088 13.9086 3.60322 12.5319 2.1967C11.1552 0.790176' +
                                ' 9.28796 0 7.341 0C5.39405 0 3.52683 0.790176 2.15013 2.1967C0.773425 3.60322' +
                                ' 2.90119e-08 5.51088 0 7.5C0 12.8925 7.341 20 7.341 20Z" fill="#CD1D1D"/><path' +
                                ' class="fill-light-red dark:fill-grayLight" d="M7.34116 10C6.69217 10 6.06977' +
                                ' 9.73661 5.61086 9.26777C5.15196 8.79893' +
                                ' 4.89415 8.16304 4.89415 7.5C4.89415 6.83696 5.15196 6.20107 5.61086' +
                                ' 5.73223C6.06977 5.26339 6.69217 5 7.34116 5C7.99014 5 8.61254 5.26339' +
                                ' 9.07145 5.73223C9.53035 6.20107 9.78816 6.83696 9.78816 7.5C9.78816 8.16304' +
                                ' 9.53035 8.79893 9.07145 9.26777C8.61254 9.73661 7.99014 10 7.34116 10ZM7.34116' +
                                ' 11.25C8.31463 11.25 9.24824 10.8549 9.93659 10.1517C10.6249 9.44839 11.0117' +
                                ' 8.49456 11.0117 7.5C11.0117 6.50544 10.6249 5.55161 9.93659 4.84835C9.24824' +
                                ' 4.14509 8.31463 3.75 7.34116 3.75C6.36768 3.75 5.43407 4.14509 4.74572' +
                                ' 4.84835C4.05737 5.55161 3.67065 6.50544 3.67065 7.5C3.67065 8.49456 4.05737' +
                                ' 9.44839 4.74572 10.1517C5.43407 10.8549 6.36768 11.25 7.34116 11.25Z"' +
                                ' fill="#CD1D1D"/></svg>' +
                                '<span class="font-weight-500">Доставка + цена </span>'
                        }),
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'basis-1/4'
                            },
                            html: '<svg class="inline-block mr-2" width="36" height="21" viewBox="0 0 36 21" fill="none"' +
                                ' xmlns="http://www.w3.org/2000/svg"><path class="stroke-light-red' +
                                ' dark:stroke-grayLight" d="M11.5928 19.3313C12.3955 19.3313' +
                                ' 13.1653 19.0342 13.7328 18.5053C14.3004 17.9765 14.6193 17.2591 14.6193' +
                                ' 16.5112C14.6193 15.7632 14.3004 15.0459 13.7328 14.517C13.1653 13.9882 12.3955' +
                                ' 13.691 11.5928 13.691C10.7902 13.691 10.0204 13.9882 9.45283 14.517C8.88526' +
                                ' 15.0459 8.56641 15.7632 8.56641 16.5112C8.56641 17.2591 8.88526 17.9765 9.45283' +
                                ' 18.5053C10.0204 19.0342 10.7902 19.3313 11.5928 19.3313ZM26.725 19.3313C27.5277' +
                                ' 19.3313 28.2974 19.0342 28.865 18.5053C29.4326 17.9765 29.7514 17.2591 29.7514' +
                                ' 16.5112C29.7514 15.7632 29.4326 15.0459 28.865 14.517C28.2974 13.9882 27.5277' +
                                ' 13.691 26.725 13.691C25.9223 13.691 25.1526 13.9882 24.585 14.517C24.0174 15.0459' +
                                ' 23.6986 15.7632 23.6986 16.5112C23.6986 17.2591 24.0174 17.9765 24.585' +
                                ' 18.5053C25.1526 19.0342 25.9223 19.3313 26.725 19.3313Z" stroke="#CD1D1D"' +
                                ' stroke-width="1.5" stroke-miterlimit="1.5" stroke-linecap="round"' +
                                ' stroke-linejoin="round"/><path class="stroke-light-red dark:stroke-grayLight"' +
                                ' d="M14.6946 16.5108H22.185V1.84604C22.185' +
                                ' 1.62166 22.0894 1.40647 21.9191 1.2478C21.7488 1.08914 21.5179 1 21.2771' +
                                ' 1H1M8.03646 16.5108H4.93436C4.81513 16.5108 4.69707 16.4889' +
                                ' 4.58691 16.4464C4.47676 16.4039 4.37667 16.3416 4.29236 16.263C4.20805' +
                                ' 16.1845 4.14117 16.0912 4.09554 15.9885C4.04992 15.8859 4.02643 15.7759' +
                                ' 4.02643 15.6648V8.75541" stroke="#CD1D1D" stroke-width="1.5" stroke-linecap="round"/>' +
                                '<path class="stroke-light-red dark:stroke-grayLight" d="M2.51758 5.23016H8.57044"' +
                                ' stroke="#CD1D1D" stroke-width="1.5"' +
                                ' stroke-linecap="round" stroke-linejoin="round"/><path class="stroke-light-red' +
                                ' dark:stroke-grayLight" d="M22.1836' +
                                ' 5.23016H30.6727C30.8482 5.2302 31.02 5.27764 31.1671 5.36673C31.3143 5.45582' +
                                ' 31.4306 5.58275 31.502 5.73215L34.2106 11.4119C34.2622 11.5198 34.289' +
                                ' 11.6365 34.2893 11.7546V15.6647C34.2893 15.7758 34.2658 15.8858 34.2202' +
                                ' 15.9885C34.1746 16.0911 34.1077 16.1844 34.0234 16.263C33.9391 16.3415' +
                                ' 33.839 16.4038 33.7288 16.4464C33.6187 16.4889 33.5006 16.5108 33.3814' +
                                ' 16.5108H30.5063M22.1836 16.5108H23.6968" stroke="#CD1D1D" stroke-width="1.5"' +
                                ' stroke-linecap="round"/></svg> ' +
                                '<span class="font-weight-500">Срок доставки </span>'
                        }),
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'basis-1/4'
                            },
                            html: '<svg class="inline-block mr-2" width="24" height="22" viewBox="0 0 24 22" fill="none"' +
                                ' xmlns="http://www.w3.org/2000/svg"><path class="fill-light-red dark:fill-grayLight"' +
                                ' d="M11.8681 0C5.31036 0 0 4.78688' +
                                ' 0 10.685C0 16.5831 5.31036 21.37 11.8681 21.37C18.4378 21.37 23.76 16.5831' +
                                ' 23.76 10.685C23.76 4.78688 18.4378 0 11.8681 0ZM11.88 19.233C6.62904 19.233' +
                                ' 2.376 15.4078 2.376 10.685C2.376 5.96223 6.62904 2.137 11.88 2.137C17.131' +
                                ' 2.137 21.384 5.96223 21.384 10.685C21.384 15.4078 17.131 19.233 11.88' +
                                ' 19.233ZM12.474 5.3425H10.692V11.7535L16.929 15.1193L17.82 13.805L12.474' +
                                ' 10.9521V5.3425Z" fill="#CD1D1D"/></svg>' +
                                '<span class="font-weight-500">Режим работы </span>'
                        })
                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'w-full px-[15px] mx-auto flex flex-col overflow-auto my-2 table-body border' +
                            ' border-x-0 border-grayLight p-0 pr-lg-4 pr-md-4 pt-3'
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
                className: 'flex lg:flex-row md:flex-row flex-col lg:basis-1/2 lg:max-w-[50%]' +
                    ' md:basis-1/2 md:max-w-[50%] basis-full max-w-[100%] p-0'
            },
            children: [
                //checkbox
                BX.create({
                    tag: 'input',
                    props: {
                        type: 'radio',
                        id: el.id,
                        name: 'pvz',
                        className: 'form-check-input radio-field form-check-input ring-0 focus:ring-0' +
                            ' focus:ring-transparent focus:ring-offset-transparent focus:shadow-none focus:outline-none',
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
                                    html: '<svg class="inline-block mr-2" width="15" height="20" viewBox="0 0 15 20" fill="none"' +
                                        ' xmlns="http://www.w3.org/2000/svg"><path d="M10.1074' +
                                        ' 9.33333L8.12732 10.6667H7.46729H6.80727L4.8272 10L4.16717' +
                                        ' 8L5.48722 4.66667L7.46729 4L9.44736 4.66667L10.7674' +
                                        ' 7.33333L10.1074 9.33333ZM7.50203 20C7.50203 20 14.797 12.8925' +
                                        ' 14.797 7.5C14.797 5.51088 14.0285 3.60322 12.6604 2.1967C11.2923' +
                                        ' 0.790176 9.43679 0 7.50203 0C5.56728 0 3.71177 0.790176 2.34369' +
                                        ' 2.1967C0.975609 3.60322 0.207031 5.51088 0.207031 7.5C0.207031' +
                                        ' 12.8925 7.50203 20 7.50203 20Z" fill="#CD1D1D"/><path d="M7.47465' +
                                        ' 10C6.82973 10 6.21123 9.73661 5.7552 9.26777C5.29917 8.79893' +
                                        ' 5.04298 8.16304 5.04298 7.5C5.04298 6.83696 5.29917 6.20107' +
                                        ' 5.7552 5.73223C6.21123 5.26339 6.82973 5 7.47465 5C8.11957' +
                                        ' 5 8.73807 5.26339 9.1941 5.73223C9.65012 6.20107 9.90631' +
                                        ' 6.83696 9.90631 7.5C9.90631 8.16304 9.65012 8.79893 9.1941' +
                                        ' 9.26777C8.73807 9.73661 8.11957 10 7.47465 10ZM7.47465' +
                                        ' 11.25C8.44203 11.25 9.36978 10.8549 10.0538 10.1517C10.7379' +
                                        ' 9.44839 11.1221 8.49456 11.1221 7.5C11.1221 6.50544 10.7379' +
                                        ' 5.55161 10.0538 4.84835C9.36978 4.14509 8.44203 3.75 7.47465' +
                                        ' 3.75C6.50727 3.75 5.57952 4.14509 4.89548 4.84835C4.21144' +
                                        ' 5.55161 3.82715 6.50544 3.82715 7.5C3.82715 8.49456 4.21144' +
                                        ' 9.44839 4.89548 10.1517C5.57952 10.8549 6.50727 11.25 7.47465' +
                                        ' 11.25Z" fill="#CD1D1D"/></svg>' +
                                        ' <span class="font-15">'+
                                        '<span class="font-bold text-lg">'
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
                        className: 'flex flex-col'
                    },
                    children: [
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'flex lg:flex-row md:flex-row flex-col' +
                                    ' lg:mb-1 md:mb-1 mb-0 box-with-price-delivery lg:pl-4 md:pl-4 pl-6'
                            },
                            children: [
                                // deliveryName
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        className: 'font-bold text-lg mb-3 leading-4'
                                    },
                                    text: el.properties.deliveryName
                                }),
                            ]
                        }),
                        BX.create({
                            tag: 'span',
                            props: {
                                className: 'inline-flex items-center pl-3 mb-3 lg:pl-4 md:pl-4 pl-6 font-13'
                            },
                            html: '<svg class="inline-flex lg:w-[15px] md:w-[15px] lg:h-[20px] md:h-[20px] w-[25px]' +
                                ' h-[25px] mr-2" width="15" height="20" viewBox="0 0 15 20"' +
                                ' fill="none" xmlns="http://www.w3.org/2000/svg"><path class="fill-light-red' +
                                ' dark:fill-white" d="M12.4381 11.175C11.797' +
                                ' 12.5025 10.9283 13.825 10.04 15.0125C9.19742 16.132 8.29643 17.2044 7.341' +
                                ' 18.225C6.38555 17.2044 5.48457 16.132 4.64196 15.0125C3.7537 13.825 2.88501' +
                                ' 12.5025 2.2439 11.175C1.59544 9.83375 1.2235 8.5775 1.2235 7.5C1.2235 5.8424' +
                                ' 1.86802 4.25269 3.01528 3.08058C4.16253 1.90848 5.71854 1.25 7.341 1.25C8.96346' +
                                ' 1.25 10.5195 1.90848 11.6667 3.08058C12.814 4.25269 13.4585 5.8424 13.4585' +
                                ' 7.5C13.4585 8.5775 13.0853 9.83375 12.4381 11.175ZM7.341 20C7.341 20 14.682' +
                                ' 12.8925 14.682 7.5C14.682 5.51088 13.9086 3.60322 12.5319 2.1967C11.1552 0.790176' +
                                ' 9.28796 0 7.341 0C5.39405 0 3.52683 0.790176 2.15013 2.1967C0.773425 3.60322' +
                                ' 2.90119e-08 5.51088 0 7.5C0 12.8925 7.341 20 7.341 20Z" fill="#CD1D1D"/><path' +
                                ' class="fill-light-red dark:fill-white" d="M7.34116 10C6.69217 10 6.06977 9.73661 5.61086 9.26777C5.15196 8.79893' +
                                ' 4.89415 8.16304 4.89415 7.5C4.89415 6.83696 5.15196 6.20107 5.61086' +
                                ' 5.73223C6.06977 5.26339 6.69217 5 7.34116 5C7.99014 5 8.61254 5.26339' +
                                ' 9.07145 5.73223C9.53035 6.20107 9.78816 6.83696 9.78816 7.5C9.78816 8.16304' +
                                ' 9.53035 8.79893 9.07145 9.26777C8.61254 9.73661 7.99014 10 7.34116 10ZM7.34116' +
                                ' 11.25C8.31463 11.25 9.24824 10.8549 9.93659 10.1517C10.6249 9.44839 11.0117' +
                                ' 8.49456 11.0117 7.5C11.0117 6.50544 10.6249 5.55161 9.93659 4.84835C9.24824' +
                                ' 4.14509 8.31463 3.75 7.34116 3.75C6.36768 3.75 5.43407 4.14509 4.74572' +
                                ' 4.84835C4.05737 5.55161 3.67065 6.50544 3.67065 7.5C3.67065 8.49456 4.05737' +
                                ' 9.44839 4.74572 10.1517C5.43407 10.8549 6.36768 11.25 7.34116 11.25Z"' +
                                ' fill="#CD1D1D"/></svg>' + el.properties.fullAddress
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
                    className: 'column mb-3 bx-selected-delivery p-[19px] rounded-[10px] border' +
                        ' dark:border-0 border-grey-line-order dark:!border-white bg-transparent dark:bg-lightGrayBg',
                },
                children: [
                    BX.create({
                        tag: 'div',
                        props: {
                            className: 'flex lg:flex-row md:flex-row flex-col flex-wrap'
                        },
                        children: [
                            boxWithDeliveryInfo,
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'lg:basis-1/4 lg:max-w-[25%] md:basis-1/4 md:max-w-[25%] basis-full' +
                                        ' max-w-[100%] mb-2'
                                },
                                children: [
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'ml-lg-2 ml-md-2 ml-0 lg:mb-0 md:mb-0 mb-3 lg:pl-4 md:pl-4 pl-6 font-13'
                                        },
                                        html: '<svg class="lg:hidden md:hidden inline-flex mr-2"' +
                                            ' width="25" height="25" viewBox="0 0 36 21" fill="none"' +
                                            ' xmlns="http://www.w3.org/2000/svg"><path class="stroke-light-red' +
                                            ' dark:stroke-white" d="M11.5928 19.3313C12.3955 19.3313' +
                                            ' 13.1653 19.0342 13.7328 18.5053C14.3004 17.9765 14.6193 17.2591 14.6193' +
                                            ' 16.5112C14.6193 15.7632 14.3004 15.0459 13.7328 14.517C13.1653 13.9882 12.3955' +
                                            ' 13.691 11.5928 13.691C10.7902 13.691 10.0204 13.9882 9.45283 14.517C8.88526' +
                                            ' 15.0459 8.56641 15.7632 8.56641 16.5112C8.56641 17.2591 8.88526 17.9765 9.45283' +
                                            ' 18.5053C10.0204 19.0342 10.7902 19.3313 11.5928 19.3313ZM26.725 19.3313C27.5277' +
                                            ' 19.3313 28.2974 19.0342 28.865 18.5053C29.4326 17.9765 29.7514 17.2591 29.7514' +
                                            ' 16.5112C29.7514 15.7632 29.4326 15.0459 28.865 14.517C28.2974 13.9882 27.5277' +
                                            ' 13.691 26.725 13.691C25.9223 13.691 25.1526 13.9882 24.585 14.517C24.0174 15.0459' +
                                            ' 23.6986 15.7632 23.6986 16.5112C23.6986 17.2591 24.0174 17.9765 24.585' +
                                            ' 18.5053C25.1526 19.0342 25.9223 19.3313 26.725 19.3313Z" stroke="#CD1D1D"' +
                                            ' stroke-width="1.5" stroke-miterlimit="1.5" stroke-linecap="round"' +
                                            ' stroke-linejoin="round"/><path class="stroke-light-red dark:stroke-white"' +
                                            ' d="M14.6946 16.5108H22.185V1.84604C22.185' +
                                            ' 1.62166 22.0894 1.40647 21.9191 1.2478C21.7488 1.08914 21.5179 1 21.2771' +
                                            ' 1H1M8.03646 16.5108H4.93436C4.81513 16.5108 4.69707 16.4889' +
                                            ' 4.58691 16.4464C4.47676 16.4039 4.37667 16.3416 4.29236 16.263C4.20805' +
                                            ' 16.1845 4.14117 16.0912 4.09554 15.9885C4.04992 15.8859 4.02643 15.7759' +
                                            ' 4.02643 15.6648V8.75541" stroke="#CD1D1D" stroke-width="1.5" stroke-linecap="round"/>' +
                                            '<path class="stroke-light-red dark:stroke-white" d="M2.51758 5.23016H8.57044"' +
                                            ' stroke="#CD1D1D" stroke-width="1.5"' +
                                            ' stroke-linecap="round" stroke-linejoin="round"/><path class="stroke-light-red' +
                                            ' dark:stroke-white" d="M22.1836' +
                                            ' 5.23016H30.6727C30.8482 5.2302 31.02 5.27764 31.1671 5.36673C31.3143 5.45582' +
                                            ' 31.4306 5.58275 31.502 5.73215L34.2106 11.4119C34.2622 11.5198 34.289' +
                                            ' 11.6365 34.2893 11.7546V15.6647C34.2893 15.7758 34.2658 15.8858 34.2202' +
                                            ' 15.9885C34.1746 16.0911 34.1077 16.1844 34.0234 16.263C33.9391 16.3415' +
                                            ' 33.839 16.4038 33.7288 16.4464C33.6187 16.4889 33.5006 16.5108 33.3814' +
                                            ' 16.5108H30.5063M22.1836 16.5108H23.6968" stroke="#CD1D1D" stroke-width="1.5"' +
                                            ' stroke-linecap="round"/></svg> ' + 'от 1 дня'
                                    }),
                                ]

                            }),
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'lg:basis-1/4 lg:max-w-[25%] md:basis-1/4 md:max-w-[25%] basis-full' +
                                        ' max-w-full flex flex-col mb-2'
                                },
                                children: [
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'worktime-shedule lg:pl-4 md:pl-4 pl-6 font-13'
                                        },
                                        html: '<svg class="lg:hidden md:hidden inline-flex mr-2" width="25"' +
                                            ' height="25" viewBox="0 0 24 22" fill="none"' +
                                            ' xmlns="http://www.w3.org/2000/svg"><path class="fill-light-red dark:fill-white"' +
                                            ' d="M11.8681 0C5.31036 0 0 4.78688' +
                                            ' 0 10.685C0 16.5831 5.31036 21.37 11.8681 21.37C18.4378 21.37 23.76 16.5831' +
                                            ' 23.76 10.685C23.76 4.78688 18.4378 0 11.8681 0ZM11.88 19.233C6.62904 19.233' +
                                            ' 2.376 15.4078 2.376 10.685C2.376 5.96223 6.62904 2.137 11.88 2.137C17.131' +
                                            ' 2.137 21.384 5.96223 21.384 10.685C21.384 15.4078 17.131 19.233 11.88' +
                                            ' 19.233ZM12.474 5.3425H10.692V11.7535L16.929 15.1193L17.82 13.805L12.474' +
                                            ' 10.9521V5.3425Z" fill="#CD1D1D"/></svg>' + el.properties.workTime
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
                        className: 'text-light-red dark:text-white font-bold' +
                            ' text-[13px] leading-4 lg:mb-0' +
                            ' md:mb-0 mb-3 lg:ml-3 md:ml-3 ml-0',
                        role: 'button'
                    },
                    html: '<svg class="lg:hidden md:hidden inline-flex mr-2"' +
                        ' width="25" height="25" viewBox="0 0 24 24" fill="none"' +
                        ' xmlns="http://www.w3.org/2000/svg">' +
                        '<g clip-path="url(#clip0_2881_7234)">' +
                        '<path class="fill-light-red dark:fill-white"' +
                        ' fill-rule="evenodd" clip-rule="evenodd" d="M11.7637' +
                        ' 23.1953C5.41208 23.1953 0.263672 18.0469 0.263672' +
                        ' 11.6953C0.263672 5.34372 5.41208 0.195312 11.7637' +
                        ' 0.195312C18.1153 0.195312 23.2637 5.34372 23.2637' +
                        ' 11.6953C23.2637 18.0469 18.1153 23.1953 11.7637' +
                        ' 23.1953ZM7.81055 11.1476V12.4471H9.07842V14.3216H7' +
                        '.81055V15.5471H9.07842V18.1641H10.5073V15.5471H13.' +
                        '3823V14.3216H10.5073V12.4471H12.3106C12.86 12.4517 13.4072' +
                        ' 12.3767 13.935 12.2243C14.4353 12.0755 14.8694 11.8506' +
                        ' 15.2388 11.5465C15.6083 11.2439 15.9001 10.8637 16.1143' +
                        ' 10.4052C16.3285 9.94731 16.4355 9.40897 16.4355' +
                        ' 8.79012C16.4355 8.172 16.3349 7.64012 16.1322 7.1945C15.9295' +
                        ' 6.74888 15.65 6.38016 15.2927 6.08978C14.9355 5.79941 14.5071' +
                        ' 5.58234 14.0069 5.44003C13.466 5.29236 12.9072 5.22052' +
                        ' 12.3466 5.22656H9.07842V11.1476H7.81055ZM12.3459' +
                        ' 11.1476H10.5073V6.52606H12.3466C13.1674 6.52606 13.8135' +
                        ' 6.70575 14.2836 7.06441C14.7537 7.42306 14.9894 7.99878' +
                        ' 14.9894 8.79012C14.9894 9.58219 14.7537 10.173 14.2836' +
                        ' 10.5633C13.8135 10.9528 13.1674 11.1476 12.3466' +
                        ' 11.1476H12.3459Z" fill="white"/>'+
                        '</g>' +
                        '<defs>' +
                        '<clipPath id="clip0_2881_7234">' +
                        '<rect width="23" height="23" fill="white"' +
                        ' transform="translate(0.263672 0.195312)"/>' +
                        '</clipPath>' +
                        '</defs>' +
                        '</svg> ' + 'Узнать цену',
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
                        className: 'text-light-red dark:text-white font-bold' +
                            ' lg:text-lg md:text-lg text-[13px] lg:ml-3 md:ml-3 mb-2 flex flex-row lg:items-start md:items-start' +
                            ' items-center !leading-4'
                    },
                    html: '<svg class="lg:hidden md:hidden inline-flex mr-2"' +
                        ' width="25" height="25" viewBox="0 0 24 24" fill="none"' +
                        ' xmlns="http://www.w3.org/2000/svg">' +
                        '<g clip-path="url(#clip0_2881_7234)">' +
                        '<path class="fill-light-red dark:fill-white"' +
                        ' fill-rule="evenodd" clip-rule="evenodd" d="M11.7637' +
                        ' 23.1953C5.41208 23.1953 0.263672 18.0469 0.263672' +
                        ' 11.6953C0.263672 5.34372 5.41208 0.195312 11.7637' +
                        ' 0.195312C18.1153 0.195312 23.2637 5.34372 23.2637' +
                        ' 11.6953C23.2637 18.0469 18.1153 23.1953 11.7637' +
                        ' 23.1953ZM7.81055 11.1476V12.4471H9.07842V14.3216H7' +
                        '.81055V15.5471H9.07842V18.1641H10.5073V15.5471H13.' +
                        '3823V14.3216H10.5073V12.4471H12.3106C12.86 12.4517 13.4072' +
                        ' 12.3767 13.935 12.2243C14.4353 12.0755 14.8694 11.8506' +
                        ' 15.2388 11.5465C15.6083 11.2439 15.9001 10.8637 16.1143' +
                        ' 10.4052C16.3285 9.94731 16.4355 9.40897 16.4355' +
                        ' 8.79012C16.4355 8.172 16.3349 7.64012 16.1322 7.1945C15.9295' +
                        ' 6.74888 15.65 6.38016 15.2927 6.08978C14.9355 5.79941 14.5071' +
                        ' 5.58234 14.0069 5.44003C13.466 5.29236 12.9072 5.22052' +
                        ' 12.3466 5.22656H9.07842V11.1476H7.81055ZM12.3459' +
                        ' 11.1476H10.5073V6.52606H12.3466C13.1674 6.52606 13.8135' +
                        ' 6.70575 14.2836 7.06441C14.7537 7.42306 14.9894 7.99878' +
                        ' 14.9894 8.79012C14.9894 9.58219 14.7537 10.173 14.2836' +
                        ' 10.5633C13.8135 10.9528 13.1674 11.1476 12.3466' +
                        ' 11.1476H12.3459Z" fill="white"/>'+
                        '</g>' +
                        '<defs>' +
                        '<clipPath id="clip0_2881_7234">' +
                        '<rect width="23" height="23" fill="white"' +
                        ' transform="translate(0.263672 0.195312)"/>' +
                        '</clipPath>' +
                        '</defs>' +
                        '</svg> ' +el.properties?.price + ' руб.'
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

        BX.removeClass(this.checkout.paysystem.titleBox, 'justify-content-between')

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

        //Поиск блока с единой доставкой и замена его на виджет
        const pvzCheckBox = BX('ID_DELIVERY_ID_' + this.pvzDeliveryId)
        const listBox = BX.findParent(pvzCheckBox, {class: 'box_with_del_js'})
        BX.removeClass(listBox, 'd-flex')
        BX.addClass(listBox, 'd-none')
        const rootDelivery = BX.create({
            tag: 'div',
            props: {id: 'common-delivery-section'},
        })
        BX.remove(BX('common-delivery-section'));
        BX.insertBefore(rootDelivery, listBox)
        this.checkout.delivery.variants = {}

        const chooseBlock = BX.create('div', {
            attrs: {
                className: 'delivery-choose js__delivery-choose inline-block underline text-light-red' +
                    ' dark:text-white font-semibold dark:font-medium cursor-pointer text-sm',
                id: 'delivery-choose'
            },
            text: 'Выбрать адрес',
            events: {
                click: BX.proxy(function () {
                    __this.openMap()
                })
            }
        })

        this.checkout.delivery.variants.rootEl = BX.create('div', {
            props: {className: 'delivery-variants border border-light-red rounded-[10px] dark:bg-darkBox dark:text-white dark:border-grey-line-order', id: 'delivery-variants'},
            children: [
                BX.create('div', {
                    attrs: {className: 'delivery-variants-title font-medium dark:font-normal text-xl mb-4 dark:text-white'},
                    html: 'Укажите адрес и способ доставки'
                }),

                BX.create('div', {
                    props: {className: 'delivery-description row mb-3 flex flex-wrap', id: 'delivery-description'},
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
        // блок региона
        BX.addClass(this.checkout.region.rootEl[0], 'd-none');
        BX.remove(this.checkout.region.rootEl[1]);
        this.checkout.region.rootEl = this.checkout.region.rootEl[0];

        return this
    },

};


