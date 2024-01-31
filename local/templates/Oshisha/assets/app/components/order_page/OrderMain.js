import React, {useEffect} from 'react';
import OrderUserTypeCheck from "./OrderUserTypeCheck";
import OrderUserProps from "./OrderUserProps";
import OrderDelivery from "./OrderDelivery";
import OrderPaySystems from "./OrderPaySystems";
import OrderUserAgreements from "./OrderUserAgreements";
import OrderComments from "./OrderComments";
import OrderTotal from "./OrderTotal";
import {OrderContextProvider} from "./Context/OrderContext";

OrderMain.propTypes = {};

export const ajaxDeliveryUrl = '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php';

function OrderMain({result, locations, params, options, contrAgents, OrderGeneralUserPropsBlockId, ajaxUrl}) {

    useEffect(()=>{
        // ymap.init(()=>{console.log('ymap')})
    }, [])

    const renderDependingOnDeliveryToPaysystem = () => {
        return (
            <>
                <script type="text/template" id="osh-pickup-template">
                    <div className="row">
                        <div className="col-lg-6 col-12">
                            <div id="pickup-address">
                                <span></span> <span></span>
                                <address></address>
                            </div>
                            <div id="pickup-station">
                                <div className="pickup-station-img"></div>
                            </div>
                            <div id="pickup-time">
                                <div className="pickup-time-img"></div>
                            </div>
                            <div id="pickup-info">
                                <div className="pickup-info-img"></div>
                            </div>
                        </div>
                        <div className="col-lg-6 col-12">
                            <div id="map-pick-up"></div>
                        </div>
                        <div className="pickup-pass-data">
                            Данные для пропуска
                        </div>
                    </div>
                </script>

                {/*PICKUP BLOCK*/}
                <div id="bx-soa-pickup" data-visited="false" className="bx-soa-section mb-4 "
                     style={{display: "none"}}>
                    <div
                        className="bx-soa-section-title-container overflow-hidden flex justify-between
                            items-center flex-nowrap">
                        <div className="bx-soa-section-title" data-entity="section-title">
                        </div>
                    </div>
                    <div className="bx-soa-section-content"></div>
                </div>


            </>
        );

    }

    return (
        <>
            <OrderContextProvider result={result} params={params} options={options} locations={locations}
                                  OrderGeneralUserPropsBlockId={OrderGeneralUserPropsBlockId} ajaxUrl={ajaxUrl}>
                <div id="bx-soa-main-notifications" className="col-span-2">
                    <div className="alert alert-danger text-light-red dark:text-whitep p-4" style={{display: "none"}}></div>
                    <div datatype="informer" style={{display: "none"}}></div>
                </div>
                <div className="col-span-2">
                    <h5 className="md:mb-3 md:text-2xl text-xl mb-5 font-medium dark:font-normal text-textLight dark:text-textDarkLightGray">
                        Покупатель
                        <i className="inline-block w-[19px] h-[19px] ml-2.5">
                            <svg width="19" height="19" viewBox="0 0 19 19" xmlns="http://www.w3.org/2000/svg">
                                <g clipPath="url(#clip0_2706_1834)">
                                    <path fillRule="evenodd" className="dark:fill-white fill-black" clipRule="evenodd"
                                          d="M0 15.0422V19H3.95778L15.6306 7.32718L11.6728 3.36939L0 15.0422ZM18.6913
                                   4.26649C19.1029 3.85488 19.1029 3.18997 18.6913 2.77836L16.2216 0.308707C15.81
                                    -0.102902 15.1451 -0.102902 14.7335 0.308707L12.8021 2.24011L16.7599
                                     6.19789L18.6913 4.26649Z"
                                          fill="#393939"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_2706_1834">
                                        <rect width="19" height="19" fill="white"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </i>
                    </h5>
                    <div className="bx-soa">
                        <div id="bx-soa-properties" data-visited="true" className="bx-soa-section mb-14 bx-active">
                            <div id="user-properties-title-block"
                                 className="bx-soa-section-title-container hidden">
                                <div className=" userCheck" id="userCheck">
                                    <OrderUserTypeCheck/>
                                </div>

                            </div>
                            <div id="user-properties-block" className="bx-soa-section-content">
                                <div className="alert alert-danger text-light-red dark:text-white p-4"
                                     style={{display: "none"}}></div>
                                <OrderUserProps contrAgents={contrAgents}/>
                            </div>
                        </div>
                        {/*AUTH BLOCK	*/}
                        <div id="bx-soa-auth" className="bx-soa-section mb-4 bx-soa-auth" style={{display: "none"}}>
                            <div className="bx-soa-section-title-container overflow-hidden">
                                <div className="bx-soa-section-title" data-entity="section-title">
                                    {params['MESS_AUTH_BLOCK_NAME']}
                                </div>
                            </div>
                            <div className="bx-soa-section-content"></div>
                        </div>

                        {/*REGION BLOCK*/}
                        <div id="bx-soa-region hidden" data-visited="false" className="bx-soa-section bx-active">
                            <div className="bx-soa-section-title-container overflow-hidden">
                                <h2 className="bx-soa-section-title text-[22px] col-sm-9">
                                    <span className="bx-soa-section-title-count"></span>
                                    {params['MESS_REGION_BLOCK_NAME']}
                                </h2>
                                <div className="col-xs-12 col-sm-3 text-right">
                                    <a href="" className="bx-soa-editstep">
                                        {params['MESS_EDIT']}
                                    </a>
                                </div>
                            </div>
                            <div className="bx-soa-section-content container-fluid"></div>
                        </div>

                        {/*DELIVERY BLOCK*/}
                        <div id="bx-soa-delivery" data-visited="false"
                             className="bx-soa-section mb-4  bx-active"
                             style={!result['DELIVERY'] ? {display: "none"} : {display: "block"}}>
                            <div
                                className="bx-soa-section-title-container mt-2 mb-4 overflow-hidden flex
                            justify-between items-center flex-nowrap">
                                    <div className="bx-soa-section-title text-textLight dark:text-textDarkLightGray
                                    md:mb-3 md:text-2xl text-xl mb-5 font-medium dark:font-normal"
                                         data-entity="section-title">
                                        {params['MESS_DELIVERY_BLOCK_NAME']}
                                    </div>
                            </div>
                            <div className="box_with_delivery_type">
                            <div className="bx-soa-section-content" id="delivery-block">
                                    <div className="alert alert-danger text-light-red dark:text-white p-4"
                                         style={{display: "none"}}></div>
                                    <OrderDelivery/>
                                </div>
                                <div id="bx-soa-region" data-visited="false"
                                     className="bx-soa-section mb-4 hidden">
                                    <div
                                        className="bx-soa-section-title-container flex justify-between
                                    items-center flex-nowrap">
                                        <div className="bx-soa-section-title" data-entity="section-title">
                                            {params['MESS_REGION_BLOCK_NAME']}
                                        </div>
                                    </div>
                                    <div className="bx-soa-section-content"></div>
                                </div>
                            </div>
                        </div>

                        {renderDependingOnDeliveryToPaysystem()}

                        {/*PAY SYSTEMS BLOCK*/}
                        <div id="bx-soa-paysystem" data-visited="false" className="bx-soa-section mb-14  bx-active">
                            <div className="bx-soa-section-title-container overflow-hidden flex justify-between
                            items-center flex-nowrap">
                                <div
                                    className="bx-soa-section-title md:mb-3 md:text-2xl text-xl mb-5 font-medium
                                     text-textLight dark:text-textDarkLightGray dark:font-normal"
                                    data-entity="section-title">
                                    {params['MESS_PAYMENT_BLOCK_NAME']}
                                </div>
                            </div>
                            <div className="bx-soa-section-content py-2.5" id="paysystems_block">
                                <div className="alert alert-danger text-light-red dark:text-white p-4"
                                     style={{display: "none"}}></div>
                                <OrderPaySystems/>
                            </div>
                        </div>

                        <div id="user-agreements" className="mb-14"><OrderUserAgreements/></div>
                        <div className="new_block_with_comments mt-0 mb-8" id="new_block_with_comments">
                            <div id="new_block_with_comment_box"><OrderComments/></div>
                        </div>

                        {/*ORDER SAVE BLOCK*/}
                        <div id="bx-soa-orderSave">
                            <div className="checkbox">
                                {/*<?php*/}
                                {/*if ($arParams['USER_CONSENT'] === 'Y') {*/}
                                {/*    $APPLICATION->IncludeComponent(*/}
                                {/*        'bitrix:main.userconsent.request',*/}
                                {/*        '',*/}
                                {/*        array(*/}
                                {/*            'ID' => $arParams['USER_CONSENT_ID'],*/}
                                {/*            'IS_CHECKED' => $arParams['USER_CONSENT_IS_CHECKED'],*/}
                                {/*            'IS_LOADED' => $arParams['USER_CONSENT_IS_LOADED'],*/}
                                {/*            'AUTO_SAVE' => 'N',*/}
                                {/*            'SUBMIT_EVENT_NAME' => 'bx-soa-order-save',*/}
                                {/*            'REPLACE' => array(*/}
                                {/*                'button_caption' => isset($arParams['~MESS_ORDER']) ? $arParams['~MESS_ORDER'] : $arParams['MESS_ORDER'],*/}
                                {/*                'fields' => $arResult['USER_CONSENT_PROPERTY_DATA']*/}
                                {/*            )*/}
                                {/*        )*/}
                                {/*    );*/}
                                {/*}*/}
                                {/*?>*/}
                            </div>
                        </div>

                        <div style={{display: "none"}}>
                            <div id='bx-soa-region-hidden' className="bx-soa-section"></div>
                            <div id='bx-soa-paysystem-hidden' className="bx-soa-section"></div>
                            <div id='bx-soa-delivery-hidden' className="bx-soa-section"></div>
                            <div id='bx-soa-pickup-hidden' className="bx-soa-section"></div>
                            <div id="bx-soa-properties-hidden" className="bx-soa-section"></div>
                            <div id="bx-soa-auth-hidden" className="bx-soa-section">
                                <div className="bx-soa-section-content reg"></div>
                            </div>
                        </div>
                    </div>
                </div>
                {/*SIDEBAR BLOCK*/}
                <div id="order_total_block" className="col-start-3 lg:ml-5">
                    <OrderTotal/>
                </div>
            </OrderContextProvider>
        </>
    )
}

export default OrderMain;