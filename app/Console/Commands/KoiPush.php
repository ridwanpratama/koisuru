<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KoiPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'koi-push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically push changes to Git Repository';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Pushing changes...');
        $this->warn('Use Personal Access Token as Password to authenticate with Git. To generate a Personal Access Token: https://github.com/settings/tokens');
        exec('git push -u origin main');
    }
}
