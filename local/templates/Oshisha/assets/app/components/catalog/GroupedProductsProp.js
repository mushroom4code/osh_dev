import React, {useEffect} from 'react';

/**
 * GroupedProductsPropValue
 * @param group
 * @param propData
 * @param setActiveGroup
 * @param select
 * @param setCode
 * @returns {JSX.Element}
 * @constructor
 */
function GroupedProductsPropValue({
                                      group,
                                      propData,
                                      setActiveGroup,
                                      select,
                                      setCode
                                  }) {

    const keys = Object.keys(group);

    if (propData.TYPE === 'color') {
        const srcPicture = group[keys[0]].PREVIEW_PICTURE
        return (
            <a className={"offer-link " + (select ? 'selected' : '')}>
                <div data-prop_code={propData.CODE}
                     data-active={select}
                     data-prop_group={JSON.stringify(group)}
                     onClick={() => {
                         setCode(propData.CODE)
                         setActiveGroup(group)
                     }}
                     className={"offer-box offer-link bg-white mr-2 " +
                         "text-xs border rounded-md p-3 " +
                         (!select ? "dark:opacity-50 border-textDarkLightGray" : "")}>
                    <img src={srcPicture} className="w-16 h-16" alt=""/>
                </div>
            </a>
        )
    } else if (propData.TYPE === 'colorWithText') {
        const groupParsed = Object.entries(group)

        return (
            <a className={"offer-link " + (select ? 'selected' : '')}>
                <div data-prop_code={propData.CODE}
                     data-active={select}
                     onClick={() => {
                         setCode(propData.CODE)
                         setActiveGroup(group)
                     }}
                     data-prop_group={JSON.stringify(group)}
                     className={"red_button_cart taste variation_taste w-fit p-3 mb-2 " +
                         " mr-2 rounded-md flex flex-row min-w-20 " +
                         "offer-box cursor-pointer dark:bg-grayButton border-2 " +
                         (
                             select ? "border-light-red dark:border-white dark:border dark:bg-grayButton"
                                 : " border-textDarkLightGray dark:border-0"
                         )}>
                    {
                        groupParsed.map((itemGroup, i_key) => {
                            const item = itemGroup[1];
                            const name = itemGroup[0];
                            const colorNew = item.VALUE_XML_ID?.split('#')[1];

                            return (
                                <span key={i_key}
                                      style={{
                                          backgroundColor: '#' + colorNew,
                                          borderColor: '#' + colorNew,
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
                <div data-prop_code={propData.CODE}
                     data-active={select}
                     onClick={() => {
                         setCode(propData.CODE)
                         setActiveGroup(group)
                     }}
                     data-prop_group={JSON.stringify(group)}
                     className={"text-dark dark:bg-grayButton dark:text-textDarkLightGray" +
                         " min-w-20 mb-2 mr-2 offer-box cursor-pointer font-medium dark:font-normal " +
                         "text-sm font-bolder red_button_cart w-fit rounded-full px-5 py-2 bg-white border-2 "
                         + (select ? " border-light-red dark:border-white dark:border" :
                             " border-textDarkLightGray dark:border-0")}>
                    {keys[0] + propData.PREF}
                </div>
            </a>
        )
    }
}

/**
 * GroupedProductsProp
 * @param propSettings
 * @param props
 * @param productId
 * @param listProducts
 * @param updateProduct
 * @param setActiveGroup
 * @param setCode
 * @param selectPropValue
 * @returns {JSX.Element}
 * @constructor
 */
function GroupedProductsProp({
                                 propSettings, props, productId, listProducts, updateProduct,
                                 setCode, selectPropValue, setActiveGroup,
                             }) {

    if (propSettings[props[0]]) {

        const propData = propSettings[props[0]];
        const dataProps = props[1];
        setCode(props[0])

        if (propData.CODE === 'USE_DISCOUNT') {
            return <></>
        }

        if (selectPropValue.findIndex(item => item.prop === props[0]) === -1) {
            setActiveGroup(listProducts[productId].PROPERTIES[propData.CODE].JS_PROP);
        }
        return (
            selectPropValue.length > 0 ?
                <div className="flex flex-row mb-4">
                    {dataProps.map((group, g_key) => {
                        const select = selectPropValue.find(item => arrayDiff(item.group, group) && item.prop === props[0]) !== undefined
                        return <GroupedProductsPropValue
                            key={g_key} select={select} propData={propData}
                            listProducts={listProducts} selectPropValue={selectPropValue}
                            group={group} updateProduct={updateProduct} setCode={setCode}
                            setActiveGroup={setActiveGroup}/>
                    })}
                </div>
                : <></>
        )
    }
}

export default GroupedProductsProp;