import React, {useContext} from 'react'
import OrderContext from "../Context/OrderContext";
import PropTypes from 'prop-types'
import { listOshDeliveryProp } from './OrderOshishaDelivery';
import OrderProp from '../OrderProp';

function OshishaDoorDelivery({ result, params }) {
    const {sendRequest} = useContext(OrderContext);

    const doorDelivery = result.DELIVERY.find(delivery =>
        delivery.ID === params.OSH_DELIVERY.doorDeliveryId && delivery.CHECKED === 'Y'
    )

    const propTypeDelivery = result.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_DELIVERY')
    const deliveryInfo = JSON.parse(doorDelivery.CALCULATE_DESCRIPTION);

    return (
        <div>
            {deliveryInfo.map(delivery => {

                const isChecked = delivery.code === propTypeDelivery?.VALUE[0]

                return <div key={delivery.name} className={`mt-3 p-3 flex items-center rounded-xl border border-grey-line-order
                     dark:bg-lightGrayBg ${isChecked ? 'dark:border-white' : 'dark:border-0'} `}>
                    <input type='radio' name='delivery' className='form-check-input radio-field form-check-input ring-0 focus:ring-0
                            focus:ring-transparent focus:ring-offset-transparent focus:shadow-none focus:outline-none'
                        checked={isChecked} onChange={() => {
                            sendRequest('refreshOrderAjax', {}, { [`ORDER_PROP_${propTypeDelivery.ID}`]: delivery.code });
                        }} />
                    <div className='ml-2 text-light-red text-lg font-semibold dark:text-white '>
                        {`${delivery.name} - ${delivery.price}`}
                    </div>

                </div>
            })}
        </div>
    )
}

OshishaDoorDelivery.propTypes = {}

export default OshishaDoorDelivery
