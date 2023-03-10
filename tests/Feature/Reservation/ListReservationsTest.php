<?php

namespace Tests\Feature\Reservation;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\FeatureTestCase;

class ListReservationsTest extends FeatureTestCase
{
    public function test_show_reservations()
    {
        $user = User::factory()->create();
        Reservation::create([
            'start_date' => now(),
            'end_date' => now()->addDay(),
            'user_id' => $user->id,
            'status' => ReservationStatus::NEW,
            'price' => 100
        ]);

        $response = $this->actingAs($user)->get(route('reservations.list'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'start_date',
                    'end_date',
                    'created_at',
                    'updated_at',
                    'status',
                    'price',
                ]
            ],
            'links',
            'meta',
        ]);
    }

    public function test_user_only_see_his_reservations()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Reservation::create([
            'start_date' => now(),
            'end_date' => now()->addDay(),
            'user_id' => $user->id,
            'status' => ReservationStatus::NEW,
            'price' => 100,
        ]);

        $response = $this->actingAs($otherUser)->get(route('reservations.list'));

        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $assertableJson) {
            $assertableJson->has('data', 0)->etc();
        });
    }
}
