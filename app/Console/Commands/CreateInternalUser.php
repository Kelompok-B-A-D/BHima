<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateInternalUser extends Command
{
    protected $signature = 'bhima:create-user {email} {--role=admin}';

    protected $description = 'Buat akun internal tanpa menyimpan kata sandi dalam argumen shell';

    public function handle(): int
    {
        $input = ['email' => $this->argument('email'), 'role' => $this->option('role'), 'name' => $this->ask('Nama lengkap'), 'password' => $this->secret('Kata sandi (minimal 12 karakter)')];
        $validator = Validator::make($input, ['email' => 'required|email|unique:users,email', 'name' => 'required|string|max:255', 'role' => 'required|in:admin,verifikator', 'password' => 'required|string|min:12']);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }
        $user = new User;
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->password = Hash::make($input['password']);
        $user->role = $input['role'];
        $user->is_active = true;
        $user->save();
        $this->info('Akun internal dibuat.');

        return self::SUCCESS;
    }
}
