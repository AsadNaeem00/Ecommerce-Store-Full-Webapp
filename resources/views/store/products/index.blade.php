@extends('store.layouts.app')
@section('title','Products — '.\App\Models\Setting::get('store_name'))

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

{{-- Header --}}
<div class="flex flex-wrap gap-4 justify-between items-center mb-8">
  <div>
    <h1 class="text-3xl font-bold text-gray-900">All Products</h1>
    <p class="text-gray-500 text-sm mt-1">{{ $products->total() }} products found</p>
  </div>
  <div class="flex gap-2 items-center">
    <select name="sort" onchange="window.location.href=updateParam('sort',this.value)"
            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary">
      <option value="">Sort by</option>
      <option value="featured"   {{ request('sort')==='featured'  ?'selected':'' }}>Featured</option>
      <option value="newest"     {{ request('sort')==='newest'    ?'selected':'' }}>Newest</option>
      <option value="price_asc"  {{ request('sort')==='price_asc' ?'selected':'' }}>Price: Low → High</option>
      <option value="price_desc" {{ request('sort')==='price_desc'?'selected':'' }}>Price: High → Low</option>
    </select>
  </div>
</div>

<div class="flex gap-8">

  {{-- Sidebar Filters --}}
  <aside class="hidden lg:block w-60 flex-shrink-0">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-24 space-y-6">
      <h3 class="font-semibold text-gray-800">Filter</h3>

      {{-- Search --}}
      <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Search</label>
        <form method="GET">
          @foreach(request()->except('q') as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
          @endforeach
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..." class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 ring-primary">
        </form>
      </div>

      {{-- Categories --}}
      <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Categories</label>
        <ul class="space-y-1">
          <li><a href="{{ route('store.products.index', request()->except('category')) }}"
                 class="flex justify-between items-center px-3 py-1.5 rounded-lg text-sm {{ !request('category') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-50' }} transition">
            <span>All</span>
          </a></li>
          @foreach($categories as $cat)
          <li><a href="{{ route('store.products.index', array_merge(request()->except('category'), ['category'=>$cat->slug])) }}"
                 class="flex justify-between items-center px-3 py-1.5 rounded-lg text-sm {{ request('category')===$cat->slug ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-50' }} transition">
            <span>{{ $cat->name }}</span>
            <span class="text-xs opacity-70">{{ $cat->products_count }}</span>
          </a></li>
          @endforeach
        </ul>
      </div>

      {{-- Price Range --}}
      <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Price Range</label>
        <form method="GET" class="space-y-2">
          @foreach(request()->except('min_price','max_price') as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
          @endforeach
          <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min {{ $sym }}" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 ring-primary">
          <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max {{ $sym }}" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 ring-primary">
          <button type="submit" class="w-full btn-primary py-2 rounded-xl text-xs font-medium">Apply</button>
        </form>
      </div>

      @if(request()->hasAny(['q','category','min_price','max_price','sort']))
        <a href="{{ route('store.products.index') }}" class="block text-center text-sm text-red-500 hover:text-red-700">✕ Clear Filters</a>
      @endif
    </div>
  </aside>

  {{-- Product Grid --}}
  <div class="flex-1">
    @if($products->isEmpty())
      <div class="text-center py-20">
        <i class="fas fa-box-open text-6xl text-gray-200 mb-4"></i>
        <p class="text-xl font-medium text-gray-400">No products found</p>
        <p class="text-gray-400 mt-1">Try adjusting your filters</p>
        <a href="{{ route('store.products.index') }}" class="mt-4 inline-block btn-primary px-6 py-2.5 rounded-xl text-sm font-medium">Clear Filters</a>
      </div>
    @else
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @foreach($products as $product)
          @include('store.products._card', compact('product','sym'))
        @endforeach
      </div>
      <div class="mt-10">{{ $products->withQueryString()->links() }}</div>
    @endif
  </div>
</div>
</div>

@push('scripts')
<script>
function updateParam(key, value) {
  const url = new URL(window.location);
  url.searchParams.set(key, value);
  return url.toString();
}
</script>
@endpush
@endsection
