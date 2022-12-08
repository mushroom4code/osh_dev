<?php
namespace PickPoint\DeliveryService\Admin\Grid;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class DBGrid
 * @package PickPoint\DeliveryService\Admin\Grid
 */
abstract class DBGrid extends AbstractGrid
{
    /**
     * @var string
     */
    protected $fetchMode;

    /**
     * @var array
     */
    protected $select = ['*'];

    /**
     * Return ORM data mapper for data selection
     *
     * @return string
     */
    abstract public function getDataMapper();

    /**
     * Get data fetch mode
     *
     * @return string
     */
    public function getFetchMode()
    {
        return $this->fetchMode;
    }

    /**
     * Set data fetch mode
     *      
     * @param string $fetchMode: 'object' - data return as object or 'default' - data return as array
     * @return self
     */
    public function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;
        return $this;
    }

    /**
     * Get selected columns
     *
     * @return array
     */
    public function getSelect()
    {
        return $this->select;
    } 

    /**
     * Set selected columns
     *
     * @param array $select
     * @return self
     */
    public function setSelect(array $select)
    {
        $this->select = $select;
        return $this;
    }
    
    /**
     * Get raw data used for creating grid rows
     *
     * @return array
     */
    protected function getRawData()
    {
        $ret        = [];
        $query      = $this->getQuery();				
		//\Bitrix\Main\Diag\Debug::WriteToFile($query->getQuery(), 'SQL Query', '__q.log');		
        $result     = $query->exec();

        if ($this->getFetchMode() == 'object') {
            $ret = [];
            while($item = $result->fetchObject()) {
                $ret[] = $item;
            }
        } else {
            $ret = $result->fetchAll();
        }

        $pagination = $this->getPagination();
        $pagination->setRecordCount($result->getCount());   
        
        return $ret;
    }

    /**
     * Query constructor
     *
     * @return Query
     */
    protected function getQuery()
    {
        $dataMapper = $this->getDataMapper();

        $query = $dataMapper::query()->setSelect($this->getSelect())->setFilter($this->getFilterValues())->setOrder($this->getSorting())->countTotal(true);     
        $pagination = $this->getPagination();

        if (!$pagination->allRecordsShown()) {
            $query->setLimit($pagination->getLimit());
            $query->setOffset($pagination->getOffset());
        }

        return $query;
    }
}