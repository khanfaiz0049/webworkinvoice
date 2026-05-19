<?php

namespace App\Http\Controllers;

use App\Models\HsnMaster;
use Illuminate\Http\Request;

class HsnMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = HsnMaster::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('service_name', 'like', "%{$search}%")
                  ->orWhere('hsn_code', 'like', "%{$search}%");
        }

        $hsns = $query->get();

        if ($request->ajax()) {
            return view('hsn_masters.partials.table', compact('hsns'))->render();
        }

        return view('hsn_masters.index', compact('hsns'));
    }

    public function create()
    {
        return view('hsn_masters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.service_name' => 'required|string|max:255',
            'items.*.hsn_code' => 'required|string|max:20',
        ]);

        foreach ($request->input('items') as $item) {
            HsnMaster::create([
                'service_name' => $item['service_name'],
                'hsn_code' => $item['hsn_code'],
            ]);
        }

        return redirect()->route('hsn-masters.index')->with('success', 'HSN Master records added successfully.');
    }

    public function edit(HsnMaster $hsnMaster)
    {
        return view('hsn_masters.edit', compact('hsnMaster'));
    }

    public function update(Request $request, HsnMaster $hsnMaster)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'hsn_code' => 'required|string|max:20',
        ]);

        $hsnMaster->update($validated);

        return redirect()->route('hsn-masters.index')->with('success', 'HSN Master record updated successfully.');
    }

    public function destroy(HsnMaster $hsnMaster)
    {
        $hsnMaster->delete();
        return redirect()->route('hsn-masters.index')->with('success', 'HSN Master record deleted successfully.');
    }
}
