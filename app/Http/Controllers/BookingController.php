<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class BookingController
 * Handles booking functionalities including booking, listing, and clearing session data.
 *
 * @package App\Http\Controllers
 */
class BookingController extends Controller
{
    /**
     * The facilities array with available facilities and their rates.
     *
     * @var array
     */
    private array $facilities;

    /**
     * BookingController constructor.
     * Initializes the facilities array with predefined rates and times.
     */
    public function __construct()
    {
        $this->facilities = [
            'Clubhouse' => [
                '10:00' => ['end' => '16:00', 'rate' => 100],
                '16:00' => ['end' => '22:00', 'rate' => 500],
            ],
            'Tennis Court' => ['rate' => 50],
        ];
    }

    /**
     * Handles the booking request.
     * Validates the input and processes the booking if valid.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function book(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'facility' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $validated = $validator->validated();

        $response = $this->processBooking(
            $validated['facility'],
            $validated['date'],
            $validated['start_time'],
            $validated['end_time']
        );

        return response()->json($response);
    }

    /**
     * Processes the booking by checking for overlaps and calculating the cost.
     *
     * @param string $facility
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @return array
     */
    private function processBooking(string $facility, string $date, string $startTime, string $endTime): array
    {
        if (!isset($this->facilities[$facility])) {
            return [
                'success' => false,
                'message' => 'Facility Not Found',
            ];
        }

        $start = strtotime("$date $startTime");
        $end = strtotime("$date $endTime");

        if ($start >= $end) {
            return [
                'success' => false,
                'message' => 'Invalid Time Range',
            ];
        }

        $bookings = session('bookings', []);

        foreach ($bookings as $booking) {
            $bookingStart = strtotime("{$booking['date']} {$booking['start_time']}");
            $bookingEnd = strtotime("{$booking['date']} {$booking['end_time']}");

            if ($booking['facility'] === $facility &&
                $booking['date'] === $date &&
                !($end <= $bookingStart || $start >= $bookingEnd)
            ) {
                return [
                    'success' => false,
                    'message' => 'Already Booked',
                ];
            }
        }

        $cost = $this->calculateCost($facility, $start, $end);

        if ($cost === null) {
            return [
                'success' => false,
                'message' => 'Invalid Time Range',
            ];
        }

        $bookings[] = [
            'facility' => $facility,
            'date' => $date,
            'start_time' => date('H:i', $start),
            'end_time' => date('H:i', $end),
            'cost' => $cost,
        ];
        session(['bookings' => $bookings]);

        return [
            'success' => true,
            'message' => 'Booked',
            'cost' => $cost,
        ];
    }

    /**
     * Calculates the cost of booking based on the facility and time.
     *
     * @param string $facility
     * @param int $start
     * @param int $end
     * @return int|null
     */
    private function calculateCost(string $facility, int $start, int $end): ?int
    {
        if (!isset($this->facilities[$facility])) {
            return null;
        }

        $totalCost = 0;
        $current = $start;

        if (isset($this->facilities[$facility]['10:00'])) {
            while ($current < $end) {
                $hour = date('H:i', $current);
                $nextHour = strtotime('+1 hour', $current);

                if ($hour >= '10:00' && $hour < '16:00') {
                    $rate = $this->facilities[$facility]['10:00']['rate'];
                } elseif ($hour >= '16:00' && $hour < '22:00') {
                    $rate = $this->facilities[$facility]['16:00']['rate'];
                } else {
                    $rate = 0;
                }

                $totalCost += $rate;
                $current = $nextHour;
            }
        } else {
            $hours = ($end - $start) / 3600;
            $totalCost = $hours * $this->facilities[$facility]['rate'];
        }

        return $totalCost;
    }

    /**
     * Lists all bookings from the session.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(): \Illuminate\Http\JsonResponse
    {
        $bookings = session('bookings', []);

        return response()->json([
            'success' => true,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Clears all session data related to bookings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearAllSession(): \Illuminate\Http\JsonResponse
    {
        session()->flush();

        return response()->json([
            'success' => true,
            'message' => 'All session data cleared',
        ]);
    }
}