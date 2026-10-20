<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\MealBooking;
use App\Models\Menu;

class HeadcountController extends Controller
{
    public function index()
    {
        $menus = Menu::orderBy('serve_at')->get();
        foreach ($menus as $menu) {
            if (! $menu->is_locked && now()->addHours(4)->gt($menu->serve_at)) {
                $count = MealBooking::where('menu_id', $menu->id)->where('status', 'booked')->count();
                $menu->update([
                    'is_locked' => true,
                    'status' => 'locked',
                    'headcount_locked' => $count,
                    'locked_at' => null,
                ]);
                $menu->refresh();
            }
        }
        return view('contractor.headcount', compact('menus'));
    }
}
