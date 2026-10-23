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
        $mealTimeSlots = [
            'BREAKFAST' => ['07:00:00', '09:00:00'],
            'LUNCH' => ['12:00:00', '14:00:00'],
            'SNACKS' => ['16:00:00', '17:00:00'],
            'DINNER' => ['19:00:00', '21:00:00'],
        ];

        foreach ($mealTimeSlots as $mealType => [$startTime, $endTime]) {
            MealTimeSlot::query()->updateOrCreate(
                ['meal_type' => $mealType],
                [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'cutoff_hours_before' => 4,
                ],
            );
        }
    }

    private function seedUsers(): void
    {
        User::query()->updateOrCreate(
            ['college_id' => 'CONTRACTOR001'],
            [
                'name' => 'Contractor Admin',
                'email' => 'contractor@zerowastemess.io',
                'password' => Hash::make('password'),
                'role' => 'contractor',
                'hostel' => 'Main Block',
                'dietary_pref' => 'veg',
                'dietary_tags' => ['veg'],
            ],
        );

        $hostels = ['Block A', 'Block B', 'Block C'];
        $dietaryPreferences = ['veg', 'non-veg'];
        $dietaryTags = [['veg'], ['non-veg'], ['jain']];

        for ($i = 1; $i <= 10; $i++) {
            User::query()->updateOrCreate(
                ['college_id' => 'STU'.str_pad((string) $i, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => "Student {$i}",
                    'email' => "student{$i}@college.edu",
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'hostel' => $hostels[($i - 1) % count($hostels)],
                    'dietary_pref' => $dietaryPreferences[($i - 1) % count($dietaryPreferences)],
                    'dietary_tags' => $dietaryTags[($i - 1) % count($dietaryTags)],
                ],
            );
        }
    }

    private function seedMenus(): void
    {
        $breakfastItems = ['Idli Sambar', 'Dosa', 'Upma', 'Poha', 'Paratha', 'Egg Curry', 'Cornflakes', 'Oats'];
        $lunchItems = ['Rice', 'Dal', 'Roti', 'Vegetable Curry', 'Chicken Curry', 'Fish Fry', 'Salad', 'Curd'];
        $snacksItems = ['Samosa', 'Pakora', 'Tea/Coffee', 'Sandwich', 'Biscuits', 'Fruits'];
        $dinnerItems = ['Roti', 'Paneer Butter Masala', 'Chicken Biryani', 'Dal Makhani', 'Vegetable Pulao', 'Mixed Veg'];

        $today = Carbon::now()->startOfWeek();
        $mealTimeSlots = MealTimeSlot::query()->get()->keyBy('meal_type');

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

                    $timeSlot = $mealTimeSlots->get(strtoupper($mealType));

                    if ($timeSlot === null) {
                        continue;
                    }

                    $serveAt = $currentDate->copy()->setTimeFromTimeString($timeSlot->start_time);

                    Menu::query()->updateOrCreate(
                        [
                            'meal_type' => $mealType,
                            'serve_at' => $serveAt,
                        ],
                        [
                            'protein' => rand(15, 35),
                            'carbs' => rand(40, 80),
                            'calories' => rand(250, 600),
                            'recipe_text' => implode(', ', $items),
                            'items' => $items,
                            'is_locked' => $weekOffset === 0,
                            'status' => $weekOffset === 0 ? 'locked' : 'open',
                            'headcount_locked' => $weekOffset === 0,
                            'meal_time_slot_id' => $timeSlot->id,
                        ],
                    );
                }
            }
        }
    }
}
