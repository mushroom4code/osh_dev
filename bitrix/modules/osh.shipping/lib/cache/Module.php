<?php
namespace Osh\Delivery\Cache;

use Bitrix\Main\Web\Json;

class Module{
    const FOLDER = '/bitrix/cache/osh.shipping/%s';
    private static $instance = null;

    private function __construct(){
        $this->path = $_SERVER['DOCUMENT_ROOT'].self::FOLDER;
    }
    public static function getInstance(){
        if(empty(static::$instance)){
            static::$instance = new Module();
        }
        return static::$instance;
    }
    public function getCachePath($addition){
        return sprintf($this->path,$addition);
    }
    public function set($id, $value){
        $jsonCache = Json::encode($value);
        if(empty($jsonCache)){
            throw new \Exception('json fail');
        }
        $fileName = $this->getCachePath($id);
        $res = $this->put($fileName,$jsonCache);
        if(!$res){
            throw new \Exception('file cache fail '.$id);
        }
    }
    public function get($ttl, $id){
        if(!file_exists($this->getCachePath($id))){
            return false;
        }
        $jsonCache = file_get_contents($this->getCachePath($id));
        $arCache = Json::decode($jsonCache);
        if(empty($arCache)){
            throw new \Exception('json fail');
        }
        $currentTime = time();
        $validTo = filectime($this->getCachePath($id)) + $ttl;
        if($currentTime > $validTo){
            $this->delete($id);
            return false;
        }
        if(empty($arCache)){
            $this->delete($id);
            return false;
        }
        return $arCache;
    }
    public function delete($id){
        unlink($this->getCachePath($id));
    }
    private function put($dir,$contents){
        $parts = explode('/', $dir);
        $file = array_pop($parts);
        $dir = '';
        foreach($parts as $part){
            if(strpos($part,':') !== false && empty($dir)){
                $dir = $part;
                continue;
            }
            if(!is_dir($dir .= '/'.$part)){
                mkdir($dir);
            }
        }
        return file_put_contents("$dir/$file", $contents);
    }
    public function cleanAll(){
        $files = glob(self::getCachePath('*'));
        foreach($files as $file){
            if(is_file($file)){
                unlink($file);
            }
        }
    }
}