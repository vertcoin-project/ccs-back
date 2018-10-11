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
        if (!$project) {
            abort(404);
        }
        $contributions = $project->deposits->count();
        $amountReceived = $project->deposits->sum('amount');
        $percentage = round($amountReceived / $project->target_amount * 100);
        return view('ffs')
            ->with('project', $project)
            ->with('contributions', $contributions)
            ->with('percentage', $percentage)
            ->with('amount_received', $amountReceived);
    }
}
