<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\WasteTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WasteController extends Controller
{
    public function index()
    {
        $contractor = Auth::guard('contractor')->user();
        $transactions = WasteTransaction::where('contractor_id', $contractor->id)->latest()->get();
        return view('contractor.waste.index', compact('transactions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'weight_kg' => 'required|numeric|min:0',
            'type' => 'required|in:compost,biogas,donation,sale',
            'earnings' => 'nullable|numeric|min:0',
        ]);
        $contractor = Auth::guard('contractor')->user();
        WasteTransaction::create([
            'contractor_id' => $contractor->id,
            'weight_kg' => $data['weight_kg'],
            'type' => $data['type'],
            'earnings' => $data['earnings'] ?? 0,
            'logged_at' => now(),
        ]);
        return redirect()->route('contractor.waste.index')->with('status', 'Waste logged.');
    }
}
