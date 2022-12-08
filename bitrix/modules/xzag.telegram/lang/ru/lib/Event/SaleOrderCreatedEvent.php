<?php

$MESS['XZAG_TELEGRAM_ANONYMOUS_USER'] = 'Анонимный пользователь';
// phpcs:disable
$MESS['XZAG_TELEGRAM_NOTIFICATION_SALE_ORDER_CREATED_EVENT'] = <<<'TEXT'
Новый заказ #{{ ORDER.ID }} на сумму {{ ORDER.PRICE | number_format(2, '.', '') }} {{ ORDER.CURRENCY }}
{{ LINK }}
Пользователь: {{ ORDER.PROPERTY_VALUES.FIO.VALUE ?? ORDER.PROPERTY_VALUES.COMPANY.VALUE }} {{ ORDER.PROPERTY_VALUES.EMAIL.VALUE }} {{ ORDER.PROPERTY_VALUES.PHONE.VALUE }}

{% if ITEMS %}
{% for ITEM in ITEMS %}
{{ ITEM.NAME }}, {{ ITEM.QUANTITY | number_format }}{{ ITEM.MEASURE_NAME }} - {{ ITEM.PRICE | number_format(2, '.', '') }} {{ ITEM.CURRENCY }}{% if ITEM.QUANTITY > 1 %} * {{ ITEM.QUANTITY | number_format }}{% endif %}

{% if ITEM.PROPERTY_VALUES %}
({% for PROP in ITEM.PROPERTY_VALUES %}
{% if PROP.VALUE %}{{ PROP.NAME | trim }}: {{PROP.VALUE}}{% if not loop.last %} | {% endif %}{% endif %}
{% endfor %})

{% endif %}
{% endfor %}
{% endif %} 
{% if SHIPMENTS %}
{% for SHIPMENT in SHIPMENTS %}
Доставка: {{ SHIPMENT.DELIVERY_NAME }}{% if SHIPMENT.store %}, {{ SHIPMENT.store.TITLE }}, {{ SHIPMENT.store.ADDRESS }}{% endif %} - {{ SHIPMENT.PRICE_DELIVERY | number_format(2, '.', '') }} {{ SHIPMENT.CURRENCY }}
{% endfor %}
{% endif %}

{% if PAYMENT_METHODS %}
Оплата: {% for METHOD in PAYMENT_METHODS %}{{ METHOD.NAME }}{% if not loop.last %} | {% endif %}{% endfor %}
{% endif %}
TEXT;
// phpcs:enable
