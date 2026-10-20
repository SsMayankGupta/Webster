<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MealConfigController extends Controller
{
    public function index() { return view('admin.meals.index', ['menus' => Menu::all()]); }
    public function create() { return view('admin.meals.create'); }
    public function store(Request $r) {
        $data = $r->validate(['meal_type'=>'required','serve_at'=>'required|date','protein'=>'nullable|numeric','carbs'=>'nullable|numeric','calories'=>'nullable|numeric']);
        Menu::create($data);
        return redirect()->route('admin.meals.index');
    }
    public function show(Menu $meal) { return view('admin.meals.show', compact('meal')); }
    public function edit(Menu $meal) { return view('admin.meals.edit', compact('meal')); }
    public function update(Request $r, Menu $meal) { $meal->update($r->only(['meal_type','serve_at','protein','carbs','calories','is_locked'])); return redirect()->route('admin.meals.index'); }
    public function destroy(Menu $meal) { $meal->delete(); return back(); }
}
