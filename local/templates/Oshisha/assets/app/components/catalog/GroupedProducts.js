import React, {useState} from 'react';
import GroupedProductsProp from "./GroupedProductsProp";

function GroupedProducts({groupedSettings, groupedProducts, groupedProps, setPrice, setID, setName, productId}) {

    const [selectPropValue, setSelectPropValue] = useState([])

    return (
        groupedProps.length > 0 ?
            groupedProps.map((props, pr_key) =>
                <GroupedProductsProp key={pr_key} props={props} groupedProducts={groupedProducts}
                                     groupedSettings={groupedSettings} setPrice={setPrice} setID={setID}
                                     setName={setName} productId={productId} setSelectPropValue={setSelectPropValue}
                                     selectPropValue={selectPropValue}/>
            )
            : <></>
    )
}

export default GroupedProducts;