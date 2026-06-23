<?php

namespace App\Console\Commands;

use App\Services\ProtiddhoniVoiceService;
use Illuminate\Console\Command;

/**
 * Cron-friendly batch dispatcher for the voice module. Pushes a small batch of
 * queued (and retryable failed) calls to the Protiddhoni API on each tick — no
 * queue worker required, safe for shared cPanel hosting.
 *
 * Cron: * * * * * php /path/to/artisan voice:dispatch >> /dev/null 2>&1
 */
class VoiceDispatch extends Command
{
    protected $signature = 'voice:dispatch {--limit=25 : Max calls to push this run}';

    protected $description = 'Send a small batch of queued/failed voice calls (retry max 3)';

    public function handle(ProtiddhoniVoiceService $voice): int
    {
        $sent = $voice->processBatch((int) $this->option('limit'));
        $this->info("Processed {$sent} voice call(s).");

        return self::SUCCESS;
    }
}
