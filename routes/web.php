<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;

Route::get('/', function () {
    return view('welcome');
});

// MAIL SEND FORM
Route::get('/email', [MailController::class, 'index']);      // show form
Route::post('/send-email', [MailController::class, 'send']); // send mail

// ADMIN PANEL ROUTES
Route::get('/mail', [MailController::class, 'list']);                // list mails
Route::get('/mail/view/{id}', [MailController::class, 'view']);      // show single mail details

Route::get('/mail/delete/{id}', [MailController::class, 'delete']);  // soft delete
Route::get('/mail/restore/{id}', [MailController::class, 'restore']); // restore
Route::get('/mail/force-delete/{id}', [MailController::class, 'forceDelete']); // permanent delete

Route::get('/mail/status/{id}', [MailController::class, 'changeStatus']); // active/inactive
