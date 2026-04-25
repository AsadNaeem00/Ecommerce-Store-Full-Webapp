{{-- ORDER CONFIRMATION --}}
@extends('store.layouts.app')
@section('title','Order Confirmed — '.\App\Models\Setting::get('store_name'))
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">

  {{-- Success Icon --}}
  <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
    <i class="fas fa-check-circle text-green-500 text-5xl"></i>
  </div>

  <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Placed Successfully!</h1>
  <p class="text-gray-500 text-lg mb-2">Thank you, <strong>{{ $order->customer_name }}</strong>!</p>
  <p class="text-gray-400 mb-8">We have received your order and will contact you shortly to confirm.</p>

  {{-- Order Number --}}
  <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5 mb-8">
    <p class="text-sm text-indigo-500 font-medium mb-1">Your Order Number</p>
    <p class="text-2xl font-mono font-bold text-indigo-700 tracking-wider">{{ $order->order_number }}</p>
    <p class="text-xs text-indigo-400 mt-1">Save this for your reference</p>
  </div>

  {{-- Order Summary --}}
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-left mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">Order Summary</h3>
    <div class="space-y-3 divide-y divide-gray-50">
      @foreach($order->items as $item)
      <div class="flex items-center gap-3 pt-3 first:pt-0">
        @if($item->product_image)
          <img src="{{ $item->product_image }}" class="w-12 h-12 object-cover rounded-xl bg-gray-100 flex-shrink-0">
        @else
          <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center text-gray-300 flex-shrink-0"><i class="fas fa-box"></i></div>
        @endif
        <div class="flex-1 min-w-0">
          <p class="font-medium text-gray-800 text-sm truncate">{{ $item->product_name }}</p>
          <p class="text-xs text-gray-400">Qty: {{ $item->quantity }} × {{ $sym }}{{ number_format($item->unit_price) }}</p>
        </div>
        <p class="font-semibold text-gray-700 text-sm flex-shrink-0">{{ $sym }}{{ number_format($item->total_price) }}</p>
      </div>
      @endforeach
    </div>
    <div class="border-t border-gray-100 mt-4 pt-4 space-y-2 text-sm">
      <div class="flex justify-between text-gray-500">
        <span>Subtotal</span><span>{{ $sym }}{{ number_format($order->subtotal) }}</span>
      </div>
      <div class="flex justify-between text-gray-500">
        <span>Shipping</span>
        <span>{{ $order->shipping_cost > 0 ? $sym.number_format($order->shipping_cost) : 'Free' }}</span>
      </div>
      <div class="flex justify-between font-bold text-gray-900 text-base pt-1 border-t border-gray-100">
        <span>Total</span><span>{{ $sym }}{{ number_format($order->total_amount) }}</span>
      </div>
    </div>
  </div>

  {{-- Shipping & Payment Info --}}
  <div class="grid sm:grid-cols-2 gap-4 mb-8 text-left">
    <div class="bg-gray-50 rounded-2xl p-4">
      <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2"><i class="fas fa-map-marker-alt mr-1"></i> Shipping To</h4>
      <p class="text-sm text-gray-700 font-medium">{{ $order->customer_name }}</p>
      <p class="text-sm text-gray-500">{{ $order->shipping_address }}</p>
      <p class="text-sm text-gray-500">{{ $order->shipping_city }}, {{ $order->shipping_province }}</p>
    </div>
    <div class="bg-gray-50 rounded-2xl p-4">
      <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2"><i class="fas fa-credit-card mr-1"></i> Payment</h4>
      <p class="text-sm font-medium text-gray-700">{{ strtoupper($order->payment_method) }}</p>
      @php $pColors=['unpaid'=>'text-amber-600','paid'=>'text-green-600','partially_paid'=>'text-blue-600']; @endphp
      <p class="text-sm {{ $pColors[$order->payment_status]??'text-gray-500' }} font-medium mt-1">
        {{ ucwords(str_replace('_',' ',$order->payment_status)) }}
      </p>
      @if($order->payment_method === 'cod')
        <p class="text-xs text-gray-400 mt-1">Pay when your order arrives</p>
      @endif
    </div>
  </div>

  {{-- What's Next --}}
  <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-8 text-left">
    <h4 class="font-semibold text-amber-800 mb-2"><i class="fas fa-info-circle mr-2"></i>What happens next?</h4>
    <ol class="text-sm text-amber-700 space-y-1.5 list-decimal list-inside">
      <li>Our team will review and confirm your order</li>
      <li>We'll call or message you at <strong>{{ $order->customer_phone }}</strong></li>
      <li>Your order will be packed and dispatched</li>
      <li>Delivery typically takes 2–5 business days</li>
    </ol>
  </div>

  {{-- WhatsApp CTA --}}
  @if($wa = \App\Models\Setting::get('whatsapp_number'))
  <a href="https://wa.me/{{ preg_replace('/\D/','', $wa) }}?text={{ urlencode('Hi! I just placed order #'.$order->order_number.'. Can you confirm?') }}"
     target="_blank"
     class="w-full bg-green-500 hover:bg-green-600 text-white py-3.5 rounded-2xl font-semibold flex items-center justify-center gap-2 mb-3 transition">
    <i class="fab fa-whatsapp text-xl"></i> Track via WhatsApp
  </a>
  @endif

  <a href="{{ route('store.products.index') }}" class="block w-full btn-primary py-3.5 rounded-2xl font-semibold text-center">
    <i class="fas fa-shopping-bag mr-2"></i> Continue Shopping
  </a>
</div>
@endsection
