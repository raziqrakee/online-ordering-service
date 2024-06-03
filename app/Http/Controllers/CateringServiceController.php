<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CateringServiceController extends Controller
{
    public function submitForm(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        $data = [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'message' => $validatedData['message'],
        ];

        // Log data to check if it is correctly validated and received
        Log::info('Catering Service Request Data: ', $data);

        try {
            Mail::send('emails.catering_service', ['data' => $data], function ($message) {
                $message->to('raziq.ziq12@gmail.com')
                        ->subject('New Catering Service Request');
            });

            // Log successful email sending
            Log::info('Email sent successfully to raziq.ziq12@gmail.com');
        } catch (\Exception $e) {
            // Log any errors during the email sending process
            Log::error('Error sending email: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Request submitted successfully'], 200);
    }
}
