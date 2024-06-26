<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KoiInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'koi-init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Koi Git Repository';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initializing Koi Git Repository');

        $isPrivateRepo = $this->choice('Are you using a private Git repository?', ['Yes', 'No'], 1);

        if ($isPrivateRepo === 'Yes') {
            exec('git config --global --unset user.email');
        }

        exec('git config --global --get user.email', $output, $returnCode);
        if ($returnCode !== 0) {
            $email = $this->ask('Enter your email for Git:');
            exec('git config --global user.email "' . $email . '"');
        }

        exec('git config --global --get user.name', $output, $returnCode);
        if ($returnCode !== 0) {
            $name = $this->ask('Enter your name for Git:');
            exec('git config --global user.name "' . $name . '"');
        }

        exec('git config --global init.defaultBranch main');
        exec('git init');
        exec('git branch -m main');

        $remoteUrl = $this->ask('What is your git remote url?');

        exec('git remote add origin ' . $remoteUrl);
        $this->info('Koi Git Repository Initialized');
        $this->info('Run "koi-commit" to commit changes');
    }
}
