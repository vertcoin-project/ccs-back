<?php

namespace App\Http\Controllers;

use App\Deposit;
use App\Project;
use Illuminate\Http\Request;
use Monero\Wallet;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FundingController extends Controller
{
    /**
     * Shows all projects
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index()
    {
        $projects = Project::all();
        return view('projects.index')
            ->with('projects', $projects);
    }

    /**
     * Shows the project based on the payment id
     *
     * @param $paymentId
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
        $qrcode = QrCode::format('png')->size(100)->generate($project->uri);
        return view('projects.show')
            ->with('project', $project)
            ->with('contributions', $contributions)
            ->with('percentage', $percentage)
            ->with('qrcode', $qrcode)
            ->with('amount_received', $amountReceived);
    }
}
