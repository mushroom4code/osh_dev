import ContragentList from "./components/ContragentList";
import {createRoot} from 'react-dom/client';
import {StrictMode} from "react";
import CatalogProductPopup from "./components/CatalogProductPopup";

const contragentForm = document.getElementById('createContragent');
if (contragentForm) {
    createRoot(contragentForm).render(<StrictMode><ContragentList/></StrictMode>);
}


const popupProduct = document.getElementsByClassName('initialPopup');
if (popupProduct.length > 0) {
    for (let i = 0; i < popupProduct.length; i++) {
        let item = popupProduct[i];
        item.addEventListener("click", (e)=>{
            const productId = e.target.getAttribute('data-product-id');
            createRoot(e.target).render(<StrictMode><CatalogProductPopup product_id={productId}/></StrictMode>);
        });
    }
}
