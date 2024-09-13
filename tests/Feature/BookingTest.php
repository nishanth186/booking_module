<?php

namespace Tests\Feature;
use Tests\TestCase;

class BookingTest extends TestCase
{
    protected $facilities = [
        'Clubhouse' => [
            'cost_per_hour' => 500,
            'bookings' => []
        ],
        'Tennis Court' => [
            'cost_per_hour' => 50,
            'bookings' => []
        ]
    ];

    // Helper method to simulate booking
    private function makeBooking($facility, $date, $start_time, $end_time)
    {
        $start = strtotime("$date $start_time");
        $end = strtotime("$date $end_time");

        // Check for double bookings by comparing full timestamps
        foreach ($this->facilities[$facility]['bookings'] as $existing_booking) {
            $existingStart = strtotime("{$existing_booking['date']} {$existing_booking['start_time']}");
            $existingEnd = strtotime("{$existing_booking['date']} {$existing_booking['end_time']}");
            
            // Check for overlapping bookings
            if (!($end <= $existingStart || $start >= $existingEnd)) {
                return ['status' => 409, 'message' => 'Booking Failed, Already Booked'];
            }
        }

        // Add booking and calculate cost
        $this->facilities[$facility]['bookings'][] = [
            'date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time
        ];

        $hours = ($end - $start) / 3600;
        $cost = $hours * $this->facilities[$facility]['cost_per_hour'];

        return ['status' => 200, 'message' => 'Booked', 'cost' => $cost];
    }

    public function testClubhouseBooking()
    {
        $response = $this->makeBooking('Clubhouse', '2024-10-26', '16:00', '22:00');
        
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Booked', $response['message']);
        $this->assertEquals(3000, $response['cost']); // Rs. 500/hour for 6 hours
    }

    public function testTennisCourtBooking()
    {
        $response = $this->makeBooking('Tennis Court', '2024-10-26', '16:00', '20:00');
        
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Booked', $response['message']);
        $this->assertEquals(200, $response['cost']); // Rs. 50/hour for 4 hours
    }

    public function testClubhouseDoubleBooking()
    {
        // First booking
        $this->makeBooking('Clubhouse', '2024-10-26', '16:00', '22:00');

        // Attempt second booking with overlapping times
        $response = $this->makeBooking('Clubhouse', '2024-10-26', '18:00', '20:00');
        
        $this->assertEquals(409, $response['status']);
        $this->assertEquals('Booking Failed, Already Booked', $response['message']);
    }

    public function testClubhouseNonOverlappingBooking()
    {
        // First booking from 10:00 to 12:00
        $this->makeBooking('Clubhouse', '2024-10-26', '10:00', '12:00');

        // Second booking from 12:30 to 14:30, no overlap
        $response = $this->makeBooking('Clubhouse', '2024-10-26', '12:30', '14:30');
        
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Booked', $response['message']);
        $this->assertEquals(1000, $response['cost']); // Rs. 500/hour for 2 hours
    }
}
