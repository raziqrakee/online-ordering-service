<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Mail\ReservationStatusMail;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    public function index()
    {
        return Reservation::all();
    }

    public function store(Request $request)
    {
        Log::info('Reservation store request:', $request->all());

        try {
            $request->validate([
                'customer' => 'required|string|max:255',
                'date' => 'required|date',
                'time_slot' => 'required|string',
                'pax' => 'required|integer|min:1|max:6',
                'phone' => 'required|string|max:15',
                'email' => 'required|email|max:255',
                'status' => 'required|string|in:Pending,Confirmed,Cancelled'
            ]);

            $existingReservation = Reservation::where('date', $request->date)
                ->where('time_slot', $request->time_slot)
                ->first();

            if ($existingReservation) {
                return response()->json(['error' => 'Time slot is already booked'], 400);
            }

            $reservation = Reservation::create($request->all());
            return response()->json($reservation, 201);

        } catch (\Exception $e) {
            Log::error('Failed to create reservation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create reservation'], 500);
        }
    }

    public function show($id)
    {
        return Reservation::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);

            $request->validate([
                'customer' => 'string|max:255',
                'date' => 'date',
                'time_slot' => 'string',
                'pax' => 'integer|min:1|max:6',
                'phone' => 'string|max:15',
                'email' => 'email',
                'status' => 'string|in:Pending,Confirmed,Cancelled'
            ]);

            $existingReservation = Reservation::where('date', $request->date)
                ->where('time_slot', $request->time_slot)
                ->where('id', '!=', $id)
                ->first();

            if ($existingReservation) {
                return response()->json(['error' => 'Time slot is already booked'], 400);
            }

            $reservation->update($request->all());

            // Send email notification for any update
            $this->sendStatusEmail($reservation);

            return response()->json($reservation, 200);

        } catch (\Exception $e) {
            Log::error('Failed to update reservation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update reservation'], 500);
        }
    }

    protected function sendStatusEmail($reservation)
    {
        Mail::to($reservation->email)->send(new ReservationStatusMail($reservation));
    }

    public function destroy($id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            $reservation->delete();

            return response()->json(null, 204);

        } catch (\Exception $e) {
            Log::error('Failed to delete reservation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete reservation'], 500);
        }
    }

    public function availableSlots($date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        // Define operation hours based on the day of the week
        $operationHours = [
            0 => ['09:00', '22:00'], // Sunday
            1 => ['09:00', '22:00'], // Monday
            2 => ['09:00', '22:00'], // Tuesday
            3 => ['09:00', '22:00'], // Wednesday
            4 => ['09:00', '23:00'], // Thursday
            5 => ['09:00', '20:00'], // Friday
            6 => ['09:00', '22:00'], // Saturday
        ];

        $hours = $operationHours[$dayOfWeek];
        $startTime = Carbon::createFromFormat('H:i', $hours[0]);
        $endTime = Carbon::createFromFormat('H:i', $hours[1]);

        // Generate time slots
        $slots = [];
        while ($startTime < $endTime) {
            $slots[] = $startTime->format('H:i') . '-' . $startTime->copy()->addHour()->format('H:i');
            $startTime->addHour();
        }

        // Remove already booked slots
        $bookedSlots = Reservation::where('date', $date)->pluck('time_slot')->toArray();
        $availableSlots = array_diff($slots, $bookedSlots);

        // Remove past time slots for today
        $currentDate = Carbon::now()->format('Y-m-d');
        if ($date === $currentDate) {
            $currentTime = Carbon::now()->format('H:i');
            $availableSlots = array_filter($availableSlots, function ($slot) use ($currentTime) {
                $start = explode('-', $slot)[0];
                return $start >= $currentTime;
            });
        }

        return response()->json(array_values($availableSlots));
    }

    public function updateReservationStatus(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            $status = $request->input('status');
            $reservation->status = $status;
            $reservation->save();

            // Send email notification
            Mail::to($reservation->email)->send(new ReservationStatusMail($reservation));

            return response()->json($reservation, 200);

        } catch (\Exception $e) {
            Log::error('Failed to update reservation status: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update reservation status'], 500);
        }
    }
}

