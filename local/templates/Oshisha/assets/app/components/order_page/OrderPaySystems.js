import React, {useContext, useRef} from "react";
import OrderContext from "./Context/OrderContext";

function OrderPaySystems() {
    const {result, options, afterSendReactRequest} = useContext(OrderContext);
    const paySystemBlockRef = useRef(null);

    const selectPaySystem = (event) => {
        BX.OrderPageComponents.startLoader();
        var target = event.target || event.srcElement,
            innerPaySystemSection = paySystemBlockRef.current.querySelector('div.bx-soa-pp-inner-ps'),
            innerPaySystemCheckbox = paySystemBlockRef.current.querySelector('input[type=checkbox][name=PAY_CURRENT_ACCOUNT]'),
            fullPayFromInnerPaySystem = result.TOTAL && parseFloat(result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY) === 0;

        var actionSection = BX.hasClass(target, 'bx-soa-pp-company') ? target : BX.findParent(target, {className: 'bx-soa-pp-company'}),
            actionInput, selectedSection;

        if (actionSection) {
            if (BX.hasClass(actionSection, 'bx-selected'))
                return BX.PreventDefault(event);

            if (innerPaySystemCheckbox && innerPaySystemCheckbox.checked && fullPayFromInnerPaySystem) {
                BX.addClass(actionSection, 'bx-selected');
                actionInput = actionSection.querySelector('input[type=checkbox]');
                actionInput.checked = true;
                BX.removeClass(innerPaySystemSection, 'bx-selected');
                innerPaySystemCheckbox.checked = false;
            } else {
                selectedSection = paySystemBlockRef.current.querySelector('.bx-soa-pp-company.bx-selected');
                BX.addClass(actionSection, 'bx-selected');
                actionInput = actionSection.querySelector('input[type=checkbox]');
                actionInput.checked = true;

                if (selectedSection) {
                    BX.removeClass(selectedSection, 'bx-selected');
                    selectedSection.querySelector('input[type=checkbox]').checked = false;
                }
            }
        }

        BX.Sale.OrderAjaxComponent.sendRequest('refreshOrderAjax', [], afterSendReactRequest);
    }

    var arReserve, pages, arPages, i, paySystemPagination = {};
    if (result.PAY_SYSTEM) {
        if (options.paySystemsPerPage > 0 && result.PAY_SYSTEM.length > options.paySystemsPerPage) {
            arReserve = result.PAY_SYSTEM.slice();
            pages = Math.ceil(arReserve.length / options.paySystemsPerPage);
            arPages = [];

            for (i = 0; i < pages; i++) {
                arPages.push(arReserve.splice(0, options.paySystemsPerPage));
            }
            paySystemPagination.pages = arPages;

            for (i = 0; i < result.PAY_SYSTEM.length; i++) {
                if (result.PAY_SYSTEM[i].CHECKED === 'Y') {
                    paySystemPagination.pageNumber = Math.ceil(++i / options.paySystemsPerPage);
                    break;
                }
            }

            paySystemPagination.pageNumber = paySystemPagination.pageNumber || 1;
            paySystemPagination.currentPage = arPages.slice(paySystemPagination.pageNumber - 1, paySystemPagination.pageNumber)[0];
            paySystemPagination.show = true
        } else {
            paySystemPagination.pageNumber = 1;
            paySystemPagination.currentPage = result.PAY_SYSTEM;
            paySystemPagination.show = false;
        }
    }
    
    

    var itemsJsx = [];

    if (!result.PAY_SYSTEM || result.PAY_SYSTEM.length <= 0) {
        return (<div></div>);
    } else {
        var i;
        for (i = 0; i < paySystemPagination.currentPage.length; i++) {
            var item = paySystemPagination.currentPage[i],
                checked = item.CHECKED == 'Y',
                paySystemId = parseInt(item.ID);

            itemsJsx.push(
                <div key={'paysystem_block_' + paySystemId} className={'bx-soa-pp-company relative mt-5' +
                    ' w-full bg-textDark rounded-[10px] flex min-h-[170px] dark:bg-darkBox' +
                    ' ' + (checked
                        ? 'bx-selected border-2 border-solid border-light-red dark:border-grey-line-order bg-white'
                        : '')}
                     onClick={selectPaySystem}>
                    <div className="bx-soa-pp-company-graf-container pay_system rounded-[10px] p-[30px]
                                 flex w-full cursor-pointer">
                        <input type="checkbox" id={'ID_PAY_SYSTEM_ID_' + paySystemId} name="PAY_SYSTEM_ID"
                               className="bx-soa-pp-company-checkbox hidden" value={paySystemId}
                               defaultChecked={checked}/>
                        <div className="bx-soa-pp-company-title text-base w-full">
                            <p className={'bx-soa-pp-company-title font-medium mt-2 mb-2.5 dark:text-white' +
                                (checked ? ' text-light-red' : ' text-black')}>{item.NAME}</p>
                            <div className="bx-soa-pp-company-smalltitle text-stone-600 text-sm
                                         dark:text-gray-300">{item.DESCRIPTION}</div>
                        </div>
                    </div>
                </div>
            );
        }
    }

    return (
        <div ref={paySystemBlockRef}>
            <div className="alert alert-danger hidden"></div>
            <div className="bx-soa-pp">
                <div className="bx-soa-pp-item-container order-1">
                    <div className="row grid grid-cols-2 gap-x-4 auto-rows-[1fr]">
                        {itemsJsx}
                    </div>
                </div>
            </div>
        </div>
    );

}

export default OrderPaySystems;