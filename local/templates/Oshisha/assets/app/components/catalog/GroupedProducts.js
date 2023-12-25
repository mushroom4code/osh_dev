import React, {useEffect, useState} from 'react';

function GroupedProducts({groupedSettings, groupedProducts, groupedProps, setPrice, setID}) {

    console.log(groupedSettings)
    console.log(groupedProducts)
    console.log(groupedProps)

    return (
        groupedProps.length > 0 ?
            groupedProps.map((props, pr_key) => {
                if (groupedSettings[props[0]]) {
                    const typeProduct = groupedSettings[props[0]].TYPE
                    const code = groupedSettings[props[0]].CODE
                    let prefix = groupedSettings[props[0]].PREF
                    const dataProps = props[1];

                    return (
                        <div className="flex flex-row" key={pr_key}>
                            {
                                groupedProducts.map((product, p_key) => {
                                    const productData = product[1];
                                    const productID = product[0];
                                    let classType = 'lg:mb-2 md:m-2 m-1 offer-box cursor-pointer'
                                    const valuePropsProduct = productData.PROPERTIES[code].JS_PROP
                                    let itemChild = productData.NAME + prefix
                                    const price = productData.PRICES.PRICE_DATA.PRICE.split('.')[0];
                                    const keys = Object.keys(valuePropsProduct);

                                    if (typeProduct === 'color') {
                                        const srcPicture = valuePropsProduct[keys[0]].PREVIEW_PICTURE
                                        return (
                                            <div key={p_key}
                                                 data-prop_code={code}
                                                 data-prop_group={valuePropsProduct}
                                                 onClick={() => {
                                                     setPrice(price)
                                                     setID(productID)
                                                 }}
                                                 className="offer-box text-xs border border-gray rounded-md p-3 bg-white mr-2">
                                                <img src={srcPicture} className="w-16 h-16" alt=""/>
                                            </div>
                                        )
                                    } else if (typeProduct === 'colorWithText') {
                                        return (
                                            <div key={p_key}
                                                 data-prop_code={code}
                                                 onClick={() => {
                                                     setPrice(price)
                                                     setID(productID)
                                                 }}
                                                 data-prop_group={valuePropsProduct}
                                                 className="flex offer-box flex-row overflow-auto max-w-full text-xs
                                                  red_button_cart taste variation_taste w-fit rounded-xl dark:bg-grayButton
                                                lg:mb-2 md:m-2 p-2 m-1 offer-box cursor-pointer bg-white
                                                 border-light-red border dark:border-0">
                                                {itemChild}
                                            </div>
                                        )
                                    } else {
                                        return (
                                            <div key={p_key}
                                                 data-prop_code={code}
                                                 onClick={() => {
                                                     setPrice(price)
                                                     setID(productID)
                                                 }}
                                                 data-prop_group={valuePropsProduct}
                                                 className="flex offer-box flex-row overflow-auto max-w-full text-xs
                                                 w-fit rounded-xl dark:bg-grayButton lg:mb-2 md:m-2 p-2 m-1 offer-box
                                                 cursor-pointer bg-white border-light-red border dark:border-0">
                                                {itemChild}
                                            </div>
                                        )
                                    }
                                    // const bool = arrayDiff(valuePropsProduct,dataProps)
                                    // console.log(product)

                                })
                            }
                        </div>
                    )

                }
            })
            : <></>
    )
}

export default GroupedProducts;