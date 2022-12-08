<?
namespace Ipol\Fivepost\Bitrix\Entity;

use Ipol\Fivepost\Bitrix\Tools;

/**
 * Class Profile
 * @package namespace Ipol\Fivepost\Bitrix\Entity
 */
class Profile
{
    protected $id;
    protected $price;
    protected $termMin;
    protected $termMax;
    protected $termIncrease;
    protected $details;
    protected $success;

    public function __construct()
    {
        $this->termIncrease = 0;
        $this->success = true;
    }

    /**
     * @return array
     */
    public function toBitrix()
    {
        if ($this->success)
        {
            $arReturn = array(
                'RESULT' => 'OK',
                'VALUE' => $this->getPrice(),
                //'PRINT_VALUE' => Tools::formatCurrency($this->getPrice(),'RUB')
            );
            if ($this->getTerm()) {
                $arReturn['TRANSIT'] = (string)$this->getTerm()." ".Tools::getDayEnd($this->getTerm());
            }
            if ($this->getTermMax() && $this->getTermMin())
            {
                $arReturn['periodFrom'] = $this->getTermMin() + $this->getTermIncrease();
                $arReturn['periodTo']   = $this->getTermMax() + $this->getTermIncrease();
            }
        }
        else
        {
            $arReturn = array(
                'RESULT' => 'ERROR',
                'TEXT'   => $this->getDetails()
            );
        }

        return $arReturn;
    }


    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTerm()
    {
        if ($this->getTermMax() == $this->getTermMin())
        {
            return $this->getTermMax() + $this->getTermIncrease();
        }
        else
        {
            return ($this->getTermMin() + $this->getTermIncrease())."-".($this->getTermMax() + $this->getTermIncrease());
        }
    }

    /**
     * @return mixed
     */
    public function getTermMax()
    {
        return $this->termMax;
    }

    /**
     * @param mixed $termMax
     * @return $this
     */
    public function setTermMax($termMax)
    {
        $this->termMax = $termMax;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTermMin()
    {
        return $this->termMin;
    }

    /**
     * @param mixed $termMin
     * @return $this
     */
    public function setTermMin($termMin)
    {
        $this->termMin = $termMin;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTermIncrease()
    {
        return $this->termIncrease;
    }

    /**
     * @param mixed $termIncrease
     * @return $this
     */
    public function setTermIncrease($termIncrease)
    {
        $this->termIncrease = intval($termIncrease);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }
}