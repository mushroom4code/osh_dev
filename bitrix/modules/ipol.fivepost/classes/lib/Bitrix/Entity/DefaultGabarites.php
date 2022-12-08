<?
namespace Ipol\Fivepost\Bitrix\Entity;

class DefaultGabarites extends Options
{
    protected $mode;
    protected $weight;
    protected $length;
    protected $width;
    protected $height;

    public function __construct()
    {
        $this->mode   = self::fetchOption('defMode');
        $this->weight = self::fetchOption('weightD');
        $this->length = self::fetchOption('lengthD');
        $this->width  = self::fetchOption('widthD');
        $this->height = self::fetchOption('heightD');

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }
}