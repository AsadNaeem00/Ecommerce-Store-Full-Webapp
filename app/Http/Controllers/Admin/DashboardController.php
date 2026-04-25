<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, Product, Category, Review};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $month = now()->startOfMonth();

        // Revenue stats
        $stats = [
            'total_orders'        => Order::count(),
            'pending_orders'      => Order::where('status', 'pending')->count(),
            'total_revenue'       => Order::whereIn('status', ['confirmed','processing','shipped','delivered'])->sum('total_amount'),
            'today_revenue'       => Order::where('created_at', '>=', $today)->whereIn('status', ['confirmed','processing','shipped','delivered'])->sum('total_amount'),
            'month_revenue'       => Order::where('created_at', '>=', $month)->whereIn('status', ['confirmed','processing','shipped','delivered'])->sum('total_amount'),
            'total_products'      => Product::count(),
            'low_stock_products'  => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->where('track_quantity', true)->count(),
            'pending_reviews'     => Review::where('status', 'pending')->count(),
            'total_customers'     => Order::distinct('customer_email')->count('customer_email'),
        ];

        // Recent orders
        $recentOrders = Order::with('items')->latest()->take(8)->get();

        // Revenue chart (last 7 days)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData[] = [
                'label'   => $date->format('M d'),
                'revenue' => (float) Order::whereDate('created_at', $date->toDateString())
                    ->whereIn('status', ['confirmed','processing','shipped','delivered'])
                    ->sum('total_amount'),
                'orders'  => Order::whereDate('created_at', $date->toDateString())->count(),
            ];
        }

        // Order status breakdown
        $statusBreakdown = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->pluck('count', 'status')->toArray();

        // Top products
        $topProducts = DB::table('order_items')
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)->get();

        return view('admin.dashboard.index', compact('stats', 'recentOrders', 'chartData', 'statusBreakdown', 'topProducts'));
    }
}
