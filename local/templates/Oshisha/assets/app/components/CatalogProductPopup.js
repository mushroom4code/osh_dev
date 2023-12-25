import React, {useEffect, useState} from 'react';
import axios from "axios";

function CatalogProductPopup({productId, areaBuyQuantity, areaBuy, groupedProduct}) {

    const [name, setName] = useState('Товар')
    const [srcProduct, setSrcProduct] = useState('Товар')
    const [countLike, setCountLike] = useState('Товар')
    const [productPage, setProductPage] = useState('Товар')
    const [quantityProduct, setQuantityProduct] = useState(0)
    const [maxQuantity, setMaxQuantity] = useState(0)
    const [price, setPrice] = useState(0)
    const [salePrice, setSalePrice] = useState(0)
    const [saleBool, setSaleBool] = useState(false)
    const [description, setDescription] = useState('')
    const [groupedProducts, setGroupedProduct] = useState([])
    const [groupedProps, setGroupedProps] = useState([])
    const [groupedSettings, setGroupedSettings] = useState([])
    const [id, setID] = useState(false)
    const [classBlock, setClassBlock] = useState('flex')

    useEffect(() => {
        if (id !== productId) {
            getProductData({prodId: productId, action: 'fastProduct', groupedProduct: groupedProduct})
        }
    }, [id, productId]);


    function getProductData(data) {
        loaderForSite('appendLoader', document.querySelector('body'))
        axios.post('/local/ajax/catalog_item.php', data).then(res => {
            const productData = res.data;
            if (productData?.NAME !== '') {
                setName(productData.NAME)
                setSrcProduct(productData.PREVIEW_PICTURE)
                setCountLike(productData.LIKE.COUNT_LIKES)
                setProductPage(productData.DETAIL_PAGE_URL)
                setQuantityProduct(productData.ACTUAL_BASKET ?? 0)
                setMaxQuantity(productData.PRODUCT.QUANTITY ?? 0)
                setPrice(productData.PRODUCT.PRICE ?? 0)
                setSaleBool(productData.PRODUCT.SALE_BOOL)
                setSalePrice(productData.PRODUCT.SALE_PRICE)
                setDescription(productData.DESCRIPTION)
                setGroupedProduct(Object.entries(productData.GROUPED_PRODUCT.GROUPED_PRODUCTS))
                setGroupedProps(Object.entries(productData.GROUPED_PRODUCT.GROUPED_PROPS_DATA))
                setGroupedSettings(productData.GROUPED_PRODUCT.SETTING)
                loaderForSite('', document.querySelector('body'))
                setClassBlock('flex');
                setID(productId)
            } else if (productData?.error) {
                if (productData?.error?.code) {
                    alert('Ошибка запроса данных по товару')
                }
            } else {
            }
        })
    }


    // $.each(arData.GROUPED_PROPS_DATA, function (groupName, group) {
    //     if (groupName !== 'USE_DISCOUNT') {
    //         const groupBox = box_with_prop.appendChild(BX.create('DIV', {
    //             props: {
    //                 className: 'flex flex-row overflow-auto mb-2 width-100 overflow-custom'
    //             },
    //         }));
    //
    //         let pref;
    //         arData.SETTING[groupName] !== undefined ? pref = arData.SETTING[groupName].PREF : pref = ''
    //         // перебор групп элементов
    //         let selectedBool = false;
    //         $.each(group, function (key, itemsGroup) {
    //
    //             let selected = '', type, itemWithPropValues;
    //             arData.SETTING[groupName] !== undefined ? type = arData.SETTING[groupName].TYPE : type = 'text'
    //             // if (selectedBool === false && currentProduct.PROPERTIES[groupName].JS_PROP !== undefined) {
    //             //     if (arrayDiff(itemsGroup, currentProduct.PROPERTIES[groupName].JS_PROP)) {
    //             //         selected = 'selected';
    //             //         selectedBool = true;
    //             //     }
    //             // }
    //
    //             itemWithPropValues = BX.create('DIV', {
    //                 dataset: {
    //                     active: selected !== '' ? 'true' : 'false',
    //                     prop_code: groupName,
    //                     prop_group: JSON.stringify(itemsGroup)
    //                 },
    //                 events: {
    //                     click: () => {
    //                         const arrProductGrouped = arData.GROUPED_PRODUCTS;
    //                         thisComponent.clickItemGrouped(thisButton, arrProductGrouped, groupName, box_popup,
    //                             attr_val, itemWithPropValues, box_with_price);
    //                     }
    //                 }
    //             })
    //
    //             if (type === 'color') {
    //                 BX.addClass(itemWithPropValues, 'mr-1 offer-box color-hookah br-10 mb-1');
    //             } else if (type === 'colorWithText') {
    //                 BX.addClass(itemWithPropValues, 'red_button_cart taste variation_taste font-14 ' +
    //                     'w-fit mb-lg-2 m-md-2 p-10 m-1 offer-box cursor-pointer');
    //             } else if (type === 'text') {
    //                 BX.addClass(itemWithPropValues, 'red_button_cart font-11 w-fit rounded-full ' +
    //                     ' mb-lg-2 m-md-2 m-1 offer-box cursor-pointer');
    //             }
    //
    //             const groupItems = groupBox.appendChild(BX.create('A', {
    //                 props: {
    //                     className: 'offer-link ' + selected
    //                 },
    //                 dataset: {
    //                     prop_code: groupName,
    //                     prop_group: JSON.stringify(group)
    //                 },
    //                 children: [
    //                     itemWithPropValues
    //                 ],
    //             }));
    //             // добавление элементов вкусов граммовок и тд - элементы группы
    //             const elemBox = BX.findChildByClassName(groupItems, 'offer-box');
    //             $.each(itemsGroup, function (itemKey, item) {
    //                 if (type === 'colorWithText') {
    //                     const colorNew = item.VALUE_XML_ID?.split('#');
    //                     elemBox.appendChild(BX.create('SPAN', {
    //                         props: {
    //                             className: 'taste mb-0 br-100 font-11',
    //                             style: "background-color:#" + colorNew[1] + "; " +
    //                                 "border-color:#" + colorNew[1] + "; padding: 6px 11px;"
    //                         },
    //                         dataset: {
    //                             background: '#' + colorNew[1],
    //                         },
    //                         text: item.VALUE_ENUM + pref
    //                     }))
    //                 } else if (type === 'color') {
    //                     if ($(elemBox).find('img[src="' + item.PREVIEW_PICTURE + '"]').length <= 0) {
    //                         elemBox.appendChild(
    //                             BX.create('IMG', {
    //                                 props: {
    //                                     className: 'br-10',
    //                                     src: item.PREVIEW_PICTURE,
    //                                 },
    //                             }))
    //                     }
    //                 } else {
    //                     elemBox.appendChild(BX.create('DIV', {
    //                         props: {
    //                             className: ''
    //                         },
    //                         text: item.VALUE_ENUM + pref
    //                     }))
    //                 }
    //
    //                 if (selected !== '') {
    //                     // вывод названия и смена ссылки
    //                     $(BX.findChildByClassName(box_popup, ('title-product'))).text(item.NAME);
    //                     $(BX.findChildByClassName(box_popup, ('href-product'))).attr('href', item.CODE);
    //                 }
    //             });
    //
    //         });
    //     }
    // });


    return (<div
        className={"fixed w-screen left-0 top-0 bg-lightOpacityWindow dark:bg-darkOpacityWindow " +
            "justify-center h-screen z-50 box-popup-product " + classBlock}>
        <div
            className="open-modal-product md:m-auto m-0 md:h-fit  h-full catalog-item-product bg-white p-6 max-w-4xl
                 w-full md:rounded-lg rounded-0 catalog-fast-window dark:bg-darkBox">
            <div className="mb-2 flex flex-row justify-between">
                    <span className="font-medium dark:font-light md:text-2xl mb-2 p-0 w-4/5 text-lightGrayBg
                    dark:text-textDarkLightGray text-lg">{name}</span>
                <span className="text-right p-0 close-box cursor-pointer" title="Закрыть"
                      onClick={(e) => {
                          setClassBlock('hidden');
                      }}>
                        <svg width="25" height="25" viewBox="0 0 9 8" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 7.5L8 0.5M1 0.5L8 7.5"
                                  className="stroke-darkBox dark:stroke-textDarkLightGray"
                                  strokeLinecap="round"
                                  strokeLinejoin="round"/></svg>
                    </span>
            </div>
            <div className="flex md:flex-row flex-col js-parent-flex">
                <div className="product-image-sliders-box md:mr-7 mr-0 md:w-1/2 w-full">
                    <div className="flex lg:flex-row product-image-main-slider">
                        <div className="box-with-image-prod p-10 bg-white rounded-xl mb-4 md:mb-0 relative border
                            border-borderColor dark:border-white md:w-auto w-full">
                            <div className="flex items-center justify-center box-with-image-one">
                                <img className="md:w-80 md:h-80 h-64 w-64 js-one-img object-contain" src={srcProduct}
                                     alt="oshisha"/>
                            </div>
                            <div className="absolute like-with-fav right-3 top-3">
                                <div className="box_with_like like-modal flex flex-col items-center">
                                    <a className="icon_like method mb-3"
                                       data-method="like">
                                        <svg width="23" height="22" viewBox="0 0 20 19"
                                             className="md:w-6 md:h-6 h-5 w-5"
                                             fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M9.22011 17.249L9.22307 17.2508C9.46452 17.4032 9.74419 17.4851 10.0312 17.4851C10.3183 17.4851 10.598 17.4032 10.8394 17.2508L10.8394 17.2508L10.8424 17.249C14.3602 15.0066 16.3461 12.7447 17.4489 10.7609C18.5536 8.77384 18.75 7.10597 18.75 6.08998C18.75 3.43533 16.6483 1.25 14.0156 1.25C12.6451 1.25 11.4764 2.01156 10.7095 2.67058C10.4473 2.89598 10.2191 3.12053 10.0312 3.31989C9.84341 3.12053 9.61524 2.89598 9.35296 2.67058C8.58612 2.01156 7.41738 1.25 6.04688 1.25C3.41417 1.25 1.3125 3.43533 1.3125 6.08998C1.3125 7.10597 1.50891 8.77384 2.61355 10.7609C3.71642 12.7447 5.70226 15.0066 9.22011 17.249Z"
                                                strokeWidth="1" className="stroke-black"
                                                strokeLinecap="round"
                                                strokeLinejoin="round"></path>
                                        </svg>
                                        <article className="like_span text-center text-xs" id="likes">
                                            {countLike}
                                        </article>
                                    </a>
                                    <a className="product-item__favorite-star method"
                                       data-method="favorite" title="Добавить в избранное">
                                        <svg width="23" height="22" viewBox="0 0 25 26"
                                             fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M13.1765 19.9412L5.05882 24L7.08823 15.8824L1 9.11765L9.79412 8.44118L13.1765 1M13.1765 1L16.5588 8.44118L25.3529 9.11765L19.2647 15.8824L21.2941 24L13.1765 19.9412"
                                                strokeLinecap="round" strokeLinejoin="round"
                                                className="stroke-black"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="flex flex-col justify-between md:w-1/2 w-full">
                    <div>
                        <div
                            className="prices-box ml-lg-4 ml-md-4 ml-0 mb-lg-4 mb-md-2 mb-2 flex flex-row items-center relative">
                            {saleBool ? <div className="base-price group-prices product-item-detail-price-current text-3xl
                                 font-medium dark:font-normal text-lightGrayBg dark:text-textDarkLightGray mr-5">
                                    {salePrice}₽ <span
                                    className="mx-3 line-through decoration-hover-red text-2xl text-tagFilterGray"> {price}₽</span>
                                </div> :
                                <div className="base-price group-prices product-item-detail-price-current text-3xl
                                 font-medium dark:font-normal text-lightGrayBg dark:text-textDarkLightGray mr-5">
                                    {price}₽
                                </div>}
                            <div
                                className="add-to-basket box-basket flex flex-row items-center
                                 bx_catalog_item_controls">
                                <div className="product-item-amount-field-contain-wrap mr-4">
                                    <div
                                        className="product-item-amount-field-contain flex flex-row h-full">
                                        <a className="btn-minus minus_icon no-select add2basket
                                       rounded-full md:py-0 md:px-0 py-3.5 px-1.5 dark:bg-dark md:dark:bg-darkBox
                                       bg-none no-select add2basket cursor-pointer flex items-center justify-center
                                       h-auto md:w-full w-auto removeToBasketOpenWindow"
                                           data-url={srcProduct}
                                           data-product_id={productId}
                                           data-max-quantity={maxQuantity}
                                           id={areaBuy}>
                                            <svg width="20" height="2" viewBox="0 0 22 2" fill="none"
                                                 className="stroke-dark dark:stroke-white stroke-[1.5px]"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1 1H21" strokeLinecap="round" strokeLinejoin="round"></path>
                                            </svg>
                                        </a>
                                        <div className="product-item-amount-field-block">
                                            <input className="product-item-amount card_element inputBasketOpenWindow
                                             dark:bg-tagFilterGray bg-textDarkLightGray focus:border-none text-center
                                              border-none text-md shadow-none py-2.5 px-3 md:mx-2 mx-1 outline-none
                                              rounded-md md:w-14 w-16"
                                                   type="number"
                                                   max={maxQuantity}
                                                   data-max-quantity={maxQuantity}
                                                   id={areaBuyQuantity}
                                                   onChange={(e) => {
                                                       setQuantityProduct(e.target.value)
                                                   }}
                                                   data-url={srcProduct}
                                                   data-product_id={productId}
                                                   value={quantityProduct}/>
                                        </div>
                                        <a className="btn-plus plus_icon no-select add2basket addToBasketOpenWindow
                                       no-select add2basket cursor-pointer flex items-center justify-center rounded-full
                                       md:p-0 p-1.5 dark:bg-dark md:dark:bg-darkBox bg-none h-auto md:w-full w-auto"
                                           data-url={srcProduct}
                                           data-product_id={productId}
                                           data-max-quantity={maxQuantity}
                                           title={'Доступно: ' + maxQuantity}
                                           id={areaBuy}>
                                            <svg width="20" height="20" viewBox="0 0 20 20"
                                                 className="fill-light-red dark:fill-white"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M18.8889 11.111H1.11111C0.503704 11.111 0 10.6073 0 9.9999C0 9.3925 0.503704 8.88879 1.11111 8.88879H18.8889C19.4963 8.88879 20 9.3925 20 9.9999C20 10.6073 19.4963 11.111 18.8889 11.111Z"></path>
                                                <path
                                                    d="M10 20C9.39262 20 8.88892 19.4963 8.88892 18.8889V1.11111C8.88892 0.503704 9.39262 0 10 0C10.6074 0 11.1111 0.503704 11.1111 1.11111V18.8889C11.1111 19.4963 10.6074 20 10 20Z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                    <div className="alert_quantity absolute md:p-4 p-2 text-xs left-0 top-12 bg-filterGray
                                dark:bg-tagFilterGray w-full shadow-lg rounded-md z-20 hidden"
                                         data-id={productId}></div>
                                </div>
                            </div>
                        </div>
                        <div className="flex flex-col">
                            {
                                groupedProps !== null ?
                                    groupedProps.map((props, pr_key) => {
                                        // console.log(groupedSettings)
                                        // console.log(groupedProducts)
                                        // console.log(groupedProps)

                                        if (groupedSettings[props[0]]) {
                                            const typeProduct = groupedSettings[props[0]].TYPE
                                            const code = groupedSettings[props[0]].CODE
                                            let prefix = groupedSettings[props[0]].PREF
                                            const dataProps = props[1];

                                            return (
                                                groupedProducts.map((product, p_key) => {
                                                    let classType = 'lg:mb-2 md:m-2 m-1 offer-box cursor-pointer'
                                                    const valuePropsProduct = product[1].PROPERTIES[code].JS_PROP
                                                    let itemChild = product[1].NAME + prefix
                                                    const keys = Object.keys(valuePropsProduct);

                                                    if (typeProduct === 'color') {
                                                        const srcPicture = valuePropsProduct[keys[0]].PREVIEW_PICTURE
                                                        itemChild = '<img src=' + srcPicture + ' alt="" />';
                                                        classType = 'border border-gray rounded-md p-3 bg-white'
                                                    } else if (typeProduct === 'colorWithText') {
                                                        classType = 'red_button_cart taste variation_taste text-sm ' +
                                                            'w-fit lg:mb-2 md:m-2 p-10 m-1 offer-box cursor-pointer'
                                                    }
                                                    // const bool = arrayDiff(valuePropsProduct,dataProps)
                                                    // console.log(product)
                                                    return (
                                                        <div key={p_key}
                                                             data-prop_code={code}
                                                             data-prop_group={valuePropsProduct}
                                                             className={"flex offer-box flex-row overflow-auto max-w-full text-xs " + classType}>
                                                            {itemChild}
                                                        </div>
                                                    )
                                                })
                                            )
                                        }
                                    })
                                    : <></>
                            }
                        </div>
                        <p className="text-xs font-medium text-textLight dark:font-light dark:text-whiteOpacity mt-4 mb-4 w-full">
                            {description}
                        </p>
                    </div>
                    <div className="props-box ml-lg-4 ml-md-4 ml-0 flex flex-col justify-between items-end">
                        <a className="text-light-red text-lg dark:text-textDarkLightGray font-medium
                            dark:font-light underline underline-offset-2"
                           href={productPage}>Подробнее</a>
                    </div>
                </div>
            </div>
        </div>
    </div>);
}

export default CatalogProductPopup;