<?php

use App\Http\Controllers\AttachmentController;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('test', fn (Request $request) => $request->expectsJson() ? response()->json('Hello World') : 'Hello World');

Route::get('attachments/{attachment}/{name}', AttachmentController::class)->name('file.attachment')->where('name', '.*');

Route::get('/feedback/form/print/{record}', function ($recordId) {
    $record = \App\Models\Feedback::findOrFail($recordId);
    return view('filament.panels.feedback.feedback-form', ['record' => $record, 'preview' => true]);
})->name('feedback.form.print');

Route::get('feedbackform', function(){
    $record = Feedback::first();
    return view('filament.panels.feedback.feedback-form', ['record' => $record]);
});
