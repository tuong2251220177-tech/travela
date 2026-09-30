<?php
namespace Tests\Feature;

use Tests\TestCase;

class TourPriceValidationTest extends TestCase
{
    public function test_decimal_and_negative_prices_are_rejected_before_writing(): void
    {
        $this->withSession(['admin' => true]);
        foreach (['/admin/add-tours', '/admin/edit-tour'] as $url) {
            foreach (['3.590000', '3.590.000', '-3590000'] as $price) {
                $this->postJson($url, ['price_adult' => $price, 'price_child' => '2490000'])
                    ->assertUnprocessable()->assertJsonValidationErrors('price_adult');
            }
            $this->postJson($url, ['price_adult' => '3590000', 'price_child' => '2.490000'])
                ->assertUnprocessable()->assertJsonValidationErrors('price_child');
        }
    }
}
