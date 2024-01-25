import React, { useEffect, useState } from 'react'
import axios from 'axios';
import PropTypes from 'prop-types'
import OshishaYMap from './OshishaYMap';

function OshishaPvzDelivery({ cityCode, params, result }) {

    const [curCityName, setCurCityName] = useState('')
    const [features, setFeatures] = useState([])

    useEffect(() => {
        axios.post('/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php', {
            sessid: BX.bitrix_sessid(),
            codeCity: cityCode,
            action: 'getCityName'
        }, { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }).then(response => {
            //todo errors from back

            const cityName = response.data.LOCATION_NAME;
            axios.post('/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php', {
                sessid: BX.bitrix_sessid(),
                codeCity: cityCode,
                cityName: cityName,
                orderPackages: params.OSH_DELIVERY.packages,
                action: 'getPVZList'
            }, { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }).then(response => {
                setCurCityName(cityName)
                setFeatures(response.data.features)
            })
        })
    }, [])

    return (
        <div>
            <OshishaYMap cityCode={cityCode} cityName={curCityName} features={features}
                params={params} orderResult={result} />
        </div>
    )
}

OshishaPvzDelivery.propTypes = {}

export default OshishaPvzDelivery
