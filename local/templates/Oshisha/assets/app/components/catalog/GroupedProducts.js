import React, {useState} from 'react';
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

    return (
        groupedProps.length > 0 ?
            groupedProps.map((props, pr_key) =>
                <GroupedProductsProp key={pr_key} props={props} listProducts={listProducts}
                                     propSettings={propSettings} updateProduct={updateProduct}
                                     productId={productId} setSelectPropValue={setSelectPropValue}
                                     selectPropValue={selectPropValue}/>
            )
            : <></>
    )
}

export default GroupedProducts;