<?php

namespace App\Console\Commands;

use App\Jobs\AutoQueueRequests;
use Illuminate\Console\Command;

class AutoQueueRequestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-queue-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto queue requests for offices with auto queue settings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        AutoQueueRequests::dispatchSync();
    }
}
