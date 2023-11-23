import ContragentList from "./components/ContragentList";
import OrderUserProps from "./components/order_page/OrderUserProps";
import OrderComments from "./components/order_page/OrderComments";
import {createRoot} from 'react-dom/client';
import {StrictMode} from "react";


const contragentForm = document.getElementById('createContragent');
if (contragentForm) {
    createRoot(contragentForm).render(<StrictMode><ContragentList/></StrictMode>);
}

var OrderCommentsBlock = document.getElementById('new_block_with_comment_box');
if(OrderCommentsBlock) {
    createRoot(OrderCommentsBlock).render(
        <OrderComments
            result={JSON.parse(document.currentScript.dataset.result)}
            params={JSON.parse(document.currentScript.dataset.params)}
        />
    )
}

var OrderUserPropsBlock = document.getElementById('user-properties-block');
if (OrderUserPropsBlock) {
    createRoot(OrderUserPropsBlock).render(
            <OrderUserProps
                result={JSON.parse(document.currentScript.dataset.result)}
                locations={JSON.parse(document.currentScript.dataset.locations)}
            />
    );

}