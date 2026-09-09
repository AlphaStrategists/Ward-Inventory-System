<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SyrupController extends Controller
{
    public function index(Request $request)
    {
        // 10 controlled medicines mock dataset with pharmacy order & patient dispensation logs
        $medicines = [
            [
                'name' => 'Paracetamol Syrup',
                'balance' => '18 amps',
                'orders' => [
                    [
                        'date' => '08 Jul 2026',
                        'req_no' => 'REQ-2026-042',
                        'qty_requested' => '20 amps',
                        'req_officer' => 'Nurse Priya S.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '20 amps',
                        'issue_officer' => 'Pharm. A. Perera',
                        'receive_officer' => 'Nurse Priya S.',
                    ],
                    [
                        'date' => '04 Jul 2026',
                        'req_no' => 'REQ-2026-031',
                        'qty_requested' => '20 amps',
                        'req_officer' => 'Nurse Priya S.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '20 amps',
                        'issue_officer' => 'Pharm. A. Perera',
                        'receive_officer' => 'Nurse Priya S.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-48291',
                        'qty_given' => '2 amps',
                        'balance' => '18 amps',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Post-op pain management'
                    ],
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-48502',
                        'qty_given' => '1 amp',
                        'balance' => '20 amps',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Administered for trauma pain'
                    ],
                    [
                        'date' => '09 Jul 2026',
                        'bht_no' => 'BHT-47120',
                        'qty_given' => '2 amps',
                        'balance' => '21 amps',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Severe chest pain relief'
                    ],
                    [
                        'date' => '08 Jul 2026',
                        'bht_no' => 'BHT-46294',
                        'qty_given' => '2 amps',
                        'balance' => '23 amps',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Post-op recovery room'
                    ]
                ]
            ],
            [
                'name' => 'Amoxicillin Syrup',
                'balance' => '45 mcg',
                'orders' => [
                    [
                        'date' => '09 Jul 2026',
                        'req_no' => 'REQ-2026-045',
                        'qty_requested' => '100 mcg',
                        'req_officer' => 'Nurse Anita K.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '100 mcg',
                        'issue_officer' => 'Pharm. J. Dilan',
                        'receive_officer' => 'Nurse Anita K.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-39281',
                        'qty_given' => '25 mcg',
                        'balance' => '45 mcg',
                        'sister_initials' => 'A.K.',
                        'remark' => 'ICU sedation maintenance'
                    ],
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-39904',
                        'qty_given' => '30 mcg',
                        'balance' => '70 mcg',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Pain management post-surgery'
                    ],
                    [
                        'date' => '09 Jul 2026',
                        'bht_no' => 'BHT-38102',
                        'qty_given' => '25 mcg',
                        'balance' => '100 mcg',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Anesthesia induction'
                    ]
                ]
            ],
            [
                'name' => 'Cough Syrup',
                'balance' => '12 amps',
                'orders' => [
                    [
                        'date' => '07 Jul 2026',
                        'req_no' => 'REQ-2026-039',
                        'qty_requested' => '15 amps',
                        'req_officer' => 'Nurse Priya S.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '15 amps',
                        'issue_officer' => 'Pharm. A. Perera',
                        'receive_officer' => 'Nurse Priya S.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-44820',
                        'qty_given' => '1 amp',
                        'balance' => '12 amps',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Obstetric analgesia'
                    ],
                    [
                        'date' => '09 Jul 2026',
                        'bht_no' => 'BHT-44119',
                        'qty_given' => '2 amps',
                        'balance' => '13 amps',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Acute renal colic relief'
                    ],
                    [
                        'date' => '08 Jul 2026',
                        'bht_no' => 'BHT-43209',
                        'qty_given' => '1 amp',
                        'balance' => '15 amps',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Post-surgical pain'
                    ]
                ]
            ],
            [
                'name' => 'Multivitamin Syrup',
                'balance' => '8 vials',
                'orders' => [
                    [
                        'date' => '06 Jul 2026',
                        'req_no' => 'REQ-2026-034',
                        'qty_requested' => '10 vials',
                        'req_officer' => 'Nurse Anita K.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '10 vials',
                        'issue_officer' => 'Pharm. J. Dilan',
                        'receive_officer' => 'Nurse Anita K.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-55291',
                        'qty_given' => '1 vial',
                        'balance' => '8 vials',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Pediatric procedural sedation'
                    ],
                    [
                        'date' => '08 Jul 2026',
                        'bht_no' => 'BHT-54128',
                        'qty_given' => '1 vial',
                        'balance' => '9 vials',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Anesthesia induction in OT'
                    ]
                ]
            ],
            [
                'name' => 'Vitamin C Syrup',
                'balance' => '24 amps',
                'orders' => [
                    [
                        'date' => '08 Jul 2026',
                        'req_no' => 'REQ-2026-043',
                        'qty_requested' => '30 amps',
                        'req_officer' => 'Nurse Priya S.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '30 amps',
                        'issue_officer' => 'Pharm. A. Perera',
                        'receive_officer' => 'Nurse Priya S.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-48291',
                        'qty_given' => '2 amps',
                        'balance' => '24 amps',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Pre-medication for ICU patient'
                    ],
                    [
                        'date' => '09 Jul 2026',
                        'bht_no' => 'BHT-47120',
                        'qty_given' => '2 amps',
                        'balance' => '26 amps',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Seizure management'
                    ],
                    [
                        'date' => '08 Jul 2026',
                        'bht_no' => 'BHT-46903',
                        'qty_given' => '2 amps',
                        'balance' => '28 amps',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Sedation before endoscopy'
                    ]
                ]
            ],
            [
                'name' => 'Loratadine Syrup',
                'balance' => '85 tabs',
                'orders' => [
                    [
                        'date' => '05 Jul 2026',
                        'req_no' => 'REQ-2026-032',
                        'qty_requested' => '100 tabs',
                        'req_officer' => 'Nurse Anita K.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '100 tabs',
                        'issue_officer' => 'Pharm. J. Dilan',
                        'receive_officer' => 'Nurse Anita K.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-22910',
                        'qty_given' => '5 tabs',
                        'balance' => '85 tabs',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Relieve acute anxiety'
                    ],
                    [
                        'date' => '09 Jul 2026',
                        'bht_no' => 'BHT-22481',
                        'qty_given' => '10 tabs',
                        'balance' => '90 tabs',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Muscle spasm treatment'
                    ]
                ]
            ],
            [
                'name' => 'Phenobarbital',
                'balance' => '32 vials',
                'orders' => [
                    [
                        'date' => '04 Jul 2026',
                        'req_no' => 'REQ-2026-028',
                        'qty_requested' => '40 vials',
                        'req_officer' => 'Nurse Priya S.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '40 vials',
                        'issue_officer' => 'Pharm. A. Perera',
                        'receive_officer' => 'Nurse Priya S.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-11942',
                        'qty_given' => '4 vials',
                        'balance' => '32 vials',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Control status epilepticus'
                    ],
                    [
                        'date' => '07 Jul 2026',
                        'bht_no' => 'BHT-11204',
                        'qty_given' => '4 vials',
                        'balance' => '36 vials',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Maintenance anticonvulsant'
                    ]
                ]
            ],
            [
                'name' => 'Methadone',
                'balance' => '150 ml',
                'orders' => [
                    [
                        'date' => '03 Jul 2026',
                        'req_no' => 'REQ-2026-021',
                        'qty_requested' => '200 ml',
                        'req_officer' => 'Nurse Priya S.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '200 ml',
                        'issue_officer' => 'Pharm. A. Perera',
                        'receive_officer' => 'Nurse Priya S.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-99381',
                        'qty_given' => '10 ml',
                        'balance' => '150 ml',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Opioid dependence therapy'
                    ],
                    [
                        'date' => '09 Jul 2026',
                        'bht_no' => 'BHT-98120',
                        'qty_given' => '20 ml',
                        'balance' => '160 ml',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Chronic pain control'
                    ],
                    [
                        'date' => '08 Jul 2026',
                        'bht_no' => 'BHT-97211',
                        'qty_given' => '20 ml',
                        'balance' => '180 ml',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Substance withdrawal management'
                    ]
                ]
            ],
            [
                'name' => 'Lactulose Syrup',
                'balance' => '42 tabs',
                'orders' => [
                    [
                        'date' => '09 Jul 2026',
                        'req_no' => 'REQ-2026-044',
                        'qty_requested' => '50 tabs',
                        'req_officer' => 'Nurse Anita K.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '50 tabs',
                        'issue_officer' => 'Pharm. J. Dilan',
                        'receive_officer' => 'Nurse Anita K.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-66291',
                        'qty_given' => '4 tabs',
                        'balance' => '42 tabs',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Severe cancer pain control'
                    ],
                    [
                        'date' => '09 Jul 2026',
                        'bht_no' => 'BHT-65103',
                        'qty_given' => '4 tabs',
                        'balance' => '46 tabs',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Post-orthopedic surgery pain'
                    ]
                ]
            ],
            [
                'name' => 'Ibuprofen Syrup',
                'balance' => '60 amps',
                'orders' => [
                    [
                        'date' => '08 Jul 2026',
                        'req_no' => 'REQ-2026-041',
                        'qty_requested' => '80 amps',
                        'req_officer' => 'Nurse Priya S.',
                        'ms_approval' => 'Approved',
                        'qty_received' => '80 amps',
                        'issue_officer' => 'Pharm. A. Perera',
                        'receive_officer' => 'Nurse Priya S.',
                    ]
                ],
                'dispensations' => [
                    [
                        'date' => '10 Jul 2026',
                        'bht_no' => 'BHT-77291',
                        'qty_given' => '10 amps',
                        'balance' => '60 amps',
                        'sister_initials' => 'P.S.',
                        'remark' => 'Severe bone fracture pain'
                    ],
                    [
                        'date' => '09 Jul 2026',
                        'bht_no' => 'BHT-76120',
                        'qty_given' => '10 amps',
                        'balance' => '70 amps',
                        'sister_initials' => 'A.K.',
                        'remark' => 'Post-op recovery ward pain'
                    ]
                ]
            ]
        ];

        // Augment mock data with levels
        foreach ($medicines as &$medicine) {
            $val = (int) preg_replace('/[^0-9]/', '', $medicine['balance']);
            $medicine['numeric_balance'] = $val;
            
            // Set dynamic limits to ensure we see all color states (Red, Yellow, Green)
            if ($medicine['name'] === 'Paracetamol Syrup') { $medicine['min_level'] = 10; $medicine['warning_limit'] = 30; } // Balance 25 -> YELLOW
            elseif ($medicine['name'] === 'Amoxicillin Syrup') { $medicine['min_level'] = 50; $medicine['warning_limit'] = 80; } // Balance 45 -> RED
            elseif ($medicine['name'] === 'Multivitamin Syrup') { $medicine['min_level'] = 5; $medicine['warning_limit'] = 10; } // Balance 8 -> YELLOW
            elseif ($medicine['name'] === 'Cough Syrup') { $medicine['min_level'] = 5; $medicine['warning_limit'] = 10; } // Balance 12 -> GREEN
            else {
                $medicine['min_level'] = max(5, intval($val * 0.2));
                $medicine['warning_limit'] = max(15, intval($val * 0.5));
            }
        }
        unset($medicine);

        // Overall stats counts for display
        $totalMedicinesCount = count($medicines);
        $totalOrdersCount = array_reduce($medicines, function ($carry, $med) {
            return $carry + count($med['orders']);
        }, 0);

        return view('pages.syrup', [
            'medicines' => $medicines,
            'totalMedicinesCount' => $totalMedicinesCount,
            'totalOrdersCount' => $totalOrdersCount,
            'search' => $request->input('search', ''),
            'activeStatus' => $request->input('status', 'all'),
            'selectedMedicineName' => $request->input('medicine', 'Morphine'),
        ]);
    }
}
