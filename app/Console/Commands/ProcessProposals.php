<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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
        $projects = Project::whereNull('filename');
        $details = [];
        $files = Storage::files('ffs-proposals');
        foreach ($files as $file) {
            if (strpos($file,'.md')) {
                $detail['name'] = $file;
                $detail['values'] = $this->getAmountFromText($file);
                $details[] = $detail['values']['title'];
                $project = $projects->where('title', $detail['values']['title'])->first();
                if ($project) {
                    $project->filename = $file;
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
        $input = Storage::get($filename);
        $lines = preg_split('/\r\n|\r|\n/', $input);
        $values = [];
        $flag = false;
        $isPayoutLine = false;

        foreach($lines as $line) {
            if ($line === '---') {
                if ($flag === true) {
                    break;
                }
                $flag = true;
                continue;
            }
            $details = explode(':', $line);
            if (count($details) < 2) {
                continue;
            }
            if ($details[0] === 'payouts') {
                $isPayoutLine = true;
                continue;
            }
            if ($isPayoutLine) {
                $name = trim(str_replace('-','', $details[0]));
                $values['payouts'][][$name] = ltrim($details[1]);
                continue;
            }
            $values[$details[0]] = ltrim($details[1]);
        }
        return $values;
    }
}
