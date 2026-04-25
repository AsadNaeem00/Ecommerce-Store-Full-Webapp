@extends('admin.layouts.app')
@section('title','Order #'.$order->order_number)
@section('page-title','Order Details')

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp
<div class="py-4 space-y-5">

{{-- Header --}}
<div class="flex flex-wrap gap-3 justify-between items-center">
  <div>
    <p class="font-mono text-indigo-600 font-bold text-lg">{{ $order->order_number }}</p>
    <p class="text-sm text-gray-400">Placed {{ $order->created_at->format('d M Y, h:i A') }}</p>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('admin.orders.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-medium transition">
      ← Back to Orders
    </a>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  {{-- Left: Items + Status History --}}
  <div class="lg:col-span-2 space-y-5">

    {{-- Order Items --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800">Order Items</h3>
      </div>
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Qty</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Price</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          @foreach($order->items as $item)
          <tr>
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                @if($item->product_image)
                  <img src="{{ $item->product_image }}" class="w-10 h-10 object-cover rounded-lg bg-gray-100">
                @else
                  <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400"><i class="fas fa-box text-sm"></i></div>
                @endif
                <span class="font-medium text-gray-800">{{ $item->product_name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $item->product_sku }}</td>
            <td class="px-4 py-3 text-center text-gray-700">{{ $item->quantity }}</td>
            <td class="px-4 py-3 text-right text-gray-600">{{ $sym }}{{ number_format($item->unit_price) }}</td>
            <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ $sym }}{{ number_format($item->total_price) }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="bg-gray-50 border-t border-gray-100">
          <tr>
            <td colspan="4" class="px-4 py-2 text-right text-sm text-gray-500">Subtotal</td>
            <td class="px-4 py-2 text-right text-sm font-medium text-gray-700">{{ $sym }}{{ number_format($order->subtotal) }}</td>
          </tr>
          <tr>
            <td colspan="4" class="px-4 py-2 text-right text-sm text-gray-500">Shipping</td>
            <td class="px-4 py-2 text-right text-sm font-medium text-gray-700">
              {{ $order->shipping_cost > 0 ? $sym.number_format($order->shipping_cost) : 'Free' }}
            </td>
          </tr>
          @if($order->discount_amount > 0)
          <tr>
            <td colspan="4" class="px-4 py-2 text-right text-sm text-green-600">Discount</td>
            <td class="px-4 py-2 text-right text-sm font-medium text-green-600">-{{ $sym }}{{ number_format($order->discount_amount) }}</td>
          </tr>
          @endif
          <tr class="font-bold">
            <td colspan="4" class="px-4 py-3 text-right text-gray-800">Total</td>
            <td class="px-4 py-3 text-right text-indigo-600 text-base">{{ $sym }}{{ number_format($order->total_amount) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    {{-- Update Status --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-4">Update Order Status</h3>
      <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Status</label>
            <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
              @foreach(\App\Models\Order::$statusLabels as $value => $label)
                <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Status</label>
            <form method="POST" action="{{ route('admin.orders.payment-status', $order) }}" id="payForm">
              @csrf @method('PUT')
              <select name="payment_status" onchange="document.getElementById('payForm').submit()"
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @foreach(['unpaid'=>'Unpaid','paid'=>'Paid','partially_paid'=>'Partially Paid','refunded'=>'Refunded'] as $v => $l)
                  <option value="{{ $v }}" {{ $order->payment_status === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
              </select>
            </form>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Note (optional)</label>
          <textarea name="note" rows="2" placeholder="Add a note about this status change..."
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition">
          <i class="fas fa-save mr-1"></i> Update Status
        </button>
      </form>
    </div>

    {{-- Status History --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-4">Status History</h3>
      <div class="relative">
        <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-gray-100"></div>
        <div class="space-y-4 pl-10">
          @foreach($order->statusHistory as $history)
          <div class="relative">
            <div class="absolute -left-7 w-3.5 h-3.5 rounded-full bg-indigo-500 border-2 border-white mt-0.5"></div>
            <div class="bg-gray-50 rounded-xl p-3">
              <div class="flex justify-between items-start">
                <span class="text-sm font-semibold text-gray-800 capitalize">{{ $history->status }}</span>
                <span class="text-xs text-gray-400">{{ $history->created_at->format('d M Y, h:i A') }}</span>
              </div>
              @if($history->note)
                <p class="text-xs text-gray-500 mt-1">{{ $history->note }}</p>
              @endif
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Right: Customer + Shipping --}}
  <div class="space-y-5">
    {{-- Current Status --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-3">Current Status</h3>
      @php $c = $order->status_color; @endphp
      <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold bg-{{ $c }}-100 text-{{ $c }}-700">
        {{ $order->status_label }}
      </span>
      <div class="mt-3 space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-500">Payment:</span>
          <span class="font-medium text-gray-800 uppercase">{{ $order->payment_method }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Payment Status:</span>
          <span class="font-medium {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-amber-600' }}">
            {{ ucwords(str_replace('_',' ',$order->payment_status)) }}
          </span>
        </div>
        @if($order->payment_reference)
        <div class="flex justify-between">
          <span class="text-gray-500">Ref:</span>
          <span class="font-mono text-xs text-gray-600">{{ $order->payment_reference }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- Customer Info --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-4">Customer</h3>
      <div class="space-y-3 text-sm">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-xs font-bold flex-shrink-0">
            {{ strtoupper(substr($order->customer_name, 0, 1)) }}
          </div>
          <div>
            <p class="font-semibold text-gray-800">{{ $order->customer_name }}</p>
            <p class="text-gray-500">{{ $order->customer_email }}</p>
            <p class="text-gray-500">{{ $order->customer_phone }}</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Shipping Address --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-3">Shipping Address</h3>
      <div class="text-sm text-gray-600 space-y-1">
        <p>{{ $order->shipping_address }}</p>
        <p>{{ $order->shipping_city }}, {{ $order->shipping_province }}</p>
        @if($order->shipping_postal_code)<p>{{ $order->shipping_postal_code }}</p>@endif
      </div>
    </div>

    @if($order->customer_notes)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
      <h3 class="font-semibold text-amber-800 mb-2 text-sm"><i class="fas fa-sticky-note mr-2"></i>Customer Note</h3>
      <p class="text-sm text-amber-700">{{ $order->customer_notes }}</p>
    </div>
    @endif
  </div>
</div>
</div>
@endsection
