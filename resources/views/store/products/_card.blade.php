{{-- Product Card Partial: resources/views/store/products/_card.blade.php --}}
<div class="product-card bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm group">
  <a href="{{ route('store.products.show', $product->slug) }}" class="block relative overflow-hidden aspect-square bg-gray-50">
    <img src="{{ $product->main_image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->name }}">
    @if($product->discount_percent)
      <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">-{{ $product->discount_percent }}%</span>
    @endif
    @if($product->is_featured)
      <span class="absolute top-2 right-2 bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full"><i class="fas fa-star text-[10px]"></i></span>
    @endif
    @if(!$product->isInStock())
      <div class="absolute inset-0 bg-white/60 flex items-center justify-center">
        <span class="bg-gray-800 text-white text-xs font-medium px-3 py-1 rounded-full">Out of Stock</span>
      </div>
    @endif
  </a>
  <div class="p-4">
    <p class="text-xs text-gray-400 mb-1">{{ $product->category->name ?? '' }}</p>
    <a href="{{ route('store.products.show', $product->slug) }}" class="font-semibold text-gray-800 text-sm hover:text-primary transition-colors line-clamp-2 leading-snug">{{ $product->name }}</a>
    <div class="flex items-center justify-between mt-3">
      <div>
        <span class="font-bold text-gray-900">{{ $sym }}{{ number_format($product->current_price) }}</span>
        @if($product->sale_price)<span class="text-xs text-gray-400 line-through ml-1">{{ $sym }}{{ number_format($product->price) }}</span>@endif
      </div>
      @if($product->isInStock())
        <button onclick="addToCart({{ $product->id }})"
                class="w-9 h-9 btn-primary rounded-xl flex items-center justify-center shadow hover:shadow-md transition-all">
          <i class="fas fa-shopping-cart text-xs"></i>
        </button>
      @endif
    </div>
    @if($product->reviews()->where('status','approved')->count() > 0)
      @php $avg = $product->average_rating; @endphp
      <div class="flex items-center gap-1 mt-2">
        @for($i=1;$i<=5;$i++)
          <i class="fas fa-star text-{{ $i<=$avg?'amber':'gray' }}-400 text-xs"></i>
        @endfor
        <span class="text-xs text-gray-400">({{ $product->reviews()->where('status','approved')->count() }})</span>
      </div>
    @endif
  </div>
</div>
