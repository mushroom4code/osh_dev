import React, {useState} from 'react';
import axios from "axios";

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

function ContragentForm({initToClick, loads, setState, listContragent, setResult, setColor}) {
    const [inn, setInn] = useState('')
    const [name, setName] = useState('')
    const [phone, setPhone] = useState('')
    const [result, setResultNew] = useState('')
    const [colorRes, setColorRes] = useState('dark:text-hover-red text-hover-red')
    const [email, setEmail] = useState('')
    const [type, setType] = useState(uric)

    const handleClick = (e) => {
        e.preventDefault()
        const data = {
            NAME: name,
            PHONE_COMPANY: phone,
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
            data.NAME = name
            sendContragent(data)
        }
    }

    function sendContragent(data) {
        axios.post('/local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
            data).then(res => {
                if (res.data?.success) {
                    setResult(res.data?.success)
                    setColor('dark:text-textDarkLightGray text-greenButton')
                    setColorRes('dark:text-textDarkLightGray text-greenButton')
                    setPhone('')
                    setName('')
                    setInn('')
                    setEmail('')
                    setState(false)
                } else if (res.data?.error) {
                    setResultNew(res.data?.error)
                } else {
                    setResultNew('При создании контрагента возникла ошибка! ' +
                        'Можете обратиться к менеджеру или повторить попытку');
                }
            }
        )
    }

    if (initToClick) {
        className = 'fixed top-0 h-screen flex justify-center flex-col items-center left-0 dark:bg-darkOpacityWindow' +
            ' bg-lightOpacityWindow w-screen z-50'
        classNameWindow = 'lg:w-2/4 xs:w-full'
        classInput = 'w-full'
    }
    return (
        (listContragent === 0 && loads) || initToClick ?
            <div className={className}>
                <form onSubmit={handleClick}
                      className={'dark:bg-darkBox bg-white dark:border-0 border-textDark border-2 rounded-xl p-8 mb-10'
                          + classNameWindow}>
                    <div className="mb-8">
                        <p className="text-xl font-medium dark:text-textDarkLightGray text-textLight">
                            Создайте своего первого контрагента
                        </p>
                    </div>
                    <div className="mb-8">
                        <div className="col-12 col-md-10 flex flex-row align-items-center mb-8">
                            <div className="mr-7">
                                <input className="dark:text-white text-light-red w-5 h-5 bg-grayIconLights border-grayIconLights
                                dark:checked:ring-white dark:checked:border-white dark:focus:ring-0 border-2
                                 dark:checked:focus:border-white dark:focus:checked:ring-white
                                 focus:checked:border-light-red focus:checked:ring-light-red
                                  dark:ring-offset-gray-800 dark:bg-darkBox ring-light-red checked:border-light-red
                                 dark:border-gray-slider-arrow" onChange={(e) => {
                                    setType(uric)
                                }}
                                       checked={type === uric}
                                       type="radio" name="check"
                                       value={uric}/>
                                <label className="text-sm dark:font-light font-normal text-textLight ml-3
                                    dark:text-textDarkLightGray">Юридическое лицо</label>
                            </div>
                            <div className="mr-7">
                                <input className="dark:text-white text-light-red w-5 h-5 bg-grayIconLights border-grayIconLights
                                dark:checked:ring-white dark:checked:border-white dark:focus:ring-0 border-2
                                 dark:checked:focus:border-white dark:focus:checked:ring-white
                                 focus:checked:border-light-red focus:checked:ring-light-red
                                  dark:ring-offset-gray-800 dark:bg-darkBox ring-light-red checked:border-light-red
                                 dark:border-gray-slider-arrow"
                                       onChange={(e) => {
                                           setType(ip)
                                       }}
                                       checked={type === ip}
                                       type="radio" name="check"
                                       value={ip}/>
                                <label className="text-sm dark:font-light font-normal text-textLight ml-3
                                     dark:text-textDarkLightGray">Индивидуальный предприниматель</label>
                            </div>
                            <div className="mr-7">
                                <input type="radio" name="check"
                                       className="dark:text-white text-light-red w-5 h-5 bg-grayIconLights border-grayIconLights
                                dark:checked:ring-white dark:checked:border-white dark:focus:ring-0 border-2
                                 dark:checked:focus:border-white dark:focus:checked:ring-white
                                 focus:checked:border-light-red focus:checked:ring-light-red
                                  dark:ring-offset-gray-800 dark:bg-darkBox ring-light-red checked:border-light-red
                                 dark:border-gray-slider-arrow"
                                       onChange={(e) => {
                                           setType(fiz)
                                       }}
                                       checked={type === fiz}
                                       value={fiz}/>
                                <label className="text-sm dark:font-light font-normal text-textLight ml-3
                                     dark:text-textDarkLightGray">Физическое лицо</label>
                            </div>
                        </div>
                        {type === ip || type === uric ?
                            <>
                                <div className="mb-3">
                                    <input type="text"
                                           value={name}
                                           required
                                           onChange={(e) => {
                                               setName(e.target.value)
                                           }}
                                           minLength={3}
                                           className={'dark:bg-grayButton bg-textDark border-none py-3 px-4' +
                                               'outline-none rounded-md ' + classInput}
                                           placeholder="Полное наименование организации"/>
                                </div>
                                <div className="mb-3">
                                    <input type="text"
                                           required
                                           value={inn}
                                           onChange={(e) => {
                                               setInn(e.target.value)
                                           }}
                                           minLength={8}
                                           className={'dark:bg-grayButton bg-textDark border-none py-3 px-4 ' +
                                               'outline-none rounded-md ' + classInput}
                                           placeholder="ИНН"/>
                                </div>
                            </>
                            :
                            <>
                                <div className="mb-3">
                                    <input type="text"
                                           value={name}
                                           required
                                           onChange={(e) => {
                                               setName(e.target.value)
                                           }}
                                           minLength={3}
                                           className={'dark:bg-grayButton bg-textDark border-none py-3 px-4 ' +
                                               'outline-none rounded-md ' + classInput}
                                           placeholder="Фамилия Имя Отчество"/>
                                </div>
                                <div className="mb-3">
                                    <input type="text"
                                           required
                                           value={email}
                                           onChange={(e) => {
                                               setEmail(e.target.value)
                                           }}
                                           minLength={8}
                                           className={'dark:bg-grayButton bg-textDark border-none py-3 px-4 ' +
                                               'outline-none rounded-md ' + classInput}
                                           placeholder="Email"/>
                                </div>
                            </>
                        }
                        <div className="mb-3">
                            <input type="text"
                                   required
                                   minLength={8}
                                   className={'dark:bg-grayButton bg-textDark border-none py-3 px-4 ' +
                                       'outline-none rounded-md ' + classInput}
                                   value={phone}
                                   onChange={(e) => {
                                       setPhone(e.target.value)
                                   }}
                                   placeholder="Телефон компании"/>
                        </div>
                    </div>
                    <div className="flex flex-row mt-4">
                        <div className="d-flex flex-row align-items-center justify-content-start">
                            <button className="dark:bg-dark-red rounded-md bg-light-red text-white px-7 py-3 w-fit
                             dark:shadow-md shadow-shadowDark dark:hover:bg-hoverRedDark cursor-pointer" type="submit">
                                Создать контрагента
                            </button>
                        </div>
                        {
                            initToClick ?
                                <div className="d-flex flex-row align-items-center justify-content-start ml-3">
                                    <button className="dark:bg-grayButton rounded-md bg-grayButton text-white px-7 py-3 w-fit
                             dark:shadow-md shadow-shadowDark dark:hover:bg-black cursor-pointer" onClick={
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
            </div>
            : false
    );
}

export default ContragentForm
