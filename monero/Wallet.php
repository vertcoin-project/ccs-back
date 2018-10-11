<?php

namespace Monero;

use App\Project;
use Carbon\Carbon;
use jsonRPCClient;

class Wallet
{
    /**
     * Wallet constructor.
     *
     * @param null $client
     */
    public function __construct($client = null)
    {
        $this->client = $client ?: new jsonRPCClient(env('RPC_URL'));
    }

    /**
     * Gets a Payment address for receiving payments
     *
     * @return array
     *
     * @internal param \Wallet $wallet
     */
    public function getPaymentAddress()
    {

        $integratedAddress = $this->createIntegratedAddress();
        if (!$integratedAddress) {
            return ['address' => 'not valid', 'expiration_time' => 900];
        }
        $project = new Project();
        $project->payment_id = $integratedAddress['payment_id'];
        $project->save();

        return ['address' => $integratedAddress['integrated_address'], 'paymentId' => $integratedAddress['payment_id']];
    }

    /**
     * Returns all balances (locked/unlocked) or exception state for site monitor
     *
     * @return array
     */
    public function balanceSiteMonitor()
    {
        $result = [];
        $balance = $this->client->getbalance();
        if (!$balance) {
            $result['unlocked_balance'] = 'DOWN';
            $result['balance'] = 'DOWN';

            return $result;
        }
        $result['unlocked_balance'] = $balance['unlocked_balance'] / 1000000000000;
        $result['balance'] = $balance['balance'] / 1000000000000;

        return $result;
    }

    /**
     * Returns the actual available and useable balance (unlocked balance)
     *
     * @return float|int|mixed
     */
    public function balance()
    {
        $balance = $this->client->getbalance();
        $result = $balance['unlocked_balance'] / 1000000000000;

        return $result;
    }

    public function mempoolTransfers()
    {
        return $this->client->mempoolTransfers();
    }

    public function bulkPayments($paymentIds)
    {
        $blockBuffer = 10;

        return $this->client->bulk_payments($paymentIds, intval($this->wallet->last_scanned_block_height) - $blockBuffer);
    }

    /**
     * Scans the monero blockchain for transactions for the payment ids
     *
     * @param $blockheight
     * @param $paymentIDs
     *
     * @return array|Transaction
     */
    public function scanBlocks($blockheight, $paymentIDs)
    {
        $response = $this->bulkPayments($paymentIDs);
        $address = $this->getAddress();
        $transactions = [];
        if ($response && isset($response['payments'])) {
            foreach ($response['payments'] as $payment) {
                $transaction = new Transaction(
                    $payment['tx_hash'],
                    $payment['amount'],
                    $address,
                    $blockheight - $payment['block_height'],
                    0,
                    Carbon::now(),
                    $payment['payment_id'],
                    $payment['block_height']
                );
                $transactions[] = $transaction;
            }
        }

        return collect($transactions);
    }

    /**
     * @param $blockheight
     *
     * @return \Illuminate\Support\Collection
     */
    public function scanMempool($blockheight)
    {
        $address = $this->getAddress();
        $transactions = [];
        $response = $this->mempoolTransfers();
        if ($response && isset($response['pool'])) {
            foreach ($response['pool'] as $payment) {
                $transaction = new Transaction(
                    $payment['txid'],
                    $payment['amount'],
                    $address,
                    0,
                    0,
                    Carbon::now(),
                    $payment['payment_id'],
                    $blockheight
                );
                $transactions[] = $transaction;
            }
        }

        return collect($transactions);
    }

    /**
     * Gets the current blockheight of xmr
     *
     * @return int
     */
    public function blockHeight()
    {
        $result = $this->client->getheight();
        if ($result && isset($result['height'])) {
            return $result['height'];
        }

        return 0;
    }

    /**
     * Returns monero wallet address
     *
     * @return string
     */
    public function getAddress()
    {
        $address = $this->client->getaddress();
        if ($address != null) {
            return $address['address'];
        }

        return 'Invalid';
    }

    /**
     * Returns XMR integrated address
     *
     * @return mixed
     */
    public function createIntegratedAddress()
    {
        try {
            return $this->client->make_integrated_address();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param $amount
     * @param $address
     * @param $paymentId
     *
     * @return string
     */
    public function createQrCodeString($amount, $address, $paymentId = ''): string
    {
        // @todo add tx_payment_id support
        // monero payment_id is passed through the address
        return 'monero:'.$address.'?tx_amount='.$amount;
    }

    /**
     * gets all the payment_ids outstanding from the address_pool, we use these to check against the latest mined blocks
     *
     * @return array
     */
    public function getPaymentIds()
    {
        $paymentIds = Project::pluck('payment_id'); //stop scanning for payment_ids after 24h

        return $paymentIds;
    }
}
