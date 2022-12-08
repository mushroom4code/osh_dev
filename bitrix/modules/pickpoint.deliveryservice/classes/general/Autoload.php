<?php
namespace PickPoint\DeliveryService;

/**
 * Class Autoload
 * @package PickPoint\DeliveryService
 */
final class Autoload
{	
	public static function register()
	{
		if (!defined('PICKPOINT_DELIVERYSERVICE_LIB_ROOT'))
			define('PICKPOINT_DELIVERYSERVICE_LIB_ROOT', rtrim(dirname(__FILE__), 'general').'lib'.DIRECTORY_SEPARATOR);
		
		spl_autoload_register(array(__CLASS__, 'load'));		
	}
	
	public static function load($class)	
	{	
		if (class_exists($class, false) || mb_strpos($class, __NAMESPACE__) !== 0) 
           return false;        
			
		$path = str_replace(__NAMESPACE__ .'\\', '', $class);	
				
		$classfile = PICKPOINT_DELIVERYSERVICE_LIB_ROOT.str_replace('\\', DIRECTORY_SEPARATOR, $path).'.php';		
		
        if ((file_exists($classfile) === false) || (is_readable($classfile) === false))            
            return false;        

        require($classfile);
	}	
}