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
        $ffs = json_decode(\Storage::get('_data_ffs.json'));
        $ffs[0]->proposals = array_merge($ffs[0]->proposals, $this->getNewProposals());
        $yaml = json_encode($ffs, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        \Storage::put('new.json', $yaml);
    }

    public function getNewProposals()
    {
        $responseProposals = [];
        $proposals = Project::where('gitlab_state', 'opened')->get();
        foreach ($proposals as $proposal) {
            $prop = new stdClass();
            $prop->name = $proposal->title;
            $prop->{'gitlab-url'} = $proposal->gitlab_url;
            $prop->author = $proposal->gitlab_username;
            $prop->date = $proposal->gitlab_created_at->format('F j, Y');
            $responseProposals[] = $prop;
        }
        return $responseProposals;
    }
}
