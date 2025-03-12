<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\StockTransfer;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        return inertia('Dashboard', [
            'totalProducts' => Product::count(),
            'totalStock' => Stock::sum('quantity'),
            'outOfStockProducts' => Product::whereDoesntHave('stocks')->count(),
            'lowStockProducts' => Stock::where('quantity', '<=', 5)->count(),
            'totalWarehouses' => Warehouse::count(),
            'mostStockedWarehouse' => Warehouse::withSum('stocks', 'quantity')->orderByDesc('stocks_sum_quantity')->first(),
            'recentTransfers' => StockTransfer::latest()->limit(5)->get()->map(fn($transfer) => [
                ...$transfer->toArray(),
                'created_at' => $transfer->created_at->format('Y-m-d H:i:sa'),
            ]),
            'recentTransactions' => StockTransaction::latest()->limit(5)->get()->map(fn($transaction) => [
                ...$transaction->toArray(),
                'created_at' => $transaction->created_at->format('Y-m-d H:i:sa'),
            ]),
        ]);
    }
}
