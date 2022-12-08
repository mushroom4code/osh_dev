<?
//� ������ ����� ���������� ������ �����������, � �������� ��������
//���������� ����������� �����, �� ������� ���������� ��������

class CTWEB_SMSAUTH_PROVIDERS {

    function __construct(){
        //���������� ����� � �������������
        self::includeFiles();
    }

    function getProviders(){
        //� ������ ����� ���������� ������ ����������, � �������� ����� ��������
        $arProviders = array(
            "smsru" => "sms.ru",
            "smscru" => "smsc.ru",
            "smsaeroru" => "smsaero.ru",
            "redsmsru" => "redsms.ru",
            "bytehandcom" => "bytehand.com",
            "iqsmsru" => "iqsms.ru",
            "infosmskaru" => "infosmska.ru",
            "p1smsru" => "p1sms.ru",
            "itsmsru" => "it-sms.ru",
            "prostorsmsru" => "prostor-sms.ru",
            "smssendingru" => "sms-sending.ru",
            "smsuslugiru" => "sms-uslugi.ru",
            "mainsms" => "mainsms.ru",
        );

        ksort($arProviders);

        //��������� ������ �� ��������
        asort($arProviders);

        return $arProviders;
    }

    function getProvcurl(){
        //� ������ ����� ���������� ������ ����������, � �������� ����� ��������
        $arProviders = array(
            "smsru",
            "smscru",
            "smsaeroru",
            "bytehandcom",
            "iqsmsru",
            "p1smsru",
            "itsmsru",
            "prostorsmsru",
            "smssendingru",
            "infosmskaru",
            "smsuslugiru",
            "mainsms",
        );

        return $arProviders;
    }

    function includeFiles(){
        $arProviders = self::getProviders();

        foreach($arProviders as $file=>$val){
            require_once('providers/cwsa_'.$file.'.php');
        }
    }

}