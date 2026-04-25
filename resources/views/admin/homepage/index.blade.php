{{-- HOMEPAGE BUILDER --}}
@extends('admin.layouts.app')
@section('title','Homepage Builder')
@section('page-title','Homepage Builder')
@section('content')
<div class="py-4 space-y-6">

{{-- Sections Manager --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
  <div class="px-6 py-4 border-b border-gray-100">
    <h3 class="font-semibold text-gray-800">Page Sections</h3>
    <p class="text-sm text-gray-500 mt-0.5">Enable or disable sections and set their order</p>
  </div>
  <form method="POST" action="{{ route('admin.homepage.sections') }}" class="p-6">
    @csrf @method('PUT')
    <div class="space-y-3">
      @foreach($sections as $section)
      <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
        <i class="fas fa-grip-vertical text-gray-300 cursor-move"></i>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="sections[{{ $section->section_key }}][is_enabled]" value="1"
                 {{ $section->is_enabled ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600">
          <span class="text-sm font-medium text-gray-700">{{ $section->title }}</span>
        </label>
        <input type="hidden" name="sections[{{ $section->section_key }}][sort_order]" value="{{ $section->sort_order }}">
        <input type="text" name="sections[{{ $section->section_key }}][title]" value="{{ $section->title }}"
               class="ml-auto border border-gray-200 rounded-lg px-3 py-1.5 text-sm w-48 focus:outline-none focus:ring-2 focus:ring-indigo-400">
        <span class="text-xs text-gray-400 bg-gray-200 px-2 py-0.5 rounded-full font-mono">{{ $section->section_key }}</span>
      </div>
      @endforeach
    </div>
    <div class="mt-4">
      <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">
        <i class="fas fa-save mr-1"></i> Save Section Settings
      </button>
    </div>
  </form>
</div>

{{-- Hero Slider --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
  <div class="px-6 py-4 border-b border-gray-100">
    <h3 class="font-semibold text-gray-800">Hero Slider Images</h3>
    <p class="text-sm text-gray-500 mt-0.5">Add, remove and reorder hero banner slides</p>
  </div>
  <div class="p-6 grid gap-6">
    {{-- Existing Slides --}}
    @if($sliders->count())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($sliders as $slide)
      <div class="relative group rounded-xl overflow-hidden border border-gray-100 shadow-sm">
        <img src="{{ asset('storage/'.$slide->image_path) }}" class="w-full h-36 object-cover">
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
          <form method="POST" action="{{ route('admin.homepage.slider.delete',$slide) }}">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('Remove this slide?')" class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
              <i class="fas fa-trash mr-1"></i> Remove
            </button>
          </form>
        </div>
        <div class="p-3 bg-white">
          <p class="text-sm font-medium text-gray-800 truncate">{{ $slide->title ?: 'Untitled slide' }}</p>
          <p class="text-xs text-gray-400 truncate">CTA: {{ $slide->cta_text }} → {{ $slide->cta_url }}</p>
          <span class="inline-block mt-1 text-xs {{ $slide->is_active ? 'text-green-600' : 'text-gray-400' }}">
            {{ $slide->is_active ? '● Active' : '○ Inactive' }}
          </span>
        </div>
      </div>
      @endforeach
    </div>
    @else
      <p class="text-sm text-gray-400 text-center py-4">No slides yet. Add your first slide below.</p>
    @endif

    {{-- Add New Slide --}}
    <div class="border-t border-gray-100 pt-5">
      <h4 class="font-medium text-gray-700 mb-4">Add New Slide</h4>
      <form method="POST" action="{{ route('admin.homepage.slider.add') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Slide Image <span class="text-red-500">*</span></label>
          <div x-data="{ preview: '' }" class="border-2 border-dashed border-gray-200 rounded-xl hover:border-indigo-300 transition cursor-pointer text-center"
               @click="$refs.slideImg.click()">
            <template x-if="preview">
              <img :src="preview" class="max-h-40 mx-auto rounded-xl my-2 object-cover">
            </template>
            <template x-if="!preview">
              <div class="py-8">
                <i class="fas fa-image text-3xl text-gray-300 mb-2"></i>
                <p class="text-sm text-gray-400">Click to upload slide image</p>
                <p class="text-xs text-gray-300">Recommended: 1920×600px, JPG/PNG/WebP</p>
              </div>
            </template>
            <input type="file" name="image" accept="image/*" required x-ref="slideImg" class="hidden"
                   @change="preview = URL.createObjectURL($event.target.files[0])">
          </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Slide Title</label>
            <input type="text" name="title" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. Summer Collection">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Subtitle</label>
            <input type="text" name="subtitle" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. Discover our latest arrivals">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">CTA Button Text</label>
            <input type="text" name="cta_text" value="Shop Now" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">CTA Button URL</label>
            <input type="text" name="cta_url" value="/products" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
          </div>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">
          <i class="fas fa-plus mr-1"></i> Add Slide
        </button>
      </form>
    </div>
  </div>
</div>
</div>
@endsection
