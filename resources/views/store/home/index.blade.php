@extends('store.layouts.app')
@section('title', \App\Models\Setting::get('store_name','MyStore').' - '.\App\Models\Setting::get('store_tagline','Online Store'))

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp

{{-- ===== HERO SLIDER ===== --}}
@if(isset($sections['hero']) && $sections['hero']->is_enabled && $sliders->count())
<section class="relative overflow-hidden" x-data="slider({{ $sliders->count() }})" x-init="startAuto()">
  <div class="overflow-hidden">
    <div class="slider-track" :style="`transform: translateX(-${current * 100}%)`">
      @foreach($sliders as $slide)
      <div class="min-w-full relative h-[480px] sm:h-[560px] lg:h-[640px]">
        <img src="{{ asset('storage/'.$slide->image_path) }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $slide->title }}">
        <div class="absolute inset-0 bg-gradient-to-r from-black/65 via-black/30 to-transparent"></div>
        <div class="absolute inset-0 flex items-center">
          <div class="max-w-7xl mx-auto px-6 sm:px-10 w-full">
            <div class="max-w-xl" x-show="current === {{ $loop->index }}" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
              @if($slide->title)<h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-4">{{ $slide->title }}</h1>@endif
              @if($slide->subtitle)<p class="text-lg sm:text-xl text-white/90 mb-8 leading-relaxed">{{ $slide->subtitle }}</p>@endif
              @if($slide->cta_text)
                <a href="{{ $slide->cta_url }}" class="btn-primary inline-flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-semibold shadow-xl hover:shadow-2xl">
                  {{ $slide->cta_text }} <i class="fas fa-arrow-right"></i>
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Controls --}}
  @if($sliders->count() > 1)
  <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/20 backdrop-blur-sm hover:bg-white/40 text-white rounded-full flex items-center justify-center transition">
    <i class="fas fa-chevron-left"></i>
  </button>
  <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/20 backdrop-blur-sm hover:bg-white/40 text-white rounded-full flex items-center justify-center transition">
    <i class="fas fa-chevron-right"></i>
  </button>
  <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2">
    @foreach($sliders as $i => $s)
      <button @click="current={{ $i }}" :class="current==={{ $i }} ? 'w-8 bg-white' : 'w-2.5 bg-white/40'" class="h-2.5 rounded-full transition-all duration-300"></button>
    @endforeach
  </div>
  @endif
</section>
@else
{{-- Fallback hero if no slides --}}
<section class="bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-800 py-24 text-center text-white">
  <div class="max-w-2xl mx-auto px-6">
    <h1 class="text-5xl font-bold mb-4">{{ \App\Models\Setting::get('store_name','Welcome') }}</h1>
    <p class="text-xl text-white/80 mb-8">{{ \App\Models\Setting::get('store_tagline','Quality you can trust') }}</p>
    <a href="{{ route('store.products.index') }}" class="btn-secondary inline-flex items-center gap-2 px-8 py-4 rounded-2xl font-semibold">Shop Now <i class="fas fa-arrow-right"></i></a>
  </div>
</section>
@endif

{{-- ===== TRUST BADGES ===== --}}
@if(isset($sections['trust_badges']) && $sections['trust_badges']->is_enabled)
<section class="border-y border-gray-100 bg-white py-5">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      @foreach([['fa-shipping-fast','Free Delivery','On orders over '.$sym.'2,000'],['fa-shield-alt','Secure Payment','100% protected'],['fa-undo','Easy Returns','7-day return policy'],['fa-headset','24/7 Support','Always here to help']] as $b)
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
          <i class="fas {{ $b[0] }} text-primary text-sm"></i>
        </div>
        <div>
          <p class="font-semibold text-gray-800 text-sm">{{ $b[1] }}</p>
          <p class="text-gray-400 text-xs">{{ $b[2] }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ===== CATEGORIES ===== --}}
@if(isset($sections['categories']) && $sections['categories']->is_enabled && $categories->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
  <div class="text-center mb-10">
    <h2 class="text-3xl font-bold text-gray-900">Shop by Category</h2>
    <p class="text-gray-500 mt-2">Find exactly what you're looking for</p>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @foreach($categories as $cat)
    <a href="{{ route('store.products.index', ['category'=>$cat->slug]) }}"
       class="group relative overflow-hidden rounded-2xl bg-gray-50 border border-gray-100 hover:border-primary/30 transition-all duration-300 hover:shadow-lg">
      <div class="aspect-square overflow-hidden">
        <img src="{{ $cat->image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $cat->name }}">
      </div>
      <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent"></div>
      <div class="absolute bottom-0 left-0 right-0 p-4">
        <p class="text-white font-semibold">{{ $cat->name }}</p>
        <p class="text-white/70 text-xs">{{ $cat->products_count }} products</p>
      </div>
    </a>
    @endforeach
  </div>
</section>
@endif

{{-- ===== FEATURED PRODUCTS ===== --}}
@if(isset($sections['featured']) && $sections['featured']->is_enabled && $featuredProducts->count())
<section class="bg-gray-50/60 py-14">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-end mb-10">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Featured Products</h2>
        <p class="text-gray-500 mt-1">Handpicked for you</p>
      </div>
      <a href="{{ route('store.products.index', ['sort'=>'featured']) }}" class="text-primary font-medium text-sm hover:underline hidden sm:block">View All →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
      @foreach($featuredProducts as $product)
        @include('store.products._card', compact('product','sym'))
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ===== NEW ARRIVALS ===== --}}
@if(isset($sections['new_arrivals']) && $sections['new_arrivals']->is_enabled && $newArrivals->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
  <div class="flex justify-between items-end mb-10">
    <div>
      <h2 class="text-3xl font-bold text-gray-900">New Arrivals</h2>
      <p class="text-gray-500 mt-1">Fresh stock just added</p>
    </div>
    <a href="{{ route('store.products.index', ['sort'=>'newest']) }}" class="text-primary font-medium text-sm hover:underline hidden sm:block">View All →</a>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
    @foreach($newArrivals as $product)
      @include('store.products._card', compact('product','sym'))
    @endforeach
  </div>
</section>
@endif

@push('scripts')
<script>
function slider(count) {
  return {
    current: 0, total: count, timer: null,
    next() { this.current = (this.current + 1) % this.total; this.resetAuto(); },
    prev() { this.current = (this.current - 1 + this.total) % this.total; this.resetAuto(); },
    startAuto() { this.timer = setInterval(() => this.next(), 5000); },
    resetAuto() { clearInterval(this.timer); this.startAuto(); }
  };
}
</script>
@endpush
@endsection
