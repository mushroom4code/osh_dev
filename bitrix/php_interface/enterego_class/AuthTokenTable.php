<?php

namespace Enterego;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\SystemException;
use CUser;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
 * CREATE TABLE
 *
 *
 create table ent_auth_token (
    TOKEN      varchar(255) not null
        primary key,
    USER_ID    int          not null,
    EXPIRATION int          not null,
    constraint ent_auth_token_b_user_null_fk
        foreign key (USER_ID) references b_user (ID)
 );
 */

class AuthTokenTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return 'ent_auth_token';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            'TOKEN' => new StringField(
                'TOKEN',
                [
                    'primary' => true,
                    'required' => true,
                ]
            ),
            'USER_ID' => new IntegerField(
                'USER_ID',
                [
                    'required' => true,
                ]
            ),
            'EXPIRATION' => new IntegerField(
                'EXPIRATION',
                [
                    'required' => true
                ]
            )
        ];

    }

    public static function generateToken(): array
    {
        global $USER;

        $expiraton = time() + AUTH_TOKEN_PROLONGATION;
        $data = [
            'userId' => $USER->GetID(),
            'login' => $USER->GetLogin(),
            'server' => $_SERVER['SERVER_NAME'],
            'expire' => $expiraton,
        ];
        $token = JWT::encode($data, AUTH_TOKEN_GENERATION_SECRET, 'HS512');

        return ['token' => $token, 'expiration' => $expiraton];
    }

    /**
     * @param array{token: string, expiration: int} $tokenData
     * @return void
     * @throws Exception
     */
    public static function saveToken(array $tokenData): void
    {
        global $USER;

        setcookie('authToken', $tokenData['token'], $tokenData['expiration'], '/');
        self::add([
            'TOKEN' => $tokenData['token'],
            'USER_ID' => $USER->GetID(),
            'EXPIRATION' => $tokenData['expiration']
        ]);
    }

    /**
     * @param array{token: string, expiration: int} $tokenData
     * @return void
     * @throws Exception
     */
    public static function updateToken(array $tokenData): void
    {
        setcookie('authToken', $tokenData['token'], $tokenData['expiration'], '/');

        AuthTokenTable::update(
            $tokenData['token'],
            ['EXPIRATION' => $tokenData['expiration']]
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function getNewToken(): void
    {
        if ($_REQUEST['SAVE_SESSION'] == 'Y' && !isset($_COOKIE['authToken'])) {
            $tokenData = self::generateToken();
            self::saveToken($tokenData);
        }
    }


    public static function removeToken(): void
    {
        if (isset($_COOKIE['authToken'])) {
            self::delete($_COOKIE['authToken']);
            setcookie('authToken', '', time(), '/');
        }
    }

    public static function getTokenAuth(): void
    {
        global $USER;

        if (!is_object($USER)) {
            $USER = new CUser;
        }

        if (!$USER->IsAuthorized() && isset($_COOKIE['authToken'])) {
            $userToken = $_COOKIE['authToken'];
            $decodedToken = JWT::decode($userToken, new Key(AUTH_TOKEN_GENERATION_SECRET, 'HS512'));

            $serverToken = AuthTokenTable::getByPrimary($userToken)->fetch();
            $User = CUser::GetByID($decodedToken->userId)->fetch();

            if ($decodedToken->server === $_SERVER['SERVER_NAME'] &&
                $decodedToken->expire > time() &&
                $User && $User['ACTIVE'] == 'Y' &&
                $decodedToken->login === $User['LOGIN']) {

                $tokenData = self::generateToken();
                self::updateToken($tokenData);

                (new CUser)->Authorize($serverToken['USER_ID']);
            }
        }
    }
}


