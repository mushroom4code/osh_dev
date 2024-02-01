import React, {useEffect, useState} from 'react';
import axios from "axios";
import RelationshipContragent from "./RelationshipContragent";

const uric = 'uric';
const ip = 'ip';
const fiz = 'fiz';

function is_valid_inn(i) {
    if (i.match(/\D/)) return false;

    const inn = i.match(/(\d)/g);

    if (inn.length === 10) {
        return inn[9] === String(((
            2 * inn[0] + 4 * inn[1] + 10 * inn[2] +
            3 * inn[3] + 5 * inn[4] + 9 * inn[5] +
            4 * inn[6] + 6 * inn[7] + 8 * inn[8]
        ) % 11) % 10);
    } else if (inn.length === 12) {
        return inn[10] === String(((
            7 * inn[0] + 2 * inn[1] + 4 * inn[2] +
            10 * inn[3] + 3 * inn[4] + 5 * inn[5] +
            9 * inn[6] + 4 * inn[7] + 6 * inn[8] +
            8 * inn[9]
        ) % 11) % 10) && inn[11] === String(((
            3 * inn[0] + 7 * inn[1] + 2 * inn[2] +
            4 * inn[3] + 10 * inn[4] + 3 * inn[5] +
            5 * inn[6] + 9 * inn[7] + 4 * inn[8] +
            6 * inn[9] + 8 * inn[10]
        ) % 11) % 10);
    }

    return false;
}

let className = '', classNameWindow = 'w-9/12', classInput = 'lg:w-4/5 w-full';

