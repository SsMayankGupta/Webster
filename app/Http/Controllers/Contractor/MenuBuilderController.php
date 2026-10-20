<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Jobs\CalculateMenuMacros;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuBuilderController extends Controller
{
    public function index()
    {
        $menus = Menu::latest()->limit(20)->get();
        return view('contractor.menu-builder', compact('menus'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'meal_type' => 'required|in:breakfast,lunch,snacks,dinner',
            'serve_at' => 'required|date',
            'recipe_text' => 'required|string',
        ]);
        $menu = Menu::create([
            'meal_type' => $data['meal_type'],
            'serve_at' => $data['serve_at'],
            'recipe_text' => $data['recipe_text'],
            'status' => 'open',
        ]);
        CalculateMenuMacros::dispatch($menu->id);
        return redirect()->route('contractor.menu-builder')->with('status', 'Menu created; macros queued.');
    }
}
