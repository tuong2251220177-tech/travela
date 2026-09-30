<?php

namespace Tests\Feature;

use Tests\TestCase;

class TourQuantityValidationTest extends TestCase
{
    public function test_user_cannot_book_more_than_available_tour_quantity(): void
    {
        $response = $this->from('/tour-detail/1')->post(route('create-booking'), [
            'tourId' => 1,
            'numAdults' => 99,
            'numChildren' => 0,
            'fullName' => 'Nguyen Van A',
            'email' => 'a@example.com',
            'tel' => '0901234567',
            'address' => 'HCM',
            'payment' => 'office-payment',
            'totalPrice' => 1000000,
            'payment_hidden' => 'office-payment',
            'agree' => 'on',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/tour-detail/1');
        $this->assertTrue(session()->has('error') || session()->has('errors'));
    }
}
