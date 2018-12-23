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
                $amount['amount'] = $this->getAmountFromText($file);
                $amounts[] = $amount;
            }
        }
        dd($amounts);
    }

    /**
     * Gets the ffs amount requested from a file
     *
     * @param string $filename
     * @return int
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getAmountFromText($filename = 'additional-gui-dev.md')
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
