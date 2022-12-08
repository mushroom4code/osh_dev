<?php

// phpcs:disable
$MESS['XZAG_TELEGRAM_NOTIFICATION_FORM_RESULT_CREATED_EVENT'] = <<<'FORM_RESULT'
Новый ответ в форме {{ FORM.NAME }}:

{% if RESULT %}
{% for KEY, ANSWERS in RESULT %}
{% for ANSWER in ANSWERS %}
{% if loop.first %}{{ ANSWER.TITLE }}: {% endif %}{{ (ANSWER.ANSWER_TEXT ?: ANSWER.ANSWER_VALUE) ?: ANSWER.USER_TEXT }}{% if not loop.last %} | {% endif %}
{% endfor %}

{% endfor %}
{% endif %}
FORM_RESULT;
// phpcs:enable
