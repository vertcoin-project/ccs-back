<?php

namespace App\Http\Controllers;

use App\Deposit;
use App\Project;
use Illuminate\Http\Request;

class FundingController extends Controller
{
    /**
     * Generates the interstitial
     *
     * @param $invoice
     * @param null $currency
     * @param bool $moneroOnly
     * @param bool $shopifySite
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function show($paymentId)
    {
        $project = Project::where('payment_id', $paymentId)->first();
        return view('ffs')
            ->with('amount_received', $project->deposts->sum('amount'));
    }
}
