<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\RequestedStock;
use App\Models\Patient;
use App\Models\Staff;

class DashboardController extends Controller
{
    public function index()
    {
        $lowStockThreshold = 15;

        $lowStockItems = Item::where('quantity', '<', $lowStockThreshold)->get();

        $pendingMsApprovals = RequestedStock::whereHas('item', function ($query) {
            $query->where('workflow_pattern', 'request_approved');
        })
        ->where('status', 'pending')
        ->with(['item', 'requestedBy'])
        ->orderBy('request_date', 'desc')
        ->get();

        $recentRequests = RequestedStock::with(['item', 'requestedBy', 'approvedByMs'])
            ->orderBy('request_id', 'desc')
            ->take(10)
            ->get();

        $stats = [
            'total_items' => Item::count(),
            'low_stock_count' => $lowStockItems->count(),
            'pending_approvals_count' => $pendingMsApprovals->count(),
            'total_patients' => Patient::count(),
            'ms_staff' => Staff::where('role', 'MS Officer')->get(),
        ];

        return view('dashboard.index', compact('lowStockItems', 'pendingMsApprovals', 'recentRequests', 'stats'));
    }
}
