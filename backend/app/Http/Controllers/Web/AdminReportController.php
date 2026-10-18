<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shop;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminReportController extends Controller
{
    public function sales(Request $request)
    {
        $shop = Auth::user()->shop ?? Shop::first();
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));

        $query = Sale::where('shop_id', $shop->id)
            ->whereBetween('sale_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['customer', 'cashier']);

        $sales = $query->orderBy('id', 'desc')->get();

        $totalSales = $sales->sum('total_amount');
        $totalPaid = $sales->sum('paid_amount');
        $totalDue = $sales->sum('due_amount');
        $totalProfit = $sales->sum('profit_amount');

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('admin.reports.sales_pdf', compact('sales', 'totalSales', 'totalPaid', 'totalDue', 'totalProfit', 'startDate', 'endDate', 'shop'));
            return $pdf->download("sales_report_{$startDate}_{$endDate}.pdf");
        }

        return view('admin.reports.sales', compact('sales', 'totalSales', 'totalPaid', 'totalDue', 'totalProfit', 'startDate', 'endDate'));
    }

    public function purchases(Request $request)
    {
        $shop = Auth::user()->shop ?? Shop::first();
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));

        $query = Purchase::where('shop_id', $shop->id)
            ->whereBetween('purchase_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('party');

        $purchases = $query->orderBy('id', 'desc')->get();

        $totalPurchases = $purchases->sum('total_amount');
        $totalPaid = $purchases->sum('paid_amount');
        $totalDue = $purchases->sum('due_amount');

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('admin.reports.purchases_pdf', compact('purchases', 'totalPurchases', 'totalPaid', 'totalDue', 'startDate', 'endDate', 'shop'));
            return $pdf->download("purchases_report_{$startDate}_{$endDate}.pdf");
        }

        return view('admin.reports.purchases', compact('purchases', 'totalPurchases', 'totalPaid', 'totalDue', 'startDate', 'endDate'));
    }

    public function profitLoss(Request $request)
    {
        $shop = Auth::user()->shop ?? Shop::first();
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));

        $sales = Sale::where('shop_id', $shop->id)
            ->whereBetween('sale_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $totalRevenue = $sales->sum('total_amount');
        $grossProfit = $sales->sum('profit_amount');
        $cogs = $totalRevenue - $grossProfit;

        $totalExpenses = Expense::where('shop_id', $shop->id)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $totalIncome = Income::where('shop_id', $shop->id)
            ->whereBetween('income_date', [$startDate, $endDate])
            ->sum('amount');

        $netProfit = $grossProfit - $totalExpenses + $totalIncome;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0;

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('admin.reports.profit_loss_pdf', compact(
                'totalRevenue', 'cogs', 'grossProfit', 'totalExpenses', 'totalIncome', 'netProfit', 'profitMargin', 'startDate', 'endDate', 'shop'
            ));
            return $pdf->download("profit_loss_report_{$startDate}_{$endDate}.pdf");
        }

        return view('admin.reports.profit_loss', compact(
            'totalRevenue', 'cogs', 'grossProfit', 'totalExpenses', 'totalIncome', 'netProfit', 'profitMargin', 'startDate', 'endDate'
        ));
    }
}
