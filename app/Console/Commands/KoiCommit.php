<?php

namespace App\Console\Commands;

use App\Helpers\GitHelper;
use App\Services\GeminiService;
use Illuminate\Console\Command;

class KoiCommit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'koi-commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically commit and push changes to Koi Git Repository';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        exec('git add .');
        $geminiService = new GeminiService();
        $gitHelper = new GitHelper();

        if (!$gitHelper->isRepositoryInitialized()) {
            $this->error("The repository is not initialized. Please run 'koi-init' first.\n");
            exit;
        }

        $changesToSend = $gitHelper->getChangesForAI();
        $isDetailedChanges = $gitHelper->isDetailedChanges();
        $isFirstCommit = $gitHelper->isFirstCommit();
        $commitMessage = $geminiService->generateCommitSummary($changesToSend, $isDetailedChanges, $isFirstCommit);

        $this->output->title('Suggested commit message:');
        $this->info("Commit message: " . $commitMessage);

        $confirmation = $this->choice('Are you sure you want to commit these changes?', ['Yes', 'No'], 0);

        if ($confirmation === 'No') {
            $this->info('Aborting commit');
            exit;
        } else {
            $this->info('Committing changes...');
            exec('git commit -m "' . $commitMessage . '"');
            $this->info('Changes committed ');
        }
    }
}
