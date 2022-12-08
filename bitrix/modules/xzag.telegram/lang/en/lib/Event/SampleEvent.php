<?php

$MESS['XZAG_TELEGRAM_NOTIFICATION_SAMPLE_EVENT'] = <<<'TEXT'
This is a test notification. If you see it then you correctly configured XZAG.TELEGRAM on {{ SITE.NAME 
?? SITE.SITE_NAME }} ({{ SERVER.HTTP_HOST }}).
Module settings:
Chat ID: {{ CHAT_ID }}
{% if PROXY %}
Proxy: {{ PROXY }}
{% endif %}
TEXT;
