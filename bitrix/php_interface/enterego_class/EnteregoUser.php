<?php

namespace Enterego;

use CUser;

class EnteregoUser
{
    private static ?EnteregoUser $instance = null;

    private array $name = [];
    private $phone = '';
    private $mail = '';

    private function __construct()
    {
        global $USER;

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
        return strpos($this->mail, 'noemail.sms') === false ? $this->mail : '';
    }
}