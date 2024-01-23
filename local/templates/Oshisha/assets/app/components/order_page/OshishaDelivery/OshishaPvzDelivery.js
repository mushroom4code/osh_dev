import React, { useEffect, useState } from 'react'
import axios from 'axios';
import PropTypes from 'prop-types'

function OshishaPvzDelivery({ curCityCode, params }) {

    const [curCityName, setCurCityName] = useState('')
    const [features, setFeatures] = useState([])

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
                        code_city: curCityCode,
                        delivery: point.properties.deliveryName,
                        to: point.properties.fullAddress,
                        weight: BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_WEIGHT,
                        cost: params.OSH_DELIVERY.shipmentCost,
                        packages: params.OSH_DELIVERY.packages,
                        street_kladr: point.properties.street_kladr ?? '',
                        latitude: point.geometry.coordinates[0],
                        longitude: point.geometry.coordinates[1],
                        hubregion: point.properties.hubregion,
                        name_city: curCityName,
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
        console.log(point);
    }

    useEffect(() => {
        axios.post('/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php', {
            sessid: BX.bitrix_sessid(),
            codeCity: curCityCode,
            action: 'getCityName'
        }, { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }).then(response => {
            //todo errors from back

            const cityName = response.data.LOCATION_NAME;
            axios.post('/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php', {
                sessid: BX.bitrix_sessid(),
                codeCity: curCityCode,
                cityName: cityName,
                orderPackages: params.OSH_DELIVERY.packages,
                action: 'getPVZList'
            }, { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }).then(response => {
                setCurCityName(cityName)
                setFeatures(response.data.features)
            })
        })
    }, [])

    useEffect(() => {

        if (curCityName === '') {
            return
        }
        
        ymaps.ready(() => {
            const myGeocoder = ymaps.geocode(curCityName, { results: 1 });

            myGeocoder.then(res => {
                const firstGeoObject = res.geoObjects.get(0);
                const coords = firstGeoObject.geometry.getCoordinates();

                const myMap = new ymaps.Map('map_for_delivery_react', {
                    center: [coords[0], coords[1]],
                    zoom: 12,
                    controls: ['fullscreenControl']
                });

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

                myMap.geoObjects.add(objectManager);
            })
        });

    }, [curCityName, features])

    return (
        <div>
            <div style={{ height: 600, width: 600 }} id='map_for_delivery_react'>

            </div>

        </div>
    )
}

OshishaPvzDelivery.propTypes = {}

export default OshishaPvzDelivery