function ContragentForm({
                            initToClick,
                            loads,
                            setState,
                            listContragent,
                            setResult,
                            setColor,
                            setShowForm,
                            showForm,
                            type,
                            setType
                        }) {
    const [inn, setInn] = useState('')
    const [name, setName] = useState('')
    const [phone, setPhone] = useState('')
    const [result, setResultNew] = useState('')
    const [colorRes, setColorRes] = useState('dark:text-hover-red text-hover-red')
    const [email, setEmail] = useState('')
    const [contrResult, setContrResult] = useState([])

    const handleClick = (e) => {
        e.preventDefault()
        const number = String(document.querySelector('#phoneCodeContragent').value);
        const prefix = String(document.querySelector('[name="__phone_prefix"]').value);
        const data = {
            NAME: name,
            PHONE_COMPANY: prefix + number,
            TYPE: type,
            ACTION: 'create'
        }

        if (type === uric || type === ip) {
            const innValid = is_valid_inn(inn);
            if (innValid) {
                data.INN = inn
                sendContragent(data)
            } else {
                setResultNew('Вы некорректно заполнили ИНН');
            }
        } else {
            data.EMAIL = String(document.querySelector('#emailContragent').value);
            sendContragent(data)
        }

    }

    const emptyDataInputs = () => {
        setPhone('')
        setName('')
        setInn('')
    }

    function sendContragent(data) {
        axios.post('/local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
            data).then(res => {
                if (res.data?.success) {
                    setResult(res.data?.success)
                    setColor('dark:text-textDarkLightGray text-greenButton')
                    setColorRes('dark:text-textDarkLightGray text-greenButton')
                    emptyDataInputs()
                    setState(false)
                } else if (res.data?.error) {
                    if (res.data?.error?.code) {
                        setResultNew(res.data?.error?.code)
                        setContrResult(res.data?.error?.item)
                        setState(true)
                        setShowForm(false)
                    }
                } else {
                    setResultNew('При создании контрагента возникла ошибка! ' +
                        'Можете обратиться к менеджеру нашей компании или повторить попытку');
                }
            }
        )
    }

    if (initToClick) {
        className = 'fixed top-0 h-screen flex justify-center flex-col items-center left-0 dark:bg-darkOpacityWindow' +
            ' bg-lightOpacityWindow w-screen z-50'
        classNameWindow = 'lg:w-2/4 xs:w-full h-screen md:h-auto'
        classInput = 'w-full'
    }

    return (
        (listContragent === 0 && loads) || initToClick ?
            <div className={className}>
                {contrResult?.NAME_ORGANIZATION !== undefined ?
                    <RelationshipContragent contragent={contrResult}
                                            setState={setState}
                                            setResults={setResult}
                                            setContrResult={setContrResult}
                                            setResultNew={setResultNew}
                                            setColorResNew={setColor}
                                            emptyDataInputs={emptyDataInputs}/>
                    : false
                }
                {showForm ?
                    <form onSubmit={handleClick}
                          className={'dark:bg-darkBox bg-white dark:border-0 border-textDark border-2 md:rounded-xl' +
                              ' md:px-8 md:py-8 py-11 px-6 md:mb-10 mb-0 rounded-0'
                              + classNameWindow}>
                        <div className="md:mb-8 mb-10">
                            <p className="text-xl font-medium dark:text-textDarkLightGray text-textLight">
                                Создайте контрагента для возможности покупки товаров на сайте
                            </p>
                        </div>
                        <div className="md:mb-8 mb-5">
                            <div className="col-12 col-md-10 flex flex-col md:flex-row align-items-center mb-8">
                                <div className="md:mr-7 mr-0 md:mb-0 mb-3">
                                    <input className="dark:text-white text-light-red w-5 h-5 bg-grayIconLights border-grayIconLights
                                dark:checked:ring-white dark:checked:border-white dark:focus:ring-0 border-2
                                 dark:checked:focus:border-white dark:focus:checked:ring-white
                                 focus:checked:border-light-red focus:checked:ring-light-red
                                  dark:ring-offset-gray-800 dark:bg-darkBox ring-light-red checked:border-light-red
                                 dark:border-gray-slider-arrow" onChange={(e) => {
                                        setType(uric)
                                        Inputmask.remove('#emailContragent');
                                    }}
                                           checked={type === uric}
                                           type="radio" name="check"
                                           value={uric}/>
                                    <label className="text-sm dark:font-light font-normal text-textLight ml-3
                                    dark:text-textDarkLightGray">Юридическое лицо</label>
                                </div>
                                <div className="md:mr-7 mr-0 md:mb-0 mb-3">
                                    <input className="dark:text-white text-light-red w-5 h-5 bg-grayIconLights border-grayIconLights
                                dark:checked:ring-white dark:checked:border-white dark:focus:ring-0 border-2
                                 dark:checked:focus:border-white dark:focus:checked:ring-white
                                 focus:checked:border-light-red focus:checked:ring-light-red
                                  dark:ring-offset-gray-800 dark:bg-darkBox ring-light-red checked:border-light-red
                                 dark:border-gray-slider-arrow"
                                           onChange={(e) => {
                                               setType(ip)
                                               Inputmask.remove('#emailContragent');
                                           }}
                                           checked={type === ip}
                                           type="radio" name="check"
                                           value={ip}/>
                                    <label className="text-sm dark:font-light font-normal text-textLight ml-3
                                     dark:text-textDarkLightGray">Индивидуальный предприниматель</label>
                                </div>
                            </div>
                            <div className={"mb-3 " + classInput}>
                                <input type="text"
                                       value={name}
                                       required
                                       onChange={(e) => {
                                           setName(e.target.value)
                                       }}
                                       minLength={3}
                                       className={'dark:bg-grayButton bg-textDark border-none py-3 px-4' +
                                           'outline-none rounded-md w-full'}
                                       placeholder="Полное наименование организации"/>
                            </div>
                            <div className={"mb-3 " + classInput}>
                                <input type="text"
                                       required
                                       id="inn"
                                       value={inn}
                                       onChange={(e) => {
                                           setInn(e.target.value)
                                       }}
                                       minLength={8}
                                       className={'dark:bg-grayButton bg-textDark border-none py-3 px-4 ' +
                                           'outline-none rounded-md w-full'}
                                       placeholder="ИНН"/>
                            </div>
                            <div className={"mb-3 relative " + classInput}>
                                <span className="" id="flag"></span>
                                <input type="text"
                                       required
                                       onChange={(e) => {
                                           setPhone(e.target.value)
                                       }}
                                       id="phoneCodeContragent"
                                       inputMode="text"
                                       data-input-type="phone"
                                       minLength={8}
                                       className={'dark:bg-grayButton bg-textDark border-none py-3 px-4 ' +
                                           'outline-none rounded-md w-full'}
                                       value={phone}
                                       placeholder="Телефон компании"/>
                            </div>
                        </div>
                        <div className="flex flex-row md:mt-4 mt-8">
                            <div
                                className="d-flex flex-row align-items-center justify-content-start md:w-fit w-1/2 mr-3">
                                <button className="dark:bg-dark-red rounded-md bg-light-red text-white px-7 py-3
                             dark:shadow-md shadow-shadowDark w-full dark:hover:bg-hoverRedDark cursor-pointer"
                                        type="submit">
                                    Подтвердить
                                </button>
                            </div>
                            {
                                initToClick ?
                                    <div
                                        className="d-flex flex-row align-items-center justify-content-start ml-3 md:w-fit w-1/2">
                                        <button className="dark:bg-grayButton rounded-md bg-grayButton text-white px-7
                                         py-3 dark:shadow-md w-full shadow-shadowDark
                                         dark:hover:bg-black cursor-pointer" onClick={
                                            (e) => {
                                                e.preventDefault()
                                                setState(false);
                                            }
                                        }>
                                            Отменить
                                        </button>
                                    </div>
                                    : false
                            }
                        </div>
                        <div className={"mt-5 " + colorRes}>{result}</div>
                    </form>
                    : false
                }
            </div>
            : false

    );
}

export default ContragentForm
