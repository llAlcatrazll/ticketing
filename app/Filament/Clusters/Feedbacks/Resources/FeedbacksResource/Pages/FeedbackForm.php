<?php

namespace App\Filament\Clusters\Feedbacks\Resources\FeedbacksResource\Pages;

use App\Filament\Clusters\Feedbacks\Resources\FeedbacksResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Unit;
use Spatie\LaravelPdf\Facades\Pdf;

class FeedbackForm extends ViewRecord
{
    protected static string $resource = FeedbacksResource::class;

    protected static string $view = 'filament.panels.feedback.pages.view-feedback-form';

    public function mount($record): void
    {
        parent::mount($record);

    }

    public function getTitle(): string|Htmlable
    {
        return $this->record->email . ' Feedback';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            Actions\Action::make('export')
                ->label('Download PDF')
                ->icon('gmdi-download')
                ->action(function ($record) {

                    $filename = 'feedback_' . $record->id  . now()->format('Y-m-d') . '.pdf';

                    $pdf = Pdf::view('filament.panels.feedback.feedback-form', ['record' => $record, 'preview' => true])
                        ->margins(10, 10, 10, 10)
                        ->paperSize(8.5, 13, Unit::Inch)
                        ->withBrowsershot(function (Browsershot $browsershot) {
                            return $browsershot
                                ->noSandbox()
                                ->emulateMedia('print')
                                ->portrait()
                                ->timeout(120)
                                ->showBackground();
                        })
                        ->base64();

                    return response()->streamDownload(function() use ($pdf) {
                        echo base64_decode($pdf);
                    }, $filename);
                }),
        ];
    }
}
