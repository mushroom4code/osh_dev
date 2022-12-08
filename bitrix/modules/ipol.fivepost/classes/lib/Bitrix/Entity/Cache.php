<?

namespace Ipol\Fivepost\Bitrix\Entity;

use Ipol\Fivepost\Core\Entity\CacheInterface;

/**
 * Class cache
 * @package Ipol\Fivepost\
 * ����� ��� ������ � �����
 */
class Cache extends \CPHPCache implements CacheInterface
{
    /**
     * @var int - seconds
     * Cache lifetime
     */
    protected $life;
    /**
     * @var string
     * ���� � ������ ����, �� ����� ����� ����� ����� ������
     */
    protected $path;
    /**
     * @var bool
     * ��� �� ��������������� (����� checkCache ������ ��� �� ������)
     */
    protected $inited = false;

    public function __construct()
    {
        parent::__construct();

        $this->path = '/'.\Ipol\Fivepost\AbstractGeneral::getMODULELBL().'CACHE/';

        $this->life = 86400;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function checkCache($hash)
    {
		if(
			!(defined(self::getDeactCacheConst()) && constant(self::getDeactCacheConst()) === true) && //���� ���� ���������� ��� ���������� ���� - ��������� ���
			$this->InitCache($this->getLife(),$hash,$this->getPath())
		)
		{
			$this->inited = true;
			return true;
		}
		return false;
    }

    public function getCache($hash)
    {
        //if(!$this->inited)
            $this->checkCache($hash);

        return $this->GetVars();
    }

    public function setCache($hash, $data)
    {
        //if(!$this->inited)
            $this->checkCache($hash);

        $this->StartDataCache();
        $this->EndDataCache($data);
    }

    /**
     * @return int
     */
    public function getLife()
    {
        return $this->life;
    }

    /**
     * @param int $life
     * @return $this
     */
    public function setLife($life)
    {
        $this->life = intval($life);

        return $this;
    }

    /**
     * @return string
     * �������� �������� ���������, ������� ��������� ���
     */
    public static function getDeactCacheConst()
    {
        return \Ipol\Fivepost\AbstractGeneral::getMODULELBL().'NOCACHE';
    }
}