<?php

$MESS['XZAG_TELEGRAM_NOTIFICATION_SAMPLE_EVENT'] = <<<'TEXT'
Это тестовое уведомление. Если вы его видите, значит модуль XZAG.TELEGRAM на сайте {{ SITE.NAME 
?? SITE.SITE_NAME }} ({{ SERVER.HTTP_HOST }}) настроен корректно.
При настройке были использованы следующие параметры:
ID чата: {{ CHAT_ID }}
{% if PROXY %}
Прокси-сервер: {{ PROXY }}
{% endif %}
TEXT;
