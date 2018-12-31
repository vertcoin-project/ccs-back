<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Monero\WalletOld;

class GenerateAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:addresses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates monero addresses for any merged proposals';

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
        $projects = Project::whereNotNull('filename')->whereNull('payment_id')->where('state', 'FUNDING-REQUIRED')->get();
        $wallet = new WalletOld();
        foreach ($projects as $project) {

            $addressDetails = $wallet->getPaymentAddress();
            $project->address_uri = $wallet->createQrCodeString($addressDetails['address']);
            $project->address = $addressDetails['address'];
            $project->payment_id = $addressDetails['paymentId'];
            Storage::disk('public')->put("/img/qrcodes/{$project->payment_id}.png", $project->generateQrcode());
            $project->qr_code = "img/qrcodes/{$project->payment_id}.png";
            $project->raised_amount = 0;
            $project->save();
        }

    }
}
