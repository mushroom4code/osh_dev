import React, {useEffect, useState} from 'react';

function GroupedProducts({groupedSettings, groupedProducts, groupedProps, setPrice, setID, setName}) {

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

                    if (code !== 'USE_DISCOUNT') {
                        return (
                            <div className="flex flex-row mb-4" key={pr_key}>
                                {
                                    dataProps.map((group, g_key) => {
                                        // console.log(group)
                                        const keys = Object.keys(group);
                                        const productID = groupedProducts[0];
                                        // const price = productData.PRICES.PRICE_DATA.PRICE.split('.')[0];

                                        if (typeProduct === 'color') {
                                            const srcPicture = group[keys[0]].PREVIEW_PICTURE
                                            return (
                                                <a key={g_key} className="offer-link">
                                                    <div data-prop_code={code}
                                                         data-prop_group={group}
                                                         onClick={() => {
                                                             // setPrice(price)
                                                             // setID(productID)
                                                             // setName(productData.NAME)
                                                         }}
                                                         className="offer-box offer-link dark:opacity-50 bg-white mr-2
                                                 border-textDarkLightGray text-xs border border-gray rounded-md p-3 ">
                                                        <img src={srcPicture} className="w-16 h-16" alt=""/>
                                                    </div>
                                                </a>
                                            )
                                        } else if (typeProduct === 'colorWithText') {
                                            const groupParsed = Object.entries(group)
                                            return (
                                                <a key={g_key} className="offer-link">
                                                    <div data-prop_code={code}
                                                         onClick={() => {
                                                             // setPrice(price)
                                                             // setID(productID)
                                                             // setName(productData.NAME)
                                                         }}
                                                         data-prop_group={group}
                                                         className="red_button_cart taste variation_taste
                                                      w-fit p-3 mb-2 mr-2 offer-box rounded-md flex flex-row
                                                      min-w-20 offer-box cursor-pointer dark:bg-grayButton border-2
                                                       border-textDarkLightGray dark:border-0">
                                                        {
                                                            groupParsed.map((itemGroup, i_key) => {
                                                                const item = itemGroup[1];
                                                                const name = itemGroup[0];
                                                                const colorNew = item.VALUE_XML_ID?.split('#');
                                                                return (
                                                                    <span key={i_key}
                                                                          style={{
                                                                              backgroundColor: '#' + colorNew[1],
                                                                              borderColor: '#' + colorNew[1],
                                                                          }}
                                                                          className="taste taste px-2.5 mr-1 py-1 text-xs rounded-full">
                                                                    {name}
                                                                </span>
                                                                )
                                                            })
                                                        }
                                                    </div>
                                                </a>
                                            )
                                        } else {
                                            return (
                                                <a key={g_key} className="offer-link">
                                                    <div data-prop_code={code}
                                                         onClick={() => {
                                                             // setPrice(price)
                                                             // setID(productID)
                                                             // setName(productData.NAME)
                                                         }}
                                                         data-prop_group={group}
                                                         className="border-textDarkLightGray text-dark dark:bg-grayButton
                                                     dark:text-textDarkLightGray min-w-20 mb-2 mr-2 offer-box cursor-pointer
                                                     font-medium dark:border-0 dark:font-normal text-sm font-bolder
                                                     red_button_cart w-fit rounded-full px-5 py-2 bg-white border-2">
                                                        {keys[0] + prefix}
                                                    </div>
                                                </a>
                                            )
                                        }
                                    })
                                }
                            </div>
                        )
                    }
                }
            })
            : <></>
    )
}

export default GroupedProducts;