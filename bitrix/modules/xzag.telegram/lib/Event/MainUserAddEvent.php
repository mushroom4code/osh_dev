<?php

namespace Xzag\Telegram\Event;

/**
 * Class MainUserAddEvent
 * @package Xzag\Telegram\Event
 */
class MainUserAddEvent extends MainUserRegisteredEvent
{
    const TYPE = 'OnAfterUserAdd';
}
