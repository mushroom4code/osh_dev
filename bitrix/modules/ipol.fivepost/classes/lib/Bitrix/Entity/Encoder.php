<?
namespace Ipol\Fivepost\Bitrix\Entity;


use Ipol\Fivepost\Bitrix\Tools;
use Ipol\Fivepost\Api\Entity\EncoderInterface;

/**
 * Class encoder
 * @package Ipol\Fivepost\
 *  ласс дл€ перекодировки данных из API и обратно.  ак правило, все API работают на UTF-8, поэтому encdeFromApi преобразует
 * данные из UTF-8 в кодировку сайта, а encodeToAPI - обратно
 */
class Encoder implements EncoderInterface
{
    public function encodeFromAPI($handle)
    {
        return Tools::encodeFromUTF8($handle);
    }

    public function encodeToAPI($handle)
    {
        return Tools::encodeToUTF8($handle);
    }
}