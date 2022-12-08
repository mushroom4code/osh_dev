<?php


namespace Ipol\Fivepost\Fivepost\Controller;

use Ipol\Fivepost\Api\Entity\Request\AbstractRequest;
use Ipol\Fivepost\Api\Sdk;
use Ipol\Fivepost\Fivepost\AppLevelException;


/**
 * Class RequestController
 * @package Ipol\Fivepost\Fivepost
 * @subpackage Controller
 */
abstract class RequestController
{
    /**
     * @var Sdk
     */
    protected $Sdk;
    /**
     * @var AbstractRequest|mixed
     */
    protected $requestObj;
    /**
     * @var AbstractResult|mixed
     */
    protected $resultObject;
    /**
     * @var string
     */
    protected $sdkMethodName;

    /**
     * @return $this|mixed
     */
    abstract public function execute();

    /**
     * @return mixed
     */
    public function getRequestObj()
    {
        return $this->requestObj;
    }

    /**
     * @param mixed $requestObj
     * @return $this|mixed
     */
    public function setRequestObj($requestObj)
    {
        $this->requestObj = $requestObj;
        return $this;
    }

    /**
     * @return AbstractResult|mixed
     */
    public function getResultObject()
    {
        return $this->resultObject;
    }

    /**
     * @param AbstractResult|mixed $resultObject
     * @return $this|mixed - mixed for child-classes
     */
    public function setResultObject($resultObject)
    {
        $this->resultObject = $resultObject;
        return $this;
    }

    /**
     * @return Sdk
     * @throws AppLevelException
     */
    public function getSdk(): Sdk
    {
        if(!$this->Sdk)
            throw new AppLevelException('Accessing Sdk before setting and configuring it');
        return $this->Sdk;
    }

    /**
     * @param Sdk $Sdk
     * @return $this|mixed
     */
    public function setSdk(Sdk $Sdk)
    {
        $this->Sdk = $Sdk;

        return $this;
    }
    /**
     * @return string
     */
    public function getSdkMethodName(): string
    {
        return $this->sdkMethodName;
    }

    /**
     * @param string $sdkMethodName
     * @return $this|mixed
     */
    public function setSdkMethodName(string $sdkMethodName)
    {
        $this->sdkMethodName = $sdkMethodName;
        return $this;
    }

}