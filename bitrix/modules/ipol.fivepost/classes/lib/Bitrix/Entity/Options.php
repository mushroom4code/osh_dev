<?
namespace Ipol\Fivepost\Bitrix\Entity;

use Ipol\Fivepost\Option;
use Ipol\Fivepost\Fivepost\Entity\OptionsInterface;

/**
 * Class options
 * @package Ipol\Fivepost\
 * ������ ��� �������� �������� ����� ����-������ � ������-�����������.
 * ��������� �������� �������� ����� �� ����, � ����� ������ ����������.
 * ���, ����� key ����� �������� ����� options->getOption(key) ��� �� options->getKey()
 * �������, ��� ��� ������������ option.php �� ������ ��������� � ����� ������ ������� ��� � ���������� ��������
 */
class Options implements OptionsInterface
{
    public static function fetchOption($code)
    {
        return Option::get($code);
    }

    public function pushOption($option,$handle)
    {
        $this->$option = $handle;
    }

    public function __call($name, $arguments)
    {
        if(strpos($name,'fetch') !== false)
        {
            $option = lcfirst(substr($name,5));

            if(property_exists($this,$option))
                return $this->$option;
            else {
                $this->$option = self::fetchOption($option);
                return $this->$option;
            }
        }
        elseif(strpos($name,'push') !== false)
        {
            $option = lcfirst(substr($name,4));

            $this->pushOption($option,$arguments[0]);

            return $this;
        }
        else
            throw new \Exception('Call to unknown method '.$name);
    }
}