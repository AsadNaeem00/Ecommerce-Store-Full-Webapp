@extends('store.layouts.app')
@section('title', $product->name.' — '.\App\Models\Setting::get('store_name'))

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

{{-- Breadcrumb --}}
<nav class="text-sm text-gray-400 mb-6 flex items-center gap-2 flex-wrap">
  <a href="{{ route('store.home') }}" class="hover:text-primary transition">Home</a> /
  <a href="{{ route('store.products.index', ['category'=>$product->category->slug]) }}" class="hover:text-primary transition">{{ $product->category->name }}</a> /
  <span class="text-gray-700">{{ $product->name }}</span>
</nav>

{{-- Main Product Section --}}
<div class="grid lg:grid-cols-2 gap-10 mb-16">

  {{-- Image Gallery --}}
  <div x-data="{ active: '{{ $product->main_image_url }}' }">
    <div class="rounded-3xl overflow-hidden bg-gray-50 aspect-square border border-gray-100 mb-3">
      <img :src="active" class="w-full h-full object-cover" alt="{{ $product->name }}">
    </div>
    @php $allImages = collect([['url'=>$product->main_image_url,'alt'=>$product->name]])->concat($product->images->map(fn($i)=>['url'=>$i->url,'alt'=>$i->alt_text??$product->name])); @endphp
    @if($allImages->count() > 1)
    <div class="flex gap-2 overflow-x-auto pb-1">
      @foreach($allImages->unique('url') as $img)
        <button @click="active='{{ $img['url'] }}'"
                :class="active==='{{ $img['url'] }}' ? 'ring-2 ring-primary' : 'ring-1 ring-gray-200'"
                class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden ring-offset-1 transition">
          <img src="{{ $img['url'] }}" class="w-full h-full object-cover" alt="{{ $img['alt'] }}">
        </button>
      @endforeach
    </div>
    @endif
  </div>

  {{-- Product Info --}}
  <div x-data="{ qty: 1 }">
    <a href="{{ route('store.products.index', ['category'=>$product->category->slug]) }}" class="inline-block text-xs font-semibold text-primary uppercase tracking-wider bg-primary/10 px-3 py-1 rounded-full mb-3">
      {{ $product->category->name }}
    </a>
    <h1 class="text-3xl font-bold text-gray-900 leading-tight mb-3">{{ $product->name }}</h1>

    {{-- Rating --}}
    @php $avgRating = $product->average_rating; $reviewCount = $product->reviews->count(); @endphp
    @if($reviewCount > 0)
    <div class="flex items-center gap-2 mb-4">
      <div class="flex gap-0.5">
        @for($i=1;$i<=5;$i++)<i class="fas fa-star text-{{ $i<=$avgRating?'amber':'gray' }}-400 text-sm"></i>@endfor
      </div>
      <span class="text-sm text-gray-500">{{ $avgRating }} ({{ $reviewCount }} reviews)</span>
    </div>
    @endif

    {{-- Price --}}
    <div class="flex items-end gap-3 mb-5">
      <span class="text-4xl font-bold text-gray-900">{{ $sym }}{{ number_format($product->current_price) }}</span>
      @if($product->sale_price)
        <span class="text-xl text-gray-400 line-through">{{ $sym }}{{ number_format($product->price) }}</span>
        <span class="bg-red-100 text-red-600 text-sm font-bold px-2.5 py-1 rounded-full">-{{ $product->discount_percent }}% OFF</span>
      @endif
    </div>

    {{-- Short Description --}}
    @if($product->short_description)
      <p class="text-gray-600 text-sm leading-relaxed mb-6">{{ $product->short_description }}</p>
    @endif

    {{-- Stock Status --}}
    <div class="flex items-center gap-2 mb-6">
      @if($product->isInStock())
        <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
        <span class="text-green-600 text-sm font-medium">In Stock
          @if($product->track_quantity && $product->stock_quantity <= 10)
            (Only {{ $product->stock_quantity }} left)
          @endif
        </span>
      @else
        <span class="w-2.5 h-2.5 bg-red-400 rounded-full"></span>
        <span class="text-red-500 text-sm font-medium">Out of Stock</span>
      @endif
    </div>

    @if($product->isInStock())
    {{-- Quantity + Add to Cart --}}
    <div class="space-y-4">
      <div class="flex items-center gap-4">
        <label class="text-sm font-medium text-gray-700">Quantity:</label>
        <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
          <button @click="qty=Math.max(1,qty-1)" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition text-lg font-medium">−</button>
          <span x-text="qty" class="w-12 text-center text-sm font-semibold text-gray-800"></span>
          <button @click="qty=Math.min({{ $product->track_quantity ? $product->stock_quantity : 99 }},qty+1)" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition text-lg font-medium">+</button>
        </div>
      </div>
      <div class="flex gap-3">
        <button @click="addToCart({{ $product->id }}, qty)"
                class="flex-1 btn-primary py-3.5 rounded-2xl font-semibold flex items-center justify-center gap-2 shadow-lg">
          <i class="fas fa-shopping-cart"></i> Add to Cart
        </button>
        <a href="{{ route('store.checkout.index') }}"
           onclick="addToCart({{ $product->id }}, qty)"
           class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-3.5 rounded-2xl font-semibold flex items-center justify-center gap-2 transition">
          <i class="fas fa-bolt"></i> Buy Now
        </a>
      </div>
    </div>
    @endif

    {{-- Meta --}}
    <div class="mt-6 pt-5 border-t border-gray-100 space-y-2 text-sm text-gray-500">
      <p><span class="font-medium text-gray-700">SKU:</span> {{ $product->sku }}</p>
      <p><span class="font-medium text-gray-700">Category:</span> {{ $product->category->name }}</p>
      @if($product->tags)<p><span class="font-medium text-gray-700">Tags:</span> {{ implode(', ', $product->tags) }}</p>@endif
    </div>
  </div>
