import React, { useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import axios from 'axios';

function OshishaYMap({ cityName, cityCode, features, params, orderResult, sendRequest}) {
    const [pvzMap, setPvzMap] = useState(null)

    function getSelectPvzPrice(objectManager, points, clusterId = undefined) {
        try {
            const data = points.reduce((result, point) => {
                if (!point.properties.balloonContent) {
                    point.properties.balloonContent = "Идет загрузка данных...";
                    if (clusterId === undefined) {
                        objectManager.objects.balloon.setData(point);
                    }
                    return result.concat({
                        id: point.id,
                        action: 'getPrice',
                        code_city: cityCode,
                        delivery: point.properties.deliveryName,
                        to: point.properties.fullAddress,
                        weight: orderResult.TOTAL.ORDER_WEIGHT,
                        cost: params.OSH_DELIVERY.shipmentCost,
                        packages: params.OSH_DELIVERY.packages,
                        street_kladr: point.properties.street_kladr ?? '',
                        latitude: point.geometry.coordinates[0],
                        longitude: point.geometry.coordinates[1],
                        hubregion: point.properties.hubregion,
                        name_city: cityName,
                        postindex: point.properties.postindex,
                        code_pvz: point.properties.code_pvz,
                        type_pvz: point.properties.type ?? ''
                    })
                }
                return result;
            }, [])

            
            if (data.length === 0)
                return;

            if (clusterId !== undefined && objectManager.clusters.balloon.isOpen(clusterId)) {
                objectManager.clusters.balloon.setData(objectManager.clusters.balloon.getData());
            }

            const afterSuccess = function (data) {
                if (clusterId !== undefined && objectManager.clusters.balloon.isOpen(clusterId)) {
                    objectManager.clusters.balloon.setData(objectManager.clusters.balloon.getData());
                }
            }
            
            axios.post('/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php', {
                sessid: BX.bitrix_sessid(),
                'dataToHandler': data,
                'action': 'getPVZPrice'
            }, { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }).then(response => {            
                if (response.data.status === 'success') {
                    response.data.data.forEach(item => {
                        const point = features.find(feature => feature.id == item.id)
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
                    onclick="BX.reactHandler.selectPvz(${item.id});" >Выбрать</a>`)

                        point.properties = {
                            ...point.properties,
                            price: item.price,
                            balloonContent: balloonContent,
                        };

                        if (clusterId === undefined && true) {
                            //BX.SaleCommonPVZ.componentParams.displayPVZ === typeDisplayPVZ.map) {
                            objectManager.objects.balloon.setData(point);
                        }
                    })
                }
            })

        } catch (excepction) {


        }
    }

    function selectPvz(objectManager, itemId) {
        const point = objectManager.objects.getById(itemId);

        const additionalData = {};
        //TODO move to lib
        function setAdditionalData(additionalData, code, value) {
            const propAddress = orderResult.ORDER_PROP.properties.find(prop => prop.CODE === code)
            if (propAddress !== undefined) {
                additionalData[[`ORDER_PROP_${propAddress.ID}`]] = value;
            }
            return additionalData
        }

        setAdditionalData(additionalData, 'COMMON_PVZ', point.properties.code_pvz);
        setAdditionalData(additionalData, 'TYPE_DELIVERY', point.properties.deliveryName);
        setAdditionalData(additionalData, 'ADDRESS_PVZ', point.properties.fullAddress);
        setAdditionalData(additionalData, 'TYPE_PVZ', point.properties.type);
        
        sendRequest('refreshOrderAjax', {}, additionalData);
    }

    useEffect(() => {
        if (cityName === '')
            return;

        ymaps.ready(() => {
            const myGeocoder = ymaps.geocode(cityName, { results: 1 });
            myGeocoder.then(res => {
                const firstGeoObject = res.geoObjects.get(0);
                const coords = firstGeoObject.geometry.getCoordinates();

                const yMap = new ymaps.Map('map_for_delivery_react', {
                    center: [coords[0], coords[1]],
                    zoom: 12,
                    controls: ['fullscreenControl']
                });
                setPvzMap(yMap);
            })
        })
    }, [cityName])

    useEffect(() => {

        if (cityName === '' || pvzMap === null) {
            return
        }

        const objectManager = new ymaps.ObjectManager({
            clusterize: true,
            clusterHasBalloon: true
        });
        objectManager.add({ type: 'FeatureCollection', features: features });

        //global react fix for yandex map
        BX.reactHandler.onSelectPvz.splice(0, BX.reactHandler.onSelectPvz.length)
        BX.reactHandler.onSelectPvz.push((itemId) => selectPvz(objectManager, itemId))

        objectManager.clusters.events.add(['balloonopen'], function (e) {
            const clusterId = e.get('objectId');
            const cluster = objectManager.clusters.getById(clusterId);
            if (objectManager.clusters.balloon.isOpen(clusterId)) {
                getSelectPvzPrice(objectManager, cluster.properties.geoObjects, clusterId)
            }

        });

        objectManager.objects.events.add('click', function (e) {
            const objectId = e.get('objectId')
            objectManager.objects.balloon.open(objectId);
        });

        objectManager.objects.events.add('balloonopen', function (e) {
            const objectId = e.get('objectId')
            const obj = objectManager.objects.getById(objectId);

            getSelectPvzPrice(objectManager, [obj]);
        });

        pvzMap.geoObjects.add(objectManager);
    }, [cityName, features, pvzMap])

    return (
        <div style={{ height: 600, width: 600 }} id='map_for_delivery_react'>

        </div>
    )
}

OshishaYMap.propTypes = {}

export default OshishaYMap
