<?
class itsmsAPI {

	function __construct($login, $passw){
		$this->login = $login;
		$this->passw = $passw;
	}
	
	function sendSMS($to, $text, $from = ''){		
		$xml = '<?xml version="1.0" encoding="utf-8" ?>
					<request>
						<message type="sms">
							<sender>'.$from.'</sender>
							<text>'.$text.'</text>
							<abonent phone="'.$to.'" />
						</message>
						<security>
							<login value="'.$this->login.'" />
							<password value="'.$this->passw.'" />
						</security>
					</request>';
					
		return self::sendQuery('', $xml);
	}
	
	function getSenders(){
		$xml = '<?xml  version="1.0" encoding="utf-8" ?>
					<request>
						<security>
							<login value="'.$this->login.'" />
							<password value="'.$this->passw.'" />
						</security>
					</request>';
					
		return self::sendQuery('originator.php', $xml);
	}
		
	function getBalance(){
		$xml = '<?xml  version="1.0" encoding="utf-8" ?>
					<request>
						<security>
							<login value="'.$this->login.'" />
							<password value="'.$this->passw.'" />
						</security>
					</request>';
					
		return self::sendQuery('balance.php', $xml);
	}
	
	function sendQuery($method, $src){
		$src = str_replace("
		", '', $src);
		$href = 'http://my.it-sms.ru/xml/'.$method;
		$res = '';
	
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset=utf-8'));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_CRLF, true);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $src);
	    curl_setopt($ch, CURLOPT_URL, $href);
	    $result = curl_exec($ch);
	    $res = $result;
	    curl_close($ch);
	    
	    return simplexml_load_string($res);
	}

}
