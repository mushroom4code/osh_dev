<?php
namespace Ipol\Fivepost\Api\Entity\Request;

/**
 * Class WarehousesInfo
 * @package Ipol\Fivepost\Api\Entity\Request
 */
class WarehousesInfo extends AbstractRequest
{
    /**
     * @var int
     */
    protected $page;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return WarehousesInfo
     */
    public function setPage(int $page): WarehousesInfo
    {
        $this->page = $page;
        return $this;
    }
}