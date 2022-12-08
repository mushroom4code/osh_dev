<?php
namespace Ipol\Fivepost\Bitrix\Handler;

class PaySystems
{
    /**
     * Get all active payment systems as array of ID <> Name
     * @return array
     */
    public static function getAll()
    {
        $paySystems = array();

        $paySysDB = \CSalePaySystem::GetList(array(), array('ACTIVE' => 'Y'));
        while($tmp = $paySysDB->Fetch()) {
            $paySystems[$tmp['ID']] = $tmp['NAME'];
        }

        return $paySystems;
    }
}