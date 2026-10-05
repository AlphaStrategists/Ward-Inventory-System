<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGeneralItemRequest;
use App\Http\Requests\UpdateGeneralItemRequest;
use App\Models\Category;
use App\Models\GeneralItem;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneralItemController extends Controller
{
    /**
     * Display the general inventory management page.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $selectedId = $request->query('selected_item');

        // Query general items with eager loading to prevent N+1 queries
        $itemsQuery = GeneralItem::query()
            ->with(['category'])
            ->withSum('transactions as total_received', 'quantity_received')
            ->withSum('transactions as total_issued', 'quantity_issued')
            ->withSum('stockAdjustments as total_adjustments', 'quantity');

        if ($search) {
            $itemsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $itemsQuery->where('category_id', $categoryId);
        }

        $items = $itemsQuery->orderBy('name')->get();

        // Calculate computed balance and low-stock flag for each item
        $items->each(function (GeneralItem $item) {
            $received = (int) ($item->total_received ?? 0);
            $issued = (int) ($item->total_issued ?? 0);
            $adjustments = (int) ($item->total_adjustments ?? 0);
            $balance = ($received - $issued) + $adjustments;

            $item->setAttribute('computed_balance', $balance);
            $item->setAttribute('is_low_stock', $balance <= GeneralItem::DEFAULT_LOW_STOCK_THRESHOLD);
        });

        // Determine currently selected item
        $selectedItem = null;
        if ($selectedId) {
            $selectedItem = $items->firstWhere('id', $selectedId);
        }
        if (!$selectedItem && $items->isNotEmpty()) {
            $selectedItem = $items->first();
        }

        // Load chronological transactions and running balance for selected item
        $transactions = collect();
        $adjustments = collect();

        if ($selectedItem) {
            $rawTransactions = $selectedItem->transactions()
                ->with(['ward', 'recorder'])
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            // Compute running balance in chronological order
            $runningTotal = 0;
            $transactionsWithBalance = $rawTransactions->map(function ($tx) use (&$runningTotal) {
                $runningTotal += ((int) $tx->quantity_received - (int) $tx->quantity_issued);
                $tx->running_balance = $runningTotal;
                return $tx;
            });

            // Date filtering if requested
            $filterDate = $request->query('filter_date');
            $filterSearch = $request->query('filter_search');

            if ($filterDate) {
                $transactionsWithBalance = $transactionsWithBalance->filter(function ($tx) use ($filterDate) {
                    return $tx->date?->format('Y-m-d') === $filterDate;
                });
            }

            if ($filterSearch) {
                $transactionsWithBalance = $transactionsWithBalance->filter(function ($tx) use ($filterSearch) {
                    return str_contains(strtolower($tx->recorder?->name ?? ''), strtolower($filterSearch)) ||
                           str_contains(strtolower($tx->ward?->ward_name ?? ''), strtolower($filterSearch));
                });
            }

            $transactions = $transactionsWithBalance->reverse(); // Newest on top for log viewing

            $adjustments = $selectedItem->stockAdjustments()
                ->with(['ward', 'adjuster'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $categories = Category::orderBy('name')->get();
        $wards = Ward::orderBy('ward_number')->get();
        $users = User::orderBy('name')->get();

        return view('general-inventory.index', compact(
            'items',
            'selectedItem',
            'transactions',
            'adjustments',
            'categories',
            'wards',
            'users'
        ));
    }

    /**
     * Store a newly created general item.
     */
    public function store(StoreGeneralItemRequest $request): RedirectResponse
    {
        $item = GeneralItem::create($request->validated());

        return redirect()
            ->route('general-inventory.index', ['selected_item' => $item->id])
            ->with('success', "Item '{$item->name}' ({$item->item_code}) created successfully.");
    }

    /**
     * Update the specified general item.
     */
    public function update(UpdateGeneralItemRequest $request, GeneralItem $general_item): RedirectResponse
    {
        $general_item->update($request->validated());

        return redirect()
            ->route('general-inventory.index', ['selected_item' => $general_item->id])
            ->with('success', "Item '{$general_item->name}' updated successfully.");
    }

    /**
     * Remove the specified general item if no transactions exist.
     */
    public function destroy(GeneralItem $general_item): RedirectResponse
    {
        if ($general_item->transactions()->exists() || $general_item->stockAdjustments()->exists()) {
            return redirect()
                ->route('general-inventory.index', ['selected_item' => $general_item->id])
                ->with('error', 'Cannot delete item with existing transaction or adjustment records.');
        }

        $name = $general_item->name;
        $general_item->delete();

        return redirect()
            ->route('general-inventory.index')
            ->with('success', "Item '{$name}' deleted successfully.");
    }
}
