<?
class MainSMSAPI {
    const REQUEST_SUCCESS = 'success';
    const REQUEST_ERROR = 'error';

    protected
        $project    = null,
        $key        = null,
        $testMode   = false,
        $url        = 'mainsms.ru/api/mainsms',
        $useSSL     = false,
        $response   = null;

    /**
     * Конструктор
     *
     * @param string $project
     * @param string $key
     * @param string $useSSL
     * @param integer $testMode
     */
    public function __construct($project, $key, $useSSL = false, $testMode = false)
    {
        $this->project = $project;
        $this->key = $key;
        $this->useSSL = $useSSL;
        $this->testMode = $testMode;
    }

    function getBalance(){
        $response = $this->makeRequest('message/balance');
        return $response['status'] == self::REQUEST_SUCCESS ? $response['balance'] : false;
    }

    /**
     * Отправить запрос
     *
     * @param string $function
     * @param array $params
     *
     * @return stdClass
     */
    protected function makeRequest($function, array $params = array())
    {
        $params = $this->joinArrayValues($params);
        $sign = $this->generateSign($params);
        $params = array_merge(array('project' => $this->project), $params);

        $url = ($this->useSSL ? 'https://' : 'http://') . $this->url .'/'. $function;
        $post = http_build_query(array_merge($params, array('sign' => $sign)), '', '&');

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($this->useSSL) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $post,
                    'timeout' => 10,
                ),
            ));
            $response = file_get_contents($url, false, $context);
        }
        return $this->response = json_decode($response, true);
    }

    protected function joinArrayValues($params)
    {
        $result = array();
        foreach ($params as $name => $value) {
            $result[$name] = is_array($value) ? join(',', $value) : $value;
        }
        return $result;
    }

    /**
     * Сгенерировать подпись
     *
     * @param array $params
     * @return string
     */
    protected function generateSign(array $params)
    {
        $params['project'] = $this->project;
        ksort($params);
        return md5(sha1(join(';', array_merge($params, Array($this->key)))));
    }

    /**
     * Отправить SMS
     *
     * @param string|array $recipients
     * @param string $message
     * @param string $sender
     *
     * @return boolean|integer
     * @deprecated
     */
    public function sendSMS($recipients, $message, $sender, $run_at = null)
    {
        return $this->messageSend($recipients, $message, $sender, $run_at);
    }

    /**
     * Отправить SMS
     *
     * @param string|array $recipients
     * @param string $message
     * @param string $sender
     * @param string $run_at
     *
     * @return boolean|integer
     */
    public function messageSend($recipients, $message, $sender, $run_at = null)
    {
        $params = array(
            'recipients'    => $recipients,
            'message'       => $message,
            'sender'        => $sender,
        );

        if ($run_at != null) {
            $params['run_at'] = $run_at;
        }

        if ($this->testMode) {
            $params['test'] = 1;
        }

        $response = $this->makeRequest('message/send', $params);

        return $response['status'] == self::REQUEST_SUCCESS;
    }

}
