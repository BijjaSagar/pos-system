<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $shopId = Auth::user()->shop_id ?? \App\Models\Shop::first()?->id ?? 1;
        $startOfMonth = now()->startOfMonth();

        $monthSales = Sale::where('shop_id', $shopId)->where('sale_date', '>=', $startOfMonth)->sum('total_amount');
        $monthPurchases = Purchase::where('shop_id', $shopId)->where('purchase_date', '>=', $startOfMonth)->sum('total_amount');
        $monthProfit = Sale::where('shop_id', $shopId)->where('sale_date', '>=', $startOfMonth)->sum('profit_amount');
        $monthExpenses = Expense::where('shop_id', $shopId)->where('expense_date', '>=', $startOfMonth)->sum('amount');
        $netProfit = $monthProfit - $monthExpenses;

        $totalCustomers = Party::where('shop_id', $shopId)->where('type', 'customer')->count();
        $totalCustomerDues = Party::where('shop_id', $shopId)->where('type', 'customer')->where('current_balance', '>', 0)->sum('current_balance');
        $totalSupplierDues = Party::where('shop_id', $shopId)->where('type', 'supplier')->where('current_balance', '<', 0)->sum('current_balance');

        $lowStockProducts = Product::where('shop_id', $shopId)
            ->whereColumn('stock', '<=', 'alert_quantity')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        $recentSales = Sale::where('shop_id', $shopId)
            ->with(['customer', 'cashier'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $recentPurchases = Purchase::where('shop_id', $shopId)
            ->with('party')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'monthSales',
            'monthPurchases',
            'monthProfit',
            'netProfit',
            'totalCustomers',
            'totalCustomerDues',
            'totalSupplierDues',
            'lowStockProducts',
            'recentSales',
            'recentPurchases'
        ));
    }
}
