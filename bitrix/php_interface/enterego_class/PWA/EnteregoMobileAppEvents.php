<?php

namespace Enterego\PWA;

class EnteregoMobileAppEvents
{
    /**
     * @return bool
     */
    public static function getUserRulesForContent(): bool
    {
        $showContent = false;
        $cordovaMobile = getallheaders()['X-Mobile-App'] ?? '';

        global $USER;
        if (($cordovaMobile === 'Cordova' && $USER->IsAuthorized() && $USER->getLogin() !== 'appleTestUser') ||
            $USER->IsAuthorized() && $USER->getLogin() !== 'appleTestUser' || empty($cordovaMobile) ) {
            $showContent = true;
        }

        return $showContent;
    }
}
