<?
class p1smsAPI {

	function __construct($login, $passw){
		$this->login = $login;
		$this->passw = $passw;
	}
	
	function sendSMS($to, $text, $from = ''){
	    $time = date_create((string) $this->getTime()->time)->modify('+1 min')->format('Y-m-d H:i:s');
		$xml = '<?xml version="1.0" encoding="utf-8" ?>
					<request>
						<message type="sms">
							<sender>'.$from.'</sender>
							<text>'.$text.'</text>
							<abonent phone="'.$to.'" time_send="'.$time.'" />
						</message>
						<security>
							<login value="'.$this->login.'" />
							<password value="'.$this->passw.'" />
						</security>
					</request>';
					
		return self::sendQuery('', $xml);
	}

	function getTime() {
	    $xml = '<?xml  version="1.0" encoding="utf-8" ?>
                    <request>
                        <security>
                            <login value="'.$this->login.'" />
							<password value="'.$this->passw.'" />
                        </security>
                    </request>';

        return self::sendQuery('time.php', $xml);
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
		$href = 'http://95.213.129.83/xml/'.$method;
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
