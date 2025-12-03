<?php

namespace App\Filament\Forms;

use App\Models\Attachment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;

class FileAttachment extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->collapsed();

        $this->collapsible();

        $this->icon('gmdi-attach-file-o');

        $this->heading('Attachments');

        $this->compact();

        $this->schema(fn (?Model $record) => [
            FileUpload::make('files')
                ->hiddenLabel()
                ->storeFileNamesIn('paths')
                ->disk('local')
                ->directory('attachments')
                ->visibility('private')
                ->previewable(false)
                ->multiple()
                ->maxFiles(12)
                ->moveFiles()
                ->maxSize(1024 * 12)
                ->downloadable()
                ->rule('clamav')
                ->hint('You can upload up to 12 files, each with a maximum size of 12MB.')
                ->helperText(function () use ($record) {
                    $html = array_key_exists($record?->exists ? $record::class : $this->getModel(), Attachment::$purgable) ? <<<'HTML'
                        <span class="text-sm text-custom-600 dark:text-custom-400" style="--c-400:var(--warning-400);--c-600:var(--warning-600);">
                            Attachments are <b>deleted</b> after a certain period of time.
                            Please <b>secure your files</b> somewhere else first if you intend to keep them long-term.
                        </span>
                    HTML : null;

                    return str($html)->toHtmlString();
                }),
        ]);
    }
}
