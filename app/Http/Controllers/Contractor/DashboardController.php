<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\WasteTransaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $contractor = Auth::guard('contractor')->user();
        $todayMenus = Menu::whereDate('serve_at', today())->get();
        $headcount = MealBooking::whereIn('menu_id', $todayMenus->pluck('id'))->where('status', 'booked')->count();
        $waste = WasteTransaction::where('contractor_id', $contractor->id)->latest()->limit(5)->get();
        return view('contractor.dashboard', compact('contractor', 'todayMenus', 'headcount', 'waste'));
    }
}
