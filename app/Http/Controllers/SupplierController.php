<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Supplier::query()
            ->withCount('medicineBatches');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier_name', 'like', "%{$search}%")
                  ->orWhere('contact_info', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('supplier_name')->get();

        $counts = [
            'total' => Supplier::count(),
            'in_use' => Supplier::has('medicineBatches')->count(),
            'unused' => Supplier::doesntHave('medicineBatches')->count(),
        ];

        return view('suppliers.index', compact('suppliers', 'counts'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_name' => ['required', 'string', 'max:150', 'unique:suppliers,supplier_name'],
            'contact_info' => ['nullable', 'string', 'max:255'],
        ], [
            'supplier_name.required' => 'Please enter a supplier name.',
            'supplier_name.unique' => 'This supplier already exists in the system.',
        ]);

        $supplier = Supplier::create([
            'supplier_name' => trim($validated['supplier_name']),
            'contact_info' => isset($validated['contact_info']) ? trim($validated['contact_info']) : null,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', "Supplier '{$supplier->supplier_name}' created successfully.");
    }

    /**
     * Display the specified supplier (redirects to edit view).
     */
    public function show(Supplier $supplier): RedirectResponse
    {
        return redirect()->route('suppliers.edit', $supplier);
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier): View
    {
        $supplier->loadCount('medicineBatches');

        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_name' => ['required', 'string', 'max:150', 'unique:suppliers,supplier_name,' . $supplier->id],
            'contact_info' => ['nullable', 'string', 'max:255'],
        ], [
            'supplier_name.required' => 'Please enter a supplier name.',
            'supplier_name.unique' => 'This supplier already exists in the system.',
        ]);

        $supplier->update([
            'supplier_name' => trim($validated['supplier_name']),
            'contact_info' => isset($validated['contact_info']) ? trim($validated['contact_info']) : null,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', "Supplier '{$supplier->supplier_name}' updated successfully.");
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->medicineBatches()->exists()) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', "Cannot delete supplier '{$supplier->supplier_name}' because it is currently linked to existing medicine batches.");
        }

        $name = $supplier->supplier_name;

        try {
            $supplier->delete();

            return redirect()
                ->route('suppliers.index')
                ->with('success', "Supplier '{$name}' deleted successfully.");
        } catch (QueryException $e) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', "Cannot delete supplier '{$name}' because it is referenced by existing records.");
        }
    }
}
