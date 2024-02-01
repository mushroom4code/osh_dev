import React, { useEffect, useState } from 'react'
import axios from 'axios';
import PropTypes from 'prop-types'
import OshishaYMap from './OshishaYMap';
import OshishaPvzList from './OshishaPvzList';
import { ajaxDeliveryUrl } from '../OrderMain';
import { deliveryProp } from './OrderOshishaDelivery';

function OshishaPvzDelivery({ cityCode, cityName, params, result, sendRequest, typePvzList, setShowHideBlockWithDelivery }) {

    if (cityCode === undefined) {
        return
    }

    const [features, setFeatures] = useState([])
    const [selectPvz, setSelectPvz] = useState({
        code_pvz: result.ORDER_PROP.properties.find(
            prop => prop.CODE === deliveryProp.commonPvz.code)?.VALUE[0],
        deliveryName: result.ORDER_PROP.properties.find(
            prop => prop.CODE === deliveryProp.typeDelivery.code)?.VALUE[0],
        fullAddress: result.ORDER_PROP.properties.find(
            prop => prop.CODE === deliveryProp.addressPvz.code)?.VALUE[0],
        type: result.ORDER_PROP.properties.find(
            prop => prop.CODE === deliveryProp.typePvz.code)?.VALUE[0],
    })

    async function getRequestGetPvzPrice(data) {
        const response = await axios.post(ajaxDeliveryUrl, {
            sessid: BX.bitrix_sessid(),
            'dataToHandler': data,
            'action': 'getPVZPrice'
        }, { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })

        if (response.data.status === "success") {
            return response.data.data
        } else {
            return []
        }
    }

    const getPointData = (point) => {
        return {
            id: point.id,
            action: 'getPrice',
            code_city: cityCode,
            delivery: point.properties.deliveryName,
            to: point.properties.fullAddress,
            weight: result.TOTAL.ORDER_WEIGHT,
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

        setAdditionalData(additionalData, deliveryProp.commonPvz.code, point.properties.code_pvz);
        setAdditionalData(additionalData, deliveryProp.typeDelivery.code, point.properties.deliveryName);
        setAdditionalData(additionalData, deliveryProp.addressPvz, point.properties.fullAddress);
        setAdditionalData(additionalData, deliveryProp.typePvz, point.properties.type);

        sendRequest('refreshOrderAjax', {}, additionalData);
        setShowHideBlockWithDelivery(false)
    }


    useEffect(() => {
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

    }, [cityCode])

    return (
        <div className='flex-1'>
            {typePvzList === 'map'
                ? <OshishaYMap cityCode={cityCode} cityName={cityName} features={features}
                    getPointData={getPointData} getRequestGetPvzPrice={getRequestGetPvzPrice} handleSelectPvz={handleSelectPvz} />
                : <OshishaPvzList features={features} sendRequest={sendRequest} getPointData={getPointData}
                    getRequestGetPvzPrice={getRequestGetPvzPrice} selectPvz={selectPvz} setSelectPvz={setSelectPvz}
                    handleSelectPvz={handleSelectPvz}
                />
            }
        </div>
    )
}

OshishaPvzDelivery.propTypes = {}

export default OshishaPvzDelivery
