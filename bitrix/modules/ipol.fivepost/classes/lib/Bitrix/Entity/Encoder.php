<?
namespace Ipol\Fivepost\Bitrix\Entity;


use Ipol\Fivepost\Bitrix\Tools;
use Ipol\Fivepost\Api\Entity\EncoderInterface;

/**
 * Class encoder
 * @package Ipol\Fivepost\
 * ����� ��� ������������� ������ �� API � �������. ��� �������, ��� API �������� �� UTF-8, ������� encdeFromApi �����������
 * ������ �� UTF-8 � ��������� �����, � encodeToAPI - �������
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