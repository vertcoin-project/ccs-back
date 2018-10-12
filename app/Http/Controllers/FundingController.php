<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Project;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FundingController extends Controller
{
    /**
     * Shows all projects
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $projects = Project::paginate(15);
        // If the request has header `Accept: */json`, return JSON
        if ($request->wantsJson())
        {
            return ProjectResource::collection($projects);
        }
        return view('projects.index')
            ->with('projects', $projects);
    }

    /**
     * Shows the project based on the payment id
     *
     * @param $paymentId
     *
     * @return ProjectResource|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, $paymentId)
    {
        $project = Project::where('payment_id', $paymentId)->first();
        if (!$project) {
            abort(404);
        }
        if ($request->wantsJson())
        {
            return new ProjectResource($project);
        }
        $qrcode = QrCode::format('png')->size(100)->generate($project->uri);
        return view('projects.show')
            ->with('project', $project)
            ->with('qrcode', $qrcode);
    }
}
