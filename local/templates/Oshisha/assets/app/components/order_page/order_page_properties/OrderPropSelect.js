import React from 'react'
import PropTypes from 'prop-types'

function OrderPropSelect({ property, className, disabled }) {
    return (<div className={className === '' ? "flex justify-between" : className}>
        <label className="pb-3.5 relative text-black dark:text-white font-semibold dark:font-normal text-sm">{property?.NAME}</label>
        {Object.keys(property.OPTIONS).map(key => <div key={'order_prop_enum_' + key}>
            <label className="font-semibold dark:font-normal">
                <input className="form-check-input ring-0 focus:ring-0 focus:ring-transparent
                       focus:ring-offset-transparent focus:outline-none mr-2" type="radio"
                    name={'ORDER_PROP_' + property.ID} defaultValue={key}
                    defaultChecked={
                        (property.VALUE.length !== 0)
                            ? (property.VALUE[0] === key ? true : null)
                            : (property.DEFAULT_VALUE === key ? true : null)
                    }
                />
                {property.OPTIONS[key]}
            </label>
        </div>
        )}
    </div>);
}

OrderPropSelect.propTypes = {
    property: PropTypes.object,
    className: PropTypes.string,
    disabled: PropTypes.bool,
}

OrderPropSelect.defaultProps = {
    className: ''
}

export default OrderPropSelect
