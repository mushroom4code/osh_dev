<?php

$MESS['XZAG_TELEGRAM_NOTIFICATION_SALE_ORDER_PAYED_EVENT'] = <<<'TEXT'
Order #{{ ORDER.ID }} for total {{ ORDER.PRICE }} {{ ORDER.CURRENCY }} was paid.
{{ LINK }}
Payment: {{ PAYMENTS[0].PAY_SYSTEM_NAME }} {{ PAYMENTS[0].SUM }} {{ PAYMENTS[0].CURRENCY }}
TEXT;
