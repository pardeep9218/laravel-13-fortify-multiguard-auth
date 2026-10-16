<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('admin:create {email} {name}')]
#[Description('Command description')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        \App\Models\Admin::create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => $this->secret('Password'),   // hashed by the cast
            'email_verified_at' => now(),
        ]);
        $this->info('Admin created.');
        return self::SUCCESS;
    }
}
