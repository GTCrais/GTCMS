<?php

namespace GTCrais\GTCMS\Console\Commands;

use Illuminate\Console\Command;

class GtcmsPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gtcms:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes all required vendor package files';

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
		$this->call('vendor:publish', [
			'--provider' => 'GTCrais\GTCMS\GtcmsServiceProvider',
			'--force' => true
		]);

		$this->callSilent('vendor:publish', [
			'--provider' => 'Unisharp\Laravelfilemanager\LaravelFilemanagerServiceProvider',
			'--tag' => 'lfm_public'
		]);

		$this->info("All required vendor files published.");
    }
}
