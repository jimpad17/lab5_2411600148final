<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;

class DashboardController extends Controller
{
    /**
     * Real database statistics replacing the Lab 4 localStorage demo values.
     */
    public function index()
    {
        $totalProducts = Product::count();
        $inStock = Product::whereColumn('quantity', '>', 'reorder_level')->count();
        $lowStock = Product::where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'reorder_level')->count();
        $outOfStock = Product::where('quantity', 0)->count();

        $inventoryValue = (float) Product::selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')->value('total');

        $categoryBreakdown = Product::selectRaw('category, COUNT(*) as items, SUM(quantity) as units, SUM(quantity * unit_price) as value')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $recentProducts = Product::latest()->take(5)->get();

        $topProducts = Product::selectRaw('*, (quantity * unit_price) as stock_value')
            ->orderByDesc('stock_value')
            ->take(5)
            ->get();

        $lowStockProducts = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->take(5)
            ->get();

        $recentTransactions = InventoryTransaction::with(['product', 'user'])
            ->latest()
            ->take(6)
            ->get();

        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour >= 5 && $hour < 12 => 'Good Morning',
            $hour >= 12 && $hour < 17 => 'Good Afternoon',
            $hour >= 17 && $hour < 21 => 'Good Evening',
            default => 'Good Night',
        };

        return view('dashboard', compact(
            'totalProducts',
            'inStock',
            'lowStock',
            'outOfStock',
            'inventoryValue',
            'categoryBreakdown',
            'recentProducts',
            'topProducts',
            'lowStockProducts',
            'recentTransactions',
            'greeting'
        ));
    }
}
