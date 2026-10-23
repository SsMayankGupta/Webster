<?php

namespace Database\Seeders;

use App\Models\MealTimeSlot;
use App\Models\Menu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedUsers();
            $this->seedMealTimeSlots();
            $this->seedMenus();
        });
    }

    private function seedMealTimeSlots(): void
    {
        MealTimeSlot::query()->delete();

        MealTimeSlot::query()->create([
            'meal_type' => 'breakfast',
            'start_time' => '07:00:00',
            'end_time' => '09:00:00',
            'cutoff_hours_before' => 4,
        ]);

        MealTimeSlot::query()->create([
            'meal_type' => 'lunch',
            'start_time' => '12:00:00',
            'end_time' => '14:00:00',
            'cutoff_hours_before' => 4,
        ]);

        MealTimeSlot::query()->create([
            'meal_type' => 'snacks',
            'start_time' => '16:00:00',
            'end_time' => '17:00:00',
            'cutoff_hours_before' => 4,
        ]);

        MealTimeSlot::query()->create([
            'meal_type' => 'dinner',
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'cutoff_hours_before' => 4,
        ]);
    }

    private function seedUsers(): void
    {
        User::query()->where('role', 'student')->get()->each->delete();
        User::query()->where('role', 'contractor')->get()->each->delete();

        $contractor = User::query()->create([
            'name' => 'Contractor Admin',
            'college_id' => 'CONTRACTOR001',
            'email' => 'contractor@zerowastemess.io',
            'password' => Hash::make('password'),
            'role' => 'contractor',
            'hostel' => 'Main Block',
            'dietary_pref' => 'veg',
            'dietary_tags' => ['veg'],
        ]);

        for ($i = 1; $i <= 10; $i++) {
            User::query()->create([
                'name' => "Student {$i}",
                'college_id' => 'STU'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'email' => "student{$i}@college.edu",
                'password' => Hash::make('password'),
                'role' => 'student',
                'hostel' => ['Block A', 'Block B', 'Block C'][array_rand(['Block A', 'Block B', 'Block C'])],
                'dietary_pref' => ['veg', 'non-veg'][array_rand(['veg', 'non-veg'])],
                'dietary_tags' => [['veg'], ['non-veg'], ['jain']][array_rand([['veg'], ['non-veg'], ['jain']])],
            ]);
        }
    }

    private function seedMenus(): void
    {
        Menu::query()->delete();

        $breakfastItems = ['Idli Sambar', 'Dosa', 'Upma', 'Poha', 'Paratha', 'Egg Curry', 'Cornflakes', 'Oats'];
        $lunchItems = ['Rice', 'Dal', 'Roti', 'Vegetable Curry', 'Chicken Curry', 'Fish Fry', 'Salad', 'Curd'];
        $snacksItems = ['Samosa', 'Pakora', 'Tea/Coffee', 'Sandwich', 'Biscuits', 'Fruits'];
        $dinnerItems = ['Roti', 'Paneer Butter Masala', 'Chicken Biryani', 'Dal Makhani', 'Vegetable Pulao', 'Mixed Veg'];

        $today = Carbon::now()->startOfWeek();
        $mealTimeSlots = MealTimeSlot::all();

        for ($weekOffset = 0; $weekOffset < 2; $weekOffset++) {
            for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                $currentDate = $today->copy()->addWeeks($weekOffset)->addDays($dayOffset);

                foreach (['breakfast', 'lunch', 'snacks', 'dinner'] as $mealType) {
                    $itemKeys = match ($mealType) {
                        'breakfast' => array_rand($breakfastItems, 3),
                        'lunch' => array_rand($lunchItems, 4),
                        'snacks' => array_rand($snacksItems, 2),
                        'dinner' => array_rand($dinnerItems, 3),
                    };

                    $itemArray = match ($mealType) {
                        'breakfast' => $breakfastItems,
                        'lunch' => $lunchItems,
                        'snacks' => $snacksItems,
                        'dinner' => $dinnerItems,
                    };

                    $items = [];
                    foreach ($itemKeys as $key) {
                        $items[] = $itemArray[$key];
                    }

                    $timeSlot = $mealTimeSlots->firstWhere('meal_type', $mealType);

                    if ($timeSlot === null) {
                        continue;
                    }

                    $serveAt = $currentDate->copy()->setTimeFromTimeString($timeSlot->start_time);

                    Menu::query()->create([
                        'meal_type' => $mealType,
                        'serve_at' => $serveAt,
                        'protein' => rand(15, 35),
                        'carbs' => rand(40, 80),
                        'calories' => rand(250, 600),
                        'recipe_text' => implode(', ', $items),
                        'items' => $items,
                        'is_locked' => $weekOffset === 0,
                        'status' => $weekOffset === 0 ? 'locked' : 'open',
                        'headcount_locked' => $weekOffset === 0,
                        'meal_time_slot_id' => $timeSlot->id,
                    ]);
                }
            }
        }
    }
}
