<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\User;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin {username} {email} {password}';
    protected $description = 'Create a bootstrap admin user';

    public function handle()
    {
        $username = $this->argument('username');
        $email = $this->argument('email');
        $password = $this->argument('password');

        $role = Role::create(['uuid'=>Str::uuid()->toString(),'name'=>'Super Administrator','description'=>'Bootstrap admin']);
        $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'username' => $username,
            'email' => $email,
            'password' => bcrypt($password),
            'role_uuid' => $role->uuid,
            'status' => 1
        ]);

        $this->info("Admin created: {$username} ({$email})");
        return 0;
    }
}
