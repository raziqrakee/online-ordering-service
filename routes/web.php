<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// routes/web.php
use Illuminate\Support\Facades\Mail;

Route::get('/test-email', function () {
    $data = [
        'name' => 'Test Name',
        'email' => 'test@example.com',
        'message' => 'This is a test message.',
    ];

    try {
        Mail::send('emails.catering_service', ['data' => $data], function ($message) {
            $message->to('raziq.ziq12@gmail.com')
                    ->subject('Test Email');
        });

        return 'Email sent successfully!';
    } catch (\Exception $e) {
        return 'Error sending email: ' . $e->getMessage();
    }
});

