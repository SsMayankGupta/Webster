<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index() { return view('admin.users.index', ['users' => User::all()]); }
    public function create() { return view('admin.users.create'); }
    public function store(Request $r) {
        $data = $r->validate(['name'=>'required','college_id'=>'required|unique:users','password'=>'required','role'=>'required']);
        $data['password'] = bcrypt($data['password']);
        User::create($data);
        return redirect()->route('admin.users.index');
    }
    public function show(User $user) { return view('admin.users.show', compact('user')); }
    public function edit(User $user) { return view('admin.users.edit', compact('user')); }
    public function update(Request $r, User $user) { $user->update($r->only(['name','role','hostel','dietary_pref'])); return redirect()->route('admin.users.index'); }
    public function destroy(User $user) { $user->delete(); return back(); }
}
