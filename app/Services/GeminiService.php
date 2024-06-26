<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;

class GeminiService
{
    public function generateCommitSummary($changes, $isDetailedChanges, $isFirstCommit = false)
    {
        $prompt = $this->createPrompt($changes, $isDetailedChanges, $isFirstCommit);
        $result = Gemini::geminiPro()->generateContent($prompt);
        return $this->formatCommitSummary($result->text());
    }

    private function createPrompt($changes, $isDetailedChanges, $isFirstCommit)
    {
        $basePrompt = <<<EOT
            Generate a concise Git commit summary based on the following changes. 

            Guidelines:
            1. The summary should be a single line, 50 characters or less
            2. Use the imperative mood ("Add feature" not "Added feature")
            3. Be specific and descriptive
            4. Start with a capital letter and do not end with a period
            5. Focus on the main purpose or impact of the changes

            EOT;

        if ($isFirstCommit) {
            $basePrompt .= "\n6. This is the first commit for the project, so consider using 'Initial commit' or 'First commit'.";
        }

        if ($isDetailedChanges) {
            $prompt = $basePrompt . "\n\nCode changes:\n\n$changes\n\nGenerate the commit summary:";
        } else {
            $prompt = $basePrompt . "\n\nSummary of changes:\n\n$changes\n\nGenerate the commit summary:";
        }

        if ($isFirstCommit) {
            $prompt .= "\nRemember, this is the first commit for the project.";
        }

        return $prompt;
    }

    private function formatCommitSummary($rawSummary)
    {
        $summary = trim($rawSummary);
        if (strlen($summary) > 100) {
            $summary = substr($summary, 0, 99) . '…';
        }
        return $summary;
    }
}
