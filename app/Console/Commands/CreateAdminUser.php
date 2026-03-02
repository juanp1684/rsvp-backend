<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Create an admin user';

    public function handle(): void
    {
        $name = $this->ask('Name');
        $email = $this->ask('Email');

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email {$email} already exists.");
            return;
        }

        $password = $this->secret('Password');

        $role = $this->choice('Role', ['admin', 'super_admin'], 0);

        $eventId = null;

        if ($role === 'admin') {
            $events = Event::orderBy('name')->get();

            if ($events->isEmpty()) {
                $this->warn('No events exist yet. You can assign an event later.');
            } else {
                $choices = $events->map(fn ($e) => "{$e->name} ({$e->slug})")->prepend('-- Skip --')->values()->toArray();
                $selected = $this->choice('Assign to event?', $choices, 0);

                if ($selected !== '-- Skip --') {
                    $index = array_search($selected, $choices) - 1; // offset by prepended skip
                    $eventId = $events[$index]->id;
                }
            }
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'event_id' => $eventId,
        ]);

        $this->info("User \"{$name}\" created as {$role}.");
    }
}
