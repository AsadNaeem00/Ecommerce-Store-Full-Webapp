@extends('admin.layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'Add Product')
@section('page-title', isset($product) ? 'Edit: '.$product->name : 'Add New Product')

@section('content')
<div class="py-4">
<form method="POST"
      action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}"
      enctype="multipart/form-data">
  @csrf
  @if(isset($product)) @method('PUT') @endif

  <div class="grid lg:grid-cols-3 gap-6">
    {{-- Main Column --}}
    <div class="lg:col-span-2 space-y-5">

      {{-- Basic Info --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        <h3 class="font-semibold text-gray-800 text-base border-b border-gray-100 pb-3">Product Information</h3>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                 placeholder="e.g. Premium Leather Wallet">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
          <select name="category_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <option value="">Select category...</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Short Description</label>
          <textarea name="short_description" rows="2"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    placeholder="Brief product summary (shown in listings)">{{ old('short_description', $product->short_description ?? '') }}</textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Description</label>
          <textarea name="description" rows="6" id="description"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    placeholder="Detailed product description...">{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Tags (comma-separated)</label>
          <input type="text" name="tags"
                 value="{{ old('tags', isset($product) ? implode(', ', $product->tags ?? []) : '') }}"
                 class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                 placeholder="leather, wallet, premium">
        </div>
      </div>

      {{-- Pricing --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-base border-b border-gray-100 pb-3 mb-4">Pricing</h3>
        @php $sym = \App\Models\Setting::get('currency_symbol','₨'); @endphp
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Price ({{ $sym }}) <span class="text-red-500">*</span></label>
            <input type="number" name="price" value="{{ old('price', $product->price ?? '') }}" min="0" step="0.01" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="0.00">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Sale Price ({{ $sym }})</label>
            <input type="number" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? '') }}" min="0" step="0.01"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Optional">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cost Price ({{ $sym }})</label>
            <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}" min="0" step="0.01"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Internal only">
          </div>
        </div>
      </div>

      {{-- Inventory --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-base border-b border-gray-100 pb-3 mb-4">Inventory</h3>
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">SKU</label>
            <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                   placeholder="Auto-generated">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Stock Quantity <span class="text-red-500">*</span></label>
            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" min="0" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
          </div>
        </div>
        <div class="flex gap-6 flex-wrap">
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="track_quantity" value="1" {{ old('track_quantity', $product->track_quantity ?? true) ? 'checked' : '' }}
                   class="rounded text-indigo-600">
            Track inventory
          </label>
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="allow_backorder" value="1" {{ old('allow_backorder', $product->allow_backorder ?? false) ? 'checked' : '' }}
                   class="rounded text-indigo-600">
            Allow backorders
          </label>
        </div>
      </div>

      {{-- Shipping --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-base border-b border-gray-100 pb-3 mb-4">Shipping</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Weight (kg)</label>
            <input type="number" name="weight" value="{{ old('weight', $product->weight ?? '') }}" min="0" step="0.01"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="0.50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Dimensions (LxWxH)</label>
            <input type="text" name="dimensions" value="{{ old('dimensions', $product->dimensions ?? '') }}"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="10x5x2 cm">
          </div>
        </div>
      </div>
    </div>

    {{-- Sidebar Column --}}
    <div class="space-y-5">

      {{-- Status --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-base border-b border-gray-100 pb-3 mb-4">Status</h3>
        <div class="space-y-3">
          <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-indigo-200 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} class="rounded text-indigo-600">
            <div>
              <p class="text-sm font-medium text-gray-800">Active / Visible</p>
              <p class="text-xs text-gray-400">Show this product in store</p>
            </div>
          </label>
          <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-amber-200 cursor-pointer">
            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }} class="rounded text-amber-500">
            <div>
              <p class="text-sm font-medium text-gray-800">Featured Product</p>
              <p class="text-xs text-gray-400">Show in featured section</p>
            </div>
          </label>
        </div>

        <div class="mt-4 flex gap-2">
          <a href="{{ route('admin.products.index') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-xl text-sm font-medium transition">Cancel</a>
          <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-sm font-medium transition">
            <i class="fas fa-save mr-1"></i> {{ isset($product) ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>

      {{-- Main Image --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-base border-b border-gray-100 pb-3 mb-4">Main Image</h3>
        <div x-data="{ preview: '{{ isset($product) && $product->main_image ? asset('storage/'.$product->main_image) : '' }}' }">
          <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-indigo-300 transition cursor-pointer"
               @click="$refs.mainImg.click()">
            <template x-if="preview">
              <img :src="preview" class="max-h-40 mx-auto rounded-lg object-cover">
            </template>
            <template x-if="!preview">
              <div class="py-4">
                <i class="fas fa-image text-3xl text-gray-300 mb-2"></i>
                <p class="text-sm text-gray-400">Click to upload main image</p>
                <p class="text-xs text-gray-300 mt-1">JPG, PNG, WebP – max 4MB</p>
              </div>
            </template>
          </div>
          <input type="file" name="main_image" accept="image/*" class="hidden" x-ref="mainImg"
                 @change="preview = URL.createObjectURL($event.target.files[0])">
        </div>
      </div>

      {{-- Gallery Images --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-base border-b border-gray-100 pb-3 mb-4">Gallery Images</h3>

        @if(isset($product) && $product->images->count())
          <div class="grid grid-cols-3 gap-2 mb-3">
            @foreach($product->images as $img)
              <div class="relative group" x-data>
                <img src="{{ $img->url }}" class="w-full h-16 object-cover rounded-lg">
                <button type="button"
                        @click="if(confirm('Remove this image?')) fetch('{{ route('admin.products.image.destroy',$img) }}',{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>$el.closest('[x-data]').remove())"
                        class="absolute -top-1.5 -right-1.5 bg-red-500 text-white w-5 h-5 rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition">×</button>
              </div>
            @endforeach
          </div>
        @endif

        <input type="file" name="gallery[]" multiple accept="image/*"
               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-600 file:font-medium hover:file:bg-indigo-100 cursor-pointer">
        <p class="text-xs text-gray-400 mt-1">You can select multiple images</p>
      </div>
    </div>
  </div>
</form>
</div>
@endsection
