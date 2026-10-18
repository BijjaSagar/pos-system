<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Store Owner / Cashier POS Dashboard Stats
     */
    public function getStoreStats(Request $request)
    {
        $user = $request->user();
        $shopId = $user->shop_id;

        $today = now()->startOfDay();

        $todaySales = Sale::where('shop_id', $shopId)->where('sale_date', '>=', $today)->sum('total_amount');
        $todayProfit = Sale::where('shop_id', $shopId)->where('sale_date', '>=', $today)->sum('profit_amount');
        $totalProducts = Product::where('shop_id', $shopId)->count();
        $lowStockCount = Product::where('shop_id', $shopId)->whereColumn('stock', '<=', 'alert_quantity')->count();
        $totalDue = Party::where('shop_id', $shopId)->where('type', 'customer')->where('current_balance', '>', 0)->sum('current_balance');

        $recentSales = Sale::where('shop_id', $shopId)
            ->with('customer')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'today_sales' => (float) $todaySales,
                'today_profit' => (float) $todayProfit,
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'total_due' => (float) $totalDue,
            ],
            'recent_sales' => $recentSales,
        ]);
    }

    /**
     * Super Admin SaaS Platform Stats
     */
    public function getSuperAdminStats(Request $request)
    {
        if ($request->user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $totalShops = Shop::count();
        $activeShops = Shop::where('status', 'active')->count();
        $totalPlans = Plan::count();

        $shops = Shop::with('plan')->orderBy('id', 'desc')->limit(10)->get();

        return response()->json([
            'success' => true,
            'super_admin_stats' => [
                'total_shops' => $totalShops,
                'active_shops' => $activeShops,
                'total_plans' => $totalPlans,
            ],
            'recent_shops' => $shops,
        ]);
    }
}
