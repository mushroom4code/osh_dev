import React, {useState} from 'react';
import axios from "axios";

const uric = 'Юридическое лицо';
const ip = 'Индивидуальный предприниматель';
const fiz = 'Физическое лицо';

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

function ContragentForm({listLength, initToClick}) {
    const [inn, setInn] = useState('')
    const [name, setName] = useState('')
    const [phone, setPhone] = useState('')
    const [result, setResult] = useState('')
    const [email, setEmail] = useState('')
    const [type, setType] = useState(uric)
    let className = '';

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
                setResult('Вы некорректно заполнили ИНН');
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
                } else if (res.data?.error) {
                    setResult(res.data?.error)
                } else {
                    setResult('При создании контрагента возникла ошибка! ' +
                        'Можете обратиться к менеджеру или повторить попытку');
                }
            }
        )
    }

    if (initToClick) {
        className = 'fixed top-0 h-screen flex justify-center items-center left-0 dark:bg-darkOpacityWindow' +
            ' bg-lightOpacityWindow w-screen z-50'
    }
    return (
        listLength === 0 || initToClick ?
            <div className={className}>
                <form onSubmit={handleClick}
                      className="dark:bg-darkBox bg-white w-9/12 dark:border-0 border-textDark border-2 rounded-xl p-8 mb-10">
                    <div className="mb-8">
                        <p className="text-xl font-medium dark:text-textDarkLightGray text-textLight">
                            Создайте своего первого контрагента
                        </p>
                    </div>
                    <div className="mb-8">
                        <div className="col-12 col-md-10 flex flex-row align-items-center mb-8">
                            <div className="mr-7">
                                <input className="text-white w-5 h-5 bg-darkBox border-gray-slider-arrow
                                dark:checked:ring-white dark:checked:border-white dark:focus:ring-0 border-2
                                 checked:hover:bg-none dark:ring-offset-gray-800 dark:bg-darkBox
                                 dark:border-gray-slider-arrow" onChange={(e) => {
                                    setType(e.target.value)
                                }}
                                       checked={type === uric}
                                       type="radio" name="check"
                                       value='uric'/>
                                <label className="text-sm dark:font-light font-normal text-textLight ml-3
                                    dark:text-textDarkLightGray">{uric}</label>
                            </div>
                            <div className="mr-7">
                                <input className="text-white w-5 h-5 bg-darkBox border-gray-slider-arrow
                                dark:checked:ring-white dark:checked:border-white dark:focus:ring-0 border-2
                                 checked:hover:bg-none dark:ring-offset-gray-800 dark:bg-darkBox
                                 dark:border-gray-slider-arrow"
                                       onChange={(e) => {
                                           setType(e.target.value)
                                       }}
                                       checked={type === ip}
                                       type="radio" name="check"
                                       value='ip'/>
                                <label
                                    className="text-sm dark:font-light font-normal text-textLight ml-3
                                     dark:text-textDarkLightGray">
                                    {ip}</label>
                            </div>
                            <div className="mr-7">
                                <input type="radio" name="check"
                                       className="text-white w-5 h-5 bg-darkBox border-gray-slider-arrow
                                        dark:checked:ring-white dark:checked:border-white dark:focus:ring-0 border-2
                                         checked:hover:bg-none dark:ring-offset-gray-800 dark:bg-darkBox
                                         dark:border-gray-slider-arrow"
                                       onChange={(e) => {
                                           setType(e.target.value)
                                       }}
                                       checked={type === fiz}
                                       value='fiz'/>
                                <label
                                    className="text-sm dark:font-light font-normal text-textLight ml-3
                                     dark:text-textDarkLightGray">
                                    {fiz}</label>
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
                                           className="dark:bg-grayButton bg-textDark lg:w-4/5 w-full border-none p-3
                                    outline-none rounded-md"
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
                                           className="dark:bg-grayButton bg-textDark lg:w-4/5 w-full p-3 border-none
                                   outline-none rounded-md"
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
                                           className="dark:bg-grayButton bg-textDark lg:w-4/5 w-full border-none p-3
                                    outline-none rounded-md"
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
                                           className="dark:bg-grayButton bg-textDark lg:w-4/5 w-full p-3 border-none
                                   outline-none rounded-md"
                                           placeholder="Email"/>
                                </div>
                            </>
                        }

                        <div className="mb-3">
                            <input type="text"
                                   required
                                   minLength={8}
                                   className="dark:bg-grayButton border-none outline-none p-3 lg:w-4/5
                                    w-full rounded-md bg-textDark"
                                   value={phone}
                                   onChange={(e) => {
                                       setPhone(e.target.value)
                                   }}
                                   placeholder="Телефон компании"/>
                        </div>
                    </div>
                    <div className="form-group mt-4">
                        <div className="d-flex flex-row align-items-center justify-content-start">
                            <button className="dark:bg-dark-red rounded-md bg-light-red text-white px-7 py-3 w-fit
                             dark:shadow-md shadow-shadowDark dark:hover:bg-hoverRedDark cursor-pointer" type="submit">
                                Создать контрагента
                            </button>
                        </div>
                    </div>
                </form>
                <div className="mt-5">{result}</div>
            </div>
            : false
    );
}

export default ContragentForm
