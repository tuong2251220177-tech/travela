<?php

namespace Tests\Feature;

use Tests\TestCase;

class PaymentValidationTest extends TestCase
{
    public function test_momo_payment_requires_valid_amount(): void
    {
        $response = $this->postJson(route('createMomoPayment'), [
            'tourId' => 1,
            'amount' => 0,
        ]);

        $response->assertStatus(422);
    }
}
