<?php

namespace App\Console\Commands;

use App\Project;
use GitLab\Connection;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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
     *
     * @return mixed
     */
    public function handle()
    {
        $connection = new Connection(new Client());
        $projects = $connection->mergeRequests('all');

        foreach ($projects as $project) {
            // create requests that are still pending
            $project = Project::firstOrNew([
                'merge_request_id' => $project->id
            ],[
                'title' => $project->title,
                'state' => $project->state
            ]);
            // check if there is a payment_id and an address
            // check if the amount field has been supplied
            $project->save();
        }


        // id, title, state, username,
        // save merge requests
        // compare to current merge requests
        // if merge request is missing is it closed or merged
        return;
    }

    /**
     * Gets the ffs amount requested from a file
     *
     * @param string $filename
     * @return int
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getAmountFromText($filename = 'test-ffs.md')
    {
        $input = Storage::get($filename);
        $lines = preg_split('/\r\n|\r|\n/', $input);
        foreach($lines as $line) {
            $line = str_replace(' ','', $line);
            $details = explode(':', $line);
            if ($details[0] === 'amount') {
                return $details[1];
            }
        }
        return 0;
    }
}
