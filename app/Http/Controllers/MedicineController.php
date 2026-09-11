<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $medicines = $this->getDummyMedicines();
        $firstMedicine = $medicines->first();

        return redirect()->route('medicines.details', ['id' => $firstMedicine->id]);
    }

    public function getDetails($id)
    {
        $medicines = $this->getDummyMedicines();
        $selectedMedicine = $medicines->firstWhere('id', $id) ?? $medicines->first();

        $pharmacyOrders = $this->getDummyPharmacyOrders();
        $patientAdministrations = $this->getDummyPatientAdministrations();

        return view('medicines.medicine-dashboard', compact('medicines', 'selectedMedicine', 'pharmacyOrders', 'patientAdministrations'));
    }

    private function getDummyMedicines()
    {
        return collect([
            (object) [
                'id' => 1,
                'name' => 'Amoxicillin 500mg',
                'type' => 'ampoule',
                'stock' => 33,
                'unit' => 'amps',
                'stock_status' => 'sufficient',
            ],
            (object) [
                'id' => 2,
                'name' => 'Azithromycin 500mg',
                'type' => 'ampoule',
                'stock' => 20,
                'unit' => 'mcg',
                'stock_status' => 'low',
            ],
            (object) [
                'id' => 3,
                'name' => 'Ciprofloxacin 500mg',
                'type' => 'ampoule',
                'stock' => 11,
                'unit' => 'amps',
                'stock_status' => 'sufficient',
            ],
            (object) [
                'id' => 4,
                'name' => 'Cefuroxime 250mg',
                'type' => 'vial',
                'stock' => 8,
                'unit' => 'vials',
                'stock_status' => 'warning',
            ],
            (object) [
                'id' => 5,
                'name' => 'Metronidazole 400mg',
                'type' => 'ampoule',
                'stock' => 24,
                'unit' => 'amps',
                'stock_status' => 'sufficient',
            ],
            (object) [
                'id' => 6,
                'name' => 'Doxycycline 100mg',
                'type' => 'tablet',
                'stock' => 85,
                'unit' => 'tabs',
                'stock_status' => 'sufficient',
            ]
        ]);
    }

    private function getDummyPharmacyOrders()
    {
        return collect([
            (object) [
                'id' => 1,
                'date' => '08 Jul 2026',
                'req_no' => 'REQ-2026-042',
                'qty_requested' => '20 amps',
                'requested_by' => 'Nurse Priya S.',
                'ms_approval' => 'Approved',
                'qty_received' => '20 amps',
                'issuing_officer' => 'Pharm. A. Perera',
                'receiving_officer' => 'Nurse Priya S.',
            ],
            (object) [
                'id' => 2,
                'date' => '04 Jul 2026',
                'req_no' => 'REQ-2026-031',
                'qty_requested' => '20 amps',
                'requested_by' => 'Nurse Priya S.',
                'ms_approval' => 'Approved',
                'qty_received' => '20 amps',
                'issuing_officer' => 'Pharm. A. Perera',
                'receiving_officer' => 'Nurse Priya S.',
            ]
        ]);
    }

    private function getDummyPatientAdministrations()
    {
        return collect([
            (object) [
                'id' => 1,
                'date' => '10 Jul 2026',
                'bht_no' => 'BHT-39281',
                'qty_given' => '25 mcg',
                'balance' => '45 mcg',
                'sister_initials' => 'A.K.',
                'remark' => 'ICU sedation maintenance',
            ],
            (object) [
                'id' => 2,
                'date' => '10 Jul 2026',
                'bht_no' => 'BHT-39904',
                'qty_given' => '30 mcg',
                'balance' => '70 mcg',
                'sister_initials' => 'A.K.',
                'remark' => 'Pain management post-surgery',
            ],
            (object) [
                'id' => 3,
                'date' => '09 Jul 2026',
                'bht_no' => 'BHT-38102',
                'qty_given' => '25 mcg',
                'balance' => '100 mcg',
                'sister_initials' => 'P.S.',
                'remark' => 'Anesthesia induction',
            ],
            (object) [
                'id' => 4,
                'date' => '08 Jul 2026',
                'bht_no' => 'BHT-37001',
                'qty_given' => '20 mcg',
                'balance' => '125 mcg',
                'sister_initials' => 'A.K.',
                'remark' => 'Routine admin',
            ]
        ]);
    }
}
