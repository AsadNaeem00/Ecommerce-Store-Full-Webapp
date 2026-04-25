{{-- ===== SETTINGS VIEW ===== --}}
{{-- resources/views/admin/settings/index.blade.php --}}
@extends('admin.layouts.app')
@section('title','Store Settings')
@section('page-title','Store Settings')

@section('content')
<div class="py-4">
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
  @csrf @method('PUT')

  <div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

      {{-- General --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        <h3 class="font-semibold text-gray-800 border-b border-gray-100 pb-3">General Information</h3>
        <div class="grid md:grid-cols-2 gap-4">
          @foreach(['store_name'=>'Store Name','store_tagline'=>'Tagline','store_email'=>'Store Email','store_phone'=>'Phone','store_address'=>'Address','currency'=>'Currency Code','currency_symbol'=>'Currency Symbol'] as $key => $label)
          <div {{ in_array($key,['store_address','store_tagline']) ? 'class=md:col-span-2' : '' }}>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
            <input type="{{ $key==='store_email'?'email':'text' }}" name="{{ $key }}" value="{{ $settings[$key]->value ?? '' }}"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
          </div>
          @endforeach
        </div>
      </div>

      {{-- Branding & Colors --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        <h3 class="font-semibold text-gray-800 border-b border-gray-100 pb-3">Branding & Colors</h3>
        <div class="grid md:grid-cols-3 gap-4">
          @foreach(['color_primary'=>'Primary Color','color_secondary'=>'Secondary Color','color_accent'=>'Accent Color'] as $key => $label)
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
            <div class="flex gap-2">
              <input type="color" name="{{ $key }}" value="{{ $settings[$key]->value ?? '#6366f1' }}" class="h-10 w-12 rounded-lg border border-gray-200 cursor-pointer">
              <input type="text" value="{{ $settings[$key]->value ?? '#6366f1' }}" readonly class="flex-1 border border-gray-200 rounded-xl px-3 text-sm bg-gray-50 font-mono">
            </div>
          </div>
          @endforeach
        </div>
        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Logo</label>
            @if($settings['logo']->value ?? null)
              <img src="{{ asset('storage/'.($settings['logo']->value)) }}" class="h-12 mb-2 rounded">
            @endif
            <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Favicon</label>
            @if($settings['favicon']->value ?? null)
              <img src="{{ asset('storage/'.($settings['favicon']->value)) }}" class="h-8 mb-2">
            @endif
            <input type="file" name="favicon" accept="image/x-icon,image/png" class="w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Background Image</label>
            <input type="file" name="background_image" accept="image/*,image/gif" class="w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs">
            <label class="flex items-center gap-2 mt-2 text-sm text-gray-600">
              <input type="checkbox" name="background_animated" value="1" {{ ($settings['background_animated']->value ?? '0') === '1' ? 'checked' : '' }} class="rounded text-indigo-600">
              Animated background (GIF)
            </label>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Theme Style</label>
          <select name="theme_style" class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            @foreach(['modern'=>'Modern (Default)','luxury'=>'Luxury / Premium','minimal'=>'Minimal','bold'=>'Bold & Vibrant'] as $v=>$l)
              <option value="{{ $v }}" {{ ($settings['theme_style']->value??'modern')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Social + Shipping --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        <h3 class="font-semibold text-gray-800 border-b border-gray-100 pb-3">Social & Shipping</h3>
        <div class="grid md:grid-cols-2 gap-4">
          @foreach(['facebook_url'=>'Facebook URL','instagram_url'=>'Instagram URL','whatsapp_number'=>'WhatsApp Number','free_shipping_min'=>'Free Shipping Minimum (PKR)','default_shipping_cost'=>'Default Shipping Cost (PKR)'] as $key=>$label)
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
            <input type="text" name="{{ $key }}" value="{{ $settings[$key]->value ?? '' }}"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 border-b border-gray-100 pb-3 mb-4">Controls</h3>
        <label class="flex items-center gap-3 p-3 bg-red-50 border border-red-200 rounded-xl cursor-pointer">
          <input type="checkbox" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode']->value??'0')==='1'?'checked':'' }} class="rounded text-red-500">
          <div>
            <p class="text-sm font-medium text-red-800">Maintenance Mode</p>
            <p class="text-xs text-red-500">Hide store from visitors</p>
          </div>
        </label>
        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">SEO: Meta Title</label>
          <input type="text" name="meta_title" value="{{ $settings['meta_title']->value ?? '' }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div class="mt-3">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">SEO: Meta Description</label>
          <textarea name="meta_description" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ $settings['meta_description']->value ?? '' }}</textarea>
        </div>
        <button type="submit" class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-sm font-medium transition">
          <i class="fas fa-save mr-1"></i> Save All Settings
        </button>
      </div>
    </div>
  </div>
</form>
</div>
@endsection
