<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
         * Test users:
         *
         * 1. Alex Brown
         *    Login: alex.brown@example.com
         *    Password: password123
         *
         * 2. Emma Smith
         *    Login: emma.smith@example.com
         *    Password: password123
         *
         * 3. Daniel Wilson
         *    Login: daniel.wilson@example.com
         *    Password: password123
         *
         * 4. Olivia Taylor
         *    Login: olivia.taylor@example.com
         *    Password: password123
         *
         * 5. James Anderson
         *    Login: james.anderson@example.com
         *    Password: password123
         */
        $users = [
            [
                'first_name' => 'Alex',
                'second_name' => 'Brown',
                'email' => 'alex.brown@example.com',
                'password' => 'password123',
                'locale' => 'en',
                'theme' => 'light',
            ],
            [
                'first_name' => 'Emma',
                'second_name' => 'Smith',
                'email' => 'emma.smith@example.com',
                'password' => 'password123',
                'locale' => 'en',
                'theme' => 'dark',
            ],
            [
                'first_name' => 'Daniel',
                'second_name' => 'Wilson',
                'email' => 'daniel.wilson@example.com',
                'password' => 'password123',
                'locale' => 'en',
                'theme' => 'light',
            ],
            [
                'first_name' => 'Olivia',
                'second_name' => 'Taylor',
                'email' => 'olivia.taylor@example.com',
                'password' => 'password123',
                'locale' => 'ru',
                'theme' => 'light',
            ],
            [
                'first_name' => 'James',
                'second_name' => 'Anderson',
                'email' => 'james.anderson@example.com',
                'password' => 'password123',
                'locale' => 'ru',
                'theme' => 'dark',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'first_name' => $user['first_name'],
                    'second_name' => $user['second_name'],
                    'password' => $user['password'],
                    'locale' => $user['locale'],
                    'theme' => $user['theme'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}