import React, {useEffect} from 'react';
import axios from "axios";

function CatalogProductPopup({product_id}) {

    useEffect(() => {
        getProductData()
    }, []);

    function getProductData(data) {
        axios.post('/local/ajax/catalog_item.php',
            data).then(res => {
                if (res.data?.success) {

                } else if (res.data?.error) {
                    if (res.data?.error?.code) {

                    }
                } else {
                }
            }
        )
    }

    return (<div className="fixed w-screen top-0 bg-lightOpacityWindow dark:bg-darkOpacityWindow flex justify-center
     h-screen z-30 box-popup-product">
        <div className="open-modal-product m-auto h-fit catalog-item-product bg-white p-6 max-w-4xl w-full rounded-lg
        catalog-fast-window dark:bg-darkBox">{product_id}</div></div>);
}

export default CatalogProductPopup;