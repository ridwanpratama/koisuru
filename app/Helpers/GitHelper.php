<?php

namespace App\Helpers;

use Exception;

class GitHelper
{
    private $repoPath;
    private $stagedFiles = [];
    private $stagedFilesContent = [];
    private $tokenLimit;
    private $isDetailedChanges = true;

    public function __construct($repoPath = null, $tokenLimit = 4000)
    {
        $this->repoPath = $repoPath ?: getcwd();
        $this->tokenLimit = $tokenLimit;
        $this->loadStagedFiles();
    }

    private function loadStagedFiles()
    {
        $this->stagedFiles = $this->executeGitCommand('git diff --cached --name-only');
        foreach ($this->stagedFiles as $file) {
            $this->stagedFilesContent[$file] = $this->executeGitCommand("git show :\"$file\"");
        }
    }

    private function getFormattedChanges()
    {
        $formattedChanges = [];
        foreach ($this->stagedFilesContent as $file => $content) {
            $formattedChanges[] = "File: $file\n\nContent:\n" . implode("\n", $content);
        }
        return implode("\n\n---\n\n", $formattedChanges);
    }

    private function getChangesSummary()
    {
        $summary = "Changes summary (token limit exceeded):\n\n";
        $summary .= "Files changed:\n";
        foreach ($this->stagedFiles as $file) {
            $summary .= "- $file\n";
        }
        $summary .= "\nTotal files changed: " . count($this->stagedFiles);
        return $summary;
    }

    private function estimateTokens($text)
    {
        return str_word_count($text) + preg_match_all('/[^\w\s]/', $text);
    }

    private function executeGitCommand($command)
    {
        $currentDir = getcwd();
        chdir($this->repoPath);

        $output = [];
        $returnValue = 0;
        exec($command . " 2>/dev/null", $output, $returnValue);

        chdir($currentDir);

        return $output;
    }

    public function getChangesForAI()
    {
        $detailedChanges = $this->getFormattedChanges();
        if ($this->estimateTokens($detailedChanges) <= $this->tokenLimit) {
            return $detailedChanges;
        }

        $this->isDetailedChanges = false;
        return $this->getChangesSummary();
    }

    public function isDetailedChanges()
    {
        return $this->isDetailedChanges;
    }

    public function isFirstCommit()
    {
        if (!is_dir($this->repoPath . '/.git')) {
            throw new Exception("Not a git repository");
        }

        $output = $this->executeGitCommand('git rev-list --all --max-count=1');
        return empty($output); 
    }

    public function isRepositoryInitialized()
    {
        try {
            $this->executeGitCommand('git rev-parse --is-inside-work-tree');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
