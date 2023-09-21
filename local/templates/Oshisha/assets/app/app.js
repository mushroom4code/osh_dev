import {createRoot} from 'react-dom/client';
import ContragentForm from './components/ContragentForm';
import {StrictMode} from "react";

const contragentForm = document.getElementById('createContragent');
if (contragentForm) {
    createRoot(contragentForm).render(<StrictMode><ContragentForm/></StrictMode>);
}