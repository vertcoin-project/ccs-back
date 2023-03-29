<?php

namespace Monero;

use Carbon\Carbon;

class WalletVertcoin implements WalletCommon
{
    private $rpc;

    public static function digitsAfterTheRadixPoint() : int
    {
        return 8;
    }

    public function __construct()
    {
        $this->rpc = new jsonRpcBase([  'auth_type' => 'basic',
                                        'username' => env('RPC_USER'),
                                        'password' => env('RPC_PASSWORD'),
                                        'url' => env('RPC_URL')]);
    }

//     public function getPaymentAddress()
//     {
//         return ['address' => $this->rpc->request('getnewaddress')];
//     }

    public function getPaymentAddress()
    {
        $line = '';
        $f = fopen('/path/to/multisigaddresslist.txt', 'r');
        $cursor = -1;
        fseek($f, $cursor, SEEK_END);
        $char = fgetc($f);
        //Trim trailing newline characters in the file
        while ($char === "\n" || $char === "\r") {
           fseek($f, $cursor--, SEEK_END);
           $char = fgetc($f);
        }
        //Read until the next line of the file begins or the first newline char
        while ($char !== false && $char !== "\n" && $char !== "\r") {
           //Prepend the new character
           $line = $char . $line;
           fseek($f, $cursor--, SEEK_END);
           $char = fgetc($f);
        }
        echo $line;

        // load the data and delete the line from the array
        $lines = file('/path/to/multisigaddresslist.txt');
        $last = sizeof($lines) - 1 ;
        unset($lines[$last]);

        // write the new data to the file
        $fp = fopen('/path/to/multisigaddresslist.txt', 'w');
        fwrite($fp, implode('', $lines));
        fclose($fp);

        return ['address' => $line];
    }

    private function decodeTxAmount(string $tx_amount) : int
    {
        $tx_amount = str_replace(',', '.',  $tx_amount);

        $amount = explode('.', $tx_amount);
        if (sizeof($amount) < 1 || sizeof($amount) > 2) {
            throw new \Exception('Failed to decode tx amount ' . $tx_amount);
        }

        $fraction = $amount[1] ?? "";
        if (strlen($fraction) > $this->digitsAfterTheRadixPoint()) {
            throw new \Exception('Failed to decode tx amount, too many digits after the redix point ' . $tx_amount);
        }

        $amount = $amount[0] . str_pad($fraction, $this->digitsAfterTheRadixPoint(), '0');
        $amount = intval($amount);
        if ($amount == 0) {
            throw new \Exception('Failed to convert tx amount to int ' . $tx_amount);
        }

        return $amount;
    }

    public function scanIncomingTransfers($skip_txes = 0)
    {
        return collect($this->rpc->request('listtransactions', ['*', 100, $skip_txes, true], true))->filter(function ($tx) {
            return $tx['category'] == 'receive';
        })->map(function ($tx) {
            return new Transaction(
                $tx['txid'],
                $this->decodeTxAmount($tx['amount']),
                $tx['address'],
                $tx['confirmations'],
                0,
                Carbon::now(),
                0,
                isset($tx['blockhash']) ? $this->blockHeightByHash($tx['blockhash']) : 0
            );
        });
    }

    public function blockHeight() : int
    {
        return $this->rpc->request('getblockcount');
    }

    public function createQrCodeString($address, $amount = null) : string
    {
        return 'vertcoin:' . $address . ($amount ? '?amount=' . $amount : '');
    }

    private function blockHeightByHash($block_hash) : int
    {
        return $this->rpc->request('getblockheader', [$block_hash])['height'];
    }
}
