<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;

class ReportController extends Controller
{
    public function index()
    {
        $categories = Product::selectRaw('category, COUNT(*) as items, SUM(quantity) as units, SUM(quantity * unit_price) as value')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $totalValue = (float) $categories->sum('value');
        $totalItems = (int) $categories->sum('items');
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')->get();

        $transactionCounts = InventoryTransaction::selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $recentTransactions = InventoryTransaction::with(['product', 'user'])
            ->latest()->take(10)->get();

        return view('reports.index', compact(
            'categories',
            'totalValue',
            'totalItems',
            'lowStockProducts',
            'transactionCounts',
            'recentTransactions'
        ));
    }

    public function exportCsv()
    {
        $categories = Product::selectRaw('category, COUNT(*) as items, SUM(quantity) as units, SUM(quantity * unit_price) as value')
            ->groupBy('category')->orderBy('category')->get();

        $filename = 'jimtrack-inventory-report-'.now()->format('Y-m-d').'.csv';

        return response()->stream(function () use ($categories) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Category', 'Products', 'Units On Hand', 'Inventory Value']);

            foreach ($categories as $row) {
                fputcsv($handle, [
                    $row->category,
                    $row->items,
                    $row->units,
                    number_format((float) $row->value, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
