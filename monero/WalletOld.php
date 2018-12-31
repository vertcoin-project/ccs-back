<?php

namespace Monero;

use App\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WalletOld
{
    /**
     * WalletOld constructor.
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
     * @internal param \WalletOld $wallet
     */
    public function getPaymentAddress()
    {

        $integratedAddress = $this->createIntegratedAddress();
        if (!$integratedAddress) {
            return ['address' => 'not valid'];
        }

        return ['address' => $integratedAddress['integrated_address'], 'paymentId' => $integratedAddress['payment_id']];
    }

    /**
     * Returns the actual available and useable balance (unlocked balance)
     *
     * @return float|int|mixed
     */
    public function balance()
    {
        return $this->client->balance();
    }

    public function mempoolTransfers()
    {
        return $this->client->incomingTransfers();
    }

    public function bulkPayments($paymentIds)
    {
        $blockBuffer = 10;

        return $this->client->payments($paymentIds, intval($this->wallet->last_scanned_block_height) - $blockBuffer);
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
        return $this->client->blockHeight();
    }

    /**
     * Returns monero wallet address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->client->address();
    }

    /**
     * Returns XMR integrated address
     *
     * @return mixed
     */
    public function createIntegratedAddress()
    {
        return $this->client->createIntegratedAddress();
    }

    /**
     * @param $amount
     * @param $address
     * @param $paymentId
     *
     * @return string
     */
    public function createQrCodeString($address, $amount = null, $paymentId = null): string
    {
        return $this->client->createUri($address, $amount, $paymentId);
    }

    /**
     * gets all the payment_ids outstanding from the address_pool, we use these to check against the latest mined blocks
     *
     * @return Collection
     */
    public function getPaymentIds()
    {

        return Project::pluck('payment_id'); //stop scanning for payment_ids after 24h
    }
}
