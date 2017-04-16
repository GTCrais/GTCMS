<?php

namespace GTCrais\GTCMS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GtcmsInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gtcms:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs GTCMS';

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
		$this->info("Setting up GTCMS...\n");

		if (!$this->verifyNodeJsInstallation()) {
			$this->info("Node.js not found. Aborting installation.");

			return;
		}

		if (!$this->verifyDatabaseConnection()) {
			$this->info('Could not establish database connection. Aborting installation.');

			return;
		}

		$this->publishVendorFiles();
		$this->updatePackageJson();
		$this->installFrontendPackages();
		$this->deleteUserClassFile();
		$this->runMigrations();

		$this->dumpAutoload();

		$this->info("GTCMS setup finished!");
    }

	protected function verifyNodeJsInstallation()
	{
		try {
		    $output = [];
			exec('node -v', $output);

			if (empty($output)) {
				return false;
			}

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	protected function verifyDatabaseConnection()
	{
		try {
			\DB::connection()->getPdo();

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	protected function publishVendorFiles()
	{
		$this->call('gtcms:publish');
	}

	protected function updatePackageJson()
	{
		$packageJson = file_get_contents(base_path('package.json'));
		$packageJson = json_decode($packageJson, true);

		$gtcmsPackageJson = file_get_contents(base_path('gtcms.package.json'));
		$gtcmsPackageJson = json_decode($gtcmsPackageJson, true);

		$packageJson["name"] = $gtcmsPackageJson["name"];
		$packageJson["description"] = $gtcmsPackageJson["description"];

		foreach ($gtcmsPackageJson["devDependencies"] as $dependency => $version) {
			$packageJson["devDependencies"][$dependency] = $version;
		}

		$packageJson = json_encode($packageJson, JSON_PRETTY_PRINT);

		file_put_contents(base_path('package.json'), $packageJson);

		unlink(base_path('gtcms.package.json'));

		$this->info("package.json updated.");
	}

	protected function installBower()
	{
		$output = [];
		exec("npm list --depth=0 -g", $output, $return);

		$package = 'bower';
		$packageInstalled = false;

		foreach ($output as $installedPackage) {
			if (Str::contains($installedPackage, $package . '@')) {
				$packageInstalled = true;
				break;
			}
		}

		if (!$packageInstalled) {
			exec('npm install -g bower', $output);

			$this->info('Bower package manager installed globally.');
		}
	}

	protected function installFrontendPackages()
	{
		$this->info("Installing Node packages. This might take a minute or two...");

		$output = [];
		exec('npm --loglevel=error install', $output);

		foreach ($output as $line) {
			$this->info($line);
		}

		$this->info("Node packages installed.\n");

		$this->info("Installing Bower packages...");
		$output = [];
		exec('bower install', $output);

		foreach ($output as $line) {
			$this->info($line);
		}

		$this->info("\nBower packages installed.\n");

		exec('gulp');
		$this->info('CSS files generated.');
	}

	protected function deleteUserClassFile()
	{
		if (file_exists(app_path('User.php'))) {
			unlink(app_path('User.php'));

			$this->info("Default User class file deleted.");
		}
	}

	protected function runMigrations()
	{
		try {
			$this->call('migrate');

			$this->info('Migrations ran successfully.');
		} catch (\Exception $e) {
			Log::error($e);

			$this->info('Could not run migrations: ' . $e->getMessage());
			$this->info('It\'s possible your database doesn\'t fully support utf8mb4 encoding. Check Laravel\'s error log to see detailed error information.');
		}
	}

	protected function dumpAutoload()
	{
		exec('composer dump-autoload');

		$this->info('Autoloader updated.');
	}
}
