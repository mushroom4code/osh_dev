import React, {useEffect, useState} from 'react';
import GroupedProductsProp from "./GroupedProductsProp";

/**
 * GroupedProducts
 * @param propSettings
 * @param listProducts
 * @param groupedProps
 * @param updateProduct
 * @param productId
 * @returns {*|JSX.Element}
 * @constructor
 */
function GroupedProducts({propSettings, listProducts, groupedProps, updateProduct, productId, selectProductProperties}) {

    const [selectPropValue, setSelectPropValue] = useState([])
    const [code, setCode] = useState('')

    useEffect(() => {
        if (selectPropValue.length > 0 && code !== '') {

            const productsSuccess = sortOnPriorityArDataProducts(listProducts, code);
            if(productsSuccess.length > 0){
                const productResult = listProducts[productsSuccess[0].id];
                updateProduct(productResult);
            }
            // Object.entries(productResult.PROPERTIES).map((propertyData,key)=>{
            //     console.log(propertyData)
            //     const index = selectPropValue.findIndex(item => item.prop === propertyData[0], propertyData[1].JS_PROP.length === item.group.length)
            //     if (index === -1) {
            //         setActiveGroup(propertyData[1].JS_PROP)
            //     }
            // })

        }

    }, [selectPropValue]);

    useEffect(() => {

    }, []);
    console.log('test rec')
    const setActiveGroup = (group) => {
            setSelectPropValue(prev => {
                const index = prev.findIndex(item => item.prop === code);
                if (index !== -1) {
                    prev.splice(index, 1)
                }
                prev.push({prop: code, group})
                return [...prev]
            })
    }

    console.log(groupedProps)

    // const propData = propSettings[props[0]];
    // if (selectPropValue.findIndex(item => item.prop === props[0]) === -1) {
    //     setActiveGroup(listProducts[productId].PROPERTIES[propData.CODE].JS_PROP);
    // }
    return (
        groupedProps.length > 0 ?
            groupedProps.map((props, pr_key) =>
                <GroupedProductsProp key={pr_key} props={props} listProducts={listProducts}
                                     propSettings={propSettings} updateProduct={updateProduct}
                                     selectPropValue={selectProductProperties} setCode={setCode}/>
            )
            : <></>
    )
}

export default GroupedProducts;