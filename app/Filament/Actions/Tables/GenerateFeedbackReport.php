<?php

namespace App\Filament\Actions\Tables;

use App\Jobs\GenerateFeedbackForm;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\Concerns\InteractsWithRecords;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Unit;
use Spatie\LaravelPdf\Facades\Pdf;

class GenerateFeedbackReport extends BulkAction
{
    use InteractsWithRecords;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('generate-feedback-report');

        $this->label('Generate');

        $this->icon('gmdi-picture-as-pdf');

        $this->action(function ($records){
            GenerateFeedbackForm::dispatch($records);
            // try{
            // }catch(\Exception $e){
            //     Notification::make()
            //         ->title('Failed to generate feedback report')
            //         ->body($e->getMessage())
            //         ->danger()
            //         ->send();
            // }
        });
    }
}
