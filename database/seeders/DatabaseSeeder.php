<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Student One', 'college_id' => 'STU001', 'email' => 'stu@example.com', 'password' => Hash::make('password'), 'role' => 'student', 'hostel' => 'A', 'dietary_pref' => 'veg']);
        User::create(['name' => 'Contractor One', 'college_id' => 'CON001', 'email' => 'con@example.com', 'password' => Hash::make('password'), 'role' => 'contractor', 'mess_id' => 1]);
        User::create(['name' => 'Admin One', 'college_id' => 'ADM001', 'email' => 'admin@example.com', 'password' => Hash::make('password'), 'role' => 'admin']);

        Menu::create(['meal_type' => 'lunch', 'serve_at' => now()->addHours(6), 'recipe_text' => 'Rice, dal, sabzi', 'status' => 'open']);
        Menu::create(['meal_type' => 'dinner', 'serve_at' => now()->addHours(12), 'recipe_text' => 'Roti, paneer', 'status' => 'open']);
    }
}
