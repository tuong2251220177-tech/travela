<?php

namespace Tests\Feature;

use Tests\TestCase;

class SearchValidationTest extends TestCase
{
    public function test_unknown_destination_returns_validation_error(): void
    {
        $this->getJson('/search?destination=unknown')->assertStatus(422)->assertJsonValidationErrors('destination');
    }

    public function test_invalid_calendar_date_returns_validation_error(): void
    {
        $this->getJson('/search?destination=dn&start_date=31%2F02%2F2026')->assertStatus(422)->assertJsonValidationErrors('start_date');
    }

    public function test_end_date_cannot_precede_start_date(): void
    {
        $this->getJson('/search?destination=dn&start_date=20%2F09%2F2026&end_date=19%2F09%2F2026')->assertStatus(422)->assertJsonValidationErrors('end_date');
    }
}
