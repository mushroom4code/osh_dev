import React, { useEffect, useState } from 'react'
import axios from 'axios';
import PropTypes from 'prop-types'
import OshishaYMap from './OshishaYMap';
import OshishaPvzList from './OshishaPvzList';
import { ajaxDeliveryUrl } from '../OrderMain';
import { deliveryProp } from './OrderOshishaDelivery';

function OshishaPvzDelivery({ cityCode, cityName, params, result, sendRequest, typePvzList }) {

    if (cityCode === undefined) {
        return
    }

    const [features, setFeatures] = useState([])
    const [selectPvz, setSelectPvz] = useState(result.ORDER_PROP.properties.find(
        prop => prop.CODE === deliveryProp.commonPvz.code)?.VALUE[0])

    function getRequestGetPvzPrice(data) {
        return axios.post(ajaxDeliveryUrl, {
            sessid: BX.bitrix_sessid(),
            'dataToHandler': data,
            'action': 'getPVZPrice'
        }, { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
    }

    const getPointData = (point) => {
        return {
            id: point.id,
            action: 'getPrice',
            code_city: cityCode,
            delivery: point.properties.deliveryName,
            to: point.properties.fullAddress,
            weight: result.TOTAL.ORDER_WEIGHT,
            cost:  params.OSH_DELIVERY.shipmentCost,
            packages: params.OSH_DELIVERY.packages,
            street_kladr: point.properties.street_kladr ?? '',
            latitude: point.geometry.coordinates[0],
            longitude: point.geometry.coordinates[1],
            hubregion: point.properties.hubregion,
            name_city: cityName,
            postindex: point.properties.postindex,
            code_pvz: point.properties.code_pvz,
            type_pvz: point.properties.type ?? ''
        };
    }

    const handleSelectPvz = (point) => {
        const additionalData = {};
        //TODO move to lib
        function setAdditionalData(additionalData, code, value) {
            const propAddress = result.ORDER_PROP.properties.find(prop => prop.CODE === code)
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
        axios.post('/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php', {
            sessid: BX.bitrix_sessid(),
            codeCity: cityCode,
            action: 'getCityName'
        }, { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }).then(response => {

            //todo errors from back
            axios.post('/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php', {
                sessid: BX.bitrix_sessid(),
                codeCity: cityCode,
                cityName: cityName,
                orderPackages: params.OSH_DELIVERY.packages,
                action: 'getPVZList'
            }, { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }).then(response => {
                setFeatures(response.data.features)
            })
        })
    }, [cityCode])

    return (
        <div className='flex-1 overflow-auto'>
            {typePvzList === 'map'
                ? <OshishaYMap cityCode={cityCode} cityName={cityName} features={features}
                    params={params} orderResult={result} getPointData={getPointData}
                    getRequestGetPvzPrice={getRequestGetPvzPrice} sendRequest={sendRequest} 
                    handleSelectPvz={handleSelectPvz}/>
                : <OshishaPvzList features={features} sendRequest={sendRequest} getPointData={getPointData}
                    getRequestGetPvzPrice={getRequestGetPvzPrice} selectPvz={selectPvz} setSelectPvz={setSelectPvz} />
            }
        </div>
    )
}

OshishaPvzDelivery.propTypes = {}

export default OshishaPvzDelivery
