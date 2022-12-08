<?
namespace Ipol\Fivepost;

use \Ipol\Fivepost\Bitrix\Tools;

IncludeModuleLangFile(__FILE__);

/**
 * Class ProfileHandler
 * @package Ipol\Fivepost
 */
class ProfileHandler extends AbstractGeneral
{
    /**
     * Get delivery profiles common data
     *
     * @return array
     */
    public static function getProfiles()
    {
        $arProfiles = array(
            'pickup' => array(
                'DeliveryMethod' => Tools::getMessage('DELIVERY_METHOD_PICKUP'),
                'ClassName'      => '\Ipol\Fivepost\Bitrix\Handler\DeliveryHandlerPickup',
                'NAME'           => Tools::getMessage('DELIVERY_PROFILE_PICKUP_NAME'),
                'DESCRIPTION'    => Tools::getMessage('DELIVERY_PROFILE_PICKUP_DESCRIPTION')
            ),
        );

        return $arProfiles;
    }

    /**
     * Get name for specified profile
     *
     * @param $profile
     * @return false|mixed
     */
    public static function getProfileName($profile)
    {
        return self::getProfileParamByKey($profile, 'NAME');
    }

    /**
     * Get description for specified profile
     *
     * @param $profile
     * @return false|mixed
     */
    public static function getProfileDescription($profile)
    {
        return self::getProfileParamByKey($profile, 'DESCRIPTION');
    }

    /**
     * Get profile param specified by key
     *
     * @param string $profile
     * @param string $key
     * @return false|mixed
     */
    protected static function getProfileParamByKey($profile, $key)
    {
        $profiles = self::getProfiles();

        if (array_key_exists($profile, $profiles) && array_key_exists($key, $profiles[$profile]))
            return $profiles[$profile][$key];
        return false;
    }

    /**
     * Get class names for module delivery handler profiles
     *
     * @return array
     */
    public static function getProfileClasses()
    {
        return self::fetchByKey('ClassName');
    }

    /**
     * Get profiles list for module delivery handler
     *
     * @return array
     */
    public static function getProfilesList()
    {
        return self::fetchByKey('NAME');
    }

    /**
     * Get specified data for module delivery handler profiles
     *
     * @param string $key
     * @return array
     */
    protected static function fetchByKey($key)
    {
        $result = array();
        foreach (self::getProfiles() as $code => $data)
        {
            if (array_key_exists($key, $data))
                $result[$code] = $data[$key];
        }
        return $result;
    }

    /**
     * Make default profiles params for module delivery handler
     *
     * @param array $common
     * @return array
     */
    public static function makeDefaultParams($common = array())
    {
        $result = array();
        foreach (self::getProfiles() as $code => $data)
        {
            $data = array(
                "CODE"        => $code,
                "NAME"        => $data['NAME'],
                "DESCRIPTION" => $data['DESCRIPTION'],
                "CLASS_NAME"  => $data['ClassName'],
            );

            if ($logoId = self::makeLogotip($code))
                $data["LOGOTIP"] = $logoId;

            $result[] = array_merge($data, $common);
        }
        return $result;
    }

    /**
     * Get delivery profiles data for bitrix delivery service
     *
     * @return array
     */
    public static function profilesToBitrix()
    {
        /*$_arProfiles = self::getProfiles();

        $arProfiles = array();

        foreach ($_arProfiles as $profileId => $arProfile){
            $arProfiles[$profileId] = array(
                "TITLE"       => $arProfile['TITLE'],
                "DESCRIPTION" => $arProfile['DESCRIPTION'],

                "RESTRICTIONS_WEIGHT" => array(0),
                "RESTRICTIONS_SUM"    => array(0)
            );
        }

        return $arProfiles;
		*/
    }

    /**
     * Get delivery profiles data for calculator class
     *
     * @return array of profileCode => profileLabel
     */
    public static function profilesToCalculator()
    {
        $_arProfiles = self::getProfiles();
        $arProfiles  = array();

        foreach ($_arProfiles as $profileId => $arProfile)
        {
            $arProfiles [$profileId] = $arProfile['DeliveryMethod'];
        }

        return $arProfiles;
    }

    /**
     * Make logo file for given profile code and return it's Bitrix id if success
     *
     * @param string $profile
     * @return int|false
     */
    public static function makeLogotip($profile)
    {
        $path = implode(DIRECTORY_SEPARATOR, [$_SERVER['DOCUMENT_ROOT'], 'bitrix', 'images', IPOL_FIVEPOST, $profile.'.png']);
        if (file_exists($path))
        {
            $content = file_get_contents($path);
            if ($content !== false && $content <> '')
            {
                $fileName = \Bitrix\Main\Security\Random::getString(32);
                $fileName = \CTempFile::GetFileName($fileName);
                if (\CheckDirPath($fileName))
                {
                    if (file_put_contents($fileName, $content) !== false)
                    {
                        $file = \CFile::MakeFileArray($fileName);
                        $file['MODULE_ID'] = IPOL_FIVEPOST;
                        return \CFile::SaveFile($file, implode(DIRECTORY_SEPARATOR, ['sale', 'delivery', 'logotip']));
                    }
                }
            }
        }

        return false;
    }
}