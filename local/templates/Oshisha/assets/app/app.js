import ContragentList from "./components/ContragentList";
import {createRoot} from 'react-dom/client';
import {StrictMode} from "react";
import CatalogProductPopup from "./components/CatalogProductPopup";

/**
 * PERSONAL CONTRAGENTS
 * @type {HTMLElement}
 */
const contragentForm = document.getElementById('createContragent');

if (contragentForm) {
    createRoot(contragentForm).render(<StrictMode><ContragentList/></StrictMode>);
}

/**
 * CATALOG POPUP
 * @type {HTMLCollectionOf<Element>}
 */
const popupProduct = document.getElementsByClassName('initialPopup');

if (popupProduct.length > 0) {
    for (let i = 0; i < popupProduct.length; i++) {
        let item = popupProduct[i];
        const productId = item.getAttribute('data-product-id');
        const areaBuy = item.getAttribute('data-area-buy');
        const areaBuyQuantity = item.getAttribute('data-area-quantity');

        item.addEventListener("click", () => {
            const boxForComponent = document.querySelector('.boxInitialPopup[data-product-id="' + productId + '"]');
            if (boxForComponent && !boxForComponent.querySelector('.box-popup-product')) {
                createRoot(boxForComponent).render(
                    <StrictMode>
                        <CatalogProductPopup productId={productId} areaBuyQuantity={areaBuyQuantity} areaBuy={areaBuy}/>
                    </StrictMode>);
            }
        });
    }
}
