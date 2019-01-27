<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Yaml;

class ProcessProposals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proposal:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for changes to proposals';

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
        $details = [];
        $files = Storage::files('ffs-proposals');
        foreach ($files as $file) {
            if (strpos($file,'.md')) {
                $detail['name'] = $file;
                $detail['values'] = $this->getAmountFromText($file);
                $details[] = $detail['values']['title'];
                $project = Project::where('title', $detail['values']['title'])->first();
                if ($project) {
                    $project->filename = $file;
                    if ($project->state === 'IDEA') {
                        $project->state = 'FUNDING-REQUIRED';
                    }
                    $project->target_amount = $detail['values']['amount'];
                    $project->save();
                }
            }
        }
        foreach ($details as $det) {
            $this->line($det);
        }

    }

    /**
     * Gets the ffs variables out the top of the file
     *
     * @param string $filename
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getAmountFromText($filename = 'additional-gui-dev.md')
    {
        $contents = preg_split('/\r?\n?---\r?\n/m', Storage::get($filename));
        if (sizeof($contents) < 3) {
            throw new \Exception("Failed to parse proposal, can't find YAML description surrounded by '---' lines");                        
        }
        return Yaml::parse($contents[1]);
    }
}
