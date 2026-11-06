<?php

namespace App\Http\Controllers;

use App\Models\InjectionAntibioticRequisition;
use App\Models\InjectionMedicine;
use App\Models\InjectionRequisition;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InjectionInventoryController extends Controller
{
    /**
     * Determine and normalize the user's role.
     * Maps Doctor to 'doctor' (MS), Nurse/Head Nurse to 'nurse', and Admin to 'admin'.
     */
    protected function determineRole(User $user): string
    {
        if ($user->isAdmin() || (bool) $user->is_admin) {
            return 'admin';
        }

        $raw = strtolower(trim((string) ($user->role ?? $user->roleRelation?->role_name ?? '')));

        if (in_array($raw, ['nurse', 'head nurse', 'head_nurse'], true)) {
            return 'nurse';
        }

        if (in_array($raw, ['doctor', 'ms'], true)) {
            return 'doctor';
        }

        if ($raw === 'admin') {
            return 'admin';
        }

        return 'unauthorized';
    }

    /**
     * Server-side authorization check.
     * Ensures only Nurse, Admin, or Doctor/MS can access the module.
     */
    protected function authorizeAccess(Request $request): User
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $role = $this->determineRole($user);
        if (!in_array($role, ['nurse', 'admin', 'doctor'], true)) {
            abort(403, 'Unauthorized. Access is restricted to Nurse, Admin, or Doctor/MS.');
        }

        return $user;
    }

    /**
     * Check if the user is authorized to manage MS approval.
     */
    protected function canManageMsApproval(User $user): bool
    {
        $role = $this->determineRole($user);
        return in_array($role, ['admin', 'doctor'], true);
    }

    /**
     * Get human-readable role string for UI display.
     */
    protected function getDisplayRole(string $role): string
    {
        return match ($role) {
            'doctor' => 'MS',
            'admin' => 'ADMIN',
            'nurse' => 'NURSE',
            default => 'USER',
        };
    }

    /**
     * Display the Injection inventory dashboard.
     * Strictly read-only for GET requests.
     */
    public function index(Request $request): View
    {
        $user = $this->authorizeAccess($request);
        $currentRole = $this->determineRole($user);
        $displayRole = $this->getDisplayRole($currentRole);
        $canApproveMs = $this->canManageMsApproval($user);

        $medicines = InjectionMedicine::where('type', 'injection')
            ->where('is_active', true)
            ->with(['injectionRequisitions' => function ($query) {
                $query->whereNull('deleted_at');
            }])
            ->orderBy('name')
            ->get();

        $selectedMedicineId = $request->query('medicine');
        $selectedMedicine = null;

        if ($selectedMedicineId) {
            $selectedMedicine = $medicines->firstWhere('id', (int) $selectedMedicineId);
        }

        if (!$selectedMedicine && $medicines->isNotEmpty()) {
            $selectedMedicine = $medicines->first();
        }

        $requisitions = collect();
        $totalReceived = 0;
        $totalRequested = 0;

        if ($selectedMedicine) {
            $requisitions = InjectionRequisition::with(['medicine', 'msApprover', 'creator'])
                ->where('medicine_id', $selectedMedicine->id)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $totalReceived = (int) $requisitions->sum('received_quantity');
            $totalRequested = (int) $requisitions->sum('requested_quantity');
        }

        return view('injections.index', compact(
            'medicines',
            'selectedMedicine',
            'requisitions',
            'currentRole',
            'displayRole',
            'canApproveMs',
            'totalReceived',
            'totalRequested'
        ));
    }

    /**
     * Store a new Injection medicine master record.
     */
    public function storeMedicine(Request $request): RedirectResponse
    {
        $user = $this->authorizeAccess($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('injection_medicines', 'name')
                    ->where('type', 'injection')
                    ->whereNull('deleted_at'),
            ],
        ], [
            'name.required' => 'Drug Name is required.',
            'name.unique' => 'A medicine with this name already exists in the Injection category.',
        ]);

        $medicine = InjectionMedicine::create([
            'type' => 'injection',
            'name' => trim($validated['name']),
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('injection.index', ['medicine' => $medicine->id])
            ->with('success', 'Injection medicine added successfully.');
    }

    /**
     * Store a new Injection requisition order.
     */
    public function storeOrder(Request $request): RedirectResponse
    {
        $user = $this->authorizeAccess($request);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'medicine_id' => [
                'required',
                'integer',
                Rule::exists('injection_medicines', 'id')
                    ->where('type', 'injection')
                    ->whereNull('deleted_at'),
            ],
            'requested_quantity' => ['required', 'integer', 'min:1'],
            'request_signature' => ['required', 'string', 'max:150'],
        ], [
            'requested_quantity.min' => 'Requested quantity must be at least 1.',
            'request_signature.required' => 'Confirm signature for request is required.',
        ]);

        InjectionRequisition::create([
            'medicine_id' => $validated['medicine_id'],
            'date' => $validated['date'],
            'requested_quantity' => $validated['requested_quantity'],
            'request_signature' => trim($validated['request_signature']),
            'ms_approval_status' => 'Pending',
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('injection.index', ['medicine' => $validated['medicine_id']])
            ->with('success', 'Injection requisition created successfully.');
    }

    /**
     * Update an existing Injection requisition order.
     */
    public function updateOrder(Request $request, int $id): RedirectResponse
    {
        $user = $this->authorizeAccess($request);
        $canApprove = $this->canManageMsApproval($user);

        $requisition = InjectionRequisition::findOrFail($id);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'medicine_id' => [
                'required',
                'integer',
                Rule::exists('injection_medicines', 'id')
                    ->where('type', 'injection')
                    ->whereNull('deleted_at'),
            ],
            'requested_quantity' => ['required', 'integer', 'min:1'],
            'request_signature' => ['required', 'string', 'max:150'],
            'ms_approval_status' => ['nullable', 'in:Pending,Approved,Rejected'],
            'received_quantity' => ['nullable', 'integer', 'min:0'],
            'issued_officer_signature' => ['nullable', 'string', 'max:150'],
            'issued_officer_date' => ['nullable', 'date'],
            'received_signature' => ['nullable', 'string', 'max:150'],
            'received_date' => ['nullable', 'date'],
        ]);

        $requisition->date = $validated['date'];
        $requisition->medicine_id = $validated['medicine_id'];
        $requisition->requested_quantity = $validated['requested_quantity'];
        $requisition->request_signature = trim($validated['request_signature']);

        // Only Doctor/MS or Admin can modify MS approval status
        if ($canApprove && !empty($validated['ms_approval_status'])) {
            $newStatus = $validated['ms_approval_status'];
            if ($newStatus !== $requisition->ms_approval_status) {
                $requisition->ms_approval_status = $newStatus;
                if (in_array($newStatus, ['Approved', 'Rejected'], true)) {
                    $requisition->ms_approved_by = $user->id;
                    $requisition->ms_approved_at = now();
                } else {
                    $requisition->ms_approved_by = null;
                    $requisition->ms_approved_at = null;
                }
            }
        }

        $requisition->received_quantity = isset($validated['received_quantity']) && $validated['received_quantity'] !== ''
            ? (int) $validated['received_quantity']
            : null;

        $requisition->issued_officer_signature = !empty($validated['issued_officer_signature'])
            ? trim($validated['issued_officer_signature'])
            : null;
        $requisition->issued_officer_date = !empty($validated['issued_officer_date'])
            ? $validated['issued_officer_date']
            : null;

        $requisition->received_signature = !empty($validated['received_signature'])
            ? trim($validated['received_signature'])
            : null;
        $requisition->received_date = !empty($validated['received_date'])
            ? $validated['received_date']
            : null;

        $requisition->updated_by = $user->id;
        $requisition->save();

        return redirect()
            ->route('injection.index', ['medicine' => $requisition->medicine_id])
            ->with('success', 'Injection requisition updated successfully.');
    }

    /**
     * Soft delete an Injection requisition order.
     */
    public function destroyOrder(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAccess($request);

        $requisition = InjectionRequisition::findOrFail($id);
        $medicineId = $requisition->medicine_id;
        $requisition->delete();

        return redirect()
            ->route('injection.index', ['medicine' => $medicineId])
            ->with('success', 'Requisition deleted successfully.');
    }

    /**
     * Display the Injection Antibiotics inventory dashboard.
     * Strictly read-only for GET requests.
     */
    public function antibiotics(Request $request): View
    {
        $user = $this->authorizeAccess($request);
        $currentRole = $this->determineRole($user);
        $displayRole = $this->getDisplayRole($currentRole);

        $medicines = InjectionMedicine::where('type', 'injection_antibiotics')
            ->where('is_active', true)
            ->with(['antibioticRequisitions' => function ($query) {
                $query->whereNull('deleted_at');
            }])
            ->orderBy('name')
            ->get();

        $selectedMedicineId = $request->query('medicine');
        $selectedMedicine = null;

        if ($selectedMedicineId) {
            $selectedMedicine = $medicines->firstWhere('id', (int) $selectedMedicineId);
        }

        if (!$selectedMedicine && $medicines->isNotEmpty()) {
            $selectedMedicine = $medicines->first();
        }

        $requisitions = collect();
        $latestBalance = 0;
        $totalRequired = 0;
        $totalReceived = 0;

        if ($selectedMedicine) {
            $requisitions = InjectionAntibioticRequisition::with(['medicine', 'creator'])
                ->where('medicine_id', $selectedMedicine->id)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $latestRequisition = $requisitions->first();
            $latestBalance = $latestRequisition ? (int) $latestRequisition->balance : 0;
            $totalRequired = (int) $requisitions->sum('required_quantity');
            $totalReceived = (int) $requisitions->sum('received_quantity');
        }

        return view('injections.antibiotics', compact(
            'medicines',
            'selectedMedicine',
            'requisitions',
            'currentRole',
            'displayRole',
            'latestBalance',
            'totalRequired',
            'totalReceived'
        ));
    }

    /**
     * Store a new Injection Antibiotics medicine master record.
     */
    public function storeAntibioticMedicine(Request $request): RedirectResponse
    {
        $user = $this->authorizeAccess($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('injection_medicines', 'name')
                    ->where('type', 'injection_antibiotics')
                    ->whereNull('deleted_at'),
            ],
        ], [
            'name.required' => 'Drug Name is required.',
            'name.unique' => 'A medicine with this name already exists in the Injection Antibiotics category.',
        ]);

        $medicine = InjectionMedicine::create([
            'type' => 'injection_antibiotics',
            'name' => trim($validated['name']),
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('injection.antibiotics', ['medicine' => $medicine->id])
            ->with('success', 'Injection Antibiotics medicine added successfully.');
    }

    /**
     * Store a new Injection Antibiotics requisition order.
     */
    public function storeAntibioticOrder(Request $request): RedirectResponse
    {
        $user = $this->authorizeAccess($request);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'medicine_id' => [
                'required',
                'integer',
                Rule::exists('injection_medicines', 'id')
                    ->where('type', 'injection_antibiotics')
                    ->whereNull('deleted_at'),
            ],
            'balance' => ['required', 'integer', 'min:0'],
            'required_quantity' => ['required', 'integer', 'min:1'],
            'request_signature' => ['required', 'string', 'max:150'],
            'received_quantity' => ['nullable', 'integer', 'min:0'],
            'received_signature' => ['nullable', 'string', 'max:150'],
        ], [
            'balance.required' => 'Balance is required.',
            'required_quantity.min' => 'Required quantity must be at least 1.',
            'request_signature.required' => 'Confirm signature for request is required.',
        ]);

        InjectionAntibioticRequisition::create([
            'medicine_id' => $validated['medicine_id'],
            'date' => $validated['date'],
            'balance' => $validated['balance'],
            'required_quantity' => $validated['required_quantity'],
            'request_signature' => trim($validated['request_signature']),
            'received_quantity' => isset($validated['received_quantity']) && $validated['received_quantity'] !== ''
                ? (int) $validated['received_quantity']
                : null,
            'received_signature' => !empty($validated['received_signature'])
                ? trim($validated['received_signature'])
                : null,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('injection.antibiotics', ['medicine' => $validated['medicine_id']])
            ->with('success', 'Injection Antibiotics requisition created successfully.');
    }

    /**
     * Update an existing Injection Antibiotics requisition order.
     */
    public function updateAntibioticOrder(Request $request, int $id): RedirectResponse
    {
        $user = $this->authorizeAccess($request);

        $requisition = InjectionAntibioticRequisition::findOrFail($id);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'medicine_id' => [
                'required',
                'integer',
                Rule::exists('injection_medicines', 'id')
                    ->where('type', 'injection_antibiotics')
                    ->whereNull('deleted_at'),
            ],
            'balance' => ['required', 'integer', 'min:0'],
            'required_quantity' => ['required', 'integer', 'min:1'],
            'request_signature' => ['required', 'string', 'max:150'],
            'received_quantity' => ['nullable', 'integer', 'min:0'],
            'received_signature' => ['nullable', 'string', 'max:150'],
        ]);

        $requisition->date = $validated['date'];
        $requisition->medicine_id = $validated['medicine_id'];
        $requisition->balance = $validated['balance'];
        $requisition->required_quantity = $validated['required_quantity'];
        $requisition->request_signature = trim($validated['request_signature']);

        $requisition->received_quantity = isset($validated['received_quantity']) && $validated['received_quantity'] !== ''
            ? (int) $validated['received_quantity']
            : null;

        $requisition->received_signature = !empty($validated['received_signature'])
            ? trim($validated['received_signature'])
            : null;

        $requisition->updated_by = $user->id;
        $requisition->save();

        return redirect()
            ->route('injection.antibiotics', ['medicine' => $requisition->medicine_id])
            ->with('success', 'Injection Antibiotics requisition updated successfully.');
    }

    /**
     * Soft delete an Injection Antibiotics requisition order.
     */
    public function destroyAntibioticOrder(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAccess($request);

        $requisition = InjectionAntibioticRequisition::findOrFail($id);
        $medicineId = $requisition->medicine_id;
        $requisition->delete();

        return redirect()
            ->route('injection.antibiotics', ['medicine' => $medicineId])
            ->with('success', 'Requisition deleted successfully.');
    }
}
