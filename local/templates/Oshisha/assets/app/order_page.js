import OrderUserTypeCheck from "./components/order_page/OrderUserTypeCheck";
import OrderUserProps from "./components/order_page/OrderUserProps";
import OrderUserAgreements from "./components/order_page/OrderUserAgreements";
import OrderComments from "./components/order_page/OrderComments";
import {createRoot} from 'react-dom/client';

var params = JSON.parse(document.currentScript.dataset.params);
var result = JSON.parse(document.currentScript.dataset.result);
var locations = JSON.parse(document.currentScript.dataset.locations);

var OrderUserPropsTitleBlock = document.getElementById(document.currentScript.dataset.userCheckBlockId);

if (OrderUserPropsTitleBlock) {
    createRoot(OrderUserPropsTitleBlock).render(
        <OrderUserTypeCheck
            result={result}
            params={params}
        />
    )
}

var OrderUserPropsBlock = document.getElementById(document.currentScript.dataset.userPropsBlockId);
if (OrderUserPropsBlock) {
    createRoot(OrderUserPropsBlock).render(
        <OrderUserProps
            result={result}
            locations={locations}
        />
    );
}

var OrderUserAgreementsBlock = document.getElementById(document.currentScript.dataset.userAgreementsBlockId);
if (OrderUserAgreementsBlock) {
    createRoot(OrderUserAgreementsBlock).render(
        <OrderUserAgreements/>
    );
}

var OrderCommentsBlock = document.getElementById(document.currentScript.dataset.newBlockWithCommentId);
if(OrderCommentsBlock) {
    createRoot(OrderCommentsBlock).render(
        <OrderComments
            result={JSON.parse(document.currentScript.dataset.result)}
            params={params}
        />
    )
}
