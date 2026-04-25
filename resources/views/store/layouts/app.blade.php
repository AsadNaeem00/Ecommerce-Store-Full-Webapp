<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', \App\Models\Setting::get('meta_title','Online Store'))</title>
<meta name="description" content="@yield('meta_description', \App\Models\Setting::get('meta_description',''))">

@php
  $primaryColor   = \App\Models\Setting::get('color_primary',   '#6366f1');
  $secondaryColor = \App\Models\Setting::get('color_secondary', '#f59e0b');
  $accentColor    = \App\Models\Setting::get('color_accent',    '#10b981');
  $textColor      = \App\Models\Setting::get('color_text',      '#1f2937');
  $storeName      = \App\Models\Setting::get('store_name',      'MyStore');
  $logo           = \App\Models\Setting::get('logo');
  $bgImage        = \App\Models\Setting::get('background_image');
  $bgAnimated     = \App\Models\Setting::get('background_animated','0');
  $cartCount      = app(\App\Services\CartService::class)->count();
@endphp

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
  :root {
    --color-primary:   {{ $primaryColor }};
    --color-secondary: {{ $secondaryColor }};
    --color-accent:    {{ $accentColor }};
    --color-text:      {{ $textColor }};
  }
  [x-cloak]{ display:none!important; }
  .btn-primary { background-color:var(--color-primary); color:#fff; transition:all .2s; }
  .btn-primary:hover { opacity:.88; transform:translateY(-1px); }
  .btn-secondary { background-color:var(--color-secondary); color:#fff; transition:all .2s; }
  .btn-secondary:hover { opacity:.88; }
  .text-primary { color:var(--color-primary) !important; }
  .bg-primary  { background-color:var(--color-primary) !important; }
  .border-primary { border-color:var(--color-primary) !important; }
  .ring-primary { --tw-ring-color:var(--color-primary) !important; }

  /* Smooth page transitions */
  body { opacity:1; transition:opacity .2s ease; }

  /* Product card hover */
  .product-card { transition:transform .25s,box-shadow .25s; }
  .product-card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(0,0,0,.12); }

  /* Slider */
  .slider-track { display:flex; transition:transform .6s cubic-bezier(.4,0,.2,1); }

  @if($bgImage)
    body::before {
      content:'';
      position:fixed;
      inset:0;
      background:url('{{ asset("storage/".$bgImage) }}') center/cover fixed;
      opacity:.04;
      pointer-events:none;
      z-index:-1;
      @if($bgAnimated==='1') background-size:auto; @endif
    }
  @endif
</style>
@stack('styles')
</head>
<body class="font-sans antialiased" style="color:var(--color-text)">

{{-- ===== HEADER ===== --}}
<header class="bg-white/95 backdrop-blur-sm sticky top-0 z-50 border-b border-gray-100 shadow-sm" x-data="{ mobileMenu:false, searchOpen:false }">

  {{-- Top bar --}}
  <div class="bg-gray-900 text-gray-300 text-xs py-1.5 text-center hidden sm:block">
    @php $freeMin = \App\Models\Setting::get('free_shipping_min','2000'); @endphp
    🚚 Free shipping on orders over {{ \App\Models\Setting::get('currency_symbol','₨') }}{{ number_format($freeMin) }}
    &nbsp;|&nbsp; 📞 {{ \App\Models\Setting::get('store_phone','+92 300 0000000') }}
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center h-16 gap-4">

      {{-- Mobile menu button --}}
      <button @click="mobileMenu=!mobileMenu" class="text-gray-500 lg:hidden">
        <i class="fas fa-bars text-xl"></i>
      </button>

      {{-- Logo --}}
      <a href="{{ route('store.home') }}" class="flex items-center gap-2 flex-shrink-0">
        @if($logo)
          <img src="{{ asset('storage/'.$logo) }}" class="h-9 w-auto" alt="{{ $storeName }}">
        @else
          <div class="bg-primary rounded-xl w-9 h-9 flex items-center justify-center">
            <i class="fas fa-store text-white"></i>
          </div>
          <span class="font-bold text-gray-900 text-lg hidden sm:block">{{ $storeName }}</span>
        @endif
      </a>

      {{-- Desktop Nav --}}
      <nav class="hidden lg:flex items-center gap-6 flex-1 justify-center">
        <a href="{{ route('store.home') }}"      class="text-sm font-medium text-gray-600 hover:text-primary transition-colors {{ request()->routeIs('store.home') ? 'text-primary' : '' }}">Home</a>
        <a href="{{ route('store.products.index') }}" class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">Products</a>

        {{-- Categories Mega Dropdown --}}
        <div class="relative" x-data="{ open:false }" @mouseenter="open=true" @mouseleave="open=false">
          <button class="text-sm font-medium text-gray-600 hover:text-primary transition-colors flex items-center gap-1">
            Categories <i class="fas fa-chevron-down text-xs"></i>
          </button>
          <div x-show="open" x-cloak class="absolute top-full left-1/2 -translate-x-1/2 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 p-4 w-64 z-50">
            @foreach(\App\Models\Category::active()->where('show_in_nav',true)->take(10)->get() as $cat)
              <a href="{{ route('store.products.index', ['category'=>$cat->slug]) }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 text-sm text-gray-700 transition-colors">
                @if($cat->image)<img src="{{ $cat->image_url }}" class="w-7 h-7 rounded-lg object-cover">@else<div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400"><i class="fas fa-tag text-xs"></i></div>@endif
                {{ $cat->name }}
              </a>
            @endforeach
            <a href="{{ route('store.products.index') }}" class="block text-center text-xs text-primary font-medium mt-2 pt-2 border-t border-gray-100">View All →</a>
          </div>
        </div>

        <a href="{{ route('store.page','about') }}"   class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">About</a>
        <a href="{{ route('store.contact') }}"        class="text-sm font-medium text-gray-600 hover:text-primary transition-colors">Contact</a>
      </nav>

      {{-- Right Actions --}}
      <div class="flex items-center gap-3 ml-auto">
        {{-- Search --}}
        <div x-data="{ open:false }">
          <button @click="open=!open" class="text-gray-500 hover:text-primary transition-colors p-2">
            <i class="fas fa-search"></i>
          </button>
          <div x-show="open" x-cloak class="absolute top-full left-0 right-0 bg-white border-b border-gray-100 shadow-lg p-4 z-50">
            <form action="{{ route('store.products.index') }}" method="GET">
              <div class="max-w-2xl mx-auto flex gap-2">
                <input type="text" name="q" autofocus placeholder="Search products..."
                       class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary">
                <button type="submit" class="btn-primary px-5 py-2.5 rounded-xl text-sm font-medium">Search</button>
              </div>
            </form>
          </div>
        </div>

        {{-- Cart --}}
        <a href="{{ route('store.cart') }}" class="relative p-2 text-gray-600 hover:text-primary transition-colors">
          <i class="fas fa-shopping-cart text-lg"></i>
          @if($cartCount > 0)
            <span id="cartCount" class="absolute -top-0.5 -right-0.5 bg-primary text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">{{ $cartCount }}</span>
          @endif
        </a>
      </div>
    </div>
  </div>

  {{-- Mobile Menu --}}
  <div x-show="mobileMenu" x-cloak class="lg:hidden bg-white border-t border-gray-100 pb-4">
    <nav class="px-4 pt-2 space-y-1">
      <a href="{{ route('store.home') }}"              class="block px-4 py-2.5 text-sm text-gray-700 rounded-xl hover:bg-gray-50">Home</a>
      <a href="{{ route('store.products.index') }}"    class="block px-4 py-2.5 text-sm text-gray-700 rounded-xl hover:bg-gray-50">Products</a>
      @foreach(\App\Models\Category::active()->where('show_in_nav',true)->take(6)->get() as $cat)
        <a href="{{ route('store.products.index',['category'=>$cat->slug]) }}" class="block px-4 py-2.5 text-sm text-gray-500 rounded-xl hover:bg-gray-50 pl-8">— {{ $cat->name }}</a>
      @endforeach
      <a href="{{ route('store.page','about') }}"      class="block px-4 py-2.5 text-sm text-gray-700 rounded-xl hover:bg-gray-50">About</a>
      <a href="{{ route('store.contact') }}"           class="block px-4 py-2.5 text-sm text-gray-700 rounded-xl hover:bg-gray-50">Contact</a>
    </nav>
  </div>
</header>

{{-- Flash Messages --}}
@if(session('success') || session('error') || session('warning'))
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
  @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2" x-data x-init="setTimeout(()=>$el.remove(),5000)">
      <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
      <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
  @endif
  @if(session('warning'))
    <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
      <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
    </div>
  @endif
</div>
@endif

{{-- Main Content --}}
<main>@yield('content')</main>

{{-- ===== FOOTER ===== --}}
<footer class="bg-gray-900 text-gray-300 mt-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid md:grid-cols-4 gap-8">
      <div>
        <div class="flex items-center gap-2 mb-4">
          @if($logo)<img src="{{ asset('storage/'.$logo) }}" class="h-8 w-auto brightness-0 invert">
          @else<span class="text-white font-bold text-lg">{{ $storeName }}</span>@endif
        </div>
        <p class="text-sm text-gray-400 leading-relaxed">{{ \App\Models\Setting::get('store_tagline','Quality you can trust.') }}</p>
        <div class="flex gap-3 mt-4">
          @if($fb = \App\Models\Setting::get('facebook_url'))
            <a href="{{ $fb }}" target="_blank" class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition"><i class="fab fa-facebook-f text-sm"></i></a>
          @endif
          @if($ig = \App\Models\Setting::get('instagram_url'))
            <a href="{{ $ig }}" target="_blank" class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition"><i class="fab fa-instagram text-sm"></i></a>
          @endif
          @if($wa = \App\Models\Setting::get('whatsapp_number'))
            <a href="https://wa.me/{{ preg_replace('/\D/','', $wa) }}" target="_blank" class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition"><i class="fab fa-whatsapp text-sm"></i></a>
          @endif
        </div>
      </div>
      <div>
        <h4 class="text-white font-semibold mb-4">Quick Links</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="{{ route('store.home') }}" class="hover:text-white transition">Home</a></li>
          <li><a href="{{ route('store.products.index') }}" class="hover:text-white transition">All Products</a></li>
          <li><a href="{{ route('store.page','about') }}" class="hover:text-white transition">About Us</a></li>
          <li><a href="{{ route('store.contact') }}" class="hover:text-white transition">Contact</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-white font-semibold mb-4">Legal</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="{{ route('store.page','privacy') }}" class="hover:text-white transition">Privacy Policy</a></li>
          <li><a href="{{ route('store.page','terms') }}"   class="hover:text-white transition">Terms of Service</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-white font-semibold mb-4">Contact</h4>
        <ul class="space-y-2 text-sm">
          <li class="flex items-center gap-2"><i class="fas fa-phone text-xs w-4"></i> {{ \App\Models\Setting::get('store_phone') }}</li>
          <li class="flex items-center gap-2"><i class="fas fa-envelope text-xs w-4"></i> {{ \App\Models\Setting::get('store_email') }}</li>
          <li class="flex items-center gap-2"><i class="fas fa-map-marker-alt text-xs w-4"></i> {{ \App\Models\Setting::get('store_address') }}</li>
        </ul>
      </div>
    </div>
    <div class="border-t border-white/10 mt-10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-gray-500">
      <p>© {{ date('Y') }} {{ $storeName }}. All rights reserved.</p>
      <div class="flex items-center gap-2">
        <span>We accept:</span>
        <i class="fab fa-cc-visa text-xl text-gray-400"></i>
        <i class="fab fa-cc-mastercard text-xl text-gray-400"></i>
        <span class="text-xs bg-gray-700 text-gray-300 px-2 py-0.5 rounded">EasyPaisa</span>
        <span class="text-xs bg-gray-700 text-gray-300 px-2 py-0.5 rounded">JazzCash</span>
        <span class="text-xs bg-gray-700 text-gray-300 px-2 py-0.5 rounded">COD</span>
      </div>
    </div>
  </div>
</footer>

<script>
// Update cart count dynamically
function updateCartBadge(count) {
  const badge = document.getElementById('cartCount');
  const cartIcon = badge?.parentElement;
  if (count > 0) {
    if (badge) { badge.textContent = count; }
    else if (cartIcon) {
      const span = document.createElement('span');
      span.id = 'cartCount';
      span.className = 'absolute -top-0.5 -right-0.5 bg-primary text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold';
      span.textContent = count;
      cartIcon.appendChild(span);
    }
  } else if (badge) { badge.remove(); }
}

// Add to cart via AJAX
function addToCart(productId, quantity = 1) {
  return fetch('{{ route("store.cart.add") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
    body: JSON.stringify({ product_id: productId, quantity: quantity })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) updateCartBadge(data.count);
    showToast(data.message, data.success ? 'success' : 'error');
    return data;
  });
}

// Toast notification
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `fixed bottom-6 right-6 z-[9999] px-5 py-3 rounded-2xl text-white text-sm font-medium shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
  toast.innerHTML = `<i class="fas ${type==='success'?'fa-check-circle':'fa-exclamation-circle'}"></i> ${message}`;
  document.body.appendChild(toast);
  requestAnimationFrame(() => { toast.classList.remove('translate-y-20','opacity-0'); });
  setTimeout(() => { toast.classList.add('translate-y-20','opacity-0'); setTimeout(() => toast.remove(), 300); }, 3500);
}
</script>
@stack('scripts')
</body>
</html>
