<?php

namespace App\Console\Commands;

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
        $amounts = [];
        $files = Storage::files('ffs-proposals');
        foreach ($files as $file) {
            if (strpos($file,'.md')) {
                $amount['name'] = $file;
                $amount['values'] = $this->getAmountFromText($file);
                $amounts[] = $amount;
            }
        }
        dd($amounts);
    }

    /**
     * Gets the ffs amount requested from a file
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
