<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * Display a listing of measurement units.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Unit::query()
            ->withCount('medicines');

        if ($search) {
            $query->where('unit_name', 'like', "%{$search}%");
        }

        $units = $query->orderBy('unit_name')->get();

        $counts = [
            'total' => Unit::count(),
            'in_use' => Unit::has('medicines')->count(),
            'unused' => Unit::doesntHave('medicines')->count(),
        ];

        return view('units.index', compact('units', 'counts'));
    }

    /**
     * Show the form for creating a new measurement unit.
     */
    public function create(): View
    {
        return view('units.create');
    }

    /**
     * Store a newly created measurement unit in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_name' => ['required', 'string', 'max:50', 'unique:units,unit_name'],
        ], [
            'unit_name.required' => 'Please enter a unit name.',
            'unit_name.unique' => 'This unit already exists in the system.',
        ]);

        $unit = Unit::create([
            'unit_name' => trim($validated['unit_name']),
        ]);

        return redirect()
            ->route('units.index')
            ->with('success', "Unit '{$unit->unit_name}' created successfully.");
    }

    /**
     * Display the specified unit (redirects to edit view).
     */
    public function show(Unit $unit): RedirectResponse
    {
        return redirect()->route('units.edit', $unit);
    }

    /**
     * Show the form for editing the specified measurement unit.
     */
    public function edit(Unit $unit): View
    {
        $unit->loadCount('medicines');

        return view('units.edit', compact('unit'));
    }

    /**
     * Update the specified measurement unit in storage.
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'unit_name' => ['required', 'string', 'max:50', 'unique:units,unit_name,' . $unit->id],
        ], [
            'unit_name.required' => 'Please enter a unit name.',
            'unit_name.unique' => 'This unit already exists in the system.',
        ]);

        $unit->update([
            'unit_name' => trim($validated['unit_name']),
        ]);

        return redirect()
            ->route('units.index')
            ->with('success', "Unit '{$unit->unit_name}' updated successfully.");
    }

    /**
     * Remove the specified measurement unit from storage.
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->medicines()->exists()) {
            return redirect()
                ->route('units.index')
                ->with('error', "Cannot delete unit '{$unit->unit_name}' because it is currently assigned to existing medicines.");
        }

        $name = $unit->unit_name;

        try {
            $unit->delete();

            return redirect()
                ->route('units.index')
                ->with('success', "Unit '{$name}' deleted successfully.");
        } catch (QueryException $e) {
            return redirect()
                ->route('units.index')
                ->with('error', "Cannot delete unit '{$name}' because it is referenced by existing records.");
        }
    }
}
