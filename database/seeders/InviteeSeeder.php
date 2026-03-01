<?php

namespace Database\Seeders;

use App\Models\Companion;
use App\Models\Invitee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InviteeSeeder extends Seeder
{
    public function run(): void
    {
        $invitees = [
            // Attending with companions registered
            [
                'full_name' => 'Carlos Mendoza',
                'phone' => '+1 555 0101',
                'allowed_companions' => 2,
                'status' => 'attending',
                'companions' => ['Laura Mendoza', 'Diego Mendoza'],
            ],
            [
                'full_name' => 'Ana Ramírez',
                'phone' => '+1 555 0102',
                'allowed_companions' => 1,
                'status' => 'attending',
                'companions' => ['Miguel Torres'],
            ],

            // Attending without companions
            [
                'full_name' => 'Roberto Fuentes',
                'phone' => '+1 555 0103',
                'allowed_companions' => 0,
                'status' => 'attending',
                'companions' => [],
            ],

            // Declined
            [
                'full_name' => 'Patricia Vega',
                'phone' => '+1 555 0104',
                'allowed_companions' => 1,
                'status' => 'declined',
                'notes' => 'Out of the country that weekend.',
                'companions' => [],
            ],

            // Pending (haven't responded yet)
            [
                'full_name' => 'Javier Herrera',
                'phone' => '+1 555 0105',
                'allowed_companions' => 1,
                'status' => 'pending',
                'companions' => [],
            ],
            [
                'full_name' => 'Sofía Castro',
                'phone' => '+1 555 0106',
                'allowed_companions' => 2,
                'status' => 'pending',
                'companions' => [],
            ],
            [
                'full_name' => 'Lucía Moreno',
                'phone' => null,
                'allowed_companions' => 0,
                'status' => 'pending',
                'companions' => [],
            ],
        ];

        foreach ($invitees as $data) {
            $companions = $data['companions'];
            unset($data['companions']);

            $invitee = Invitee::create([
                ...$data,
                'code' => Str::upper(Str::random(8)),
            ]);

            foreach ($companions as $name) {
                Companion::create([
                    'invitee_id' => $invitee->id,
                    'full_name' => $name,
                ]);
            }
        }
    }
}
