<?php

use App\Console\Commands\AutoQueueRequestsCommand as QueueRequestsCommand;
use App\Console\Commands\AutoResolveCompletedRequestsCommand as ResolveRequestsCommand;
use App\Console\Commands\PurgeAttachmentFilesCommand as PurgeFilesCommand;
use Illuminate\Support\Facades\Schedule;
use Laravel\Telescope\Console\PruneCommand as TelescopePruneCommand;

Schedule::command(QueueRequestsCommand::class)->withoutOverlapping()->everyMinute();

Schedule::command(ResolveRequestsCommand::class)->withoutOverlapping()->hourly();

Schedule::command(PurgeFilesCommand::class)->withoutOverlapping()->daily();

Schedule::command(TelescopePruneCommand::class)->everySixHours();
