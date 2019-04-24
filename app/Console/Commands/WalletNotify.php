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

        $min_height = Deposit::max('block_received');
        $transactions = $wallet->scanIncomingTransfers(max($min_height, 50) - 50);
        $transactions->each(function ($transaction) use ($wallet) {
            $this->processPayment($transaction);
        });

        $this->updateAllConfirmations($blockheight);
    }

    /**
     * @param Transaction $transaction
     *
     * @return null|void
     */
    public function processPayment(Transaction $transaction)
    {
        $deposit = Deposit::where('tx_id', $transaction->id)->where('subaddr_index', $transaction->subaddr_index)->first();
        if ($deposit) {
            if ($deposit->block_received == 0) {
                $deposit->block_received = $transaction->block_height;
                $deposit->save();
            }
            return null;
        }

        $this->info('amount: '.$transaction->amount / 1000000000000 .' confirmations:'.$transaction->confirmations.' tx_hash:'.$transaction->id);
        $this->info('subaddr_index: '.$transaction->subaddr_index);

        $this->createDeposit($transaction);

        $project = Project::where('subaddr_index', $transaction->subaddr_index)->first();
        if ($project) {
            // update the project total
            $project->raised_amount = $project->raised_amount + $transaction->amount * 1e-12;
            $project->save();
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
        Deposit::where('confirmations', '<', 50)
            ->where('block_received', '>', 0)
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
        $deposit = new Deposit;
        $deposit->tx_id = $transaction->id;
        $deposit->amount = $transaction->amount;
        $deposit->confirmations = $transaction->confirmations;
        $deposit->subaddr_index = $transaction->subaddr_index;
        $deposit->time_received = $transaction->time_received;
        $deposit->block_received = $transaction->block_height;
        $deposit->save();
    }

}
