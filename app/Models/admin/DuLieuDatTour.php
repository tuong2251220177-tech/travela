<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DuLieuDatTour extends Model
{
    use HasFactory;

    protected $table = 'tbl_booking';

    public function expireOverdueDeposits(): int
    {
        if (!Schema::hasColumn($this->table, 'depositDueAt')) {
            return 0;
        }

        $expired = DB::table($this->table)
            ->whereIn('bookingStatus', ['p', 'b', 'y'])
            ->where('depositStatus', 'pending')
            ->whereNotNull('depositDueAt')
            ->where('depositDueAt', '<', now())
            ->pluck('bookingId');

        $count = 0;
        foreach ($expired as $bookingId) {
            if (app(\App\Services\QuyTrinhDatTour::class)->expire((int) $bookingId)) $count++;
        }
        return $count;
    }
    public function getBooking(){

        $list_booking = DB::table($this->table)
        ->join('tbl_tours', 'tbl_tours.tourId', '=', 'tbl_booking.tourId')
        ->join('tbl_checkout', 'tbl_booking.bookingId', '=', 'tbl_checkout.bookingId')
        ->orderByDesc('bookingDate')
        ->get();

        return $list_booking;
    }

    public function updateBooking($bookingId, $data){
        return DB::table($this->table)
        ->where('bookingId',$bookingId)
        ->update($data);
    }

    public function getInvoiceBooking($bookingId){

        $invoice = DB::table($this->table)
        ->join('tbl_tours', 'tbl_tours.tourId', '=', 'tbl_booking.tourId')
        ->join('tbl_checkout', 'tbl_booking.bookingId', '=', 'tbl_checkout.bookingId')
        ->where('tbl_booking.bookingId', $bookingId)
        ->first();

        return $invoice;
    }

    public function getBookingPassengers($bookingId)
    {
        if (!Schema::hasTable('tbl_booking_passengers')) {
            return collect();
        }

        $passengers = DB::table('tbl_booking_passengers')
            ->where('bookingId', $bookingId)
            ->orderBy('passengerNumber')
            ->get();

        if ($passengers->isNotEmpty()) {
            return $passengers;
        }

        $booking = DB::table('tbl_booking')->where('bookingId', $bookingId)->first();
        if (!$booking) {
            return collect();
        }

        return collect([(object) [
            'passengerNumber' => 1,
            'passengerType' => 'adult',
            'fullName' => $booking->fullName,
            'email' => $booking->email,
            'phoneNumber' => $booking->phoneNumber,
            'address' => $booking->address,
        ]]);
    }

    public function getApprovedPassengerManifest($tourId, $groupNumber = null)
    {
        $bookings = DB::table('tbl_booking as b')
            ->when($groupNumber !== null, fn ($q) => $q->where('b.groupNumber', $groupNumber))
            ->join('tbl_tours as t', 't.tourId', '=', 'b.tourId')
            ->where('b.tourId', $tourId)
            ->whereIn('b.bookingStatus', ['y', 'f'])
            ->select(
                'b.bookingId',
                'b.fullName as booking_name',
                'b.phoneNumber as booking_phone',
                'b.email as booking_email',
                'b.address as booking_address',
                't.title as tour_title',
                't.destination',
                't.startDate',
                't.endDate',
            )
            ->orderBy('b.bookingId')
            ->get();

        $manifest = collect();
        foreach ($bookings as $booking) {
            $passengers = Schema::hasTable('tbl_booking_passengers')
                ? DB::table('tbl_booking_passengers')
                    ->where('bookingId', $booking->bookingId)
                    ->orderBy('passengerNumber')
                    ->get()
                : collect();

            if ($passengers->isEmpty()) {
                $passengers = collect([(object) [
                    'passengerNumber' => 1,
                    'passengerType' => 'adult',
                    'fullName' => $booking->booking_name,
                    'email' => $booking->booking_email,
                    'phoneNumber' => $booking->booking_phone,
                    'address' => $booking->booking_address,
                ]]);
            }

            foreach ($passengers as $passenger) {
                $manifest->push((object) [
                    'bookingId' => $booking->bookingId,
                    'booking_name' => $booking->booking_name,
                    'booking_phone' => $booking->booking_phone,
                    'booking_email' => $booking->booking_email,
                    'booking_address' => $booking->booking_address,
                    'tour_title' => $booking->tour_title,
                    'destination' => $booking->destination,
                    'startDate' => $booking->startDate,
                    'endDate' => $booking->endDate,
                    'passengerNumber' => $passenger->passengerNumber,
                    'passengerType' => $passenger->passengerType,
                    'fullName' => $passenger->fullName,
                    'email' => $passenger->email,
                    'phoneNumber' => $passenger->phoneNumber,
                    'address' => $passenger->address,
                ]);
            }
        }

        return $manifest;
    }

    public function updateCheckout($bookingId, $data){
        return DB::table('tbl_checkout')
        ->where('bookingId',$bookingId)
        ->update($data);
    }
}
