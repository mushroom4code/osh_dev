import React, { useEffect, useState } from 'react'
import axios from 'axios';
import PropTypes from 'prop-types'
import OshishaYMap from './OshishaYMap';

function OshishaPvzDelivery({ cityCode, cityName, params, result, sendRequest }) {

    if (cityCode===undefined) {
        return
    }

    const [features, setFeatures] = useState([])

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
        <div>
            <OshishaYMap cityCode={cityCode} cityName={cityName} features={features}
                params={params} orderResult={result} sendRequest={sendRequest} />
        </div>
    )
}

OshishaPvzDelivery.propTypes = {}

export default OshishaPvzDelivery
