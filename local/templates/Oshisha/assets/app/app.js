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
 * @type {Root}
 */
const root = createRoot(document.getElementById('boxInitialPopup'));
document.addEventListener('click', function (e) {

    if (e.target.closest('.image_cart')) {
        let item = e.target.closest('.image_cart').querySelector('.initialPopup');
        if (item) {
            const productId = item.getAttribute('data-product-id');
            const areaBuy = item.getAttribute('data-area-buy');
            const areaBuyQuantity = item.getAttribute('data-area-quantity');
            const groupedProduct = item.getAttribute('data-grouped-product');
            root.render(
                <StrictMode>
                    <CatalogProductPopup productId={productId} areaBuyQuantity={areaBuyQuantity} areaBuy={areaBuy}
                                         groupedProduct={groupedProduct}/>
                </StrictMode>);
        }
    }

});
