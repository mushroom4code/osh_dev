<?php
    if(!check_bitrix_sessid()) return;
    CAdminMessage::ShowNote("Модуль успешно отключен с удалением всех данных.");
?>