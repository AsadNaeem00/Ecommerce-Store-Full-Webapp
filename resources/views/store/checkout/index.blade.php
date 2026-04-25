@extends('store.layouts.app')
@section('title','Checkout — '.\App\Models\Setting::get('store_name'))

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

  <form method="POST" action="{{ route('store.checkout.place') }}" x-data="{ paymentMethod: 'cod' }">
    @csrf
    <div class="grid lg:grid-cols-5 gap-8">

      {{-- Left: Customer + Shipping + Payment --}}
      <div class="lg:col-span-3 space-y-6">

        {{-- Customer Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h3 class="font-semibold text-gray-800 text-base mb-5 flex items-center gap-2"><i class="fas fa-user text-primary text-sm"></i> Customer Information</h3>
          <div class="grid md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
              <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                     class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary @error('customer_name') border-red-400 @enderror"
                     placeholder="Your full name">
              @error('customer_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number <span class="text-red-500">*</span></label>
              <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required
                     class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary @error('customer_phone') border-red-400 @enderror"
                     placeholder="03XX-XXXXXXX">
              @error('customer_phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
              <input type="email" name="customer_email" value="{{ old('customer_email') }}" required
                     class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary @error('customer_email') border-red-400 @enderror"
                     placeholder="your@email.com">
              @error('customer_email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
          </div>
        </div>

        {{-- Shipping Address --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h3 class="font-semibold text-gray-800 text-base mb-5 flex items-center gap-2"><i class="fas fa-map-marker-alt text-primary text-sm"></i> Shipping Address</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Street Address <span class="text-red-500">*</span></label>
              <textarea name="shipping_address" rows="2" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary @error('shipping_address') border-red-400 @enderror"
                        placeholder="House #, Street, Area">{{ old('shipping_address') }}</textarea>
              @error('shipping_address')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">City <span class="text-red-500">*</span></label>
                <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary"
                       placeholder="e.g. Karachi">
                @error('shipping_city')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Province <span class="text-red-500">*</span></label>
                <select name="shipping_province" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary">
                  <option value="">Select Province</option>
                  @foreach($provinces as $province)
                    <option value="{{ $province }}" {{ old('shipping_province')===$province?'selected':'' }}>{{ $province }}</option>
                  @endforeach
                </select>
                @error('shipping_province')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Order Notes (optional)</label>
              <textarea name="customer_notes" rows="2" placeholder="Any special instructions for delivery..."
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary">{{ old('customer_notes') }}</textarea>
            </div>
          </div>
        </div>

        {{-- Payment Method --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h3 class="font-semibold text-gray-800 text-base mb-5 flex items-center gap-2"><i class="fas fa-credit-card text-primary text-sm"></i> Payment Method</h3>
          <div class="space-y-3">
            @foreach($gateways as $gwName => $gateway)
              @php $info = $gatewayInfo[$gwName] ?? []; @endphp
              <label class="flex items-center gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all"
                     :class="paymentMethod==='{{ $gwName }}' ? 'border-primary bg-primary/5' : 'border-gray-100 hover:border-gray-200'">
                <input type="radio" name="payment_method" value="{{ $gwName }}"
                       x-model="paymentMethod" class="sr-only">
                <div class="w-12 h-8 bg-gray-100 rounded-lg flex items-center justify-center text-lg flex-shrink-0">{{ $info['icon'] ?? '💳' }}</div>
                <div class="flex-1">
                  <p class="font-semibold text-gray-800 text-sm">{{ $info['label'] ?? strtoupper($gwName) }}</p>
                  <p class="text-xs text-gray-400">{{ $info['description'] ?? '' }}</p>
                </div>
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                     :class="paymentMethod==='{{ $gwName }}' ? 'border-primary' : 'border-gray-300'">
                  <div class="w-2.5 h-2.5 rounded-full bg-primary" x-show="paymentMethod==='{{ $gwName }}'"></div>
                </div>
              </label>
            @endforeach

            @if($gateways->isEmpty())
              <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                No payment methods are configured. Please contact the store.
              </div>
            @endif
          </div>
        </div>

        {{-- Terms --}}
        <div>
          <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" name="terms" required class="mt-0.5 w-4 h-4 rounded text-primary">
            <span class="text-sm text-gray-600">I agree to the <a href="{{ route('store.page','terms') }}" target="_blank" class="text-primary hover:underline">Terms of Service</a> and <a href="{{ route('store.page','privacy') }}" target="_blank" class="text-primary hover:underline">Privacy Policy</a></span>
          </label>
          @error('terms')<p class="mt-1 text-xs text-red-500">You must accept the terms to continue.</p>@enderror
        </div>

        <button type="submit"
                class="w-full btn-primary py-4 rounded-2xl font-bold text-base flex items-center justify-center gap-2 shadow-xl">
          <i class="fas fa-lock"></i>
          <span x-text="paymentMethod === 'cod' ? 'Place Order (COD)' : 'Place Order & Pay'"></span>
        </button>
      </div>

      {{-- Right: Order Summary --}}
      <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sticky top-24">
          <h3 class="font-semibold text-gray-800 text-base mb-4">Order Summary</h3>
          <div class="space-y-3 mb-4">
            @foreach($cart as $item)
            <div class="flex gap-3 items-center">
              <div class="relative flex-shrink-0">
                <img src="{{ $item['image'] }}" class="w-12 h-12 object-cover rounded-xl bg-gray-100">
                <span class="absolute -top-1.5 -right-1.5 bg-gray-700 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center">{{ $item['quantity'] }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ $item['name'] }}</p>
                <p class="text-xs text-gray-400">{{ $sym }}{{ number_format($item['price']) }} × {{ $item['quantity'] }}</p>
              </div>
              <p class="text-sm font-semibold text-gray-700 flex-shrink-0">{{ $sym }}{{ number_format($item['subtotal']) }}</p>
            </div>
            @endforeach
          </div>
          <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
            <div class="flex justify-between text-gray-500">
              <span>Subtotal</span><span>{{ $sym }}{{ number_format($cartService->subtotal()) }}</span>
            </div>
            <div class="flex justify-between text-gray-500">
              <span>Shipping</span>
              <span class="{{ $cartService->shippingCost()==0 ? 'text-green-600 font-medium' : '' }}">
                {{ $cartService->shippingCost() > 0 ? $sym.number_format($cartService->shippingCost()) : 'FREE' }}
              </span>
            </div>
            <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-100">
              <span>Total</span><span>{{ $sym }}{{ number_format($cartService->total()) }}</span>
            </div>
          </div>
          <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400 bg-gray-50 rounded-xl py-2.5">
            <i class="fas fa-lock text-green-500"></i> Secured & Encrypted
            <i class="fab fa-cc-visa text-blue-600 text-base ml-2"></i>
            <i class="fab fa-cc-mastercard text-red-500 text-base"></i>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
