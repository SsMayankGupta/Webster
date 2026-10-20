<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::where('role', 'contractor')->get();
        return view('contractor.staff', compact('staff'));
    }
}
