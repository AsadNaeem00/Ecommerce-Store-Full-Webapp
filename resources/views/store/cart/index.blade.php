{{-- CART VIEW --}}
@extends('store.layouts.app')
@section('title','Shopping Cart — '.\App\Models\Setting::get('store_name'))
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

  @if($cartService->isEmpty())
    <div class="text-center py-20 bg-white rounded-3xl border border-gray-100">
      <i class="fas fa-shopping-cart text-6xl text-gray-200 mb-4"></i>
      <p class="text-xl font-medium text-gray-400 mb-2">Your cart is empty</p>
      <p class="text-gray-400 text-sm mb-6">Add some products and come back!</p>
      <a href="{{ route('store.products.index') }}" class="btn-primary px-8 py-3 rounded-2xl font-semibold inline-block">Browse Products</a>
    </div>
  @else
  <div class="grid lg:grid-cols-3 gap-8">
    {{-- Cart Items --}}
    <div class="lg:col-span-2 space-y-3">
      @foreach($cart as $item)
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex gap-4 items-center" id="cart-item-{{ $item['product_id'] }}">
        <a href="{{ route('store.products.show', $item['slug']) }}">
          <img src="{{ $item['image'] }}" class="w-20 h-20 object-cover rounded-xl bg-gray-100 flex-shrink-0">
        </a>
        <div class="flex-1 min-w-0">
          <a href="{{ route('store.products.show', $item['slug']) }}" class="font-semibold text-gray-800 hover:text-primary transition line-clamp-1">{{ $item['name'] }}</a>
          <p class="text-sm text-gray-500">{{ $sym }}{{ number_format($item['price']) }} each</p>
          {{-- Quantity Control --}}
          <div class="flex items-center gap-2 mt-2">
            <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
              <button onclick="updateQty({{ $item['product_id'] }}, {{ $item['quantity']-1 }})" class="w-8 h-8 text-gray-600 hover:bg-gray-50 transition text-sm font-medium flex items-center justify-center">−</button>
              <span id="qty-{{ $item['product_id'] }}" class="w-10 text-center text-sm font-semibold">{{ $item['quantity'] }}</span>
              <button onclick="updateQty({{ $item['product_id'] }}, {{ $item['quantity']+1 }})" class="w-8 h-8 text-gray-600 hover:bg-gray-50 transition text-sm font-medium flex items-center justify-center">+</button>
            </div>
            <button onclick="removeItem({{ $item['product_id'] }})" class="text-red-400 hover:text-red-600 text-xs flex items-center gap-1 ml-2 transition">
              <i class="fas fa-trash"></i> Remove
            </button>
          </div>
        </div>
        <div class="text-right flex-shrink-0">
          <p id="subtotal-{{ $item['product_id'] }}" class="font-bold text-gray-900">{{ $sym }}{{ number_format($item['subtotal']) }}</p>
        </div>
      </div>
      @endforeach

      <div class="flex justify-between pt-2">
        <a href="{{ route('store.products.index') }}" class="text-primary text-sm hover:underline flex items-center gap-1">
          <i class="fas fa-arrow-left text-xs"></i> Continue Shopping
        </a>
        <form method="POST" action="{{ route('store.cart.clear') }}">
          @csrf
          <button type="submit" class="text-red-400 hover:text-red-600 text-sm transition" onclick="return confirm('Clear cart?')">Clear Cart</button>
        </form>
      </div>
    </div>

    {{-- Order Summary --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 h-fit sticky top-24">
      <h3 class="font-semibold text-gray-800 text-lg mb-5">Order Summary</h3>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between text-gray-600">
          <span>Subtotal</span>
          <span id="cartSubtotal" class="font-medium">{{ $sym }}{{ number_format($cartService->subtotal()) }}</span>
        </div>
        <div class="flex justify-between text-gray-600">
          <span>Shipping</span>
          <span id="cartShipping" class="font-medium">{{ $cartService->shippingCost() > 0 ? $sym.number_format($cartService->shippingCost()) : '🎉 Free' }}</span>
        </div>
        <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900 text-base">
          <span>Total</span>
          <span id="cartTotal">{{ $sym }}{{ number_format($cartService->total()) }}</span>
        </div>
      </div>
      <a href="{{ route('store.checkout.index') }}"
         class="mt-5 w-full btn-primary py-3.5 rounded-2xl font-semibold flex items-center justify-center gap-2 shadow-lg">
        <i class="fas fa-lock text-sm"></i> Proceed to Checkout
      </a>
      <div class="flex items-center justify-center gap-2 mt-4 text-xs text-gray-400">
        <i class="fas fa-shield-alt"></i> Secure & Encrypted Checkout
      </div>
    </div>
  </div>
  @endif
</div>

@push('scripts')
<script>
const sym = '{{ $sym }}';
function updateQty(productId, qty) {
  fetch('{{ route("store.cart.update") }}', {
    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
    body: JSON.stringify({ product_id: productId, quantity: qty })
  }).then(r=>r.json()).then(data => {
    if (data.success) {
      if (qty <= 0) { document.getElementById('cart-item-'+productId)?.remove(); }
      else {
        document.getElementById('qty-'+productId).textContent = qty;
        document.getElementById('subtotal-'+productId).textContent = sym + numberFormat(data.subtotal);
      }
      document.getElementById('cartTotal').textContent = sym + numberFormat(data.cart_total);
      updateCartBadge(data.count ?? 0);
    } else { showToast(data.message, 'error'); }
  });
}

function removeItem(productId) {
  fetch('{{ route("store.cart.remove") }}', {
    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
    body: JSON.stringify({ product_id: productId })
  }).then(r=>r.json()).then(data => {
    if (data.success) {
      document.getElementById('cart-item-'+productId)?.remove();
      updateCartBadge(data.count);
      showToast('Item removed from cart');
      if (data.count === 0) window.location.reload();
    }
  });
}

function numberFormat(n) {
  return parseFloat(n).toLocaleString('en-PK', {minimumFractionDigits:0, maximumFractionDigits:0});
}
</script>
@endpush
@endsection
