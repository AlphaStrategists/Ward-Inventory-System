<?php

namespace App\Http\Controllers;

use App\Models\MedicineForm;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormTypeController extends Controller
{
    /**
     * Display a listing of medicine form types.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = MedicineForm::query()
            ->withCount('medicines');

        if ($search) {
            $query->where('form_name', 'like', "%{$search}%");
        }

        $formTypes = $query->orderBy('form_name')->get();
        $medicineForms = $formTypes;

        $counts = [
            'total' => MedicineForm::count(),
            'in_use' => MedicineForm::has('medicines')->count(),
            'unused' => MedicineForm::doesntHave('medicines')->count(),
        ];

        return view('form-types.index', compact('formTypes', 'medicineForms', 'counts'));
    }

    /**
     * Show the form for creating a new medicine form type.
     */
    public function create(): View
    {
        return view('form-types.create');
    }

    /**
     * Store a newly created medicine form type in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'form_name' => ['required', 'string', 'max:100', 'unique:medicine_forms,form_name'],
        ], [
            'form_name.required' => 'Please enter a form type name.',
            'form_name.unique' => 'This form type already exists in the system.',
        ]);

        $formType = MedicineForm::create([
            'form_name' => trim($validated['form_name']),
        ]);

        return redirect()
            ->route('form-types.index')
            ->with('success', "Form type '{$formType->form_name}' created successfully.");
    }

    /**
     * Display the specified medicine form type (redirects to edit view).
     */
    public function show(MedicineForm $form_type): RedirectResponse
    {
        return redirect()->route('form-types.edit', $form_type);
    }

    /**
     * Show the form for editing the specified medicine form type.
     */
    public function edit(MedicineForm $form_type): View
    {
        $form_type->loadCount('medicines');
        $formType = $form_type;
        $medicineForm = $form_type;

        return view('form-types.edit', compact('formType', 'form_type', 'medicineForm'));
    }

    /**
     * Update the specified medicine form type in storage.
     */
    public function update(Request $request, MedicineForm $form_type): RedirectResponse
    {
        $validated = $request->validate([
            'form_name' => ['required', 'string', 'max:100', 'unique:medicine_forms,form_name,' . $form_type->id],
        ], [
            'form_name.required' => 'Please enter a form type name.',
            'form_name.unique' => 'This form type already exists in the system.',
        ]);

        $form_type->update([
            'form_name' => trim($validated['form_name']),
        ]);

        return redirect()
            ->route('form-types.index')
            ->with('success', "Form type '{$form_type->form_name}' updated successfully.");
    }

    /**
     * Remove the specified medicine form type from storage.
     */
    public function destroy(MedicineForm $form_type): RedirectResponse
    {
        if ($form_type->medicines()->exists()) {
            return redirect()
                ->route('form-types.index')
                ->with('error', "Cannot delete form type '{$form_type->form_name}' because it is currently assigned to existing medicines.");
        }

        $name = $form_type->form_name;

        try {
            $form_type->delete();

            return redirect()
                ->route('form-types.index')
                ->with('success', "Form type '{$name}' deleted successfully.");
        } catch (QueryException $e) {
            return redirect()
                ->route('form-types.index')
                ->with('error', "Cannot delete form type '{$name}' because it is referenced by existing records.");
        }
    }
}

