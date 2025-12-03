<?php

namespace App\Filament\Actions\Concerns;

use App\Filament\Forms\FileAttachment;
use App\Models\Dossier;
use Exception;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Support\Facades\Auth;

trait NoteDossier
{
    protected function bootTraitDossierAction(): void
    {
        $this->name('note-dossier');

        $this->label('Note');

        $this->icon('gmdi-edit-note-o');

        $this->slideOver();

        $this->form([
            MarkdownEditor::make('content')
                ->disableToolbarButtons(['attachFiles', 'codeBlock', 'blockquote'])
                ->required(),
            FileAttachment::make(),
        ]);

        $this->successNotificationTitle('Note added');

        $this->failureNotificationTitle('Failed to add note');

        $this->closeModalByClickingAway(false);

        $this->action(function (Dossier $dossier, array $data) {
            try {
                $this->beginDatabaseTransaction();

                $note = $dossier->notes()->create([
                    'content' => $data['content'],
                    'user_id' => Auth::id(),
                ]);

                if (count($data['files']) > 0) {
                    $note->attachment()->create([
                        'files' => $data['files'],
                        'paths' => $data['paths'],
                    ]);
                }

                $this->commitDatabaseTransaction();

                $this->success();
            } catch (Exception) {
                $this->rollbackDatabaseTransaction();

                $this->failure();
            }
        });
    }
}
