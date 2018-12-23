<?php

namespace App\Console\Commands;

use App\Project;
use GitLab\Connection;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class fetchMergeRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gitlab:fetch-proposals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch all the proposal merge requests from gitlab';

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
        $connection = new Connection(new Client());
        $projects = $connection->mergeRequests('all');

        foreach ($projects as $project) {
            $title = str_replace('[IDEA]','',$project->title);
            // create requests that are still pending
            $project = Project::firstOrNew([
                'merge_request_id' => $project->id,
            ],[
                'title' => $title,
                'gitlab_state' => $project->state,
                'gitlab_username' => $project->author->username,
                'gitlab_url' => $project->web_url,
            ]);
            $project->save();
        }
    }
}
