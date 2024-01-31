import React, { useState } from "react";
import PropTypes from "prop-types";
import Address from "./icon/Address";
import Price from "./icon/Price";

function PvzItem({ feature, getPointData, getRequestGetPvzPrice, selectedPvz, onSelectPvz }) {
  const [price, setPrice] = useState(null);

  return (
    <div className={`mt-3 p-3 rounded-xl border border-grey-line-order dark:bg-lightGrayBg dark:border-0 ${selectedPvz ? 'dark:bg-lightGrayBg' : '' }`}>
      <div className="flex md:flex-row flex-col md:basis-1/2 md:max-w-[50%] basis-full max-w-[100%]">
        <input onChange={()=> onSelectPvz(feature.properties.code_pvz, feature.properties.deliveryName)}
          type="radio"
          className="radio-field form-check-input ring-0 focus:ring-0 focus:ring-transparent focus:ring-offset-transparent focus:shadow-none focus:outline-none"
        />
        <div className="flex flex-col">
          <div className="flex lg:flex-row md:flex-row flex-col lg:mb-1 md:mb-1 mb-0 box-with-price-delivery lg:pl-4 md:pl-4 pl-6">
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
                getRequestGetPvzPrice([data]).then((response) => {
                  if (response.data.status === "success") {
                    // const curFeatures = features.find(feature => feature.id == response.data.data[0].id)
                    setPrice(response.data.data[0].price);

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
};

function OshishaPvzList({
  features,
  sendRequest,
  getRequestGetPvzPrice,
  getPointData,
  selectPvz,
  setSelectPvz,
}) {

  const onSelectPvz = (pvzCode, deliveryName) => {
    console.log(pvzCode);
  }

  return (
    <div className="w-full px-[15px] mx-auto flex flex-col overflow-auto">
      <div className="w-full px-[15px] mx-auto lg:flex md:flex hidden flex-row flex-wrap table-header pr-5"></div>
      {features.map((feature, index) => {
        const selectedPvz = false
        return <PvzItem
          key={index}
          feature={feature}
          getRequestGetPvzPrice={getRequestGetPvzPrice}
          getPointData={getPointData}
          selectedPvz={selectedPvz}
          onSelectPvz={onSelectPvz}
        />
      })}
    </div>
  );
}

OshishaPvzList.propTypes = {
  features: PropTypes.array,
  sendRequest: PropTypes.func,
  getRequestGetPvzPrice: PropTypes.func,
  getPointData: PropTypes.func,
};

export default OshishaPvzList;