</div>

{{-- Description Tabs --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm mb-10 overflow-hidden" x-data="{ tab: 'desc' }">
  <div class="flex border-b border-gray-100">
    @foreach(['desc'=>'Description','reviews'=>'Reviews ('.$reviewCount.')'] as $t=>$l)
      <button @click="tab='{{ $t }}'"
              :class="tab==='{{ $t }}' ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700'"
              class="px-6 py-4 text-sm font-semibold transition">{{ $l }}</button>
    @endforeach
  </div>

  <div class="p-8">
    {{-- Description Tab --}}
    <div x-show="tab==='desc'">
      @if($product->description)
        <div class="prose max-w-none text-gray-600 leading-relaxed">{!! nl2br(e($product->description)) !!}</div>
      @else
        <p class="text-gray-400">No detailed description available.</p>
      @endif
    </div>

    {{-- Reviews Tab --}}
    <div x-show="tab==='reviews'" x-cloak class="space-y-8">
      {{-- Summary --}}
      @if($reviewCount > 0)
      <div class="flex items-center gap-6 p-5 bg-gray-50 rounded-2xl">
        <div class="text-center">
          <p class="text-5xl font-bold text-gray-900">{{ $avgRating }}</p>
          <div class="flex gap-0.5 justify-center my-1">
            @for($i=1;$i<=5;$i++)<i class="fas fa-star text-{{ $i<=$avgRating?'amber':'gray' }}-400 text-sm"></i>@endfor
          </div>
          <p class="text-xs text-gray-400">{{ $reviewCount }} reviews</p>
        </div>
      </div>
      @endif

      {{-- Reviews List --}}
      @forelse($product->reviews as $review)
      <div class="border-b border-gray-50 pb-6">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold flex-shrink-0">
            {{ strtoupper(substr($review->reviewer_name,0,1)) }}
          </div>
          <div class="flex-1">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-semibold text-gray-800">{{ $review->reviewer_name }}</p>
                <div class="flex gap-0.5 mt-0.5">
                  @for($i=1;$i<=5;$i++)<i class="fas fa-star text-{{ $i<=$review->rating?'amber':'gray' }}-400 text-xs"></i>@endfor
                </div>
              </div>
              <span class="text-xs text-gray-400">{{ $review->created_at->format('M d, Y') }}</span>
            </div>
            @if($review->title)<p class="font-medium text-gray-700 mt-2">{{ $review->title }}</p>@endif
            <p class="text-gray-600 text-sm mt-1 leading-relaxed">{{ $review->body }}</p>
          </div>
        </div>
      </div>
      @empty
        <p class="text-gray-400 text-center py-4">No reviews yet. Be the first!</p>
      @endforelse

      {{-- Write Review Form --}}
      <div class="bg-gray-50 rounded-2xl p-6 mt-6">
        <h4 class="font-semibold text-gray-800 mb-5">Write a Review</h4>
        <form method="POST" action="{{ route('store.products.reviews', $product) }}" class="space-y-4">
          @csrf
          {{-- Star Rating --}}
          <div x-data="{ rating: 0, hover: 0 }">
            <label class="block text-sm font-medium text-gray-700 mb-2">Your Rating <span class="text-red-500">*</span></label>
            <div class="flex gap-1">
              @for($i=1;$i<=5;$i++)
                <button type="button" @click="rating={{ $i }}" @mouseenter="hover={{ $i }}" @mouseleave="hover=0"
                        :class="(hover||rating) >= {{ $i }} ? 'text-amber-400' : 'text-gray-200'"
                        class="text-2xl transition-colors"><i class="fas fa-star"></i></button>
              @endfor
            </div>
            <input type="hidden" name="rating" :value="rating" required>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Your Name <span class="text-red-500">*</span></label>
              <input type="text" name="reviewer_name" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Your Email <span class="text-red-500">*</span></label>
              <input type="email" name="reviewer_email" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Review Title</label>
            <input type="text" name="title" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary" placeholder="Summarize your experience">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Review <span class="text-red-500">*</span></label>
            <textarea name="body" rows="4" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 ring-primary" placeholder="Share your honest experience..."></textarea>
          </div>
          <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-semibold">
            <i class="fas fa-paper-plane mr-2"></i> Submit Review
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Related Products --}}
@if($related->count())
<section>
  <h3 class="text-2xl font-bold text-gray-900 mb-6">You May Also Like</h3>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
    @foreach($related as $product)
      @include('store.products._card', compact('product','sym'))
    @endforeach
  </div>
</section>
@endif

</div>
@endsection
