<?php
namespace App\Http\Controllers;
use App\Models\Batch;
use App\Models\Issue;
use App\Models\Item;
use App\Models\RequestLine;
use App\Models\Staff;
use App\Models\StockRequest;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class InjectionAntibioticController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch Injection Antibiotic Items for Sidebar
        $sidebarQuery = Item::query()
            ->where('item_subtype', 'injection_antibiotic');
        if ($request->filled('sidebar_search')) {
            $sidebarQuery->where('item_name', 'like', '%' . $request->sidebar_search . '%');
        }
        $items = $sidebarQuery->orderBy('item_name')->get();
        // Fallback if empty
        if ($items->isEmpty()) {
            $items = Item::where('item_subtype', 'injection_antibiotic')
                ->orWhere('item_subtype', 'oral_antibiotic')
                ->get();
        }
        $selectedItem = null;
        if ($request->filled('selected_id')) {
            $selectedItem = Item::find($request->selected_id);
        } elseif ($items->isNotEmpty()) {
            $selectedItem = $items->first();
        }

    }