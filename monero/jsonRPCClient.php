<?php

namespace Monero;

//json 2.0 rpc client
use App\Exceptions\ConnectionException;

/**
 * Class jsonRPCClient
 * JSON 2.0 RPC Client for cryptocurrency wallet
 */
class jsonRPCClient
{
    /**
     * @var
     */
    private $url;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var
     */
    private $username;

    /**
     * @var
     */
    private $password;

    /**
     * @var array
     */
    private $headers = [
        'Connection: close',
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    /**
     * jsonRPCClient constructor.
     *
     * @param $url
     * @param int $timeout
     * @param bool|false $debug
     * @param array $headers
     */
    public function __construct($url, $timeout = 20, $debug = false, $headers = [])
    {
        $this->url = $url;
        $this->timeout = $timeout;
        $this->debug = $debug;
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * Generic method executor
     *
     * @param $method
     * @param $params
     *
     * @return null
     */
    public function __call($method, $params)
    {
        return $this->execute($method, $params);
    }

    /**
     * Set auth credentials
     *
     * @param $username
     * @param $password
     */
    public function authentication($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get XMR bulk payments
     *
     * @param $payment_ids
     * @param $block_height
     *
     * @return null
     */
    public function bulk_payments($payment_ids, $block_height)
    {
        return $this->execute('get_bulk_payments', $payment_ids, $block_height);
    }

    /**
     * Transfer XMR to another destination
     *
     * @param $amount
     * @param $destination
     * @param $payment_id
     * @param $mixin
     * @param int $unlock_time
     *
     * @return string
     */
    public function transferXMR($amount, $destination, $payment_id, $mixin, $unlock_time = 0)
    {
        $dest = ['amount' => intval(0 + $amount * 1000000000000), 'address' => $destination];
        $params = [
            'destinations' => [$dest],
            'payment_id' => $payment_id,
            'mixin' => $mixin,
            'unlock_time' => $unlock_time,
        ];
        $response = $this->execute('transfer', $params);
        $tx = trim($response['tx_hash'], '<>');

        return $tx;
    }

    public function mempoolTransfers()
    {
        $response = $this->execute('get_transfers', ['pool' => true]);

        return $response;
    }

    /**
     * Prepares the payload for CURL and evaluates the result from the RPC
     *
     * @param $procedure
     * @param $params
     * @param null $params2
     *
     * @return null
     *
     * @throws WalletErrorException
     */
    public function execute($procedure, $params, $params2 = null)
    {
        $id = mt_rand();
        $payload = [
            'jsonrpc' => '2.0',
            'method' => $procedure,
            'id' => $id,
        ];

        if (!empty($params)) {
            if ($params2 != null) {
                $payload['params']['payment_ids'] = $params;
                $payload['params']['min_block_height'] = $params2;
            } else {
                if (is_array($params)) {
                    // no keys
                    //$params = array_values($params);
                    $payload['params'] = $params;
                }
            }
        }
        if ($this->debug) {
            print_r($payload);
        }

        $result = $this->doRequest($payload);
        if (isset($result['id']) && $result['id'] == $id && array_key_exists('result', $result)) {
            if ($this->debug) {
                print_r($result['result']);
            }

            return $result['result'];
        }

        if (isset($result['error'])) {
            throw new ConnectionException($result['error']['message']);
        }

        throw new ConnectionException('no response');
    }

    /**
     * Executes the CURL request.
     *
     * @param $payload
     *
     * @return array|mixed
     */
    public function doRequest($payload)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JSON-RPC PHP Client');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        if ($this->username && $this->password) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
        }

        if ($this->debug) {
            print_r(json_encode($payload)."\n");
            print_r($ch);
        }

        $result = curl_exec($ch);
        $response = json_decode($result, true);

        curl_close($ch);

        return is_array($response) ? $response : [];
    }

}
