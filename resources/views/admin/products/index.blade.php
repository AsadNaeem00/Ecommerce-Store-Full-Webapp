@extends('admin.layouts.app')
@section('title','Products')
@section('page-title','Products')

@section('content')
<div class="py-4 space-y-4">
{{-- Toolbar --}}
<div class="flex flex-wrap gap-3 justify-between items-center">
  <form method="GET" class="flex gap-2 flex-wrap">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU..."
           class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-60">
    <select name="category" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
      <option value="">All Categories</option>
      @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ request('category')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach
    </select>
    <select name="status" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
      <option value="">All Status</option>
      <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
      <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
    </select>
    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-medium transition">
      <i class="fas fa-search mr-1"></i> Filter
    </button>
    @if(request()->hasAny(['search','category','status']))
      <a href="{{ route('admin.products.index') }}" class="bg-white border border-gray-200 text-gray-500 px-4 py-2.5 rounded-xl text-sm">Clear</a>
    @endif
  </form>
  <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2">
    <i class="fas fa-plus"></i> Add Product
  </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 border-b border-gray-100">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        @php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp
        @forelse($products as $product)
        <tr class="hover:bg-gray-50 transition-colors">
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <img src="{{ $product->main_image_url }}" class="w-10 h-10 rounded-lg object-cover bg-gray-100" alt="{{ $product->name }}">
              <div>
                <p class="font-medium text-gray-800">{{ $product->name }}</p>
                @if($product->is_featured)
                  <span class="inline-flex items-center gap-1 text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full"><i class="fas fa-star text-[10px]"></i> Featured</span>
                @endif
              </div>
            </div>
          </td>
          <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $product->sku }}</td>
          <td class="px-4 py-3 text-gray-600">{{ $product->category->name ?? '—' }}</td>
          <td class="px-4 py-3">
            <p class="font-semibold text-gray-800">{{ $sym }}{{ number_format($product->current_price) }}</p>
            @if($product->sale_price)
              <p class="text-xs text-gray-400 line-through">{{ $sym }}{{ number_format($product->price) }}</p>
            @endif
          </td>
          <td class="px-4 py-3">
            @if(!$product->track_quantity)
              <span class="text-blue-600 text-xs font-medium">∞ Unlimited</span>
            @elseif($product->stock_quantity <= 0)
              <span class="text-red-600 text-xs font-medium">Out of Stock</span>
            @elseif($product->stock_quantity <= $product->low_stock_threshold)
              <span class="text-amber-600 text-xs font-medium">Low: {{ $product->stock_quantity }}</span>
            @else
              <span class="text-gray-700 text-sm">{{ $product->stock_quantity }}</span>
            @endif
          </td>
          <td class="px-4 py-3">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
              {{ $product->is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex justify-end gap-2">
              <a href="{{ route('store.products.show', $product->slug) }}" target="_blank" title="Preview"
                 class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <i class="fas fa-eye text-sm"></i>
              </a>
              <a href="{{ route('admin.products.edit', $product) }}" title="Edit"
                 class="p-1.5 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                <i class="fas fa-edit text-sm"></i>
              </a>
              <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                @csrf @method('DELETE')
                <button type="submit" title="Delete" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                  <i class="fas fa-trash text-sm"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">
            <i class="fas fa-box-open text-4xl mb-3 block text-gray-200"></i>
            No products found. <a href="{{ route('admin.products.create') }}" class="text-indigo-600">Add your first product</a>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($products->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $products->links() }}</div>
  @endif
</div>
</div>
@endsection
