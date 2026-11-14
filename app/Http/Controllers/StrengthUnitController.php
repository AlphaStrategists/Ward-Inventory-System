<?php

namespace App\Http\Controllers;

use App\Models\StrengthUnit;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StrengthUnitController extends Controller
{
    /**
     * Ensure only Admin users can access Master Data actions.
     */
    private function authorizeAdmin(): void
    {
        $user = auth()->user();

        $isAdmin = $user && (
            $user->is_admin ||
            strtolower(trim((string) $user->role)) === 'admin' ||
            $user->hasRole('Admin')
        );

        abort_unless($isAdmin, 403, 'Unauthorized. Only Admin users can access Master Data.');
    }

    /**
     * Display a listing of strength units.
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        StrengthUnit::ensureInitialized();

        $search = $request->query('search');

        $query = StrengthUnit::query()
            ->withCount('medicines');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $strengthUnits = $query->orderBy('id')->get();

        $counts = [
            'total' => StrengthUnit::count(),
            'in_use' => StrengthUnit::has('medicines')->count(),
            'unused' => StrengthUnit::doesntHave('medicines')->count(),
        ];

        return view('master-data.strength-units', compact('strengthUnits', 'counts'));
    }

    /**
     * Store a newly created strength unit in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        StrengthUnit::ensureInitialized();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20', 'unique:strength_units,name'],
        ], [
            'name.required' => 'Please enter a strength unit name (e.g., mg, ml, g).',
            'name.unique' => 'This strength unit already exists in the system.',
        ]);

        $strengthUnit = StrengthUnit::create([
            'name' => trim($validated['name']),
        ]);

        return redirect()
            ->route('strength-units.index')
            ->with('success', "Strength unit '{$strengthUnit->name}' created successfully.");
    }

    /**
     * Update the specified strength unit in storage.
     */
    public function update(Request $request, StrengthUnit $strength_unit): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20', 'unique:strength_units,name,' . $strength_unit->id],
        ], [
            'name.required' => 'Please enter a strength unit name.',
            'name.unique' => 'This strength unit already exists in the system.',
        ]);

        $strength_unit->update([
            'name' => trim($validated['name']),
        ]);

        return redirect()
            ->route('strength-units.index')
            ->with('success', "Strength unit '{$strength_unit->name}' updated successfully.");
    }

    /**
     * Remove the specified strength unit from storage.
     */
    public function destroy(StrengthUnit $strength_unit): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($strength_unit->medicines()->exists()) {
            return redirect()
                ->route('strength-units.index')
                ->with('error', "Cannot delete strength unit '{$strength_unit->name}' because it is currently assigned to existing medicines.");
        }

        $name = $strength_unit->name;

        try {
            $strength_unit->delete();

            return redirect()
                ->route('strength-units.index')
                ->with('success', "Strength unit '{$name}' deleted successfully.");
        } catch (QueryException $e) {
            return redirect()
                ->route('strength-units.index')
                ->with('error', "Cannot delete strength unit '{$name}' because it is referenced by existing records.");
        }
    }
}

