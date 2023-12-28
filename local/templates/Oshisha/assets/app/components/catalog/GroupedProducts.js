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
function GroupedProducts({propSettings, listProducts, groupedProps, updateProduct, productId}) {

    const [selectPropValue, setSelectPropValue] = useState([])
    const [code, setCode] = useState('')

    useEffect(() => {
        if (selectPropValue.length > 0 && code !== '') {
            const productsSuccess = sortOnPriorityArDataProducts(listProducts, code);
            console.log(productsSuccess)
            if(productsSuccess.length > 0){
                const productResult = listProducts[productsSuccess[0].id];
                updateProduct(productResult);
                console.log(Object.entries(productResult.PROPERTIES))
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

    return (
        groupedProps.length > 0 ?
            groupedProps.map((props, pr_key) =>
                <GroupedProductsProp key={pr_key} props={props} listProducts={listProducts} setActiveGroup={setActiveGroup}
                                     propSettings={propSettings} updateProduct={updateProduct}
                                     productId={productId} selectPropValue={selectPropValue} setCode={setCode}/>
            )
            : <></>
    )
}

export default GroupedProducts;