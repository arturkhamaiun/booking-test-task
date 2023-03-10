<?php

namespace App\Http\Controllers\Reservation;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CancelReservation extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \App\Http\Requests\Request  $request
     * @param  \App\Models\Reservation  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, string $id): Response
    {
        DB::transaction(function () use ($id) {
            $reservation = Reservation::whereId($id)->lockForUpdate()->firstOrFail();
            $vacanciesQuery = Vacancy::whereBetween('date', [$reservation->start_date, $reservation->end_date]);

            $vacanciesQuery->lockForUpdate()->count();

            $this->authorize('cancel', $reservation);

            abort_if(
                $reservation->status === ReservationStatus::CANCELLED,
                400,
                'Reservation is already cancelled.'
            );

            $vacanciesQuery->increment('total');
            $reservation->update(['status' => ReservationStatus::CANCELLED]);
        });

        return response(null, 204);
    }
}
