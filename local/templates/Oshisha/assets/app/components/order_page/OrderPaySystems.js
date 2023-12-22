import React from "react";

class OrderPaySystems extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            result: this.props.result,
            params: this.props.params,
            options: this.props.options,
            paySystemPagination: {},
            paySystemBlockNode: this.props.domNode
        }
        this.selectPaySystem = this.selectPaySystem.bind(this);
    }

    selectPaySystem(event) {
        var target = event.target || event.srcElement,
            innerPaySystemSection = this.state.paySystemBlockNode.querySelector('div.bx-soa-pp-inner-ps'),
            innerPaySystemCheckbox = this.state.paySystemBlockNode.querySelector('input[type=checkbox][name=PAY_CURRENT_ACCOUNT]'),
            fullPayFromInnerPaySystem = this.state.result.TOTAL && parseFloat(this.state.result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY) === 0;

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
                selectedSection = this.state.paySystemBlockNode.querySelector('.bx-soa-pp-company.bx-selected');
                BX.addClass(actionSection, 'bx-selected');
                actionInput = actionSection.querySelector('input[type=checkbox]');
                actionInput.checked = true;

                if (selectedSection) {
                    BX.removeClass(selectedSection, 'bx-selected');
                    selectedSection.querySelector('input[type=checkbox]').checked = false;
                }
            }
        }

        BX.Sale.OrderAjaxComponent.sendRequest();
    }

    static getDerivedStateFromProps(props, state) {
        var arReserve, pages, arPages, i;
        if (state.result.PAY_SYSTEM) {
            if (state.options.paySystemsPerPage > 0 && state.result.PAY_SYSTEM.length > state.options.paySystemsPerPage) {
                arReserve = state.result.PAY_SYSTEM.slice();
                pages = Math.ceil(arReserve.length / state.options.paySystemsPerPage);
                arPages = [];

                for (i = 0; i < pages; i++) {
                    arPages.push(arReserve.splice(0, state.options.paySystemsPerPage));
                }
                state.paySystemPagination.pages = arPages;

                for (i = 0; i < state.result.PAY_SYSTEM.length; i++) {
                    if (state.result.PAY_SYSTEM[i].CHECKED === 'Y') {
                        state.paySystemPagination.pageNumber = Math.ceil(++i / state.options.paySystemsPerPage);
                        break;
                    }
                }

                state.paySystemPagination.pageNumber = state.paySystemPagination.pageNumber || 1;
                state.paySystemPagination.currentPage = arPages.slice(state.paySystemPagination.pageNumber - 1, state.paySystemPagination.pageNumber)[0];
                state.paySystemPagination.show = true
            } else {
                state.paySystemPagination.pageNumber = 1;
                state.paySystemPagination.currentPage = state.result.PAY_SYSTEM;
                state.paySystemPagination.show = false;
            }
        }
        return state;
    }

    render() {
        var itemsJsx = [];

        if (!this.state.result.PAY_SYSTEM || this.state.result.PAY_SYSTEM.length <= 0) {
            return(<div></div>);
        } else {
            var i;
            for (i = 0; i < this.state.paySystemPagination.currentPage.length; i++) {
                var item = this.state.paySystemPagination.currentPage[i],
                    checked = item.CHECKED == 'Y',
                    paySystemId = parseInt(item.ID);

                itemsJsx.push(
                    <div key={'paysystem_block_' + paySystemId} className={'bx-soa-pp-company relative mt-5' +
                        ' w-full bg-textDark rounded-[10px] flex min-h-[170px] dark:bg-darkBox' +
                        ' ' + (checked
                            ? 'bx-selected border-2 border-solid border-light-red dark:border-grey-line-order bg-white'
                            : '')}
                         onClick={this.selectPaySystem}>
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
            <div>
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
}

export default OrderPaySystems;