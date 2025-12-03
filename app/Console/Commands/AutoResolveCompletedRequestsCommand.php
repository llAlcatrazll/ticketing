<?php

namespace App\Console\Commands;

use App\Jobs\AutoResolveCompletedRequests;
use Illuminate\Console\Command;

class AutoResolveCompletedRequestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-resolve-completed-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically resolve completed requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        AutoResolveCompletedRequests::dispatchSync();
    }
}
