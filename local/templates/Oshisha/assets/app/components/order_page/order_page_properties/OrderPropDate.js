import React, { useState } from "react";
import PropTypes from 'prop-types'
import Datepicker from "react-tailwindcss-datepicker";

function OrderPropDate({ property, className, disabled, minDate = null, handleOnSelect }) {

    const [currentDate, setCurrentDate] = useState(new Date());

    const onChageValueChange = (newValue) => {
        setCurrentDate(newValue)
        if (handleOnSelect!==undefined) {
            handleOnSelect(newValue)
        }
    }

    return (
        <div className={className === '' ? "flex justify-between" : className}>
            <label className="pb-3.5 relative text-black dark:text-white font-bold dark:font-normal text-sm">{property?.NAME}</label>
            <Datepicker 
                disabled={disabled} 
                useRange={false} 
                asSingle={true} 
                i18n={"ru"} 
                minDate={minDate}  
                inputName={property.ID}
                inputClassName='relative w-full text-sm rounded-lg cursor-text border-grey-line-order ring:grey-line-order dark:border-darkBox dark:bg-grayButton absolute' 
                containerClassName='relative w-48'
                value={currentDate} 
                onChange={onChageValueChange} 
            />
        </div>
    );
}

OrderPropDate.propTypes = {
    property: PropTypes.object,
    disabled: PropTypes.bool,
    className: PropTypes.string,
    minDate: PropTypes.instanceOf(Date),
    handleOnSelect: PropTypes.func
}

OrderPropDate.defaultProps = {
    disabled: false,
    minDate: null,
    className: ''
}

export default OrderPropDate;