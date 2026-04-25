@extends('admin.layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')

@section('content')
<div class="py-4 space-y-6">

{{-- Stats Grid --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
  @php
    $symbol = \App\Models\Setting::get('currency_symbol','₨');
    $cards = [
      ['label'=>'Total Revenue',    'value'=> $symbol.' '.number_format($stats['total_revenue'],0), 'icon'=>'fa-dollar-sign',   'color'=>'from-indigo-500 to-indigo-600', 'sub'=> $symbol.' '.number_format($stats['month_revenue'],0).' this month'],
      ['label'=>'Total Orders',     'value'=> number_format($stats['total_orders']),                'icon'=>'fa-shopping-bag',   'color'=>'from-purple-500 to-purple-600', 'sub'=>$stats['pending_orders'].' pending'],
      ['label'=>'Total Products',   'value'=> number_format($stats['total_products']),              'icon'=>'fa-box',            'color'=>'from-emerald-500 to-emerald-600','sub'=>$stats['low_stock_products'].' low stock'],
      ['label'=>'Customers',        'value'=> number_format($stats['total_customers']),             'icon'=>'fa-users',          'color'=>'from-amber-500 to-amber-600',   'sub'=>$stats['pending_reviews'].' pending reviews'],
    ];
  @endphp
  @foreach($cards as $card)
  <div class="bg-gradient-to-br {{ $card['color'] }} rounded-2xl p-5 text-white">
    <div class="flex justify-between items-start mb-4">
      <p class="text-white/80 text-sm font-medium">{{ $card['label'] }}</p>
      <div class="bg-white/20 w-9 h-9 rounded-xl flex items-center justify-center">
        <i class="fas {{ $card['icon'] }} text-sm"></i>
      </div>
    </div>
    <p class="text-2xl font-bold">{{ $card['value'] }}</p>
    <p class="text-white/70 text-xs mt-1">{{ $card['sub'] }}</p>
  </div>
  @endforeach
</div>

{{-- Chart + Order Status --}}
<div class="grid lg:grid-cols-3 gap-4">
  {{-- Revenue Chart --}}
  <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
    <div class="flex justify-between items-center mb-6">
      <h3 class="font-semibold text-gray-800">Revenue (Last 7 Days)</h3>
      <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Daily</span>
    </div>
    <div class="flex items-end gap-2 h-40">
      @php $maxRev = max(array_column($chartData, 'revenue')) ?: 1; @endphp
      @foreach($chartData as $day)
        @php $height = max(4, ($day['revenue'] / $maxRev) * 100); @endphp
        <div class="flex-1 flex flex-col items-center gap-1">
          <span class="text-xs text-gray-500 font-medium">{{ $symbol }}{{ number_format($day['revenue']/1000,1) }}k</span>
          <div class="w-full bg-indigo-100 rounded-t-lg relative group"
               style="height: {{ $height }}%">
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-indigo-600 to-indigo-400 rounded-t-lg"
                 style="height: 100%"></div>
            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-10">
              {{ $day['orders'] }} orders
            </div>
          </div>
          <span class="text-xs text-gray-400">{{ $day['label'] }}</span>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Order Status --}}
  <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
    <h3 class="font-semibold text-gray-800 mb-4">Order Status</h3>
    @php
      $statusColors = ['pending'=>'bg-yellow-400','confirmed'=>'bg-blue-400','processing'=>'bg-indigo-400','shipped'=>'bg-purple-400','delivered'=>'bg-green-400','cancelled'=>'bg-red-400'];
      $totalOrders  = array_sum($statusBreakdown) ?: 1;
    @endphp
    <div class="space-y-3">
      @foreach($statusBreakdown as $status => $count)
        @php $pct = round(($count / $totalOrders) * 100); @endphp
        <div>
          <div class="flex justify-between text-sm mb-1">
            <span class="text-gray-600 capitalize">{{ $status }}</span>
            <span class="font-medium text-gray-800">{{ $count }}</span>
          </div>
          <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full {{ $statusColors[$status] ?? 'bg-gray-400' }} rounded-full" style="width:{{ $pct }}%"></div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

{{-- Recent Orders + Top Products --}}
<div class="grid lg:grid-cols-3 gap-4">
  {{-- Recent Orders --}}
  <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-800">Recent Orders</h3>
      <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 text-sm hover:underline">View all →</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Order</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Customer</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
          @forelse($recentOrders as $order)
          <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3">
              <a href="{{ route('admin.orders.show',$order) }}" class="text-indigo-600 hover:underline font-mono text-xs">{{ $order->order_number }}</a>
              <p class="text-gray-400 text-xs">{{ $order->created_at->diffForHumans() }}</p>
            </td>
            <td class="px-4 py-3">
              <p class="text-gray-800 font-medium">{{ $order->customer_name }}</p>
              <p class="text-gray-400 text-xs">{{ $order->customer_phone }}</p>
            </td>
            <td class="px-4 py-3 font-semibold text-gray-800">{{ $symbol }}{{ number_format($order->total_amount) }}</td>
            <td class="px-4 py-3">
              @php $colors=['pending'=>'yellow','confirmed'=>'blue','shipped'=>'purple','delivered'=>'green','cancelled'=>'red','processing'=>'indigo']; $c=$colors[$order->status]??'gray'; @endphp
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">
                {{ $order->status_label }}
              </span>
            </td>
          </tr>
          @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No orders yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Top Products --}}
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-800">Top Products</h3>
    </div>
    <div class="divide-y divide-gray-50">
      @forelse($topProducts as $i => $product)
      <div class="px-6 py-3 flex items-center gap-3">
        <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs flex items-center justify-center font-bold flex-shrink-0">{{ $i+1 }}</span>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 truncate">{{ $product->product_name }}</p>
          <p class="text-xs text-gray-400">{{ $product->total_qty }} sold</p>
        </div>
        <p class="text-sm font-semibold text-gray-700">{{ $symbol }}{{ number_format($product->total_revenue) }}</p>
      </div>
      @empty
        <p class="px-6 py-8 text-center text-gray-400 text-sm">No sales data</p>
      @endforelse
    </div>
  </div>
</div>

</div>
@endsection
