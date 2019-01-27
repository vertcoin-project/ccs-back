<?php

namespace Monero;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Class jsonRPCClient
 * JSON 2.0 RPC Client for cryptocurrency wallet
 */
class jsonRPCClient implements Contracts\WalletManager
{

    /** @var string */
    private $username = 'test2';

    /** @var string */
    private $password = 'test2';

    /** @var string  */
    private $url = 'http://127.0.0.1:28080/json_rpc';

    /** @var Client|null  */
    private $client;

    /**
     * JsonRPCClient constructor.
     * @param array $options
     * @param null $client
     */
    public function __construct($options, $client = null)
    {
        $this->username = $options['username'] ?? $this->username;
        $this->password = $options['password'] ?? $this->password;
        $this->url = $options['url'] ?? $this->url;

        if (empty($client)) {
            $client = new Client([
                'base_uri' => $this->url,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);
        }

        $this->client = $client;
    }

    /**
     * Gets the balance
     *
     * @return int the overall value after inputs unlock
     */
    public function balance() : int
    {
        $response = $this->request('get_balance');
        return $response['balance'];
    }

    /**
     * Gets the unlocked balance
     *
     * @return int the spendable balance
     */
    public function unlockedBalance() : int
    {
        $response = $this->request('get_balance');
        return $response['unlocked_balance'];
    }

    /**
     * Gets the primary address
     *
     * @return string wallets primary address
     */
    public function address() : string
    {
        $response = $this->request('get_address');
        return $response['address'];
    }

    /**
     * Gets the current block height
     *
     * @return int block height
     */
    public function blockHeight() : int
    {
        $response = $this->request('get_height');
        return $response['height'];
    }
    /**
     * Creates a new integrated address
     *
     * @return array ['integrated_address', 'payment_id']
     */
    public function createIntegratedAddress() : array
    {
        $response = $this->request('make_integrated_address');
        return $response;
    }

    /**
     * Gets any incoming transactions
     *
     * @return array
     */
    public function incomingTransfers($min_height = 0) : array
    {
        $response = $this->request('get_transfers', ['pool' => true, 'in' => true, 'min_height' => $min_height, 'filter_by_height' => $min_height > 0 ? true : false]);

        return $response;
    }

    /**
     * Checks for any payments made to the paymentIds
     *
     * @param array     $paymentIds list of payment ids to be searched for
     * @param int       $minHeight  the lowest block the search should start with
     *
     * @return array    payments received since min block height with a payment id provided
     */
    public function payments($paymentIds, $minHeight) : array
    {
        $response = $this->request('get_bulk_payments', ['payment_ids' => $paymentIds, 'min_block_height' => $minHeight]);

        return $response;
    }

    /**
     * creates a uri for easier wallet parsing
     *
     * @param string    $address    address comprising of primary, sub or integrated address
     * @param string    $paymentId  payment id when not using integrated addresses
     * @param int       $amount     atomic amount requested
     *
     * @return string the uri string which can be used to generate a QR code
     */
    public function createUri($address, $paymentId = null, $amount = null) : string
    {
        $response = $this->request('make_uri', ['address' => $address, 'amount' => $amount, 'payment_id' => $paymentId]);

        return $response['uri'];
    }

    /**
     * creates a random 64 char payment id
     *
     * @return string
     */
    public function generatePaymentId(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    /**
     * Sets up the request data body
     *
     * @param string    $method name of the rpc command
     * @param array     $params associative array of variables being passed to the method
     *
     * @return false|string will return a json string or false
     */
    private function preparePayload($method, $params)
    {
        $payload = [
            'jsonrpc' => '2.0',
            'id' => '0',
            'method' => $method,
            'params' => $params,
        ];
        return json_encode($payload);
    }

    /**
     * Send off request to rpc server
     *
     * @param string    $method name of the rpc command
     * @param array     $params associative array of variables being passed to the method
     *
     * @return mixed the rpc query result
     *
     * @throws \RuntimeException
     */
    protected function request(string $method, array $params = [])
    {
        $payload = $this->preparePayload($method, $params);

        try {
            $response = $this->client->request('POST', '',[
                'auth' => [$this->username, $this->password, 'digest'],
                'body' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $body = $response->getBody();
        } catch (GuzzleException $exception) {
            Log::error($exception);
            throw new \RuntimeException('Connection to node unsuccessful');
        }
        $result = json_decode((string) $body, true);
        if (isset($result['error'])) {

            throw new \RuntimeException($result['error']['message']);
        }
        return $result['result'];
    }

}
