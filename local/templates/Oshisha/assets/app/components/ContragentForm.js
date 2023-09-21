import {useState} from 'react';
import axios from "axios";

function ContragentForm() {
    const [inn, setInn] = useState('')
    const [name, setName] = useState('')
    const [phone, setPhone] = useState('')
    const [result, setResult] = useState('')
    const setInnVal = (e) => {
        setInn(e.target.value)
    }
    const setNameVal = (e) => {
        setName(e.target.value)
    }

    const setPhoneVal = (e) => {
        setPhone(e.target.value)
    }

    function sendValidation() {
        let data = {
            NAME: name,
            INN: inn,
            PHONE_COMPANY: phone,
            ACTION: 'create'
        }
        if (true) {
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

    return (
        <div>
            <form className="dark:bg-darkBox bg-white dark:border-0 border-textDark border-2 rounded-xl p-8 mb-5 w-4/5">
                <div className="mb-7">
                    <p className="text-xl font-medium dark:text-textDarkLightGray text-textLight">
                        Создайте своего первого контрагента
                    </p>
                </div>
                <div className="mb-8">
                    <div className="col-12 col-md-10 flex flex-row align-items-center mb-4">
                        <div className="mr-7">
                            <input
                                type="radio" name="check"
                                value="Юр.лицо"/>
                            <label
                                className="text-sm dark:font-light font-normal text-textLight dark:text-textDarkLightGray">
                                Юр.лицо</label>
                        </div>
                        <div className="mr-7">
                            <input className=""
                                   type="radio" name="check"
                                   value="ИП"/>
                            <label
                                className="text-sm dark:font-light font-normal text-textLight dark:text-textDarkLightGray">
                                ИП</label>
                        </div>
                        <div className="mr-7">
                            <input type="radio" name="check"
                                   value="Физ. лицо"/>
                            <label
                                className="text-sm dark:font-light font-normal text-textLight dark:text-textDarkLightGray">
                                Физ. лицо</label>
                        </div>
                    </div>
                    <div className="mb-3">
                        <input type="text"
                               value={name}
                               required
                               onChange={setNameVal}
                               minLength={3}
                               className="dark:bg-grayButton bg-textDark lg:w-4/5 w-full border-none p-3 outline-none rounded-md"
                               placeholder="Наименование организации"/>
                    </div>
                    <div className="mb-3">
                        <input type="text"
                               required
                               data-name="INN"
                               value={inn}
                               onChange={setInnVal}
                               minLength={8}
                               className="dark:bg-grayButton bg-textDark lg:w-4/5 w-full p-3 border-none outline-none rounded-md"
                               placeholder="ИНН"/>
                    </div>
                    <div className="mb-3">
                        <input type="text"
                               required
                               minLength={8}
                               className="dark:bg-grayButton border-none outline-none p-3 lg:w-4/5 w-full rounded-md bg-textDark"
                               value={phone}
                               onChange={setPhoneVal}
                               placeholder="Телефон для связи"/>
                    </div>
                </div>
                <div className="form-group mt-4">
                    <div className="d-flex flex-row align-items-center justify-content-start">
                        <div className="dark:bg-dark-red rounded-md bg-light-red text-white px-7 py-3 w-fit dark:shadow-md
                    shadow-shadowDark dark:hover:bg-hoverRedDark cursor-pointer" onClick={sendValidation}>
                            Создать контрагента
                        </div>
                    </div>
                </div>
            </form>
            <div className="mt-5">{result}</div>
        </div>
    );
}

export default ContragentForm
