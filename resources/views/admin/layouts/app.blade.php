<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin') — {{ \App\Models\Setting::get('store_name', 'MyStore') }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: {
            50:'#eef2ff', 100:'#e0e7ff', 200:'#c7d2fe',
            500:'#6366f1', 600:'#4f46e5', 700:'#4338ca', 800:'#3730a3'
          }
        }
      }
    }
  }
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
  [x-cloak]{display:none!important}
  .sidebar-link{@apply flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-white/10 hover:text-white transition-all duration-200;}
  .sidebar-link.active{@apply bg-primary-600 text-white shadow-lg;}
  .stat-card{@apply bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow;}
</style>
</head>
<body class="h-full bg-gray-50 font-sans" x-data="{ sidebarOpen: false }">

<!-- Mobile Sidebar Overlay -->
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
     class="fixed inset-0 bg-black/50 z-20 lg:hidden"></div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 flex flex-col transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto">

  <!-- Logo -->
  <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
    @php $logo = \App\Models\Setting::get('logo'); @endphp
    @if($logo)
      <img src="{{ asset('storage/'.$logo) }}" class="h-8 w-auto" alt="Logo">
    @else
      <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
        <i class="fas fa-store text-white text-sm"></i>
      </div>
    @endif
    <div>
      <p class="text-white font-bold text-sm leading-tight">{{ \App\Models\Setting::get('store_name','MyStore') }}</p>
      <p class="text-gray-400 text-xs">Admin Panel</p>
    </div>
    <button @click="sidebarOpen=false" class="ml-auto text-gray-400 lg:hidden"><i class="fas fa-times"></i></button>
  </div>

  <!-- Nav -->
  <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <i class="fas fa-chart-line w-5"></i> Dashboard
    </a>

    <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Catalog</p>
    <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
      <i class="fas fa-box w-5"></i> Products
    </a>
    <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
      <i class="fas fa-tags w-5"></i> Categories
    </a>

    <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sales</p>
    <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
      <i class="fas fa-shopping-bag w-5"></i> Orders
      @php $pending = \App\Models\Order::where('status','pending')->count(); @endphp
      @if($pending > 0)
        <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $pending }}</span>
      @endif
    </a>
    <a href="{{ route('admin.reviews.index') }}" class="sidebar-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
      <i class="fas fa-star w-5"></i> Reviews
      @php $pr = \App\Models\Review::where('status','pending')->count(); @endphp
      @if($pr > 0)
        <span class="ml-auto bg-yellow-500 text-white text-xs rounded-full px-2 py-0.5">{{ $pr }}</span>
      @endif
    </a>

    <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Store</p>
    <a href="{{ route('admin.homepage.index') }}" class="sidebar-link {{ request()->routeIs('admin.homepage.*') ? 'active' : '' }}">
      <i class="fas fa-layout w-5"></i> Homepage Builder
    </a>
    <a href="{{ route('admin.pages.index') }}" class="sidebar-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
      <i class="fas fa-file-alt w-5"></i> Pages
    </a>

    <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Configuration</p>
    <a href="{{ route('admin.payments.index') }}" class="sidebar-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
      <i class="fas fa-credit-card w-5"></i> Payment Setup
    </a>
    <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
      <i class="fas fa-cog w-5"></i> Settings
    </a>
  </nav>

  <!-- User -->
  <div class="border-t border-white/10 p-4" x-data="{ open: false }">
    <button @click="open=!open" class="flex items-center gap-3 w-full text-left">
      <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
        <p class="text-gray-400 text-xs truncate">{{ auth()->user()->role }}</p>
      </div>
      <i class="fas fa-chevron-up text-gray-400 text-xs" :class="open ? '' : 'rotate-180'"></i>
    </button>
    <div x-show="open" x-cloak class="mt-2 space-y-1">
      <a href="{{ route('admin.profile') }}" class="sidebar-link"><i class="fas fa-user w-5"></i> Profile</a>
      <a href="{{ route('store.home') }}" target="_blank" class="sidebar-link"><i class="fas fa-eye w-5"></i> View Store</a>
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="sidebar-link w-full text-red-400 hover:text-red-300"><i class="fas fa-sign-out-alt w-5"></i> Logout</button>
      </form>
    </div>
  </div>
</aside>

<!-- Main content area -->
<div class="lg:pl-64 flex flex-col min-h-screen">
  <!-- Top bar -->
  <header class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-4">
    <button @click="sidebarOpen=true" class="text-gray-500 lg:hidden"><i class="fas fa-bars text-xl"></i></button>
    <div class="flex-1">
      <h1 class="text-gray-800 font-semibold text-lg">@yield('page-title', 'Dashboard')</h1>
      @if(View::hasSection('breadcrumb'))
        <nav class="text-xs text-gray-500 mt-0.5">@yield('breadcrumb')</nav>
      @endif
    </div>
    <div class="flex items-center gap-3">
      <a href="{{ route('store.home') }}" target="_blank" class="text-gray-500 hover:text-primary-600" title="View Store">
        <i class="fas fa-external-link-alt"></i>
      </a>
      <div class="w-px h-5 bg-gray-200"></div>
      <span class="text-sm text-gray-600 hidden sm:block">{{ auth()->user()->name }}</span>
    </div>
  </header>

  <!-- Alerts -->
  <div class="px-6 pt-4">
    @if(session('success'))
      <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3" x-data x-init="setTimeout(()=>$el.remove(),5000)">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button @click="$el.remove()" class="ml-auto"><i class="fas fa-times text-green-500"></i></button>
      </div>
    @endif
    @if(session('error'))
      <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button @click="$el.remove()" class="ml-auto"><i class="fas fa-times text-red-500"></i></button>
      </div>
    @endif
    @if($errors->any())
      <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
        <p class="font-medium mb-1"><i class="fas fa-exclamation-triangle mr-2"></i>Please fix these errors:</p>
        <ul class="list-disc list-inside text-sm space-y-0.5">
          @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
    @endif
  </div>

  <!-- Page Content -->
  <main class="flex-1 px-6 pb-8">
    @yield('content')
  </main>
</div>

@stack('scripts')
</body>
</html>
