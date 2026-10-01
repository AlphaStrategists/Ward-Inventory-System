<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;

class MsApprovalController extends Controller
{
    public function index(Request $request)
    {
        $ordersQuery = OrderDetail::with([
            'order.requester',
            'order.approver',
            'order.ward',
            'medicine.unit',
            'medicine.category'
        ]);

        if ($request->filled('search_records')) {
            $searchTerm = $request->search_records;
            $ordersQuery->where(function ($query) use ($searchTerm) {
                $query->whereHas('order', function ($q) use ($searchTerm) {
                    $q->where('req_no', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('requester', function ($u) use ($searchTerm) {
                          $u->where('name', 'like', '%' . $searchTerm . '%');
                      });
                })->orWhereHas('medicine', function ($m) use ($searchTerm) {
                    $m->where('name', 'like', '%' . $searchTerm . '%');
                });
            });
        }

        if ($request->filled('date')) {
            $ordersQuery->whereHas('order', function ($q) use ($request) {
                $q->whereDate('date', $request->date);
            });
        }

        if ($request->filled('status')) {
            $ordersQuery->whereHas('order', function ($q) use ($request) {
                $q->where('ms_approval_status', $request->status);
            });
        }

        if ($request->filled('category_id')) {
            $ordersQuery->whereHas('medicine', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $requisitionLogs = $ordersQuery->orderBy('id', 'desc')->get();

        // Statistics
        $allOrders = OrderDetail::with('order')->get();
        $totalCount = $allOrders->count();
        $pendingCount = $allOrders->where('order.ms_approval_status', 'Pending')->count();
        $completedCount = $allOrders->where('order.ms_approval_status', 'Completed')->count();
        $approvedCount = $allOrders->where('order.ms_approval_status', 'Approved')->count();

        $categories = Category::all();
        $wards = Ward::all();
        $users = User::all();

        return view('ms.index', compact(
            'requisitionLogs',
            'totalCount',
            'pendingCount',
            'completedCount',
            'approvedCount',
            'categories',
            'wards',
            'users'
        ));
    }
}
