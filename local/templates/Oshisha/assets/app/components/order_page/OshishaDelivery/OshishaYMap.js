import React, { useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import axios from "axios";
import {ajaxDeliveryUrl} from "../OrderMain";

function OshishaYMap({ cityName, features,
    getPointData, handleSelectPvz }) {

    const [pvzMap, setPvzMap] = useState(null)

    async function getRequestGetPvzPrice(data) {
        const response = await axios.post(ajaxDeliveryUrl, {
            sessid: BX.bitrix_sessid(),
            'dataToHandler': data,
            'action': 'getPVZPrice'
        }, {headers: {'Content-Type': 'application/x-www-form-urlencoded'}})

        if (response.data.status === "success") {
            return response.data.data
        } else {
            return []
        }
    }

    function getSelectPvzPrice(objectManager, points, clusterId = undefined) {
        const data = points.reduce((result, point) => {
            if (!point.properties.balloonContent) {
                point.properties.balloonContent = "Идет загрузка данных...";
                if (clusterId === undefined) {
                    objectManager.objects.balloon.setData(point);
                }
                return result.concat(getPointData(point))
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

        getRequestGetPvzPrice(data).then(data => {

            data.forEach(item => {
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

        })
    }

    function selectPvzOnMap(objectManager, itemId) {
        const point = objectManager.objects.getById(itemId);
        objectManager.objects.balloon.close()
        handleSelectPvz(point)
    }

    useEffect(() => {
        if (cityName === '')
            return;

        ymaps.ready(() => {
            const myGeocoder = ymaps.geocode(cityName, { results: 1 });
            myGeocoder.then(res => {
                const firstGeoObject = res.geoObjects.get(0);
                const coords = firstGeoObject.geometry.getCoordinates();

                if (pvzMap === null) {
                    const yMap = new ymaps.Map('map_for_delivery_react', {
                        center: [coords[0], coords[1]],
                        zoom: 12,
                        controls: ['fullscreenControl']
                    })
                    setPvzMap(yMap)
                } else {
                    pvzMap.setCenter([coords[0], coords[1]])
                }
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
        BX.reactHandler.onSelectPvz.push((itemId) => selectPvzOnMap(objectManager, itemId))

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
        <div className="h-full w-full pt-2" id='map_for_delivery_react'>

        </div>
    )
}

OshishaYMap.propTypes = {
    cityName: PropTypes.string,
    features: PropTypes.array,
    getPointData: PropTypes.func,
    handleSelectPvz: PropTypes.func
}

export default OshishaYMap
