<?php 
namespace Megaads\Localization\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Artisan;

class PublishConfigCommand extends AbtractCommand {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'localization:config:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish localization config file';

    public function getOptions() {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
        ];
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $force = $this->option('force');
        $this->response([
            'status' => 'successful',
            'message' => 'Publish localization config file'
        ]);
        Artisan::call('vendor:publish', [
            '--provider' => 'Megaads\Localization\Providers\LocalizationServiceProvider',
            '--tag' => 'config',
            '--force' => $force
        ]);
        $output = Artisan::output();
        $this->info($output);
    }

}