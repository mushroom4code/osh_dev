<?php

namespace Enterego;

use CUser;

class EnteregoUser
{
    private static ?EnteregoUser $instance = null;

    private $isAuthorized = false;
    private array $name = [];
    private $phone = '';
    private $mail = '';

    private function __construct()
    {
        global $USER;

        $this->isAuthorized = $USER->IsAuthorized();
        $this->name = [
            'last' => $USER->GetLastName() ?? '',
            'first' => $USER->GetFirstName() ?? '',
            'second' => $USER->GetParam('SECOND_NAME') ?? ''
        ];
        $this->mail = $USER->GetEmail();

        $rsUser = CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        $this->phone = $arUser['PERSONAL_PHONE'] ?? false;
    }

    public static function getInstance(): EnteregoUser
    {
        return self::$instance ?? new self();
    }

    public function isAuthorized()
    {
        return $this->isAuthorized;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getMail()
    {
        return $this->mail;
    }
}