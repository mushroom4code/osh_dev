<?
namespace Ipol\Fivepost\Admin;

use Ipol\Fivepost\Bitrix\Tools;
use Ipol\Fivepost\Api\Entity\LoggerInterface;
use Ipol\Fivepost\Option;

/**
 * Class Logger
 * @package Ipol\Fivepost\
 * Класс для создания логов. Пишутся либо в Tools::getJSPath()/logs/, либо в корень сайта
 */
class Logger implements LoggerInterface
{
    /**
     * @param $wat - что именно пишем
     * @param $label
     * @param bool $src - имя файла, если задан - будет писаться в путь к js-файлам в папке логов, иначе - в корень сайта
     * с названием KM+log.txt
     * @param bool $flags - флаги, типичные для file_put_contents
     */
    public static function toLog($wat, $label = "", $src = false, $flags = false){
        if(!$flags){
            $flags = array('ADMIN' => false, 'APPEND' => false);
        }

        if(!$flags['ADMIN'] || Tools::isAdmin()) {
            if ($src) {
                file_put_contents(self::getFileName($src), "\n" . date('H:i:s d.m.Y') . "\n" . $wat."\n", FILE_APPEND);
            } else {
                $arDebugSrc = array('createOrder','warehouse','cancelOrderByNumber','getOrderStatus');
                if(in_array($src,$arDebugSrc)){
                    $flags['APPEND'] = (Option::get('debug_fileMode') === 'a');
                }
                self::toLogFile($wat,$flags);
            }
        }
    }

    /**
     * @param $src - путь к bitrix/js/модуль/src/
     * @return bool|string
     * Получение содержимого лога в папке логов. Нужен, например, для вывода инфы в опциях.
     */
    public static function getLogInfo($src){
        if(
            !self::checkSrc(true) ||
            !file_exists(self::getFileName($src))
        ){
            return '';
        } else {
            return file_get_contents(self::getFileName($src));
        }
    }

    /**
     * @param $src
     * Очищаем файлы лога
     */
    public static function clearLog($src){
        if(
            self::checkSrc(true) ||
            file_exists(self::getFileName($src))
        ) {
            unlink(self::getFileName($src));
        }
    }

    /**
     * @param bool $noCreate - не создавать, если папки нет
     * @return bool
     * проверяем, есть ли папка src в логах
     */
    protected static function checkSrc($noCreate = false){
        $exist = file_exists(self::getRootPath());
        if(!$exist && !$noCreate){
            mkdir(self::getRootPath());
        }
        return $exist;
    }

    /**
     * @param bool $src
     * @return string
     * получаем полный путь к файлу лога
     */
    protected static function getFileName($src = false){
        if(!$src){
            return $_SERVER['DOCUMENT_ROOT']."/ipol.fivepost_log.txt";
        } else {
            self::checkSrc();
            return self::getRootPath()."/".$src.".txt";
        }
    }

    /**
     * @return string
     * Получение пути к папке с логами. У нас она лежит в bitrix/js/ipol.fivepost/
     */
    protected static function getRootPath()
    {
        return $_SERVER['DOCUMENT_ROOT']."/".Tools::getJSPath().'logs';
    }

    // simpleLog

    protected static $fileLink = false;

    /**
     * @param $wat
     * @param array $flags
     * если нам надо тупо записать лог в корень сайта
     */
    protected static function toLogFile($wat, $flags=array('APPEND'=>false)){
        if(!self::$fileLink){
            self::$fileLink = fopen(self::getFileName(),($flags['APPEND']) ? 'a' : 'w');
            fwrite(self::$fileLink,"\n\n".date('H:i:s d.m.Y')."\n");
        }
        fwrite(self::$fileLink,print_r($wat,true)."\n");
    }

    // toOptions

    /**
     * @param $src
     * @return string
     * Выводим содержимое лога в html-е (как правило, в опциях)
     */
    public static function toOptions($src)
    {
        $strInfo   = self::getLogInfo($src);
        $strReturn = '';

        if($strInfo){
            $arInfo = explode("\n\n",$strInfo);
            rsort($arInfo);
            foreach ($arInfo as $text){
                if($text){
                    $strReturn .= str_replace("\n","<br>",$text)."<br>";
                }
            }
        }

        return $strReturn;
    }
}