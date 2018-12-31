<?php

namespace App\Console\Commands;

use App\Deposit;
use App\Project;
use Illuminate\Console\Command;
use Monero\Transaction;
use Monero\WalletOld;

class walletNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monero:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks the monero blockchain for transactions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $wallet = new WalletOld();

        $blockheight = $wallet->blockHeight();
        if ($blockheight < 1) {
            $this->error('monero daemon down or wrong port in db ?');

            return;
        }

        // check mempool
        $transactionsMempool = $wallet->scanMempool($blockheight);
        $transactionsMempool->each(function ($transaction) use ($wallet) {
            $this->processPayment($transaction);
        });

        $paymentIDs = $wallet->getPaymentIds();
        if (count($paymentIDs)) {
            // check blockchain
            $transactions = $wallet->scanBlocks($blockheight, $paymentIDs);
            $transactions->each(function ($transaction) use ($wallet) {
                $this->processPayment($transaction);
            });
        }

        $this->updateAllConfirmations($blockheight);
    }

    /**
     * @param Transaction $transaction
     *
     * @return null|void
     */
    public function processPayment(Transaction $transaction)
    {
        // if the deposit exist, no need to try add it again
        if (Deposit::where('tx_id', $transaction->id)->exists()) {
            return null;
        }
        $this->info('amount: '.$transaction->amount / 1000000000000 .' confirmations:'.$transaction->confirmations.' tx_hash:'.$transaction->id);
        $this->info('paymentid: '.$transaction->paymentId);

        $this->createDeposit($transaction);

        $project = Project::where('payment_id', $transaction->paymentId)->first();
        if ($project) {
            // update the project total
        }

        return;
    }

    /**
     * Adds confirmations on for all xmr transactions with confirmations below 50
     *
     * @param int blockheight
     *
     * @return int
     */
    public function updateAllConfirmations($blockheight)
    {
        $count = 0;
        //update all xmr deposit confirmations
        Deposit::where('confirmations', '<', 10)
            ->where('processed', 0)
            ->each(function ($deposit) use ($blockheight, &$count) {
                $this->updateConfirmation($blockheight, $deposit);
                $count++;
            });

        return $count;
    }

    /**
     * Updates the confirmations for the deposit and calls the process method if it is not assigned to a payflow
     *
     * @param $blockheight
     * @param Deposit $deposit
     *
     * @return bool
     */
    public function updateConfirmation($blockheight, Deposit $deposit)
    {
        $diff = $blockheight - $deposit->block_received;
        $deposit->confirmations = $diff;
        $deposit->save();

        return false;
    }

    /**
     * Creates a deposit entry in the deposit table
     *
     * @param Transaction $transaction
     *
     * @return Deposit
     */
    public function createDeposit(Transaction $transaction)
    {
        return Deposit::create([
            'tx_id' => $transaction->id,
            'amount' => $transaction->amount,
            'confirmations' => $transaction->confirmations,
            'payment_id' => $transaction->paymentId,
            'time_received' => $transaction->time_received,
            'block_received' => $transaction->blockHeight,
        ]);

    }

}
