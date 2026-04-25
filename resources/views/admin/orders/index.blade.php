@extends('admin.layouts.app')
@section('title','Orders')
@section('page-title','Orders')

@section('content')
<div class="py-4 space-y-4">
{{-- Filters --}}
<div class="flex flex-wrap gap-3 items-center justify-between">
  <form method="GET" class="flex gap-2 flex-wrap">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Order#, customer, phone..."
           class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-60">
    <select name="status" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
      <option value="">All Status</option>
      @foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $s)
        <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
    <select name="payment" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
      <option value="">All Payments</option>
      @foreach(['cod','easypaisa','jazzcash','card'] as $p)
        <option value="{{ $p }}" {{ request('payment')==$p?'selected':'' }}>{{ strtoupper($p) }}</option>
      @endforeach
    </select>
    <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
      <i class="fas fa-filter mr-1"></i> Filter
    </button>
  </form>
  <span class="text-sm text-gray-500">{{ $orders->total() }} orders</span>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 border-b border-gray-100">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order #</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        @php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp
        @forelse($orders as $order)
        <tr class="hover:bg-gray-50 transition-colors">
          <td class="px-4 py-3 font-mono text-xs text-indigo-600 font-medium">{{ $order->order_number }}</td>
          <td class="px-4 py-3">
            <p class="font-medium text-gray-800">{{ $order->customer_name }}</p>
            <p class="text-xs text-gray-400">{{ $order->customer_phone }}</p>
          </td>
          <td class="px-4 py-3 text-gray-600">{{ $order->items->count() }} item(s)</td>
          <td class="px-4 py-3 font-semibold text-gray-800">{{ $sym }}{{ number_format($order->total_amount) }}</td>
          <td class="px-4 py-3">
            <span class="inline-flex items-center gap-1 text-xs font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">
              {{ strtoupper($order->payment_method) }}
            </span>
            @if($order->payment_status === 'paid')
              <span class="block mt-0.5 text-xs text-green-600"><i class="fas fa-check-circle mr-0.5"></i>Paid</span>
            @endif
          </td>
          <td class="px-4 py-3">
            @php $c = $order->status_color; @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">
              {{ $order->status_label }}
            </span>
          </td>
          <td class="px-4 py-3 text-xs text-gray-400">{{ $order->created_at->format('M d, Y') }}</td>
          <td class="px-4 py-3">
            <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-700 text-xs font-medium">
              View →
            </a>
          </td>
        </tr>
        @empty
          <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">
            <i class="fas fa-shopping-bag text-4xl mb-3 block text-gray-200"></i> No orders found
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($orders->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $orders->links() }}</div>
  @endif
</div>
</div>
@endsection
