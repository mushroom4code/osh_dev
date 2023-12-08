import React from "react";

class OrderPaysystems extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            result: this.props.result,
            params: this.props.params,
            paysystemsInitialized: false
        }
    }

    editActivePaySystemBlock: function (activeNodeMode) {
        var node = activeNodeMode ? this.paySystemBlockNode : this.paySystemHiddenBlockNode,
            paySystemContent, paySystemNode;

        if (this.initialized.paySystem) {
            BX.remove(BX.lastChild(node));
            node.appendChild(BX.firstChild(this.paySystemHiddenBlockNode));
        } else {
            paySystemContent = node.querySelector('.bx-soa-section-content');
            if (!paySystemContent) {
                paySystemContent = this.getNewContainer();
                node.appendChild(paySystemContent);
            } else
                BX.cleanNode(paySystemContent);

            this.getErrorContainer(paySystemContent);
            paySystemNode = BX.create('DIV', {props: {className: 'bx-soa-pp'}});
            this.editPaySystemItems(paySystemNode);
            paySystemContent.appendChild(paySystemNode);

            if (this.params.SHOW_COUPONS_PAY_SYSTEM == 'Y')
                this.editCoupons(paySystemContent);

            this.getBlockFooter(paySystemContent);
        }
    },
    editPaySystemItems: function (paySystemNode) {
        if (!this.result.PAY_SYSTEM || this.result.PAY_SYSTEM.length <= 0)
            return;

        var paySystemItemsContainer = BX.create('DIV', {props: {className: 'bx-soa-pp-item-container order-1'}}),
            paySystemItemsContainerRow = BX.create('DIV', {props: {className: 'row flex flex-wrap gap-4'}}),
            paySystemItemNode, i;

        for (i = 0; i < this.paySystemPagination.currentPage.length; i++) {
            paySystemItemNode = this.createPaySystemItem(this.paySystemPagination.currentPage[i]);
            paySystemItemsContainerRow.appendChild(paySystemItemNode);
        }
        paySystemItemsContainer.appendChild(paySystemItemsContainerRow);

        if (this.paySystemPagination.show)
            this.showPagination('paySystem', paySystemItemsContainer);

        paySystemNode.appendChild(paySystemItemsContainer);
    },

    createPaySystemItem: function (item) {
        var checked = item.CHECKED == 'Y',
            paySystemId = parseInt(item.ID),
            label, itemNode;

        label = BX.create('DIV', {
            props: {className: 'bx-soa-pp-company-graf-container pay_system rounded-[10px]' +
                    ' p-[30px] flex w-full cursor-pointer'},
            children: [
                BX.create('INPUT', {
                    props: {
                        id: 'ID_PAY_SYSTEM_ID_' + paySystemId,
                        name: 'PAY_SYSTEM_ID',
                        type: 'checkbox',
                        className: 'bx-soa-pp-company-checkbox hidden',
                        value: paySystemId,
                        checked: checked
                    }
                }),
                BX.create('DIV', {
                    props: {className: 'bx-soa-pp-company-title text-base w-full'},
                    children: [BX.create('P', {
                        props: {className: 'bx-soa-pp-company-title text-light-red font-medium mt-2 mb-2.5' +
                                ' dark:text-white'},
                        text: item.NAME
                    }), BX.create('DIV', {
                        props: {className: 'bx-soa-pp-company-smalltitle text-stone-600 text-sm' +
                                ' dark:text-gray-300'},
                        text: item.DESCRIPTION
                    })]
                }),
            ]
        });
        itemNode = BX.create('DIV', {
            props: {className: 'bx-soa-pp-company relative mt-5 w-full bg-textDark rounded-[10px] flex flex-1' +
                    ' min-h-[170px] dark:bg-darkBox dark:border-grey-line-order'},
            children: [label],
            events: {
                click: BX.proxy(this.selectPaySystem, this)
            }
        });

        if (checked)
            BX.addClass(itemNode, 'bx-selected border-2 border-solid border-light-red bg-white');

        return itemNode;
    },

    editPaySystemInfo: function (paySystemNode) {
        if (!this.result.PAY_SYSTEM || (this.result.PAY_SYSTEM.length == 0 && this.result.PAY_FROM_ACCOUNT != 'Y'))
            return;

        var paySystemInfoContainer = BX.create('DIV', {
                props: {
                    className: (this.result.PAY_SYSTEM.length == 0 ? 'col-12 mb-3' : 'col-md-5 mb-lg-0') + ' col-12 mb-3 order-md-2 order-1 bx-soa-pp-desc-container'
                }
            }),
            innerPs, currentPaySystem,
            logotype, logoNode;

        BX.cleanNode(paySystemInfoContainer);

        if (this.result.PAY_FROM_ACCOUNT == 'Y')
            innerPs = this.getInnerPaySystem(paySystemInfoContainer);

        currentPaySystem = this.getSelectedPaySystem();
        if (currentPaySystem) {
            logoNode = BX.create('DIV', {props: {className: 'bx-soa-pp-company-image'}});
            logotype = this.getImageSources(currentPaySystem, 'PSA_LOGOTIP');
            if (logotype && logotype.src_2x) {
                logoNode.setAttribute('style',
                    'background-image: url("' + logotype.src_1x + '");' +
                    'background-image: -webkit-image-set(url("' + logotype.src_1x + '") 1x, url("' + logotype.src_2x + '") 2x)'
                );
            }
        }
        paySystemInfoContainer.appendChild(
            BX.create('DIV', {
                props: {className: 'bx-soa-pp-company'},
                children: [innerPs]
            })
        );
        paySystemNode.appendChild(paySystemInfoContainer);
    }

    render() {
        var resultOuterJsx = [];
        if (!this.state.paysystemsInitialized) {
            resultOuterJsx.push(<div key={'paysytems_error_block'} className="alert alert-danger" style="display: none"></div>);
            // resultOuterJsx.push(<div className="bx-soa-pp"></div>)
            // paySystemNode = BX.create('DIV', {props: {className: 'bx-soa-pp'}});
            // BX.create('DIV', {props: {className: 'alert alert-danger'}, style: {display: 'none'}})
            this.editPaySystemItems(paySystemNode);
            paySystemContent.appendChild(paySystemNode);

            if (this.params.SHOW_COUPONS_PAY_SYSTEM == 'Y')
                this.editCoupons(paySystemContent);

            this.getBlockFooter(paySystemContent);



            var resultInnerJsx = [];

            if (!this.state.result.PAY_SYSTEM || this.state.result.PAY_SYSTEM.length <= 0) {
                return resultInnerJsx;
            } else {
                // var paySystemItemsContainer = BX.create('DIV', {props: {className: 'bx-soa-pp-item-container order-1'}}),
                //     paySystemItemsContainerRow = BX.create('DIV', {props: {className: 'row flex flex-wrap gap-4'}}),
                //     paySystemItemNode, i;
                //
                // for (i = 0; i < this.paySystemPagination.currentPage.length; i++) {
                //     paySystemItemNode = this.createPaySystemItem(this.paySystemPagination.currentPage[i]);
                //     paySystemItemsContainerRow.appendChild(paySystemItemNode);
                // }
                // paySystemItemsContainer.appendChild(paySystemItemsContainerRow);

                // if (this.paySystemPagination.show)
                //     this.showPagination('paySystem', paySystemItemsContainer);

                // paySystemNode.appendChild(paySystemItemsContainer);
                for (i = 0; i < this.paySystemPagination.currentPage.length; i++) {
                    var checked = item.CHECKED == 'Y',
                        paySystemId = parseInt(item.ID),
                        label, itemNode;

                    label = BX.create('DIV', {
                        props: {className: 'bx-soa-pp-company-graf-container pay_system rounded-[10px]' +
                                ' p-[30px] flex w-full cursor-pointer'},
                        children: [
                            BX.create('INPUT', {
                                props: {
                                    id: 'ID_PAY_SYSTEM_ID_' + paySystemId,
                                    name: 'PAY_SYSTEM_ID',
                                    type: 'checkbox',
                                    className: 'bx-soa-pp-company-checkbox hidden',
                                    value: paySystemId,
                                    checked: checked
                                }
                            }),
                            BX.create('DIV', {
                                props: {className: 'bx-soa-pp-company-title text-base w-full'},
                                children: [BX.create('P', {
                                    props: {className: 'bx-soa-pp-company-title text-light-red font-medium mt-2 mb-2.5' +
                                            ' dark:text-white'},
                                    text: item.NAME
                                }), BX.create('DIV', {
                                    props: {className: 'bx-soa-pp-company-smalltitle text-stone-600 text-sm' +
                                            ' dark:text-gray-300'},
                                    text: item.DESCRIPTION
                                })]
                            }),
                        ]
                    });
                    itemNode = BX.create('DIV', {
                        props: {className: 'bx-soa-pp-company relative mt-5 w-full bg-textDark rounded-[10px] flex flex-1' +
                                ' min-h-[170px] dark:bg-darkBox dark:border-grey-line-order'},
                        children: [label],
                        events: {
                            click: BX.proxy(this.selectPaySystem, this)
                        }
                    });

                    if (checked)
                        BX.addClass(itemNode, 'bx-selected border-2 border-solid border-light-red bg-white');

                    return itemNode;
                }


                resultInnerJsx.push(
                    <div className="bx-soa-pp-item-container order-1">
                        <div className="row flex flex-wrap gap-4">

                        </div>
                    </div>
                );
            }

        }
        return(<div>{resultJsx}</div>);
    }
}

export default OrderPaysystems;