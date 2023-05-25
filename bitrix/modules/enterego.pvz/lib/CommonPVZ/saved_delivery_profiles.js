BX.namespace('BX.SavedDeliveryProfiles');

BX.SavedDeliveryProfiles = {
    drawSavedProfiles: function(commonPvzObject) {
        console.log('sus');
        commonPvzObject.checkout.delivery.separator = BX.create('div', {attrs: {className: 'delivery-separator'}, text: 'Или'})
        commonPvzObject.checkout.delivery.recentWrap = {}
        commonPvzObject.checkout.delivery.recentWrap.rootEl = BX.create('div', {attrs: {className: 'recent-deliveries-wrap'}})
        commonPvzObject.checkout.delivery.recentWrap.title = BX.create('div', {attrs: {className: 'recent-deliveries-title'},
            html: '<span class="recent-title-accent">Выберите настройки</span> доставки из прошлых заказов'})

        BX.insertAfter(commonPvzObject.checkout.delivery.separator, commonPvzObject.checkout.delivery.variants.rootEl)
        BX.insertAfter(commonPvzObject.checkout.delivery.recentWrap.rootEl, commonPvzObject.checkout.delivery.separator)
        BX.append(commonPvzObject.checkout.delivery.recentWrap.title, commonPvzObject.checkout.delivery.recentWrap.rootEl)

        var childrenArray = [];
        BX.Sale.OrderAjaxComponent.savedDeliveryProfiles.forEach((element) => {
            childrenArray.push(
                BX.create({
                    tag: 'div',
                    props: {
                        id: element['ID'],
                        className: 'recent-profile'
                    },
                    events: {click: () => this.applySavedProfile(element)},
                    children: [
                        BX.create({
                            tag: 'span',
                            props: {
                                className: 'recent-profile-title'
                            },
                            text: (element['PROPERTIES'].find(prop => prop.CODE === 'COMMON_PVZ') ? 'ПВЗ' : 'Курьер')
                                + ' ' + element['PROPERTIES'].find(prop => prop.CODE === 'TYPE_DELIVERY')?.VALUE
                        }),
                        BX.create({
                            tag: 'span',
                            props: {
                                className: 'recent-profile-address'
                            },
                            text: element['PROPERTIES'].find(prop => prop.CODE === 'COMMON_PVZ')
                                ? element['PROPERTIES'].find(prop => prop.CODE === 'DEFAULT_ADDRESS_PVZ')?.VALUE
                                : element['ADDRESS']
                        }),
                    ]
                })
            )
        })
        BX.append(
            BX.create({
                tag: 'div',
                props: {
                    className: 'recent-profiles'
                },
                children: childrenArray
            }),
            commonPvzObject.checkout.delivery.recentWrap.rootEl
        )
    },

    applySavedProfile: function (element) {
        BX.Sale.OrderAjaxComponent.result.DELIVERY.forEach((delivery) => {
            if (delivery['CHECKED'])
                delete delivery['CHECKED']
            if(delivery['ID'] == element['PROFILE_ID'])
                delivery['CHECKED'] = 'Y'
        })
        element['PROPERTIES'].forEach((property) => {
            BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.find(prop => prop.ID == property['PROPERTY_ID']).VALUE[0] = property['VALUE'];
        });
        BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.find(prop => prop.CODE == 'ADDRESS').VALUE[0] = element['ADDRESS'];
        var tempLocations = BX.Sale.OrderAjaxComponent.locations;
        var elementLocation = element['PROPERTIES'].find(prop => prop['CODE'] == 'LOCATION');
        Object.keys(tempLocations).forEach((locationKey) => {
            tempLocations[locationKey] = tempLocations[locationKey][0];
        });
        var payload = {error: false, locations: tempLocations, order:BX.Sale.OrderAjaxComponent.result};

        BX.Sale.OrderAjaxComponent.startLoader();
        BX.Sale.OrderAjaxComponent.refreshOrder(payload);
        payload['savedProfileLocation'] = elementLocation;
        this.sendRequestToComponent('refreshOrderAjax', [], payload);
    },

    getDataForPVZ: function (action, actionData, savedProfileData = false) {
        var data = {
            order: BX.Sale.OrderAjaxComponent.getAllFormData(),
            sessid: BX.bitrix_sessid(),
            via_ajax: 'Y',
            SITE_ID: BX.Sale.OrderAjaxComponent.siteId,
            signedParamsString: BX.Sale.OrderAjaxComponent.signedParamsString,
            dataToHandler: actionData
        };
        if (savedProfileData) {
            Object.keys(data.order).forEach((prop, index) => {
                if (prop == 'ORDER_PROP_' + savedProfileData['savedProfileLocation']['PROPERTY_ID'])
                    data.order[prop] = savedProfileData['savedProfileLocation']['VALUE'];
            });
        }
        data[BX.Sale.OrderAjaxComponent.params.ACTION_VARIABLE] = action;
        return data;
    },

    sendRequestToComponent: function (action, actionData, savedProfileData = false) {
        BX.ajax({
            method: 'POST',
            dataType: 'json',
            url: BX.Sale.OrderAjaxComponent.ajaxUrl,
            data: this.getDataForPVZ(action, actionData, savedProfileData),
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
};