<?php

use App\Http\Controllers\Play\CurlingController;
use App\Http\Controllers\Share\ShareController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/staff');
});

Route::get('/staff', [CurlingController::class, 'staffDashboard'])->name('staff.dashboard');

Route::post('/staff/resources/{resource}/start', [CurlingController::class, 'startGame'])->name('staff.resources.start');
Route::post('/staff/sessions/{session}/end', [CurlingController::class, 'endGame'])->name('staff.sessions.end');
Route::post('/staff/sessions/{session}/reset', [CurlingController::class, 'resetGame'])->name('staff.sessions.reset');
Route::post('/staff/sessions/{session}/pause', [CurlingController::class, 'pauseGame'])->name('staff.sessions.pause');
Route::post('/staff/sessions/{session}/resume', [CurlingController::class, 'resumeGame'])->name('staff.sessions.resume');

Route::post('/staff/sessions/{session}/add-time', [CurlingController::class, 'addTime'])->name('staff.sessions.add-time');
Route::get('/staff/customers/export', [CurlingController::class, 'exportCustomersCsv'])->name('staff.customers.export');

Route::get('/play/{resource:slug}', [CurlingController::class, 'tablet'])->name('play.tablet');
Route::post('/play/{session}/setup', [CurlingController::class, 'saveSetup'])->name('play.setup');
Route::post('/play/{session}/start', [CurlingController::class, 'startTimer'])->name('play.start');
Route::post('/play/{session}/score', [CurlingController::class, 'saveScore'])->name('play.score');
Route::post('/play/{session}/emails', [CurlingController::class, 'sendEmails'])->name('play.emails');

Route::get('/share/{session:share_code}', [ShareController::class, 'show'])->name('share.show');