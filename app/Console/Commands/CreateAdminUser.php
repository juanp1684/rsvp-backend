<?php

namespace App\Console\Commands;

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

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Admin user \"{$name}\" created successfully.");
    }
}
