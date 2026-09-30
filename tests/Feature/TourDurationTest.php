<?php
namespace Tests\Feature;

use App\Http\Controllers\admin\DieuKhienQuanLyTour;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TourDurationTest extends TestCase
{
    public function test_duration_counts_both_departure_and_return_days(): void
    {
        $method = new \ReflectionMethod(DieuKhienQuanLyTour::class, 'tourDates');
        $method->setAccessible(true);
        foreach ([['03/10/2026', '05/10/2026', '3 ngày 2 đêm'], ['03/10/2026', '03/10/2026', '1 ngày 0 đêm'], ['30/10/2026', '02/11/2026', '4 ngày 3 đêm']] as [$start, $end, $expected]) {
            $dates = $method->invoke(new DieuKhienQuanLyTour(), Request::create('/', 'POST', ['start_date' => $start, 'end_date' => $end]));
            $this->assertSame($expected, $dates['duration']);
            $this->assertSame($expected, $dates['time']);
        }
    }

    public function test_return_before_departure_is_rejected(): void
    {
        $this->withSession(['admin' => true])->postJson('/admin/add-tours', [
            'price_adult' => 2990000, 'price_child' => 2090000,
            'number' => 30, 'minGuests' => 5,
            'start_date' => '05/10/2026', 'end_date' => '03/10/2026',
        ])->assertUnprocessable()->assertJsonValidationErrors('end_date');
    }
}
