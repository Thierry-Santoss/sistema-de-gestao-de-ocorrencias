<?php

use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\OccurrenceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.key'])->group(function () {
    Route::post('/integrations/occurrences', [IntegrationController::class, 'store']);

    Route::get('/occurrences', [OccurrenceController::class, 'index']);
    Route::get('/occurrences/{id}', [OccurrenceController::class, 'show']);

    Route::post('/occurrences/{id}/start', [OccurrenceController::class, 'start']);
    Route::post('/occurrences/{id}/resolve', [OccurrenceController::class, 'resolve']);
    Route::post('/occurrences/{id}/cancel', [OccurrenceController::class, 'cancel']);

    Route::post('/occurrences/{id}/dispatches', [OccurrenceController::class, 'addDispatch']);

    Route::patch('/dispatches/{id}/status', [OccurrenceController::class, 'updateDispatchStatus']);

});
