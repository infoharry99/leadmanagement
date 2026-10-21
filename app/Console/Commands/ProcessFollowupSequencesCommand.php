<?php

namespace App\Console\Commands;

use App\Services\SequenceEngineService;
use Illuminate\Console\Command;

class ProcessFollowupSequencesCommand extends Command
{
    protected $signature = 'nexteck:process-followups';
    protected $description = 'Process due automated follow-up sequences according to schedule and sending windows';

    public function handle(SequenceEngineService $sequenceEngine): int
    {
        $this->info('Starting automated follow-up sequence check...');
        $processed = $sequenceEngine->processDueEnrollments();
        $this->info("Completed: processed {$processed} sequence enrollments.");

        return Command::SUCCESS;
    }
}
