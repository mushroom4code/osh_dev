<?
namespace Ipol\Fivepost;

use Ipol\Fivepost\Admin\Logger;
use Ipol\Fivepost\Bitrix\Entity\Cache;
use Ipol\Fivepost\Bitrix\Entity\Encoder;
use Ipol\Fivepost\Bitrix\Entity\Options;
use Ipol\Fivepost\Bitrix\Tools;

IncludeModuleLangFile(__FILE__);


/**
 * Class optionsHandler
 * @package Ipol\Fivepost\
 * ��� ������������ ������ ��� ������ �� ��������� ������� ������. ����������� ������ � ������
 */

class OptionsHandler extends AbstractGeneral
{
    // common
    /**
     * @param bool $noFdb - ���������� �� �� ����� (����� ���-�� � ����� ��������, ����� ������, ��� ��� ��)
     * ������� ���� ������.
     */
    public static function clearCache($noFdb = false)
    {
        $cacheObj = new Cache();
        $obCache = new \CPHPCache();
        $obCache->CleanDir($cacheObj->getPath());

        // Cool new D7 cache
        $path = '/'.self::getMODULEID().'/';
        $cacheInstance = \Bitrix\Main\Data\Cache::createInstance();
        $cacheInstance->CleanDir($path);

        if(!$noFdb)
            echo "Y";
    }

    /**
     * @param $params
     * ������� ����� ���� (��. Admin/Logger)
     */
    public static function clearLog($params)
    {
        if(array_key_exists('src',$params)){
            Logger::clearLog($params['src']);
        }
    }

    /**
     * @param bool $noFdb - ���������� �� �� ����� (����� ���-�� � ����� ��������, ����� ������, ��� ��� ��)
     * ����� �������� ����������
     */
    public static function resetCounter($noFdb = false){
        Option::set('barkCounter',0);
        if(!$noFdb)
            echo "Y";
    }
}