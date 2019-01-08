<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;
use stdClass;
use Symfony\Component\Yaml\Yaml;

class UpdateSiteProposals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ffs:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the files required for jeykll site';

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
     */
    public function handle()
    {
        $response = [
            $this->ideaProposals(),
            $this->fundingRequiredProposals(),
            $this->workInProgressProposals(),
        ];
        $json = json_encode($response, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        \Storage::put('ffs.json', $json);
    }

    public function ideaProposals()
    {
        $group = new stdClass();
        $group->stage = 'Ideas';
        $responseProposals = [];
        $proposals = Project::where('gitlab_state', 'opened')->where('state', 'IDEA')->get();
        foreach ($proposals as $proposal) {
            $prop = new stdClass();
            $prop->name = $proposal->title;
            $prop->{'gitlab-url'} = $proposal->gitlab_url;
            $prop->author = $proposal->gitlab_username;
            $prop->date = $proposal->gitlab_created_at->format('F j, Y');
            $responseProposals[] = $prop;
        }
        $group->proposals = $responseProposals;
        return $group;
    }

    public function fundingRequiredProposals()
    {
        $group = new stdClass();
        $group->stage = 'Funding Required';
        $responseProposals = [];
        $proposals = Project::where('gitlab_state', 'merged')->where('state', 'FUNDING-REQUIRED')->get();
        foreach ($proposals as $proposal) {
            $prop = new stdClass();
            $prop->name = $proposal->title;
            $prop->{'gitlab-url'} = $proposal->gitlab_url;
            $prop->{'local-url'} = '#';
            $prop->{'donate-url'} = url("projects/{$proposal->payment_id}/donate");
            $prop->percentage = $proposal->percentage_funded;
            $prop->amount = $proposal->target_amount;
            $prop->{'amount-funded'} = $proposal->amount_received;
            $prop->author = $proposal->gitlab_username;
            $prop->date = $proposal->gitlab_created_at->format('F j, Y');
            $responseProposals[] = $prop;
        }
        $group->proposals = $responseProposals;
        return $group;
    }

    public function workInProgressProposals()
    {
        $group = new stdClass();
        $group->stage = 'Work in Progress';
        $responseProposals = [];
        $proposals = Project::where('gitlab_state', 'merged')->where('state', 'WORK-IN-PROGRESS')->get();
        foreach ($proposals as $proposal) {
            $prop = new stdClass();
            $prop->name = $proposal->title;
            $prop->{'gitlab-url'} = $proposal->gitlab_url;
            $prop->{'local-url'} = '#';
            $prop->milestones = 0;
            $prop->{'milestones-completed'} = 0;
            $prop->{'milestones-percentage'} = 0;
            $prop->percentage = $proposal->percentage_funded;
            $prop->amount = $proposal->target_amount;
            $prop->{'amount-funded'} = $proposal->amount_received;
            $prop->author = $proposal->gitlab_username;
            $prop->date = $proposal->gitlab_created_at->format('F j, Y');
            $responseProposals[] = $prop;
        }
        $group->proposals = $responseProposals;
        return $group;

    }
}
