import React from 'react';

function GroupedProductsPropValue({
                                      group,
                                      groupedProducts,
                                      propSetting,
                                      setID,
                                      setPrice,
                                      setName,
                                      productId,
                                      setActiveGroup,
                                      select
                                  }) {
    const keys = Object.keys(group);

    if (propSetting.TYPE === 'color') {
        const srcPicture = group[keys[0]].PREVIEW_PICTURE
        return (
            <a className={"offer-link " + (select ? 'selected' : '')}>
                <div data-prop_code={propSetting.CODE}
                     data-active={select}
                     data-prop_group={JSON.stringify(group)}
                     onClick={() => {
                         setActiveGroup(group)
                         // const productsSuccess = sortOnPriorityArDataProducts(groupedProducts, code);
                         // const productResult = groupedProducts[productsSuccess[0].id];
                         //todo uncomment
                         // setPrice(productResult.PRICES.PRICE_DATA.PRICE)
                         // setID(productResult.ID)
                         // setName(productResult.NAME)
                     }}
                     className={"offer-box offer-link bg-white mr-2 " +
                         "text-xs border rounded-md p-3 " + (!select ?
                             "dark:opacity-50 border-textDarkLightGray" : "")}>
                    <img src={srcPicture} className="w-16 h-16" alt=""/>
                </div>
            </a>
        )
    } else if (propSetting.TYPE === 'colorWithText') {
        const groupParsed = Object.entries(group)

        return (
            <a className={"offer-link " + (select ? 'selected' : '')}>
                <div data-prop_code={propSetting.CODE}
                     data-active={select}
                     onClick={() => {
                         // const productsSuccess = sortOnPriorityArDataProducts(groupedProducts, propSetting.CODE);
                         // const productResult = groupedProducts[productsSuccess[0].id];
                         setActiveGroup(group)
                         //todo uncomment
                         // setPrice(productResult.PRICES.PRICE_DATA.PRICE)
                         // setID(productResult.ID)
                         // setName(productResult.NAME)
                     }}
                     data-prop_group={JSON.stringify(group)}
                     className={"red_button_cart taste variation_taste w-fit p-3 mb-2 " +
                         " mr-2 rounded-md flex flex-row min-w-20 " +
                         "offer-box cursor-pointer dark:bg-grayButton border-2 " +
                         " border-textDarkLightGray dark:border-0" + (select ?
                             "border-light-red dark:border-grayButton dark:border" +
                             " dark:bg-grayButton" :
                             "border-textDarkLightGray dark:border-0")}>
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
            <a className={"offer-link " + (select ? 'selected' : '')}>
                <div data-prop_code={propSetting.CODE}
                     data-active={select}
                     onClick={() => {
                         // const productsSuccess = sortOnPriorityArDataProducts(groupedProducts, propSetting.CODE);
                         // const productResult = groupedProducts[productsSuccess[0].id];
                         // console.log(productsSuccess)
                         // console.log(productResult)
                         setActiveGroup(group)
                     }}
                     data-prop_group={JSON.stringify(group)}
                     className="border-textDarkLightGray text-dark dark:bg-grayButton
                     dark:text-textDarkLightGray min-w-20 mb-2 mr-2 offer-box cursor-pointer
                     font-medium dark:border-0 dark:font-normal text-sm font-bolder
                     red_button_cart w-fit rounded-full px-5 py-2 bg-white border-2">
                    {keys[0] + propSetting.PREF}
                </div>
            </a>
        )
    }
}

function GroupedProductsProp({
                                 groupedSettings,
                                 props,
                                 groupedProducts,
                                 setID,
                                 setPrice,
                                 setName,
                                 productId,
                                 setSelectPropValue,
                                 selectPropValue
                             }) {
    if (groupedSettings[props[0]]) {

        const propSetting = groupedSettings[props[0]];
        const dataProps = props[1];
        const setActiveGroup = (group) => {
            setSelectPropValue(prev => {
                const index = prev.findIndex(item => item.prop === props[0]);
                if (index !== -1) {
                    prev.splice(index, 1, {prop: props[0], group});
                }

                return prev
            })
        }

        if (propSetting.CODE === 'USE_DISCOUNT') {
            return <></>
        }

        // setActiveGroup(groupedProducts[productId].PROPERTIES[propSetting.CODE].JS_PROP);

        return (
            <div className="flex flex-row mb-4">
                {dataProps.map((group, g_key) => {
                    const select = selectPropValue.find(item => item.group === group && item.prop === props[0]) !== undefined
                    return <GroupedProductsPropValue
                        key={g_key} select={select} propSetting={propSetting}
                        groupedProducts={groupedProducts}
                        group={group} setPrice={setPrice} setID={setID}
                        setName={setName} productId={productId} setActiveGroup={setActiveGroup}/>
                })}
            </div>
        )

    }
}

export default GroupedProductsProp;