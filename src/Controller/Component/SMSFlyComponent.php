<?php
namespace SMSFly\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Network\Http\Client;
use Cake\Utility\Xml;

class SMSFlyComponent extends Component
{
    /**
     * Price per one SMS.
     *
     * @var float.
     */
    protected $price;

    /**
     * Username for auth
     *
     * @var string
     */
    protected $username;

    /**
     * Password for auth
     *
     * @var string
     */
    protected $password;

    /**
     * Alpha-name for showing "From: "
     *
     * @var string
     */
    protected $source;

    /**
     * Client object
     *
     * @var \Cake\Network\Http\Client;
     */
    protected $http;

    /**
     * Balance of money.
     *
     * @var float
     */
    protected $balance;

    /**
     * Initialize properties.
     *
     * @param array $config The config data.
     *
     * @return void
     */
    public function initialize(array $config)
    {
        $this->loadConfiguration();

        $this->http = new Client();
    }

    /**
     * Send SMS to one $recipient.
     *
     * @param integer $recipient
     * @param string $message
     * @param string $desc
     * @param string $source
     * @param int $rate
     * @param int $lifeTime
     * @param string $endTime
     * @param string $startTime
     *
     * @return bool
     */

    public function sendSMS($recipient, $message, $desc = '', $source = '', $rate = 10, $lifeTime = 4, $endTime = 'AUTO', $startTime = 'AUTO')
    {
        $this->checkData($recipient, $message);
        $message = htmlspecialchars($message);
        $desc = htmlspecialchars($desc);

        $source = (empty($source)) ? $this->source : $source;

        $xmlArray = [
            'request' => [
                'operation' => 'SENDSMS',
                'message' => [
                    '@start_time' => $startTime,
                    '@end_time' => $endTime,
                    '@lifetime' => $lifeTime,
                    '@rate' => $rate,
                    '@desc' => $desc,
                    '@source' => $source,
                    'body' => $message,
                    'recipient' => $recipient
                ]
            ]
        ];

        $response = $this->makeRequest($xmlArray);

        return $response['message']['state']['@code'] == 'ACCEPT';
    }

    /**
     * Send SMS to many $recipients.
     *
     * @param array $recipients
     * @param string $message
     * @param string $desc
     * @param string $source
     * @param int $rate
     * @param int $lifeTime
     * @param string $endTime
     * @param string $startTime
     *
     * @return bool
     */
    public function sendSMSToMany($recipients, $message, $desc = '', $source = '', $rate = 120, $lifeTime = 10, $endTime = 'AUTO', $startTime = 'AUTO')
    {
        $this->checkDataMany($recipients, $message);
        $message = htmlspecialchars($message);
        $desc = htmlspecialchars($desc);

        $source = (empty($source)) ? $this->source : $source;

        $xmlArray = [
            'request' => [
                'operation' => 'SENDSMS',
                'message' => [
                    '@start_time' => $startTime,
                    '@end_time' => $endTime,
                    '@lifetime' => $lifeTime,
                    '@rate' => $rate,
                    '@desc' => $desc,
                    '@source' => $source,
                    'body' => $message,
                    'recipient' => $recipients
                ]
            ]
        ];

        $response = $this->makeRequest($xmlArray);

        return $response['message']['state']['@code'] == 'ACCEPT';
    }

    /**
     * Return balance in system.
     *
     * @return float
     */
    public function getBalance()
    {
        $xmlArray = [
            'request' => [
                'operation' => 'GETBALANCE'
            ]
        ];

        $response = $this->makeRequest($xmlArray);
        $this->balance = (float)$response['message']['balance'];

        return $this->balance;
    }

    /**
     * Returns the number of SMS available based
     * on the amount of money in account.
     *
     * @return int
     */
    public function getSMSCount()
    {
        if (is_null($this->balance)) {
            $this->balance = $this->getBalance();
        }

        return (int)floor($this->balance / $this->price);
    }

    /**
     * Load configuration
     * from CakePHP config file.
     *
     * @return void
     * @throws Exception
     */
    protected function loadConfiguration()
    {
        $config = Configure::read("SMSFly");

        if (is_null($config)) {
            throw new Exception('Missing configuration for SMSFly');
        }

        if (is_null($config['API']) || !isset($config['API']['password']) || !isset($config['API']['username'])) {
            throw new Exception('Missing Auth configuration for SMSFly');
        }

        if (!isset($config['API']['source']) || empty($config['API']['source'])) {
            $this->source = 'InfoCentr';
        }

        $this->username = $config['API']['username'];
        $this->password = $config['API']['password'];
        $this->source = $config['API']['source'];
        $this->price = $config['API']['price'];
    }

    /**
     * Check recipient and message for emptiness and type.
     *
     * @param integer $to
     * @param string $msg
     *
     * @return void
     * @throws Exception
     */
    protected function checkData($to, $msg)
    {
        if (empty($to) || is_null($to)) {
            throw new Exception('The recipient is empty');
        }

        if (!is_numeric($to)) {
            throw new Exception('The recipient is not a number');
        }

        if (empty($msg) || is_null($msg)) {
            throw new Exception('The message is empty');
        }

        if (!is_string($msg)) {
            throw new Exception('The message is not a string');
        }
    }

    /**
     * Check recipient and message for emptiness and type.
     *
     * @param array $to
     * @param string $msg
     *
     * @throws Exception
     * @return void
     */
    protected function checkDataMany($to, $msg)
    {
        if (empty($to) || is_null($to)) {
            throw new Exception('The recipient is empty');
        }

        if (!is_array($to)) {
            throw new Exception('The recipient is not an array');
        }

        if (empty($msg) || is_null($msg)) {
            throw new Exception('The message is empty');
        }

        if (!is_string($msg)) {
            throw new Exception('The message is not a string');
        }
    }

    /**
     * Sending request to API and return response converted to array.
     *
     * @param array $xmlArray
     *
     * @return array
     */
    protected function makeRequest($xmlArray = [])
    {
        $xml = Xml::build($xmlArray)->asXML();
        $response = $this->http->post('http://sms-fly.com/api/api.php', $xml, [
                'auth' => [
                    'username' => $this->username,
                    'password' => $this->password,
                ],
            ]
        );

        return Xml::toArray($response->xml);
    }
}