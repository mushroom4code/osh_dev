<?php
namespace Ipol\Fivepost\Fivepost\Controller;

use Ipol\Fivepost\Api\Entity\Request\WarehousesInfo as RequestObj;
use Ipol\Fivepost\Fivepost\Entity\WarehousesInfoResult as ResultObj;

/**
 * Class WarehousesInfo
 * @package Ipol\Fivepost\Fivepost\Controller
 */
class WarehousesInfo extends AutomatedCommonRequest
{
    /**
     * WarehousesInfo constructor.
     * @param ResultObj $resultObj
     * @param int $page
     */
    public function __construct(ResultObj $resultObj, int $page)
    {
        parent::__construct($resultObj);
        $this->requestObj = new RequestObj();
        $this->requestObj->setPage($page);
    }
}