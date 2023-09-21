import ContragentForm from './components/ContragentForm';
import ContragentList from "./components/ContragentList";
import {createRoot} from 'react-dom/client';
import {StrictMode} from "react";

const contragentForm = document.getElementById('createContragent');
if (contragentForm) {
    createRoot(contragentForm).render(<StrictMode><ContragentForm/>  <ContragentList/></StrictMode>);
}
