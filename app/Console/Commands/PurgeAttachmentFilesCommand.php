<?php

namespace App\Console\Commands;

use App\Jobs\PurgeAttachmentFiles;
use Illuminate\Console\Command;

class PurgeAttachmentFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purge-attachment-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge attachment files from the system storage to save space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PurgeAttachmentFiles::dispatchSync();
    }
}
