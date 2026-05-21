<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /* ── Index ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = Service::withTrashed()->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $services = $query->get();

        return view('services.index', compact('services'));
    }

    /* ── Store ──────────────────────────────────────── */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = true;

        Service::create($validated);

        return redirect()->route('services.index')
            ->with('success', "Service \"{$validated['name']}\" added successfully.");
    }

    /* ── Update ─────────────────────────────────────── */

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:services,name,' . $service->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', $service->is_active);

        $service->update($validated);

        return redirect()->route('services.index')
            ->with('success', "Service \"{$service->name}\" updated successfully.");
    }

    /* ── Toggle Active ──────────────────────────────── */

    public function toggleActive(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        $status = $service->is_active ? 'activated' : 'deactivated';

        return redirect()->route('services.index')
            ->with('success', "Service \"{$service->name}\" {$status}.");
    }

    /* ── Destroy ────────────────────────────────────── */

    public function destroy(Service $service)
    {
        $name = $service->name;
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', "Service \"{$name}\" deleted successfully.");
    }

    /* ── Restore ────────────────────────────────────── */

    public function restore(int $id)
    {
        $service = Service::withTrashed()->findOrFail($id);
        $service->restore();

        return redirect()->route('services.index')
            ->with('success', "Service \"{$service->name}\" restored successfully.");
    }
}
