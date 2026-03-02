<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Events
        $testEvent = Event::create([
            'name'               => 'Test Wedding',
            'ceremony_at'        => '2026-06-15 12:00:00',
            'reception_at'       => '2026-06-15 15:00:00',
            'ceremony_location'  => 'Iglesia San Francisco',
            'ceremony_url'       => 'https://maps.google.com',
            'reception_location' => 'Salón Las Palmas',
            'reception_url'      => 'https://maps.google.com',
            'dress_code'         => 'Formal',
            'rsvp_deadline'      => '2026-06-01 23:59:59',
            'notes'              => 'This is a test event for development.',
        ]);

        $realEvent = Event::create([
            'name'               => 'Real Wedding',
            'ceremony_at'        => '2026-10-04 12:00:00',
            'reception_at'       => '2026-10-04 16:00:00',
            'ceremony_location'  => 'Catedral Metropolitana',
            'ceremony_url'       => 'https://maps.google.com',
            'reception_location' => 'Hacienda San Miguel',
            'reception_url'      => 'https://maps.google.com',
            'dress_code'         => 'Cocktail',
            'rsvp_deadline'      => '2026-09-15 23:59:59',
        ]);

        // Past event — deadline and ceremony already happened
        $pastEvent = Event::create([
            'name'               => 'Past Wedding',
            'ceremony_at'        => '2025-11-08 13:00:00',
            'reception_at'       => '2025-11-08 17:00:00',
            'ceremony_location'  => 'Capilla Santa Elena',
            'ceremony_url'       => 'https://maps.google.com',
            'reception_location' => 'Villa La Joya',
            'reception_url'      => 'https://maps.google.com',
            'dress_code'         => 'Semi-formal',
            'rsvp_deadline'      => '2025-10-25 23:59:59',
        ]);

        // Super admin — can manage all events
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'super@rsvp.test',
            'password' => bcrypt('password'),
            'role'     => 'super_admin',
        ]);

        // Regular admin for test event
        User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@rsvp.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'event_id' => $testEvent->id,
        ]);

        // Regular admin for real event
        User::create([
            'name'     => 'Admin Real',
            'email'    => 'admin-real@rsvp.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'event_id' => $realEvent->id,
        ]);

        // Regular admin for past event
        User::create([
            'name'     => 'Admin Past',
            'email'    => 'admin-past@rsvp.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'event_id' => $pastEvent->id,
        ]);

        // Invitees for each event
        $this->callWith(InviteeSeeder::class, ['event' => $testEvent]);
        $this->callWith(InviteeSeeder::class, ['event' => $realEvent]);
        $this->callWith(InviteeSeeder::class, ['event' => $pastEvent]);
    }
}
