import ContragentList from "./components/ContragentList";
import OrderUserProps from "./components/order_page/OrderUserProps";
import OrderComments from "./components/order_page/OrderComments";
import {createRoot} from 'react-dom/client';
import {StrictMode} from "react";
import axios from "axios";

console.log('ups');
console.log(BX.Sale);
console.log(document.currentScript.dataset);
// console.log(JSON.parse(document.currentScript.dataset.result));
console.log(JSON.parse(document.currentScript.dataset.locations));
console.log('dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd');
console.log(document.currentScript.dataset.params)
console.log(JSON.parse(document.currentScript.dataset.params));

// const contragentForm = document.getElementById('createContragent');
// if (contragentForm) {
//     createRoot(contragentForm).render(<StrictMode><ContragentList/></StrictMode>);
// }

// let data = [
//     'ACTION_VARIABLE': 'soa-action',
//     'soa-action': 'refreshOrderAjax',
// ];
// axios.post('/local/templates/Oshisha/components/bitrix/sale.order.ajax/ajax.php',
//     data).then(res => {
//         console.log(res);
        // if (res.data?.success) {
        //     setResult(res.data?.success)
        //     setColor('dark:text-textDarkLightGray text-greenButton')
        //     setColorRes('dark:text-textDarkLightGray text-greenButton')
        //     emptyDataInputs()
        //     setState(false)
        // } else if (res.data?.error) {
        //     if (res.data?.error?.code) {
        //         setResultNew(res.data?.error?.code)
        //         setContrResult(res.data?.error?.item)
        //         setShowForm(false)
        //     }
        // } else {
        //     setResultNew('При создании контрагента возникла ошибка! ' +
        //         'Можете обратиться к менеджеру нашей компании или повторить попытку');
        // }
//     }
// )

// axios({
//     method: 'POST',
//     dataType: 'json',
//     url: this.ajaxUrl,
//     data: this.getData(action, actionData),
//     onsuccess: BX.delegate(function (result) {
//         if (result.redirect && result.redirect.length)
//             document.location.href = result.redirect;
//         this.saveFiles();
//         switch (eventArgs.action) {
//             case 'refreshOrderAjax':
//                 this.refreshOrder(result);
//                 break;
//             case 'confirmSmsCode':
//             case 'showAuthForm':
//                 this.firstLoad = true;
//                 this.refreshOrder(result);
//                 break;
//             case 'enterCoupon':
//                 if (result && result.order) {
//                     this.deliveryCachedInfo = [];
//                     this.refreshOrder(result);
//                 } else {
//                     this.addCoupon(result);
//                 }
//
//                 break;
//             case 'removeCoupon':
//                 if (result && result.order) {
//                     this.deliveryCachedInfo = [];
//                     this.refreshOrder(result);
//                 } else {
//                     this.removeCoupon(result);
//                 }
//
//                 break;
//         }
//         BX.cleanNode(this.savedFilesBlockNode);
//         this.endLoader();
//     }, this),
//     onfailure: BX.delegate(function () {
//         this.endLoader();
//     }, this)
// });
const OrderCommentsBlock = document.getElementById('new_block_with_comment_box');
if(OrderCommentsBlock) {
    createRoot(OrderCommentsBlock).render(
        <OrderComments
            result={JSON.parse(document.currentScript.dataset.result)}
            params={JSON.parse(document.currentScript.dataset.params)}
        />
    )
}

const OrderUserPropsBlock = document.getElementById('user-properties-block');
if (OrderUserPropsBlock) {
    createRoot(OrderUserPropsBlock).render(
            <OrderUserProps
                result={JSON.parse(document.currentScript.dataset.result)}
                locations={JSON.parse(document.currentScript.dataset.locations)}
            />
    );

}



// const saleOrderAjaxForm = document.getElementById('');
// if (saleOrderAjaxForm) {
//     createRoot(saleOrderAjaxForm).render()
// }
