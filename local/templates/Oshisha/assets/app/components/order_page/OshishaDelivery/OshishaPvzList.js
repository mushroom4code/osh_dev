import React, { useState } from "react";
import PropTypes from "prop-types";
import Address from "./icon/Address";
import Price from "./icon/Price";

function PvzItem({ feature, getPointData, getRequestGetPvzPrice, selectedPvz, handleSelectPvz, }) {
    const [price, setPrice] = useState(null);

    return (
        <div
            className={`mt-3 p-3 rounded-xl border border-grey-line-order dark:bg-lightGrayBg dark:border-0 ${selectedPvz ? 'dark:bg-lightGrayBg' : ''}`}>
            <div className="flex md:flex-row flex-col md:basis-1/2 md:max-w-[50%] basis-full max-w-[100%]">
                <input onChange={() => handleSelectPvz(feature)}
                    type="radio"
                    className="radio-field form-check-input ring-0 focus:ring-0 focus:ring-transparent focus:ring-offset-transparent focus:shadow-none focus:outline-none"
                />
                <div className="flex flex-col">
                    <div
                        className="flex lg:flex-row md:flex-row flex-col lg:mb-1 md:mb-1 mb-0 box-with-price-delivery lg:pl-4 md:pl-4 pl-6">
                        <div className="font-bold text-lg mb-3 leading-4">
                            {feature.properties.deliveryName}
                        </div>
                        <span
                            className={`text-light-red dark:text-white text-sm leading-4 md:mb-0 mb-3 md:ml-3 ml-0 ${price == null ? 'cursor-pointer' : ''}`}
                            onClick={() => {
                                if (price !== null) {
                                    return
                                }

                                const data = getPointData(feature)
                                getRequestGetPvzPrice([data]).then((data) => {
                                    if (data.length > 0 ) {
                                        setPrice(data[0].price);
                                    }
                                });
                            }}
                        >
                            <Price />
                            {price === null ? "Узнать цену" : `${price} руб.`}
                        </span>
                    </div>

                    <span className="inline-flex items-center mb-3 lg:pl-4 md:pl-4 pl-6 font-13">
                        <Address />
                        {feature.properties.fullAddress}
                    </span>
                </div>
            </div>
        </div>
    );
}

PvzItem.propTypes = {
    feature: PropTypes.object,
    getPointData: PropTypes.func,
    getRequestGetPvzPrice: PropTypes.func,
    selectedPvz: PropTypes.bool,
    handleSelectPvz: PropTypes.func
};

function OshishaPvzList({
    features,
    getRequestGetPvzPrice,
    getPointData,
    selectPvz,
    handleSelectPvz
}) {
    return (
        <div className="w-full px-[15px] mx-auto flex flex-col">
            <a href='javascript(0)' className='text-white text-center flex items-center w-fit justify-center
                         dark:text-textDark shadow-md dark:bg-dark-red bg-light-red py-2 lg:px-16 md:px-16 px-10
                         rounded-5 font-normal'>Подтвердить
            </a>
            <div className="w-full px-[15px] mx-auto lg:flex md:flex hidden flex-row flex-wrap table-header pr-5"></div>
            <div className="w-full px-[15px] mx-auto lg:max-h-96 overflow-auto max-h-60 pr-5">
            {features.map((feature, index) => {
                const selectedPvz = feature.properties.commonPvz === selectPvz.commonPvz
                    && feature.properties.deliveryName === selectPvz.deliveryName
                return <PvzItem
                    key={index}
                    feature={feature}
                    getRequestGetPvzPrice={getRequestGetPvzPrice}
                    getPointData={getPointData}
                    selectedPvz={selectedPvz}
                    handleSelectPvz={handleSelectPvz}
                />
            })}
            </div>
        </div>
    );
}

OshishaPvzList.propTypes = {
    features: PropTypes.array,
    sendRequest: PropTypes.func,
    getRequestGetPvzPrice: PropTypes.func,
    getPointData: PropTypes.func,
    selectPvz: PropTypes.object,
    handleSelectPvz: PropTypes.func
};

export default OshishaPvzList;
